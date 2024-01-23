<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\EventRole as EventRoleModel;
use App\Models\WPUser;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTimeImmutable;

class EventRole extends Base
{
    public function rules(): array
    {
        return [
            'roles.*.id' => ['required', 'integer'], // allow negative values for new events
            'roles.*.userId' => ['nullable', function ($a, $v, $f) {
                return $this->checkUser($a, $v, $f);
            }],
            'roles.*.role' => ['nullable', 'string', Rule::in(['organiser','registrar','cashier','accreditation'])],
        ];
    }


    private function checkUser($attribute, $value, $fail)
    {
        if ($value === 0 || $value === '0') {
            return;
        }
        $user = WPUser::where('ID', $value)->first();
        if (empty($user)) {
            $fail('invalid user set');
        }
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        if (empty($this->model) || !$this->model->exists) {
            return false;
        }
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $model = $request->get('eventObject');
        if (empty($model)) {
            // always return a model, but we check for existance in authorize()
            $model = new Event();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $roles = collect($this->model->roles)->keyBy('id');

            foreach ($data['roles'] as $newdata) {
                $model = new EventRoleModel();
                $model->event_id = $this->model->getKey();

                if ($newdata['id']) {
                    $id = intval($newdata['id']);
                    if (isset($roles[$id])) {
                        $model = $roles[$id];
                        unset($roles[$id]);
                    }
                }
                $model->user_id = $newdata['userId'] ?? null;
                $model->role_type = $newdata['role'] ?? '';

                if (!in_array($model->role_type, ['organiser', 'cashier', 'accreditation', 'registrar']) || empty($model->user_id)) {
                    $model->delete();
                }
                else {
                    $model->save();
                }
            }

            // left-overs have been removed in the front-end
            foreach ($roles as $key => $model) {
                $model->delete();
            }
        }
        return $this->model;
    }
}
