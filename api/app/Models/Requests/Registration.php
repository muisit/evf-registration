<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Fencer;
use App\Models\Event;
use App\Models\Role;
use App\Models\SideEvent;
use App\Models\Registration as RegistrationModel;
use App\Support\Contracts\EVFUser;
use App\Support\Enums\PaymentOptions;
use App\Support\Rules\ValidateTrim;
use App\Jobs\RegistrationFeedEvents;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Registration extends Base
{
    public function rules(): array
    {
        return [
            'registration.id' => ['nullable', 'int', 'min:0'],
            'registration.fencerId' => ['required','exists:TD_Fencer,fencer_id'],
            'registration.sideEventId' => ['nullable','exists:TD_Event_Side,id', function ($a, $v, $f) {
                return $this->checkSideEvent($a, $v, $f);
            }],
            'registration.roleId' => ['nullable', function ($a, $v, $f) {
                return $this->checkRole($a, $v, $f);
            }],
            'registration.team' => ['nullable', new ValidateTrim(), 'string', 'max:100'],
            'registration.payment' => ['required', Rule::enum(PaymentOptions::class)]
        ];
    }

    private function checkRole($attribute, $value, $fail)
    {
        if ($value === 0 || $value === '0') {
            return;
        }
        $role = Role::where('role_id', $value)->first();
        if (empty($role)) {
            $fail('invalid role set');
        }
    }

    private function checkSideEvent($attribute, $value, $fail)
    {
        $se = SideEvent::where('id', $value)->first();
        $event = request()->get('eventObject');
        if (empty($event) || empty($se) || ($event->getKey() != $se->event_id)) {
            $fail('side event does not match main event');
        }
    }

    private function roleOrSideEvent($data)
    {
        // we expect exactly 2 fields, which cannot both be empty
        // We allow both to be set, but that functionality is not present in
        // the front-end or accreditations. It means people get a role for a
        // specific event (coach for Women Sabre, etc.)
        if (
              !isset($data['registration'])
            || count($data['registration']) != 2
            || (empty($data['registration']['sideEventId']) && empty($data['registration']['roleId']))
        ) {
            return false;
        }
        return true;
    }

    public function createValidator(Request $request)
    {
        $validator = parent::createValidator($request);
        $validator->after(function ($validator) use ($request) {
            $data = $request->only('registration.sideEventId', 'registration.roleId');
            if (!$this->roleOrSideEvent($data)) {
                $validator->errors()->add(
                    'roleId',
                    'Either Role or Event must be set'
                );
            }
        });
        return $validator;
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        // This request type is not available for Cashier and Accredition, even
        // though they can update registrations in general
        // We check that by additionally requiring that the user can create models
        // which is only limited to Organisation, Registration and HoDs
        $this->controller->authorize('create', get_class($this->model));

        $country = null;
        $fencer = Fencer::where('fencer_id', $data['registration']['fencerId'])->first();
        if (!empty($fencer)) {
            $country = $fencer->country;
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

        // check that the event is open for registration if the current user is not an organiser or
        // sysop. 
        if (!$user->hasRole("sysop") && !$user->can('register', RegistrationModel::class) && $user->can('hod', RegistrationModel::class)) {
            $event = request()->get('eventObject');
            if (empty($event) || !$event->exists) {
                return false;
            }
            else if (!$event->isOpenForRegistration()) {
                return false;
            }
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
            $this->model->registration_event = $data['registration']['sideEventId'];
            $this->model->registration_fencer = $data['registration']['fencerId'];
            $this->model->registration_role = $data['registration']['roleId'] == null ? null : intval($data['registration']['roleId']);
            $this->model->registration_team = $data['registration']['team'] ?? null;
            $this->model->registration_payment = $data['registration']['payment'] ?? null;

            $event = request()->get('eventObject');
            $this->model->registration_mainevent = $event->getKey();

            $country = request()->get('countryObject');
            if (!empty($country)) {
                // this links the registration to the requesting country, irrespective of
                // the fencer country
                $this->model->registration_country = $country->getKey();
            }

            if (!$this->model->exists) {
                $this->model->registration_date = Carbon::now()->toDateTimeString();
                // only send out events when a fencer registers for an event for the first time, not when the registration is updated
                $competition = $this->model->sideEvent->competition;
                if (!empty($competition)) {
                    dispatch(new RegistrationFeedEvents($this->model->fencer, $this->model->sideEvent->competition, false))->handle();
                }
            }
        }
        return $this->model;
    }
}
