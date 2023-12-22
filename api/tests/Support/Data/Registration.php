<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Registration as Model;
use App\Models\Country;
use App\Models\Role;
use Carbon\Carbon;

class Registration extends Fixture
{
    public const REG1 = 1;
    public const REG2 = 2;
    public const REG3 = 3;
    public const TEAM1 = 10;
    public const TEAM2 = 11;
    public const TEAM3 = 12;
    public const TEAM4 = 13;
    public const CKT1 = 20;
    public const CKT2 = 21;
    public const CKT3 = 22;
    public const GALA1 = 30;
    public const GALA2 = 31;
    public const SUP1 = 40;
    public const SUP2 = 41;
    public const SUP3 = 42;
    public const SUP4 = 43;
    public const SUP5 = 44;
    public const SUP6 = 45;

    protected static function boot()
    {
        Event::create();
        Fencer::create();
        SideEvent::create();
        AccreditationTemplate::create();
        self::booted();

        $opens = Carbon::now()->addDays(11)->toDateString();
        Model::create([
            'registration_id' => self::REG1,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT1,
            'registration_event' => SideEvent::MFCAT1,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::REG2,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT2,
            'registration_event' => SideEvent::MFCAT2,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::ITA
        ])->save();

        Model::create([
            'registration_id' => self::REG3,
            'registration_role' => 0,
            'registration_fencer' => Fencer::WCAT1,
            'registration_event' => SideEvent::WSCAT1,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::TEAM1,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT1,
            'registration_event' => SideEvent::MFTEAM,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => 'team1',
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::TEAM2,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT1B,
            'registration_event' => SideEvent::MFTEAM,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => 'team1',
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::TEAM3,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT1C,
            'registration_event' => SideEvent::MFTEAM,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => 'team1',
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::TEAM4,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT2,
            'registration_event' => SideEvent::MFTEAM,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => 'team1',
            'registration_country' => Country::ITA
        ])->save();

        Model::create([
            'registration_id' => self::CKT1,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT1,
            'registration_event' => SideEvent::DINATOIRE,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'N',
            'registration_paid_hod' => 'N',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::CKT2,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT5,
            'registration_event' => SideEvent::DINATOIRE,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'N',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => null
        ])->save();
    
        Model::create([
            'registration_id' => self::CKT3,
            'registration_role' => 0,
            'registration_fencer' => Fencer::WCAT4,
            'registration_event' => SideEvent::DINATOIRE,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'N',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::NED
        ])->save();

        Model::create([
            'registration_id' => self::GALA1,
            'registration_role' => 0,
            'registration_fencer' => Fencer::WCAT3,
            'registration_event' => SideEvent::GALA,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'N',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::FRA
        ])->save();

        Model::create([
            'registration_id' => self::GALA2,
            'registration_role' => 0,
            'registration_fencer' => Fencer::MCAT3,
            'registration_event' => SideEvent::GALA,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'N',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => null
        ])->save();

        Model::create([
            'registration_id' => self::SUP1,
            'registration_role' => Role::HOD,
            'registration_fencer' => Fencer::MCAT5,
            'registration_event' => null,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'N',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::SUP2,
            'registration_role' => Role::COACH,
            'registration_fencer' => Fencer::MCAT5,
            'registration_event' => null,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'N',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => Country::GER
        ])->save();

        Model::create([
            'registration_id' => self::SUP3,
            'registration_role' => Role::REFEREE,
            'registration_fencer' => Fencer::MCAT5,
            'registration_event' => null,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => null
        ])->save();

        Model::create([
            'registration_id' => self::SUP4,
            'registration_role' => Role::DIRECTOR,
            'registration_fencer' => Fencer::MCAT4,
            'registration_event' => null,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => null
        ])->save();

        Model::create([
            'registration_id' => self::SUP5,
            'registration_role' => Role::VOLUNTEER,
            'registration_fencer' => Fencer::MCAT4,
            'registration_event' => null,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => null
        ])->save();

        Model::create([
            'registration_id' => self::SUP6,
            'registration_role' => Role::VOLUNTEER,
            'registration_fencer' => Fencer::MCAT5,
            'registration_event' => null,
            'registration_date' => '2022-01-01',
            'registration_paid' => 'Y',
            'registration_paid_hod' => 'Y',
            'registration_mainevent' => Event::EVENT1,
            'registration_payment' => 'I',
            'registration_state' => 'A',
            'registration_team' => null,
            'registration_country' => null
        ])->save();
    }
}
