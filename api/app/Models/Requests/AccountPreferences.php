<?php

namespace App\Models\Requests;

use App\Models\DeviceUser;
use App\Models\Follow as FollowModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTimeImmutable;

class AccountPreferences extends Base
{
    public function rules(): array
    {
        return [
            'following' => ['nullable', 'array', function ($a, $v, $f) {
                return $this->checkPreferences($a, $v, $f);
            }],
            'followers' => ['nullable', 'array', function ($a, $v, $f) {
                return $this->checkPreferences($a, $v, $f);
            }],
        ];
    }

    private function checkPreferences($attribute, $value, $fail)
    {
        \Log::debug("checking preferences " . json_encode($value));
        $value = (array) $value;
        if (!is_array($value)) {
            $fail("Invalid preferences");
        }
        else {
            $diff = array_diff($value, FollowModel::$allowedUserSettings);
            if (count($diff) > 0) {
                $fail("Invalid preferences");
            }
        }
    }

    protected function createModel(Request $request): ?Model
    {
        return $request->user();
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        // every user is allowed to set their own preferences
        return $user instanceof DeviceUser;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            \Log::debug("updating account preferences from " . json_encode($this->model->preferences) . ' with ' . json_encode($data));
            $this->model->preferences = array_merge($this->model->preferences, [
                'account' => [
                    'followers' => $data['followers'] ?? [],
                    'following' => $data['following'] ?? []
                ]
            ]);
        }
        return $this->model;
    }
}
