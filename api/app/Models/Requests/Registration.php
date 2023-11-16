<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Fencer;
use App\Models\Event;
use App\Models\SideEvent;
use App\Models\Registration as RegistrationModel;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Registration extends Base
{
    public function rules(): array
    {
        return [
            'event' => ['required', 'exists:TD_Event,event_id'],
            'country' => ['nullable', 'exists:TD_Country,country_id'],
            'registration.id' => ['nullable', 'int', 'min:0'],
            'registration.fencerId' => ['required','exists:TD_Fencer,fencer_id'],
            'registration.sideEventId' => ['nullable','exists:TD_Event_Side,id', function ($a, $v, $f) {
                return $this->checkSideEvent($a, $v, $f);
            }],
            'registration.roleId' => ['nullable','exists:TD_Role,role_id'],
        ];
    }

    private function checkSideEvent($attribute, $value, $fail)
    {
        $se = SideEvent::where('id', $value)->first();
        $event = request()->get('eventObject');
        if (empty($event) || empty($se) || ($event->getKey() != $se->event_id)) {
            $fail('side event does not match main event');
        }
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        if ($this->model->exists) {
            $country = Country::where('country_id', $this->model->fencer->fencer_country)->first();
        }
        else {
            $fencer = Fencer::where('fencer_id', $data['registration']['fencerId'])->first();
            if (!empty($fencer)) {
                $country = $fencer->country;
            }
        }

        // country is a required setting for the fencer. It must be a viewable country and if
        // there is a country set, it must match that of the fencer of the registrations
        // This restricts HoD to saving registrations only to their specific country
        if (
               empty($country)
            || !$user->can('view', $country)
            || !in_array(request()->get('countryObject')?->getKey(), [null, $country->getKey()])
        ) {
            $this->controller->authorize('not/ever');
            return false;
        }
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $registration = $request->get('registration');
        $id = 0;
        if (!empty($registration)) $id = $registration['id'] ?? 0;
        $id = intval($id);

        $model = RegistrationModel::where('registration_id', $id)->first();
        if (empty($model)) {
            $model = new RegistrationModel();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $event = request()->get('eventObject');
            $this->model->registration_event = $event->getKey();
            $this->model->registration_mainevent = $data['registration']['eventId'];
            $this->model->registration_fencer = $data['registration']['fencerId'];
            $this->model->registration_role = $data['registration']['roleId'];
            $this->model->registration_team = $data['registration']['team'] ?? null;

            $country = request()->get('countryObject');
            if (!empty($country)) {
                // this links the registration to the requesting country, irrespective of
                // the fencer country
                $this->model->registration_country = $country->getKey();
            }

            if (!$this->model->exists) {
                $this->model->registration_date = Carbon::now()->toDateTimeString();
            }
        }
        return $this->model;
    }
}
