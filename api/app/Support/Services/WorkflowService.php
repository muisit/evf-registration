<?php

namespace App\Support\Services;

use App\Models\Competition;
use App\Models\Event;
use App\Models\Workflow;
use Carbon\Carbon;

class WorkflowService
{
    private Workflow $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function handle($request): ?Workflow
    {
        $type = $this->workflow->sandbox['name'] ?? 'generic';
        if (($request->step ?? '') == 'done') {
            $this->workflow->delete();
            return null;
        }

        if ($type == 'uploadXML') {
            $this->handleUploadXML($request);
        }
        else {
            throw new \Exception("Unsupported flow");
        }
        $this->workflow->save();
        return $this->workflow;
    }

    private function handleUploadXML($request)
    {
        \Log::debug("handling UploadXML " . json_encode($request));
        switch ($request->step ?? 'init') {
            case 'initialise':
                // this is the request for GUI initialisation. Create the workflow
                // object and return it as a start
                $sb = $this->workflow->sandbox;
                $sb['step'] = 'Upload File';
                $this->workflow->sandbox = $sb;
                break;
            case 'uploaded':
                // this is the return of the uploaded file. We need to unpack it
                // and return the list of XML files
                $this->handleUnpack($request->file_id);
                break;
            case 'select_event':
                // we displayed the unpacked, available files.
                $this->findAvailableEvents();
                break;
            case 'save_event':
                // we created or selected/updated an event. Save it and prepare to start importing
                $this->saveEvent($request);
                break;
            case 'import_file':
                // the event was created and we start importing the various competitions
                $this->importCompetition($request);
                break;
            case 'save_competition':
                $this->saveCompetition((object)$request);
                break;
            case 'imported_competition':
                $this->importedCompetition();
                break;
        }
    }

    private function importedCompetition()
    {
        \Log::debug("handling end of import of a competition");
        $sb = $this->workflow->sandbox;
        $sb['step'] = 'Prepare Import';
        $sb['fencers'] = null; // clear the imported fencers
        $sb['selectedCompetition'] = null; // clear the competition
        $sb['gender'] = null;
        $sb['weapon'] = null;
        $sb['category'] = null;
        $sb['date'] = null;
        $this->workflow->sandbox = $sb;

        // the front end will try to find the next file after this one
        // we set the 'processed' attribute on the file to indicate it was in fact processed
        \Log::debug('selectinf file ' . $sb['file_id']);
        $file = $this->selectFile($sb['file_id'] ?? -1);
        if (!empty($file)) {
            $this->workflow->addFile($file['path'], ['processed' => true]);
        }
        else {
            \Log::debug('file is not available in file list');
        }
    }

    private function importCompetition($request)
    {
        $sb = $this->workflow->sandbox;

        $file = $this->selectFile($request->file_id ?? -1);
        if (empty($file)) {
            $sb['file_id'] = -1;
            $sb['step'] = 'Prepare Import';
            $this->workflow->sandbox = $sb;
            return;
        }
        $sb['file_id'] = $request->file_id ?? -1;

        \Log::debug("importing XML file to determine competition values");
        $service = new FIEXMLService($file['path']);
        $service->handle();

        $sb['step'] = 'Select Competition';
        $sb['selectedCompetition'] = $this->selectCompetition($service, $sb['competitions']);
        $sb['gender'] = $service->competition['gender'];
        $sb['weapon'] = $service->competition['weapon']?->getKey() ?? -1;
        $sb['category'] = $service->competition['category']?->getKey() ?? -1;
        $sb['date'] = $service->competition['date']?->format('Y-m-d') ?? '';

        $sb['fencers'] = collect($service->fencers)->map(function ($item, $idx) {
            return [
                "index" => $idx,
                "pos" => $item['result'] ?? 'dnf',
                "id" => $item['id'],
                "name" => strtoupper($item["lastname"] ?? ''),
                "lastname_check" => "und",
                "lastname_text" => '',
                "firstname" => $item["firstname"] ?? '',
                "firstname_check" => "und",
                "firstname_text" => '',
                "gender" => $item['gender'] ?? 'M',
                'birthday' => isset($item['dob']) ? $item['dob']->format('Y-m-d') : null,
                "dob_check" => "und",
                "dob_text" => '',
                'country' => isset($item['country']) ? $item['country']->country_abbr : null,
                "country_check" => "und",
                "country_text" => '',
                'result' => $item['result'] ?? 'dnf',
                "all_check" => 'und',
                "all" => '',
                "all_text" => '',
                "fencer_id" => -1,
                "country_id" => isset($item['country']) ? $item['country']->country_id : -1,
                "suggestions" => [],
                "status" => $item['status'] ?? 'normal'
            ];
        })->sortBy('result')->values()->all();

        $this->workflow->sandbox = $sb;
    }

    private function selectCompetition($service, $competitions)
    {
        foreach ($competitions as $c) {
            \Log::debug("testing " . json_encode($c) . ' against ' . $service->competition['category']?->getKey() . '/' . $service->competition['weapon']?->getKey());
            if (
                  intval($c['category']) == intval($service->competition['category']?->getKey())
                && intval($c['weapon']) == intval($service->competition['weapon']?->getKey())
            ) {
                return $c['id'];
            }
        }
        return null;
    }

    private function selectFile($fid)
    {
        return collect($this->workflow->sandbox['files'])->filter(fn ($item) => ($item['id'] ?? '') == $fid)->first();
    }

    private function saveCompetition($data)
    {
        $event = Event::find($this->workflow->sandbox['selectedEvent']);
        if (empty($event)) {
            throw new \Exception("missing event while saving competitions");
        }
        $competition = $event->competitions()->where('competition_id', $data->competition['id'] ?? -1)->first();
        if (empty($competition)) {
            $competition = new Competition();
            $competition->competition_event = $event->getKey();
        }
        $competition->competition_weapon = $data->competition['weapon'];
        $competition->competition_category = $data->competition['category'];
        $dt = new Carbon($data->competition['date']);
        $competition->competition_opens = $dt->format('Y-m-d');
        if (!$competition->exists) {
            $competition->competition_weapon_check = $competition->competition_opens;
        }
        $competition->save();

        $sb = $this->workflow->sandbox;
        $sb['competitions'] = $this->setCompetitionsInSandbox($event);
        $sb['selectedCompetition'] = $competition->getKey();
        $sb['step'] = 'Import Fencers';

        $this->workflow->sandbox = $sb;
    }

    private function saveEvent($data)
    {
        $evdata = (object)$data->event;
        $event = Event::find($evdata->id ?? -1);
        if (empty($event)) {
            $event = new Event();
        }
        \Log::debug("event data " . json_encode($data));
        $event->event_name = trim($evdata->name ?? '');
        $event->event_location = trim($evdata->location ?? '');
        $event->event_country = intval($evdata->country ?? -1);
        $event->event_type = intval($evdata->type ?? -1);
        $dt = new Carbon($evdata->date ?? '');
        $event->event_open = $dt->format('Y-m-d');
        $event->event_year = $dt->year;
        $event->event_duration = 2;
        $event->event_currency_symbol = '€';
        $event->event_currency_name = 'EUR';
        $event->event_bank = '';
        $event->event_account_name = '';
        $event->event_organisers_address = '';
        $event->event_iban = '';
        $event->event_swift = '';
        $event->event_in_ranking = 'Y';
        $event->event_factor = 1.0;
        $event->save();

        $sb = $this->workflow->sandbox;
        $sb['events'] = $this->setEventsInSandbox(Event::where('event_id', '>', 0)->with('type')->with('country')->get());
        $sb['competitions'] = $this->setCompetitionsInSandbox($event);
        $sb['selectedEvent'] = $event->getKey();
        $sb['step'] = 'Prepare Import';
        $this->workflow->sandbox = $sb;
    }

    private function setCompetitionsInSandbox($event)
    {
        $competitions = $event->competitions;
        $retval = $competitions->map(function ($item) {
            return [
                "id" => $item->getKey(),
                "date" => $item->competition_opens,
                "weapon" => $item->competition_weapon,
                "category" => $item->competition_category
            ];
        });
        return $retval;
    }

    private function setEventsInSandbox($events)
    {
        return $events->map(function ($item) {
            return [
                'id' => $item->getKey(),
                'name' => $item->event_name,
                'location' => $item->event_location,
                'country' => $item->country?->country_abbr,
                'country_id' => $item->event_country,
                'date' => $item->event_open,
                'type' => $item->type?->event_type_name,
                'type_id' => $item->event_type,
            ];
        });
    }

    private function findAvailableEvents()
    {
        // Do a preselection of events based on data in the available XML files. Then return a list
        // of events and our preselected event
        $events = Event::where('event_id', '>', 0)->with('type')->with('country')->get();
        $selectedEvent = null;
        $eventData = [];
        foreach ($this->workflow->sandbox['files'] as $file) {
            if (file_exists($file['path']) && ($file['unpacked'] ?? false) == true) {
                $service = new FIEXMLService($file['path']);
                $service->handle();
                $eventData = $service->competition;
                $eventData['country'] = $eventData['country']?->getKey() ?? -1;
                $eventData['weapon'] = $eventData['weapon']?->getKey() ?? -1;
                $eventData['category'] = $eventData['category']?->getKey() ?? -1;
                $eventData['date'] = $eventData['date']?->format('Y-m-d') ?? '';

                if (isset($service->competition['weapon']) && isset($service->competition['category']) && isset($service->competition['date'])) {
                    \Log::debug("looking through events searching for " . $service->competition['date']->format('Y-m-d'));
                    foreach ($events as $event) {
                        $date = new Carbon($event->event_open);
                        $diff = $service->competition['date']->diffInDays($date);
                        \Log::debug("diff in days is $diff");
                        if (abs($diff) < 7) {
                            $selectedEvent = $event;
                            break;
                        }
                    }
                    break;
                }
            }
        }

        $sb = $this->workflow->sandbox;
        $sb['events'] = $this->setEventsInSandbox($events);
        $sb['selectedEvent'] = $selectedEvent?->getKey();
        $sb['eventData'] = $eventData;
        $sb['step'] = 'Select Event';
        $this->workflow->sandbox = $sb;
    }

    private function handleUnpack($fileId)
    {
        if (!isset($this->workflow->sandbox['files']) || count($this->workflow->sandbox['files']) == 0) {
            throw new \Exception("Missing file in upload");
        }
        $this->unpackFiles($this->workflow->sandbox['files'], $fileId);
    }

    private function unpackFiles($files, $fileId)
    {
        // there should be exactly 1 archive file, but just loop over the files
        foreach ($files as $file) {
            if ($file['id'] == $fileId) {
                $this->unpackFile($file);
            }
        }
        $sb = $this->workflow->sandbox;
        $sb['step'] = 'Uploaded';
        $this->workflow->sandbox = $sb;
        $this->workflow->save();
    }

    private function unpackFile($file)
    {
        $path = $file['path'];
        if (file_exists($path)) {
            try {
                $zip = new \ZipArchive;
                $res = $zip->open($path);
                if ($res === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $this->extractSingleFile($zip, $i);
                    }
                    $zip->close();
                    return;
                }
            }
            catch (\Exception $e) {
                \Log::debug('caught error on unzipping archive ' . json_encode($e));
            }
            if (isset($file['name'])) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext == 'xml') {
                    $this->workflow->addFile($path, ["unpacked" => true]);
                }
            }
        }
    }

    private function extractSingleFile($res, $i)
    {
        $data = $res->statIndex($i);
        if ($data !== false) {
            $name = $data['name'];
            $destination = uuid_create();
            $content = $res->getFromIndex($i);
            $newfile = storage_path('app/files') . '/' . $destination . '.dat';
            file_put_contents($newfile, $content);
            $this->workflow->addFile($newfile, ['name' => $name, 'id' => $destination, 'unpacked' => true]);
        }
    }
}
