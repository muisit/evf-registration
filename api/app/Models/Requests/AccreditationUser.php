<?php

namespace App\Models\Requests;

use App\Models\Accreditation;
use App\Models\AccreditationUser as UserModel;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTimeImmutable;

class AccreditationUser extends Base
{
    public function rules(): array
    {
        return [
            'user.id' => ['required', 'integer', 'min:0'],
            'user.fencerId' => ['required', 'integer', 'exists:TD_Fencer,fencer_id'],
            'user.type' => ['required', 'string', Rule::in(['none', 'organiser', 'checkin', 'checkout', 'accreditation', 'dt'])],
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        // the interface should both pass the event id as query parameter and in the form
        $event = request()->get('eventObject');
        if (empty($event) || $event->getKey() != $this->model->event_id) {
            return false;
        }

        if (isset($data['user']) && $data['user']['type'] == 'none') {
            $this->controller->authorize('delete', $this->model);
        }

        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $user = $request->get('user');
        $id = 0;
        if (!empty($user)) $id = $user['id'] ?? 0;
        $id = intval($id);

        $model = UserModel::find($id);
        if (empty($model)) {
            $model = new UserModel();
            $event = request()->get('eventObject');
            $model->event_id = $event?->getKey();

            $fencerId = $user['fencerId'] ?? 0;
            $accreditations = Accreditation::where('fencer_id', $fencerId)->where('event_id', $model->event_id)->with('template')->get();
            $rolecount = 0;
            foreach ($accreditations as $accreditation) {
                if (($roles = $this->isCorrectAccreditation($accreditation)) !== null) {
                    if (count($roles) > $rolecount) {
                        $model->accreditation_id = $accreditation->getKey();
                        $model->code = $accreditation->getFullAccreditationId();
                        // there might be an off chance that someone of the organisations has 2 or more accreditations that are both
                        // acceptable. This occurs if we create more templates for Organisation level roles. We do this for Referees,
                        // but referees do not normally have other roles.
                        // To be safe, we take the accreditation with the largest set of roles
                    }
                }
            }

            if (empty($model->code)) {
                return null;
            }
        }
        return $model;
    }

    private function isCorrectAccreditation(Accreditation $accreditation)
    {
        $roleIds = $accreditation->template->forRoles();
        $roles = Role::whereIn('role_id', $roleIds)->with('type')->get();
        $found = false;
        foreach ($roles as $role) {
            if (in_array($role->type->org_declaration, ['Org', 'EVF'])) {
                return $roles; // return the whole list, so we can count the largest list
            }
        }
        return null;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $userdata = $data['user'];
            if (!in_array($userdata['type'] ?? '', ['organiser', 'accreditation', 'checkin', 'checkout', 'dt'])) {
                $this->model->type = 'none';
            }
            else {
                $this->model->type = $userdata['type'];
            }
        }
        return $this->model;
    }

    protected function postProcess()
    {
        if (!empty($this->model)) {
            if ($this->model->type == 'none') {
                $this->model->delete();
            }
            else {
                $this->model->save();
            }
        }
    }
}
