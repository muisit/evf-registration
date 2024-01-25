<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationTemplate;
use App\Models\Accreditation;
use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Support\Services\PDFGenerator;

class Example extends Controller
{
    /**
     * Return an example PDF of the indicated template
     *
     * @OA\Get(
     *     path = "/templates/{templateId}/print",
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful print",
     *         @OA\Schema(@OA\Property(type="string", format="binary"))
     *     ),
     *     @OA\Response(
     *         response  = "403",
     *         description = "Access not allowed",
     *     )
     * )
     */
    public function index(Request $request, string $templateId)
    {
        $template = AccreditationTemplate::where('id', $templateId)->first();
        if (empty($template)) {
            $this->authorize('not/ever');
        }
        $this->authorize('view', $template);
        \Log::debug("authorized to view");

        $fencer = new Fencer();
        $fencer->fencer_surname = "FERNANDEZ DEL CASTILLO GARCIA";
        $fencer->fencer_firstname = "Jean Claude Alexandre";
        $fencer->fencer_picture = 'Y';

        $country = Country::find(Country::TST);

        $event = new Event();
        $event->event_name = "Test Event";
        
        $accreditation = new Accreditation();
        $accreditation->fe_id = '286578914'; // event-id, 2 times 3 random digits, control id
        $accreditation->data = json_encode(array(
            "category" => 2,
            "firstname" => $fencer->fencer_firstname,
            "lastname" => $fencer->fencer_surname,
            "organisation" => $country->country_name,
            "country" => $country->country_abbr,
            "roles" => array("Athlete WS4", "Team Armourer", "Head of Delegation", "Referee"),
            "dates" => array("07 WED","21 SUN"),
            "created" => 1000,
            "modified" => 2000
        ));
        $accreditation->fencer = $fencer;
        $accreditation->event = $event;
        $accreditation->template = $template;

        try {
            $generator = app(PDFGenerator::class);
            $generator->generate($accreditation);
            $path = realpath(tempnam(null, "expdf"));
            $generator->save($path);

            if (file_exists($path)) {
                header('Content-Disposition: inline;');
                header('Content-Type: application/pdf');
                header('Expires: ' . (time() + 2 * 24 * 60 * 60));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($path));
                readfile($path);

                @unlink($path);
            }
            else {
                return response('Insufficient storage', 507);
            }
        }
        catch (\Exception $e) {
            \Log::debug("caught exception " . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }
}
