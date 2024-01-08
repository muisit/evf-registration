<?php

namespace App\Support\Services;

use App\Models\SideEvent;
use App\Models\Registration;

class ParticipantListService
{
    private SideEvent $event;

    public function __construct(SideEvent $event)
    {
        $this->event = $event;
    }

    public function asCSV($filename)
    {
        $registrations = $this->sortRegistrations($this->createListOfParticipants());
        $headers = array("name", "firstname", "country", "year-of-birth", "role", "organisation", "organisation_abbr", "type", "date", "team");
        if (empty($this->event->competition)) {
            $headers = array("name", "firstname", "country", "organisation");
        }
        $csvservice = new RegistrationCSVService($registrations);
        $lines = $csvservice->generate($headers, (object)[
            'isCompetition' => !empty($this->event->competition)
        ]);

        header('Content-Disposition: attachment; filename="' . $filename . '";');
        header('Content-Type: application/csv; charset=UTF-8');

        $f = fopen('php://output', 'w');
        foreach ($lines as $line) {
            fputcsv($f, $line, ';');
        }
        fpassthru($f);
        fclose($f);
        exit();
    }

    public function asXML($filename)
    {
        $registrations = $this->sortRegistrations($this->createListOfParticipants());
        $xmlservice = new RegistrationXMLService($this->event, $registrations);
        $doc = $xmlservice->generate();

        header('Content-Disposition: attachment; filename="' . $filename . '";');
        header('Content-Type: text/xml; charset=UTF-8');
        echo "\xEF\xBB\xBF"; // echo a BOM for Windows purposes
        echo $doc;
        exit();
    }

    private function sortRegistrations($registrations)
    {
        // combine all registrations for a given fencer into one
        $regByFencer = [];
        foreach ($registrations as $reg) {
            $key = 'f' . $reg->registration_fencer;
            if (!isset($regByFencer[$key])) {
                $reg->registrations = [$reg];
                $regByFencer[$key] = $reg;
            }
            else {
                $regByFencer[$key]->registrations[] = $reg;
            }
        }
        $registrations = array_values($regByFencer);

        usort($registrations, function (Registration $reg1, Registration $reg2) {
            return $reg1->fencer->getFullName() <=> $reg2->fencer->getFullName();
        });
        return $registrations;
    }

    private function createListOfParticipants()
    {
        $registrations = Registration::where('registration_event', $this->event->getKey())
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();

        return $registrations;
    }
}