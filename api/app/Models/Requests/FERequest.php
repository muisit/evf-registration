<?php

namespace App\Models\Requests;

use App\Support\Contracts\EVFUser;

class FERequest extends Base
{
    public function rules(): array
    {
        return [
            'path' => ['required', 'string'],
            'nonce' => ['required','string'],
            'model' => ['required']
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        \LOg::debug("authorizing FE request");
        if (!parent::authorize($user, $data)) {
            \Log::debug("parent auth fails for " . json_encode($user) . " and " . json_encode($data));
            return false;
        }
        // only allowed for FE users (sysops)
        if (!$user->hasRole('sysop')) {
            \Log::debug("user has no sysop role");
            $this->controller->authorize('not/ever');
            return false;
        }
        return true;
    }
}
