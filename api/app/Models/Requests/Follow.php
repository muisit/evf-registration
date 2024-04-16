<?php

namespace App\Models\Requests;

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
    private $allowedSettings = ["blocked", "unfollow", "handout", "checkin", "checkout", "ranking", "result", "register"];

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
            $diff = array_diff($value, $this->allowedSettings);
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
            $id = $data['fencer'] ?? 0;
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
            \Log::debug("updating model");
            // unneeded, but better be sure: reset the device_user_id
            $this->model->device_user_id = Auth::user()->getKey();

            // set the related fencer
            $fencer = Fencer::where('uuid', $data['follow']['fencer'])->first();
            $this->model->fencer_id = $fencer->getKey(); // exists check is done in validator

            // just copy all the preferences, we already validated that only supported values are inside
            // We run over all supported settings and set/unset them depending on the passed values
            $prefs = $data['follow']['preferences'] ?? [];
            if (empty($prefs)) {
                // this is the case when the user just 'follows' someone, without prior settings
                // just enable all preferences
                $prefs = array_diff($this->allowedSettings, ['unfollow']);
            }
            \Log::debug("setting preferences based on " . json_encode($prefs));
            foreach ($this->allowedSettings as $setting) {
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
                $this->model->delete();
            }
            else {
                \Log::debug("saving model");
                $this->model->save();
            }
        }
    }
}
