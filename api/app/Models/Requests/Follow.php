<?php

namespace App\Models\Requests;

use App\Events\FollowEvent;
use App\Models\Fencer;
use App\Models\Follow as FollowModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTimeImmutable;

class Follow extends Base
{
    public function rules(): array
    {
        return [
            'follow.fencer' => ['required', 'string', 'exists:TD_Fencer,uuid'],
            'follow.preferences' => ['nullable', function ($a, $v, $f) {
                return $this->checkPreferences($a, $v, $f);
            }],
        ];
    }

    private function checkPreferences($attribute, $value, $fail)
    {
        \Log::debug("value is " . json_encode($value));
        $value = (array) $value;
        if (!is_array($value)) {
            $fail("Invalid preferences");
        }
        else {
            $diff = array_diff($value, FollowModel::$allowedUserSettings);
            \Log::debug("diff is " . json_encode($diff));
            if (count($diff) > 0) {
                $fail("Invalid preferences");
            }
        }
    }

    protected function createModel(Request $request): ?Model
    {
        $data = $request->get('follow');
        $id = '';
        if (!empty($data)) {
            $id = $data['fencer'] ?? '';
        }

        $fencer = Fencer::where('uuid', $id)->first();
        if (!empty($fencer)) {
            $model = FollowModel::where('fencer_id', $fencer->getKey())->where('device_user_id', $request->user()->getKey())->first();
            if (empty($model)) {
                // always return a model, but we check for existance in authorize()
                $model = new FollowModel();
                $model->device_user_id = $request->user()->getKey();
            }
            \Log::debug("model found");
            return $model;
        }
        return null;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            \Log::debug("updating model " . json_encode($data));
            // unneeded, but better be sure: reset the device_user_id
            $this->model->device_user_id = Auth::user()->getKey();

            // set the related fencer
            $fencer = Fencer::where('uuid', $data['follow']['fencer'])->first();
            $this->model->fencer_id = $fencer->getKey(); // exists check is done in validator

            // just copy all the preferences, we already validated that only supported values are inside
            // We run over all supported settings and set/unset them depending on the passed values
            $prefs = $data['follow']['preferences'] ?? null;
            if ($prefs === null) {
                // this is the case when the user just 'follows' someone, without prior settings
                $user = Auth::user();
                if (isset($user->preferences) && isset($user->preferences['account']) && isset($user->preferences['account']['following'])) {
                    $prefs = Auth::user()->preferences['account']['following'];
                }
            }
            if ($prefs === null) {
                // just enable default preferences
                $prefs = ['handout', 'ranking', 'result', 'register'];
            }
            \Log::debug("setting preferences based on " . json_encode($prefs));
            foreach (FollowModel::$allowedUserSettings as $setting) {
                $this->model->setPreference($setting, in_array($setting, $prefs));
            }
        }
        return $this->model;
    }

    protected function postProcess()
    {
        \Log::debug("post processing follow request");
        // handle the special 'unfollow' preference
        if (!empty($this->model)) {
            if (in_array('unfollow', array_keys($this->model->preferences))) {
                \Log::debug("unfollow found, deleting model");
                // no need to dispatch the feed creation in a job, it only concerns two users at the most
                FollowEvent::dispatch($this->model->fencer, $this->model->user, true);
                $this->model->delete();
            }
            else {
                \Log::debug("saving model");
                // no need to dispatch the feed creation in a job, it only concerns two users at the most
                FollowEvent::dispatch($this->model->fencer, $this->model->user, false);
                $this->model->save();
            }
        }
    }
}
