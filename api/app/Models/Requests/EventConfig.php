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

class EventConfig extends Base
{
    public function rules(): array
    {
        return [
            'event.id' => ['required', 'exists:TD_Event,event_id'],
            'event.config' => ['nullable', 'json'],
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        $this->controller->authorize('configure', $this->model);

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
            $this->model->event_config = json_encode(array_merge(json_decode($this->model->event_config, true), $this->safeConfig($data['event']['config'] ?? '{}')));
            \Log::debug("new model config is " . $this->model->event_config);
        }
        return $this->model;
    }

    private function safeConfig($obj)
    {
        if (!is_array($obj)) {
            $obj = json_decode($obj, true);
        }
        $keys = [
            'allow_registration_lower_age',
            'allow_more_teams',
            'no_accreditations',
            'no_accreditations',
            'use_accreditation',
            'use_registration',
            'require_cards',
            'require_documents',
            'allow_incomplete_checkin',
            'allow_hod_checkout',
            'mark_process_start',
            'combine_checkin_checkout',
            'overviewstyle',
        ];

        $keylist = array_keys($obj);

        foreach ($keys as $key) {
            if (in_array($key, $keylist)) {
                $retval[$key] = $obj[$key] ? true : false;
                if ($key == 'overviewstyle') {
                    $retval[$key] = $obj[$key];
                }
            }
        }
        return $retval;
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
