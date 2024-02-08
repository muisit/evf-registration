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
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RegistrationState extends Base
{
    private array $registrations = [];

    public function rules(): array
    {
        return [
            'state.registrations' => ['required', 'array', function ($a, $v, $f) {
                return $this->checkRegistrations($a, $v, $f);
            }],
            // Absent, Present, Registration-only
            'state.value' => [Rule::in(['A', 'P', 'R'])],
            'state.previous' => [Rule::in(['A', 'P', 'R'])],
        ];
    }

    private function checkRegistrations($attribute, $value, $fail)
    {
        $event = request()->get('eventObject');
        if (!is_array($value) || empty($event)) {
            $fail("invalid list of registration ids");
        }
        foreach ($value as $id) {
            $reg = RegistrationModel::find(intval($id));
            if (empty($reg) || !$reg->exists) {
                $fail('invalid list of registration ids');
            }
            if ($reg->registration_mainevent != $event->getKey()) {
                $fail('invalid list of registration ids');
            }
            $this->registrations[] = $reg;
        }
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        // checked against the current event and country
        $this->controller->authorize('viewAny', RegistrationModel::class);

        foreach ($this->registrations as $registration) {
            $this->controller->authorize('updateState', $registration);
        }
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        return new RegistrationModel();
    }

    protected function updateModel(array $data): ?Model
    {
        $previous = $data['state']['previous'] ?? null;
        foreach ($this->registrations as $registration) {
            if ($data['state']['value'] == 'R') {
                // only update if the previous value matches what we expect, to prevent two process from running into each other
                if (empty($previous) || ($previous == 'R' && empty($registration->registration_state)) || ($previous == $registration->registration_state)) {
                    $registration->registration_state = null;
                }
            }
            else {
                // only update if the previous value matches what we expect, to prevent two process from running into each other
                if (empty($previous) || ($previous == 'R' && empty($registration->registration_state)) || ($previous == $registration->registration_state)) {
                    $registration->registration_state = $data['state']['value'];
                }
            }
            $registration->save();
        }
        return $this->model;
    }

    protected function postProcess()
    {
        // no additional save operation
    }
}
