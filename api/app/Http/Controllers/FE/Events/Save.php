<?php

namespace App\Http\Controllers\FE\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competition;
use App\Models\Event as Model;
use App\Models\EventType;
use App\Models\SideEvent;
use App\Models\Schemas\Event as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Save extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/events/save') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        $this->authorize('update', $data);
        if ($data->validate()) {
            $this->process($data, $model);
            return response()->json(new WPResponse(["item" => new Schema($data)]));
        }
        return response()->json(["success" => false]);
    }

    private function process(Model $data, $model)
    {
        $data->save();
        $this->saveCompetitions($data, $model);
        return $data;
    }

    private function populateModel($model)
    {
        $data = Model::find($model->id);
        if (empty($data)) {
            $data = new Model();
            $data->event_year = intval(Carbon::now()->format('Y'));
            $data->event_payments = 'group';
            $data->event_currency_symbol = '€';
            $data->event_currency_name = 'EUR';
            $data->event_duration = 2;
            $data->event_bank = '';
            $data->event_account_name = '';
            $data->event_organisers_address = '';
            $data->event_iban = '';
            $data->event_swift = '';
            $data->event_in_ranking = 'N';
            $data->event_factor = 1.0;
        }
        $data->event_name = $model->name;
        $data->event_location = $model->location ?? null;
        $data->event_country = $model->countryId ?? null;
        $data->event_type = $model->typeId ?? null;

        $data->event_open = $this->safeDate($model->opens);
        $data->event_year = intval($model->year);
        $data->event_duration = intval($model->duration);
        if ($data->event_duration <= 0) {
            $data->event_duration = 2;
        }

        $cfg = json_decode($data->event_config ?? '{}', true);
        if (isset($model->config) && ($model->config['use_registration'] ?? false) === true) {
            $cfg['use_registration'] = true;
        }
        else {
            $cfg['use_registration'] = false;
        }
        $data->event_config = json_encode($cfg);

        $data->event_in_ranking = $model->inRanking == 'Y' ? 'Y' : 'N';
        $data->event_factor = $model->factor ?? 1.0;
        return $data;
    }

    private function safeDate($dt)
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dt);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
        return null;
    }

    private function saveCompetitions(Model $event, $data)
    {
        $oldCompetitions = $event->competitions;
        $oldEvents = $event->sides;
        $newCompetitions = $data->competitions ?? [];

        $removeCompetitions = [];
        $cByCatWpn = [];
        $cByCatWpn2 = [];
        $sById = [];
        foreach ($oldCompetitions as $c) $cByCatWpn['c' . $c->competition_category . '_w' . $c->competition_weapon] = $c;
        foreach ($oldEvents as $s) $sById['s' . $s->competition_id] = $s;

        $existingIds = [];
        foreach ($newCompetitions as $competitionData) {
            $competitionData = (object) $competitionData;
            $key = 'c' . $competitionData->categoryId . '_w' . $competitionData->weaponId;
            $competition = Competition::find($competitionData->id);
            if (isset($cByCatWpn[$key])) {
                $competition = $cByCatWpn[$key];
                unset($cByCatWpn[$key]);
            }
            else if(empty($competition)) {
                $competition = new Competition();
            }

            // prevent two competitions of the same type to be created
            if (isset($cByCatWpn2[$key])) {
                continue;
            }
            $cByCatWpn2[$key] = $competition;

            $competition->competition_event = $event->getKey();
            $competition->competition_category = $competitionData->categoryId;
            $competition->competition_weapon = $competitionData->weaponId;
            $competition->competition_opens = $this->safeDate($competitionData->starts);
            $competition->competition_weapon_check = $this->safeDate($competitionData->weaponsCheck);
            $competition->save();
            $existingIds[] = $competition->getKey();

            // make sure there is a SideEvent linked to this as well
            $se = null;
            if (!isset($sById["s" . $competition->getKey()])) {
                $se = new SideEvent();
                $se->event_id = $event->getKey();
                $se->competition_id = $competition->getKey();
                $se->description = '';
                $se->costs = 0.0;
            }
            else {
                $se = $sById["s" . $competition->getKey()];
            }

            // overwrite the SideEvent details if they were changed on the competition
            $se->title = $competition->weapon->weapon_name . " " . $competition->category->category_name;
            $se->starts = $competition->competition_opens;
            $se->save();
        }

        foreach ($oldCompetitions as $c) {
            if (!in_array($c->getKey(), $existingIds)) {
                $se = $c->sideEvent;
                if (!empty($se)) {
                    $se->delete();
                }
                $c->delete();
            }
        }
    }
}
