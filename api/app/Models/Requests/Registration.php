<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Fencer;
use App\Models\Event;
use App\Models\SideEvent;
use App\Models\Registration as BaseModel;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Registration extends Base
{
    public function rules(): array
    {
        return [
            'registration.id' => ['nullable', 'int', 'min:0'],
            'registration.fencerId' => ['required','exists:TD_Fencer,fencer_id'],
            'registration.eventId' => ['required','exists:TD_Event,event_id'],
            'registration.sideEventId' => ['nullable','exists:TD_Event_Side,id'],
            'registration.roleId' => ['nullable','exists:TD_Role,role_id'],
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        if ($this->model->exists) {
            $country = Country::where('country_id', $this->model->fencer->fencer_country)->first();
        }
        else {
            $fencer = Fencer::where('fencer_id', $data['registration']['fencerId'])->first();
            if (!empty($fencer)) {
                $country = $fencer->country;
            }
        }

        // country is a required setting for the fencer. It must be a viewable country and if
        // there is a country set, it must match that of the fencer of the registrations
        // This restricts HoD to saving registrations only to their specific country
        if (
               empty($country)
            || !$user->can('view', $country)
            || !in_array(request()->get('countryObject')?->getKey(), [null, $country->getKey()])
        ) {
            $this->controller->authorize('not/ever');
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
