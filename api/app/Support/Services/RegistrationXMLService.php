<?php

namespace App\Support\Services;

use App\Models\Registration;
use App\Models\SideEvent;
use DateTimeImmutable;
use DateInterval;

class RegistrationXMLService
{
    private $sideEvent;
    private $event;
    private $competition;
    private $category;
    private $weapon;
    private $registrations;

    private $root;
    private $doc;
    private $dom;

    public function generate(SideEvent $event, $registrations)
    {
        $this->sideEvent = $event;
        $this->registrations = $registrations;

        $this->event = $this->sideEvent->event;
        $this->competition = $this->sideEvent->competition;
        $this->category = $this->sideEvent->competition?->category;
        $this->weapon = $this->sideEvent->competition?->weapon;

        $this->dom = new \DOMDocument();
        $this->dom->encoding = 'UTF-8';
        $this->dom->xmlVersion = '1.0';
        $this->dom->formatOutput = true;

        $this->createDocument();

        $this->root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xsd", "http://www.w3.org/2001/XMLSchema");
        $this->root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $this->dom->appendChild($this->root);
        return $this->dom->saveXML();
    }

    private function createDocument()
    {
        $implementation = new \DOMImplementation();
        if (!empty($this->category)) {
            if ($this->category->category_type == 'T') {
                $this->root = $this->dom->createElement('BaseCompetitionParEquipes');
                $this->fillDataEquipe();
                $doctype = $implementation->createDocumentType('BaseCompetitionParEquipes');
            }
            else {
                $this->root = $this->dom->createElement('BaseCompetitionIndividuelle');
                $this->fillDataIndividual();
                $doctype = $implementation->createDocumentType('BaseCompetitionIndividuelle');
            }
        }
        else {
            $this->root = $this->dom->createElement('BaseCompetitionIndividuelle');
            $doctype = $implementation->createDocumentType('BaseCompetitionIndividuelle');
            $this->fillDataIndividual($data);
        }
        $this->dom->appendChild($doctype);
    }

    private function fillDataEquipe()
    {
        $this->doc = $this->root;
        $this->setYear()->setWeapon()->setCategory()->setDates()->setEventData();
        $this->doc->setAttribute("TypeCompetition", "S"); // V is for Veterans weelchair

        $this->addFencers();
        $this->doc = $this->root;
        $this->addTeams();
    }

    private function addTeams()
    {
        // sort all data according to teams
        $teams = [];
        foreach ($this->registrations as $reg) {
            if (isset($reg->registration_team)) {
                // team name is a unique value within country
                $key = '' . $reg->country?->country_abbr . $reg->registration_team;
                if (!isset($teams[$key])) {
                    $teams[$key] = [];
                }
                $teams[$key][] = $reg;
            }
        }

        $equipes = $this->dom->createElement('Equipes');
        $this->root->appendChild($equipes);
        $this->doc = $equipes;
        foreach ($teams as $team => $regs) {
            $this->addEquipe($team, $regs);
        }
    }

    private function fillDataIndividual()
    {
        $this->doc = $this->root;
        $this->setYear()->setWeapon()->setCategory()->setDates()->setEventData();
        $this->addFencers();
    }

    private function addFencers()
    {
        $currentranking = [];
        if (!empty($this->competition)) {
            $service = new RankingService($this->category, $this->weapon);
            $ranking = $service->generate();

            foreach ($ranking as $row) {
                $key = "fid" . $row["id"];
                $currentranking[$key] = $row;
            }
        }

        $tireurs = $this->dom->createElement("Tireurs");
        $this->root->appendChild($tireurs);
        $this->doc = $tireurs;
        foreach ($this->registrations as $reg) {
            $this->addFencer($reg, $currentranking);
        }
    }

    private function addEquipe(string $team, $regs)
    {
        $equipe = $this->dom->createElement("Equipe");
        $firstreg = $regs[0]; // there must be at least 1 registration

        $equipe->setAttribute("ID", $team);
        $equipe->setAttribute('Nation', $firstreg->country->country_abbr);
        $equipe->setAttribute('Nom', $firstreg->country->country_name . " " . $firstreg->registration_team);
        if ($firstreg->fencer->fencer_gender == 'M') {
            $equipe->setAttribute('Sexe', 'M');
        }
        else {
            $equipe->setAttribute('Sexe', 'F');
        }

        // According to Ophardt, Tireur do not need to be included in Equipe, the Equipe-attribute of
        // the original Tireur is sufficient.
        //$oridoc=$this->doc;
        //$this->doc = $equipe;
        //foreach($regs as $r) {
        //    $this->addFencerRef($r);
        //}
        //$this->doc=$oridoc;

        $this->doc->appendChild($equipe);
    }

    private function addFencerRef(Registration $reg)
    {
        $tireur = $this->dom->createElement("Tireur");
        $tireur->setAttribute("REF", $reg->registration_fencer);
        $this->doc->appendChild($tireur);
    }

    private function addFencer(Registration $reg, $ranking)
    {
        $key = "fid" . $reg->registration_fencer;
        $pos = null;
        $points = null;
        if (isset($ranking[$key])) {
            $pos = $ranking[$key]["pos"];
            $points = $ranking[$key]["points"];
        }
        $tireur = $this->dom->createElement("Tireur");
        $tireur->setAttribute("ID", $reg->registration_fencer);
        $tireur->setAttribute('Nom', $reg->fencer->fencer_surname);
        $tireur->setAttribute('Prenom', $reg->fencer->fencer_firstname);
        $dob = DateTimeImmutable::createFromFormat('Y-m-d', $reg->fencer->fencer_dob);
        if ($dob !== false) {
            $tireur->setAttribute("DateNaissance", $dob->format('d.m.Y'));
        }
        $tireur->setAttribute('Nation', $reg->country->country_abbr);

        if ($reg->fencer->fencer_gender == 'M') $tireur->setAttribute('Sexe', 'M');
        else $tireur->setAttribute('Sexe', 'F');

        // Lateralite is required, but we do not have it
        $tireur->setAttribute('Lateralite', 'D');

        if (!empty($pos)) $tireur->setAttribute("Classement", $pos);
        if (!empty($points)) $tireur->setAttribute("Points", $points);

        if (!empty($reg->registration_team)) {
            $tireur->setAttribute("Equipe", $reg->country->country_abbr . $reg->registration_team);
        }

        // skip Arme, used for mixed competitions
        // skip Club
        // skip Dossard... mask number
        // Licence: FencingTime requires a license field before it can link people to a team... aaarrghh
        $tireur->setAttribute("Licence", $reg->registration_fencer);
        // skip LicenceNat
        // skip Ligue
        // skip NbMatches
        // skip NbVictoires
        // skip NoDansLaPoule
        // skip PhotoURL, privacy issue
        // skip RangFinal
        // skip RangInitial
        // skip RangPoule
        // skip Score
        // skip Statut
        // skip TD
        // skip TR
        $this->doc->appendChild($tireur);
    }

    private function setEventData()
    {
        if(!empty($this->event->event_location)) $this->doc->setAttribute("Lieu", $this->event->event_location);
        if(!empty($this->event->event_feed)) $this->doc->setAttribute("LiveURL", $this->event->event_feed);
        $this->doc->setAttribute("Organisateur", $this->event->country->country_name);

        // Sonja Lange requested: 'title should not contain weapon/sex/etc' and suggested a generic short title
        $this->doc->setAttribute("TitreCourt", "EVC" . $this->category->category_type);
        $this->doc->setAttribute("TitreLong", $this->event->event_name);
        if(!empty($this->event->event_web)) $this->doc->setAttribute("URLOrganisateur", $this->event->event_web);
        $this->doc->setAttribute("Championnat", 'EVF');
        $this->doc->setAttribute("Domaine", 'Z'); // EVF is concerned only with the European Zone 
        $this->doc->setAttribute("Federation", $this->event->country->country_abbr); // country of the organiser
        $this->doc->setAttribute("ID", $this->sideEvent->getKey());
    }

    private function setDates()
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $this->sideEvent->starts);
        $opens = DateTimeImmutable::createFromFormat('Y-m-d', $this->event->event_open);

        if ($date !== false) {
            $this->doc->setAttribute("Date", $date->format('d.m.Y'));
        }
        if ($opens !== false) {
            $this->doc->setAttribute("DateDebut", $opens->format('d.m.Y'));
            $close = $opens->add(new DateInterval("P" . ($this->event->event_duration + 1) . "D"));
            $this->doc->setAttribute("DateFin", $close->format('d.m.Y'));
        }
        $now = new DateTimeImmutable();
        $this->doc->setAttribute("DateFichierXML", $now->format('d.m.Y'));
        return $this;
    }

    private function setCategory()
    {
        switch ($this->category->category_abbr) {
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
                $this->doc->setAttribute("Categorie", 'V' . $this->category->category_abbr); 
                break;
            case 'T':
                $this->doc->setAttribute("Categorie", 'V');
                break;
            case 'T(G)':
                $this->doc->setAttribute("Categorie", 'GV');
                break;
        }
        return $this;
    }
    private function setYear()
    {
        $this->doc->setAttribute("Annee", $this->event->event_year);
        return $this;
    }

    private function setWeapon()
    {
        if (!empty($this->weapon)) {
            switch ($this->weapon->weapon_abbr) {
                case 'MF':
                case 'WF':
                    $this->doc->setAttribute("Arme", "F");
                    break;
                case 'ME':
                case 'WE':
                    $this->doc->setAttribute("Arme", "E");
                    break;
                case 'MS':
                case 'WS':
                    $this->doc->setAttribute("Arme", "S");
                    break;
            }

            switch ($this->weapon->weapon_abbr) {
                case 'MS':
                case 'ME':
                case 'MF':
                    $this->doc->setAttribute("Sexe", "M");
                    break;
                case 'WE':
                case 'WS':
                case 'WF':
                    $this->doc->setAttribute("Sexe", "F");
                    break;
            }
        }
        return $this;
    }
}
