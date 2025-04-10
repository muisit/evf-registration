<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Event;
use App\Models\Registration;
use App\Models\SideEvent;
use App\Models\Weapon;
use App\Http\Controllers\Controller;
use App\Support\Services\PDFGenerator;
use App\Support\Services\ParticipantListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Plovdiv2025 extends Controller
{
    private $eventId = 67;
    private $token;

    public function __construct()
    {
        $this->token = env('PLOVDIV_API');
    }

    public function competition(Request $request, $comp)
    {
        $apitoken = $request->get('token') ?? '';
        if ($apitoken != $this->token) {
            return response()->json(["error" => "Unauthorized"], 403);
        }
        $event = Event::find($this->eventId);
        if (empty($event)) {
            return response()->json(["error" => "no such event"], 404);
        }
        $comps = $this->findCompetitionByName([$comp], $event);
        if (empty($comps)) {
            return response()->json(["error" => "no such competition"], 404);
        }
        $service = new ParticipantListService(SideEvent::find($comps[0]));
        return $service->asXML("plovdiv_2025_" . $comp . ".xml");
    }

    public function index(Request $request)
    {
        $apitoken = $request->get('token') ?? '';
        if ($apitoken != $this->token) {
            return response()->json(["error" => "Unauthorized"], 403);
        }

        $event = Event::find($this->eventId);
        if (empty($event)) {
            return response()->json(["error" => "no such event"], 404);
        }

        $pagesize = intval($request->get('ps') ?? 200);
        $offset = intval($request->get('offset') ?? 0);
        \Log::debug("pagesize $pagesize, offset $offset");
        $countries = $this->findCountryByName(explode(",", $request->get('countries') ?? ''));
        $comps = $this->findCompetitionByName(explode(",", $request->get('competitions') ?? ''), $event);

        $result = $this->selectRegistrations($event, $countries, $comps)->get();
        $fencers = $this->aggregateByFencer($result);
        $slice = $this->slicePart($fencers, $pagesize, $offset);

        $data = $this->convertFencers($slice, $request->get("nopic") ? false : true);
        return response()->json([
            "total" => count($fencers),
            "offset" => $offset,
            "length" => count($slice),
            "data" => $data
        ]);
    }

    private function convertFencers($fencers, $withPicture)
    {
        $retval = [];
        foreach ($fencers as $k => $registrations) {
            $retval[] = $this->convertFencer($registrations, $withPicture);
        }
        return $retval;
    }

    private function convertFencer($registrations, $withPicture)
    {
        $index = 0;
        $firstReg = $registrations[$index];
        // find an athlete registration if it is available
        while ($firstReg->registration_role != 0 && $index < (count($registrations) - 1)) {
            $index += 1;
            $firstReg = $registrations[$index];
        }

        $fencer = $firstReg->fencer;
        $country = $firstReg->country ?? $fencer->country;
        return [
            "first_name" => $fencer->fencer_firstname,
            "last_name" => $fencer->fencer_surname,
            "gender" => $fencer->fencer_gender,
            "efc_id" => $fencer->getKey(),
            "date_of_birth" => $fencer->fencer_dob ? (new Carbon($fencer->fencer_dob))->format('Y-m-d') : '',
            "nation" => $country->country_abbr,
            //"access" => not implemented
            "function" => $this->rolesToFunctions($registrations),
            "photo" => $withPicture ? $this->generateFencerPhoto($fencer) : ''
        ];
    }

    private function generateFencerPhoto($fencer)
    {
        if ($fencer->fencer_picture != 'N') {
            $path = $fencer->image();
        }
        else {
            return null;
        }

        if (file_exists($path)) {
            $img = $this->createWatermark($path);
            if (!empty($img)) {
                ob_start();
                imagejpeg($img);
                $data = ob_get_clean();
                return 'data:image/jpeg;base64,' . base64_encode($data);
            }
        }
    }

    private function createWatermark($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        // we expect a '.dat' extension here
        if ($ext == "dat" || $ext == "jpg" || $ext == "jpeg") {
            $img = imagecreatefromjpeg($path);
        }
        else if ($ext == "png") {
            $img = imagecreatefrompng($path);
        }
        else {
            return null;
        }
        $w = imagesx($img);
        $h = imagesy($img);

        // if the ratio is not okay, we need to crop the image a bit
        // This should not happen (anymore), as we crop and scale the image at upload
        // But there are some older images available...
        // The ideal ratio is 0.7777777777777777... (width is 77% of height)
        $goldenratio = 413.0 / 531.0;
        $ratio = floatval($w) / $h;
        if ($ratio > 0.78) {
            // image is too wide, crop it sideways
            $newwidth = round($h * $goldenratio);
            $offx = round((floatval($w) - $newwidth) / 2);
            $img2 = imagecrop($img, ['x' => $offx, 'y' => 0, 'width' => $newwidth, 'height' => $h]);
            imagedestroy($img);
            $img = $img2;
        }
        else if ($ratio < 0.77) {
            // image is too high, crop in the height
            $newheight = round($w / $goldenratio);
            $offy = round((floatval($h) - $newheight) / 2);
            $img2 = imagecrop($img, ['x' => 0, 'y' => $offy, 'width' => $w, 'height' => $newheight]);
            imagedestroy($img);
            $img = $img2;
        }
        // re-establish proper width and height
        $w = imagesx($img);
        $h = imagesy($img);

        $text_color = imagecolorallocate($img, 196, 196, 196);
        $ffile = $this->getFontFile("arial");
        $fsize = 19; // we start with a font size decrement
        $rotation = 0;
        $wdiff = $w + 1;
        $hdiff = $h + 1;
        while ($wdiff > $w || $hdiff > $h) {
            $fsize -= 1;
            $box = imagettfbbox($fsize, $rotation, $ffile, 'EVF Plovdiv 2025');
            $maxx = max(array($box[0], $box[2], $box[4], $box[6]));
            $minx = min(array($box[0], $box[2], $box[4], $box[6]));
            $maxy = max(array($box[1], $box[3], $box[5], $box[7]));
            $miny = min(array($box[1], $box[3], $box[5], $box[7]));
            $wdiff = abs($maxx - $minx);
            $hdiff = abs($maxy - $miny);
        }
        $x = ($w - abs($maxx - $minx)) / 2.0;
        $y = $h - abs($maxy - $miny) - 2;
        imagettftext($img, $fsize, $rotation, $x, $y, $text_color, $ffile, 'EVF Plovdiv 2025');
        return $img;
    }

    protected function getFontFile($family)
    {
        $ffile = base_path(PDFGenerator::FONTPATH . "/$family.ttf");
        if (!file_exists($ffile)) {
            $ffile = resource_path("fonts/$family.ttf");
        }
        if (!file_exists($ffile)) {
            return $this->getFontFile("arial");
        }
        return $ffile;
    }

    private function aggregateByFencer($results)
    {
        // results is a list of Registration objects
        $fencers = [];
        foreach ($results as $reg) {
            $fid = sprintf("f%08d", $reg->registration_fencer);
            if (!isset($fencers[$fid])) {
                $fencers[$fid] = [];
            }
            $fencers[$fid][] = $reg;
        }
        return $fencers;
    }

    private function slicePart($fencers, $p, $o)
    {
        $keys = array_keys($fencers);
        ksort($keys);

        if ($p < 1) {
            $p = 1;
        }
        else if ($p > count($keys)) {
            $p = count($keys);
            $o = 0;
        }
        if ($o < 0) {
            $o = 0;
        }
        else if ($o > count($keys)) {
            $o = count($keys) - $p;
        }
        $keys = array_slice($keys, $o, $p);
        $retval = [];
        foreach ($keys as $k) {
            $retval[$k] = $fencers[$k];
        }

        return $retval;
    }

    private function findCompetitionByName($comps, $event)
    {
        $cats = Category::where('category_type', 'I')->get()->mapWithKeys(function ($i, $k) {
            return ['c' . $i->getKey() => $i];
        });
        $weapons = Weapon::get()->mapWithKeys(function ($i, $k) {
            return ['w' . $i->getKey() => $i];
        });
        $retval = [];
        foreach ($event->competitions as $competition) {
            $w = $weapons['w' . $competition->competition_weapon];
            $c = $cats['c' . $competition->competition_category];
            $st = $w->weapon_abbr . $c->category_abbr;
            if (in_array($st, $comps)) {
                $retval[] = $competition->sideEvent->getKey();
            }
        }
        return $retval;
    }

    private function findCountryByName($countryabbrs)
    {
        $retval = [];
        if (!empty($countryabbrs)) {
            $countries = Country::whereIn('country_abbr', $countryabbrs)->get();
            if (!empty($countries)) {
                $retval = $countries->map(fn ($c) => $c->getKey())->toArray();
            }
        }
        return $retval;
    }

    private function selectRegistrations($event, $countries, $comps)
    {
        $registrations = Registration::where('registration_mainevent', $event->getKey());
        if (!empty($countries)) {
            $registrations = $registrations->whereIn('registration_country', $countries);
        }

        if (empty($comps)) {
            // for no country or competition, so org roles
            $registrations = $registrations->where('registration_role', '<>', 0);
        }
        else {
            // must be country specific
            $registrations = $registrations->where('registration_country', '<>', null);
            // and for a specific competition/side-event
            $registrations = $registrations->whereIn('registration_event', $comps);
        }
        $registrations = $registrations->with(['fencer', 'fencer.country', 'role']);
        return $registrations;
    }

    private function rolesToFunctions($regs)
    {
        $roles = [];
        foreach ($regs as $r) {
            $roles[] = $this->roleToCategory($r->role);
        }
        $roles = array_values(array_unique($roles));
        sort($roles);
        return $roles;
    }

    private function roleToCategory($role)
    {
        if (!$role) {
            return 'ATH';
        }

        switch ($role->role_name) {
            case 'Head of Delegation': return 'CHD';
            case 'Coach': return 'ENT';
            case 'Physio': return 'MEDFN';
            case 'Team Support': return 'INVFN';
            case 'Team Armourer': return 'TCHFN';
            case 'Referee': return 'ARB';
            case 'Weapon Control': return 'TCH';
            case 'Medical': return 'MED';
            case 'Official': return 'OF';
            case 'Volunteer': return 'BEN';
            case 'VIP': return 'VIPCOL';
            case 'Media': return 'PR';
            case 'Event Manager': return 'ORG';
            case 'Referee Co-ordinator': return 'OF';
            case 'DT': return 'OF';
            case 'Cashier': return 'BEN';
            case 'Logistics': return 'BEN';
            case 'Tech Support': return 'TCH';
            case 'EVFC Director': return 'PFN';
            case 'EVF Member of Honour': return 'MH';
            case 'EVF Support': return 'OF';
        }

        return 'BEN';
    }
}
