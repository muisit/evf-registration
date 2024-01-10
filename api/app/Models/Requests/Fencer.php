<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Fencer as FencerModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Fencer extends Base
{
    private bool $canChangePictureState = false;

    public function rules(): array
    {
        return [
            'fencer.id' => ['required', 'int', 'min:0'],
            'fencer.firstName' => ['required','max:45','min:2'],
            'fencer.lastName' => ['required','max:45','min:2'],
            'fencer.countryId' => ['required','exists:TD_Country,country_id'],
            'fencer.gender' => ['required', Rule::in(['M', 'F'])],
            'fencer.dateOfBirth' => ['nullable', 'date_format:Y-m-d', 'before:' . Carbon::now()->subMinutes(1)->toDateString()],
            'fencer.photoStatus' => ['nullable', Rule::in(['N','Y','A','R'])]
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        if ($this->model->exists) {
            $country = Country::where('country_id', $this->model->fencer_country)->first();
        }
        else {
            $country = Country::where('country_id', $data['fencer']['countryId'])->first();
        }

        // country is a required setting for the fencer. It must be a viewable country and if
        // there is a country set, it must match that of the fencer
        // This restricts HoD to saving fencers only to their specific country
        if (
               empty($country)
            || !$user->can('view', $country)
            || !in_array(request()->get('countryObject')?->getKey(), [null, $country->getKey()])
        ) {
            $this->controller->authorize('not/ever');
            return false;
        }

        $this->canChangePictureState = $user->can('pictureState', FencerModel::class);
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
            $this->model->fencer_firstname = $data['fencer']['firstName'];
            $this->model->fencer_surname = $data['fencer']['lastName'];
            $this->model->fencer_gender = $data['fencer']['gender'];
            $this->model->fencer_country = $data['fencer']['countryId'];
            $this->model->fencer_dob = $data['fencer']['dateOfBirth'] ?? null;

            if ($this->canChangePictureState) {
                $this->model->fencer_picture = $data['fencer']['photoStatus'] ?? null;
            }
        }
        return $this->model;
    }
}
