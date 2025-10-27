<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Event as EventModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTimeImmutable;

class Event extends Base
{
    public function rules(): array
    {
        $modelRules = EventModel::rules();
        return [
            'event.id' => $modelRules['event_id'],
            'event.name' => $modelRules['event_name'],
            'event.opens' => $modelRules['event_opens'],
            'event.reg_open' => $modelRules['event_reg_open'],
            'event.reg_close' => $modelRules['event_reg_close'],
            'event.year' => $modelRules['event_year'],
            'event.duration' => $modelRules['event_duration'],
            'event.email' => $modelRules['event_email'],
            'event.web' => $modelRules['event_web'],
            'event.location' => $modelRules['event_location'],
            'event.countryId' => $modelRules['event_country'],
            'event.config' => $modelRules['event_config'],
            'event.payments' => $modelRules['event_payments'],
            'event.symbol' => $modelRules['event_symbol'],
            'event.currency' => $modelRules['event_currency'],
            'event.bank' => $modelRules['event_bank'],
            'event.account' => $modelRules['event_account'],
            'event.address' => $modelRules['event_address'],
            'event.iban' => $modelRules['event_iban'],
            'event.swift' => $modelRules['event_swift'],
            'event.reference' => $modelRules['event_reference'],
            'event.baseFee' => $modelRules['event_base_fee'],
            'event.competitionFee' => $modelRules['event_competition_fee']
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        if (!$this->model->exists) {
            return false; // the interface does not allow creating new events
        }

        // the interface should both pass the event id as query parameter and in the form
        $event = request()->get('eventObject');
        if ($event->getKey() != $this->model->getKey()) {
            return false;
        }
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $event = $request->get('event');
        $id = 0;
        if (!empty($event)) $id = $event['id'] ?? 0;
        $id = intval($id);

        $model = EventModel::where('event_id', $id)->first();
        if (empty($model)) {
            // always return a model, but we check for existance in authorize()
            $model = new EventModel();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $this->model->event_name = $data['event']['name'];
            $this->model->event_open = $this->safeDate($data['event']['opens']);
            $this->model->event_year = intval($data['event']['year']);
            $this->model->event_duration = intval($data['event']['duration']);
            $this->model->event_registration_open = $this->safeDate($data['event']['reg_open'] ?? '');
            $this->model->event_registration_close = $this->safeDate($data['event']['reg_close'] ?? '');

            $this->model->event_email = $data['event']['email'] ?? null;
            $this->model->event_web = $data['event']['web'] ?? null;
            $this->model->event_location = $data['event']['location'] ?? null;
            $this->model->event_country = $data['event']['countryId'] ?? null;
            $this->model->event_config = json_encode(array_merge(json_decode($this->model->event_config, true), $this->safeConfig($data['event']['config'] ?? '{}')));
            $this->model->event_payments = $data['event']['payments'] ?? 'group';
            $this->model->event_currency_symbol = $data['event']['symbol'] ?? '€';
            $this->model->event_currency_name = $data['event']['currency'] ?? 'EUR';
            $this->model->event_bank = $data['event']['bank'] ?? '';
            $this->model->event_account_name = $data['event']['account'] ?? '';
            $this->model->event_organisers_address = $data['event']['address'] ?? '';
            $this->model->event_iban = $data['event']['iban'] ?? '';
            $this->model->event_swift = $data['event']['swift'] ?? '';
            $this->model->event_reference = $data['event']['reference'] ?? '';
            $this->model->event_base_fee = floatval($data['event']['baseFee'] ?? '0');
            $this->model->event_competition_fee = floatval($data['event']['competitionFee'] ?? '0');
        }
        return $this->model;
    }

    private function safeConfig($obj)
    {
        if (!is_object($obj)) {
            $obj = json_decode($obj);
        }
        return [
            "allow_registration_lower_age" => $obj->allow_registration_lower_age ?? false,
            "allow_more_teams" => $obj->allow_more_teams ?? false,
            "no_accreditations" => $obj->no_accreditations ?? true,
            "use_accreditation" => $obj->use_accreditation ?? false,
            "use_registration" => $obj->use_registration ?? true,
        ];
    }

    private function safeDate($dt)
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dt);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
        return null;
    }
}
