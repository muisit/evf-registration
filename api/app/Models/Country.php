<?php

namespace App\Models;

use App\Support\Contracts\AccreditationRelation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\Builder;

class Country extends Model implements AccreditationRelation
{
    protected $table = 'TD_Country';
    protected $primaryKey = 'country_id';
    public $timestamps = false;

    public const GBR = 1;
    public const ITA = 2;
    public const FRA = 11;
    public const GER = 12;
    public const NED = 21;
    public const UKR = 32;
    public const TST = 46;
    public const ALB = 47;
    public const OTH = 49;

    public function selectAccreditations(Event $event)
    {
        // only select accreditations with an athlete or federative role template
        $templateIdByType = AccreditationTemplate::byRoleId($event);
        $rtype = RoleType::find(RoleType::COUNTRY);

        $athleteTemplates = $templateIdByType["r0"] ?? [];
        $federativeTemplates = $templateIdByType["r" . $rtype->getKey()] ?? [];
        $acceptableTemplates = array_merge($athleteTemplates, $federativeTemplates);

        $accreditations = Accreditation::with(['fencer', 'template'])
            ->whereIn('template_id', $acceptableTemplates)
            ->joinRelationship('fencer')
            ->where('event_id', $event->getKey())
            ->where(Fencer::tableName() . '.fencer_country', $this->getKey())
            ->get();
        return $accreditations;
    }
}
