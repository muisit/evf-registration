<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Fencer as FencerModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FencerPhoto extends Base
{
    private bool $canChangePictureState = false;

    public function rules(): array
    {
        return [
            'fencer.id' => ['required', 'int', 'min:0'],
            'fencer.photoStatus' => ['nullable', Rule::in(['N','Y','A','R'])]
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        $this->canChangePictureState = $user->can('pictureState', Fencer::class);
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $fencer = $request->get('fencer');
        $id = 0;
        if (!empty($fencer)) $id = $fencer['id'] ?? 0;
        $id = intval($id);

        $model = FencerModel::where('fencer_id', $id)->first();
        if (empty($model)) {
            $model = new FencerModel();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $this->model->fencer_picture = $data['fencer']['photoStatus'] ?? null;
        }
        return $this->model;
    }
}
