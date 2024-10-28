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

class AccountSave extends Base
{
    public function rules(): array
    {
        return [
            'language' => ['string', 'nullable', 'max:20'],
        ];
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
            $this->model->preferences = array_merge($this->model->preferences, [
                'account' => [
                    'language' => $data['language'] ?? 'en_GB',
                ]
            ]);
        }
        return $this->model;
    }
}
