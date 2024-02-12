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

class RegistrationPay extends Base
{
    private array $registrations = [];
    private $asHod = false;
    private $asOrg = false;

    public function rules(): array
    {
        return [
            'payment.registrations' => ['required', 'array', function ($a, $v, $f) {
                return $this->checkRegistrations($a, $v, $f);
            }],
            'payment.paidHod' => ['nullable', Rule::in(['Y','N'])],
            'payment.paidOrg' => ['nullable', Rule::in(['Y','N'])],
        ];
    }

    private function checkRegistrations($attribute, $value, $fail)
    {
        $country = request()->get('countryObject');
        $event = request()->get('eventObject');
        if (!is_array($value) || empty($country) || empty($event)) {
            $fail("invalid list of registration ids");
        }
        foreach ($value as $id) {
            $reg = RegistrationModel::find(intval($id));
            if (empty($reg) || !$reg->exists) {
                $fail('invalid list of registration ids');
            }
            else if ($reg->registration_mainevent != $event->getKey()) {
                $fail('invalid list of registration ids');
            }
            else if ($reg->registration_country != $country->getKey()) {
                $fail('invalid list of registration ids');
            }
            else {
                $this->registrations[] = $reg;
            }
        }
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        // checked against the current event and country
        $this->controller->authorize('viewAny', RegistrationModel::class);

        foreach ($this->registrations as $registration) {
            $this->controller->authorize('update', $registration);
        }

        $this->asHod = $user->can('hod', RegistrationModel::class);
        $this->asOrg = $user->can('cashier', RegistrationModel::class);
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        return new RegistrationModel();
    }

    protected function updateModel(array $data): ?Model
    {
        foreach ($this->registrations as $registration) {
            if ($this->asHod && isset($data['payment']['paidHod'])) {
                $registration->registration_paid_hod = $data['payment']['paidHod'];
            }
            elseif ($this->asOrg && isset($data['payment']['paidOrg'])) {
                $registration->registration_paid = $data['payment']['paidOrg'];
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
