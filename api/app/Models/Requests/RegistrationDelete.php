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

class RegistrationDelete extends Base
{
    public function rules(): array
    {
        return [
            'registration.id' => ['nullable', 'int', 'min:0'],
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (empty($this->model) || !$this->model->exists) {
            $this->controller->authorize('not/ever');
        }
        \Log::debug("authorizing registration delete " . get_class($this->controller));
        $this->controller->authorize('delete', $this->model);
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

    public function validate(Request $request): ?Model
    {
        $model = parent::validate($request);

        if (!empty($model) && $model->exists) {
            $model->delete();
        }
        return $model;
    }
}
