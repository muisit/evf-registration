<?php

namespace App\Models\Requests;

use App\Events\BlockEvent;
use App\Models\DeviceUser;
use App\Models\Follow as FollowModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTimeImmutable;

class Block extends Base
{
    public function rules(): array
    {
        return [
            'block.id' => ['required', 'string', 'exists:device_users,uuid'],
            'block.block' => ['required', Rule::in(['Y', 'N'])],
        ];
    }

    protected function createModel(Request $request): ?Model
    {
        $data = $request->get('block');
        $id = '';
        if (!empty($data)) {
            $id = $data['id'] ?? '';
        }

        \Log::debug("getting user based on uuid $id");
        $follower = DeviceUser::where('uuid', $id)->first();
        if (!empty($follower)) {
            // if not found, then we cannot block
            // You cannot block or unblock a follower that is not following you
            $model = FollowModel::where('device_user_id', $follower->getKey())->where('fencer_id', $request->user()->fencer?->getKey())->first();
            \Log::debug("found model " . $model?->getKey());
            return $model;
        }
        return null;
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!empty($this->model)) {
            if (!$this->model->exists) {
                // you cannot block a non-existing model
                return false;
            }
            else {
                $this->controller->authorize('block', $this->model);
            }
            return true;
        }
        return false;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            if ($data['block']['block'] == 'Y') {
                $this->model->setPreference('block', true);
            }
            else {
                $this->model->setPreference('block', false);
            }
            // no need to dispatch the feed creation in a job, it only concerns two users at the most
            BlockEvent::dispatch($this->model->fencer, $this->model->user, $data['block']['block'] == 'Y');
        }
        return $this->model;
    }
}
