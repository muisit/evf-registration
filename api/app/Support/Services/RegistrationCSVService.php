<?php

namespace App\Support\Services;

use App\Models\Category;
use App\Models\Registration;
use DateTimeImmutable;
use App\Support\Services\SessionCacheService;

class RegistrationCSVService
{
    private $registrations;
    private $headers;
    private $config;

    public function __construct($registrations)
    {
        $this->registrations = $registrations;
    }

    public function generate($headers, $config = null)
    {
        $this->headers = $headers;
        $this->config = $config;
        $csv = array_map([$this, 'makeMap'], $this->registrations);
        $csv = array_merge([$headers], $csv); // add headers at the top
        return $csv;
    }

    private function makeMap(Registration $registration)
    {
        $retval = [];
        foreach ($this->headers as $hd) {
            $retval[] = $this->mapHeader($registration, $hd);
        }
        return $retval;
    }

    public function mapHeader(Registration $registration, string $header)
    {
        switch ($header) {
            case 'name':
                return $registration->fencer->fencer_surname;
                break;
            case 'firstname':
                return $registration->fencer->fencer_firstname;
                break;
            case 'country':
                return $registration->fencer->country->country_name;
                break;
            case 'country_abbr':
                return $registration->fencer->country->country_abbr;
                break;
            case 'year-of-birth':
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $registration->fencer->fencer_dob);
                if ($date === false) {
                    return '';
                }
                return $date->format('Y');
                break;
            case 'date':
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $registration->sideEvent->starts);
                if ($date === false) {
                    return '';
                }
                return $date->format('Y-m-d');
                break;
            case 'event':
                return $registration->sideEvent?->title ?? '';
                break;
            case 'role':
                if (!empty($registration->role)) {
                    return $registration->role?->name;
                }
                return $this->config?->isCompetition ? 'Athlete' : 'Participant';
                break;
            case 'organisation':
                if (empty($registration->role)) {
                    return $registration->country->country_name;
                }
                else {
                    if ($registration->role->roleType->org_declaration == 'Country') {
                        return $registration->country->country_name;
                    }
                    else if ($registration->role->roleType->org_declaration == 'Org') {
                        return "Organisation " . $this->event->event->event_name;
                    }
                    else if ($registration->role->roleType->org_declaration == 'EVF') {
                        return "European Veterans Fencing";
                    }
                }
                break;
            case 'organisation_abbr':
                if (empty($registration->role)) {
                    return $registration->country->country_abbr;
                }
                else {
                    if ($registration->role->roleType->org_declaration == 'Country') {
                        return $registration->country->country_abbr;
                    }
                    else if ($registration->role->roleType->org_declaration == 'Org') {
                        return "Org";
                    }
                    else if ($registration->role->roleType->org_declaration == 'EVF') {
                        return "EVF";
                    }
                }
                break;
            case 'type':
                if (!empty($registration->role)) {
                    return 'Official';
                }
                return $this->config?->isCompetition ? 'Athlete' : 'Participant';
                break;
            case 'cat':
                $cat = Category::categoryFromYear($registration->fencer->fencer_dob, $this->event->event_starts);
                if ($cat < 1) {
                    return '(no category)';
                }
                else {
                    if ($this->config?->category && $cat != intval($this->config?->category)) {
                        return "$cat (wrong category)";
                    }
                    else {
                        return $cat;
                    }
                }
                break;
            case 'gender':
                if ($this->config?->gender && $this->config?->gender != $registration->fencer->fencer_gender) {
                    return ($registration->fencer->fencer_gender == 'F' ? 'Female' : 'Male') . " (wrong gender)";
                }
                else {
                    return ($registration->fencer->fencer_gender == 'F' ? 'Female' : 'Male');
                }
                break;
            case "team":
                if (!empty($registration->registration_team)) {
                    return $registration->registration_team;
                }
                break;
            case "picture":
                if (!empty($registration->fencer->fencer_picture)) {
                    if ($registration->fencer->fencer_picture == 'R') {
                        return 'REPLACE';
                    }
                    else if($registration->fencer->fencer_picture == 'N') {
                        return 'NONE';
                    }
                    else if($registration->fencer->fencer_picture == 'Y') {
                        return 'NEW';
                    }
                    else if($registration->fencer->fencer_picture == 'A') {
                        return 'OK';
                    }
                    else {
                        return 'OTHER (' . $registration->fencer->fencer_picture . ')';
                    }
                }
                else {
                    return 'NONE';
                }
                break;
        }
    }
}
