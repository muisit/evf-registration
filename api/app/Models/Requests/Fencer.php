<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Fencer as FencerModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use App\Support\Services\FencerLabelService;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Fencer extends Base
{
    private bool $canChangePictureState = false;

    public function rules(): array
    {
        $rules = FencerModel::rules();
        return [
            'fencer.id' => $rules['fencer_id'],
            'fencer.firstName' => $rules['fencer_firstname'],
            'fencer.lastName' => $rules['fencer_surname'],
            'fencer.countryId' => $rules['fencer_country'],
            'fencer.gender' => $rules['fencer_gender'],
            'fencer.dateOfBirth' => $rules['fencer_dob'],
            'fencer.photoStatus' => $rules['fencer_picture']
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
            // make sure the labels update along with the model
            $service = new FencerLabelService();
            $service->updateFencer($this->model, $data['fencer']['firstName'], $data['fencer']['lastName']);

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
