<?php

namespace App\Models\Requests;

use App\Models\Accreditation;
use App\Models\AccreditationDocument as DocumentModel;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTimeImmutable;

class AccreditationDocument extends Base
{
    public function rules(): array
    {
        return [
            'doc.id' => ['nullable', 'integer', 'min:0'],
            'doc.badge' => ['required', 'string', 'size:14'],
            'doc.card' => ['nullable', 'integer', 'min:0', 'max:999'],
            'doc.document' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'doc.fencerId' => ['required', 'integer', 'exists:TD_Fencer,fencer_id'],
            'payload' => ['nullable', 'json'],
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        \Log::debug("authorizing user based on data");
        if (!parent::authorize($user, $data)) {
            return false;
        }

        // the interface should both pass the event id as query parameter and in the form
        $event = request()->get('eventObject');
        if (empty($event) || $event->getKey() != $this->model->accreditation->event_id) {
            return false;
        }
        return true;
    }

    public function createValidator(Request $request)
    {
        $validator = parent::createValidator($request);
        $validator->after(function ($validator) use ($request) {
            $data = $request->only('doc.badge', 'doc.fencerId');
            if (!$this->checkBadge($data)) {
                $validator->errors()->add(
                    'badge',
                    'Badge code incorrect'
                );
            }
            if (!$this->checkFencer($data)) {
                $validator->errors()->add(
                    'fencerId',
                    'Fencer not set correctly'
                );
            }
        });
        return $validator;
    }

    private function checkBadge($data)
    {
        $badge = $data['doc']['badge'] ?? '';
        \Log::debug("checking badge $badge");
        if (strlen($badge) != 14) {
            \Log::debug("badge has wrong size");
            return false;
        }
        $feid = substr($badge, 2, 7);
        if ($this->model->exists) {
            if ($this->model->accreditation->fe_id != $feid) {
                \Log::debug("model has different accreditation");
                return false;
            }
        }
        else {
            $event = request()->get('eventObject');
            if (empty($event)) {
                \Log::debug("event is not set");
                return false;
            }
            $accreditation = Accreditation::where('event_id', $event->getKey())->where('fe_id', $feid)->first();
            if (empty($accreditation)) {
                \Log::debug("accreditation not found");
                return false;
            }
            $this->model->accreditation_id = $accreditation->getKey();
        }
        return true;
    }

    private function checkFencer($data)
    {
        $fencerId = $data['doc']['fencerId'] ?? 0;
        if ($this->model->accreditation->fencer_id != $fencerId) {
            \Log::debug("fencer does not match accreditation");
            return false;
        }
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $doc = $request->get('doc');
        $id = 0;
        if (!empty($doc)) $id = $doc['id'] ?? 0;
        $id = intval($id);

        $model = DocumentModel::find($id);
        if (empty($model)) {
            $model = new DocumentModel();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $this->model->card = isset($data['doc']['card']) ? intval($data['doc']['card']) : $this->model->card;
            $this->model->document_nr = isset($data['doc']['document']) ? intval($data['doc']['document']) : $this->model->document_nr;

            if (isset($data['doc']['payload'])) {
                $payload = (array)$data['doc']['payload'];
                $this->model->payload = array_merge($this->model->payload ?? [], $payload);
            }
        }
        return $this->model;
    }
}
