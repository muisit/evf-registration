<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS `VW_Ranking`;");
        DB::statement(
            "CREATE VIEW `VW_Ranking`  AS SELECT " .
            " e.event_id, e.event_name, e.event_open, e.event_location, cnt.country_name, " .
            " c.competition_id, " .
            " cat.category_id, cat.category_name, cat.category_abbr, " .
            " w.weapon_id, w.weapon_name, w.weapon_abbr, w.weapon_gender, " .
            " f.fencer_id, f.fencer_firstname, f.fencer_surname, f.fencer_dob, f.fencer_gender, " .
            " fcnt.country_abbr as fencer_country_abbr, fcnt.country_name as fencer_country_name, fcnt.country_registered as fencer_country_registered, " .
            " r.result_id, r.result_place, r.result_points, r.result_entry, " .
            " r.result_de_points, r.result_podium_points, r.result_total_points, e.event_factor, r.result_in_ranking " .
            " FROM TD_Result r " .
            " inner join TD_Competition c on c.competition_id=r.result_competition " .
            " inner join TD_Event e on e.event_id = c.competition_event " .
            " inner join TD_Fencer f on f.fencer_id=r.result_fencer " .
            " inner join TD_Country cnt on cnt.country_id=e.event_country " .
            " inner join TD_Country fcnt on fcnt.country_id=f.fencer_country " .
            " inner join TD_Category cat on cat.category_id=c.competition_category " .
            " inner join TD_Weapon w on w.weapon_id=c.competition_weapon " .
            " WHERE e.event_in_ranking='Y'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // do not drop the view ever
    }
};
