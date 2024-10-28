<?php

namespace App\Models\Requests;

use App\Models\Audit;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\Follow as FollowModel;
use App\Models\Schemas\FencerPrivate;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTimeImmutable;

class AccountLink extends Base
{
    public $forceCreate = false;

    public function rules(): array
    {
        return [
            'fencer.firstName' => ['required','max:45','min:2'],
            'fencer.lastName' => ['required','max:45','min:2'],
            'fencer.country' => ['required','exists:TD_Country,country_abbr'],
            'fencer.gender' => ['required', Rule::in(['M', 'F'])],
            'fencer.dateOfBirth' => ['required', 'date_format:Y-m-d', 'before:' . Carbon::now()->subMinutes(1)->toDateString()],
            'fencer.photoStatus' => ['nullable', Rule::in(['N','Y','A','R'])],
            'fencer.forceCreate' => ['nullable', Rule::in(['N','Y'])]
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!$user->hasRole('device')) {
            return false;
        }
        return parent::authorize($user, $data);
    }

    protected function createModel(Request $request): ?Model
    {
        // if there is already a linked fencer, use that model
        if (!empty($request->user()->fencer)) {
            $model = $request->user()->fencer;
            $this->forceCreate = true;
        }
        else {
            $data = $request->get('fencer');
            $model = Fencer::where('fencer_surname', $data['lastName'])
                ->joinRelationship('country')
                ->where('fencer_firstname', $data['firstName'])
                ->where('fencer_gender', $data['gender'])
                ->where('fencer_dob', $data['dateOfBirth'])
                ->where(Country::tableName() . '.country_abbr', $data['country'])
                ->first();

            if (empty($model)) {
                \Log::debug("creating new model");
                $model = new Fencer();
            }
            else {
                \Log::debug("model found, setting force create");
                $this->forceCreate = true;
            }
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            \Log::debug("updating fencer model");
            Audit::createFromAction($this->model, "FencerUpdate", new FencerPrivate($this->model), $data['fencer']);

            $this->model->fencer_surname = $data['fencer']['lastName'];
            $this->model->fencer_firstname = $data['fencer']['firstName'];
            $this->model->fencer_gender = $data['fencer']['gender'] == 'M' ? 'M' : 'F';
            $this->model->fencer_dob = $data['fencer']['dateOfBirth'];

            $country = Country::where('country_abbr', $data['fencer']['country'])->first();
            $this->model->fencer_country = $country->getKey(); // must exist due to the rules

            if (!$this->forceCreate && isset($data['fencer']['forceCreate']) && $data['fencer']['forceCreate'] == 'Y') {
                $this->forceCreate = true;
            }
        }
        return $this->model;
    }

    protected function postProcess()
    {
        if (!empty($this->model) && $this->forceCreate) {
            $this->model->save();

            $user = Auth::user();
            if ($user->fencer_id != $this->model->getKey()) {
                Audit::createFromAction($this->model, "AccountLink", $user->fencer_id, $this->model->getKey());
            }
            $user->fencer_id = $this->model->getKey();
            $user->save();
        }
    }
}
