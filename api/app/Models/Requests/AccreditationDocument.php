<?php

namespace App\Models\Requests;

use App\Events\CheckinEvent;
use App\Events\ProcessStartEvent;
use App\Events\ProcessEndEvent;
use App\Events\CheckoutEvent;
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
            'doc.id' => ['required', 'integer', 'min:0'],
            'doc.badge' => ['nullable', 'string', 'size:14'],
            'doc.card' => ['nullable', 'integer', 'min:0', 'max:999'],
            'doc.document' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'doc.fencerId' => ['nullable', 'integer', 'exists:TD_Fencer,fencer_id'],
            'payload' => ['nullable', 'json'],
            'status' => ['nullable', 'string', Rule::in(['C', 'P', 'G', 'E', 'O'])]
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
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
            $data = $request->only('doc.badge', 'doc.fencerId', 'doc.document', 'doc.card');
            if (!$this->model->exists) {
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
                if (!$this->checkCard($data)) {
                    $validator->errors()->add(
                        'card',
                        'Card is in use already'
                    );
                }
                if (!$this->checkDocument($data)) {
                    $validator->errors()->add(
                        'document',
                        'Document is in use already'
                    );
                }
            }
        });
        return $validator;
    }

    private function checkCard($data)
    {
        $event = request()->get('eventObject');
        $card = $data['doc']['card'] ?? '';
        if (!empty($card) && !empty($event)) {
            $docs = DocumentModel::where('card', $card)
                ->joinRelationship('accreditation')
                ->where(Accreditation::tableName() . '.event_id', $event->getKey())
                ->where('status', '<>', DocumentModel::STATUS_CHECKOUT)
                ->count();
            return $docs == 0;
        }
        return true;
    }

    private function checkDocument($data)
    {
        $event = request()->get('eventObject');
        $document = $data['doc']['document'] ?? '';
        if (!empty($document) && !empty($event)) {
            $docs = DocumentModel::where('document_nr', $document)
                ->joinRelationship('accreditation')
                ->where(Accreditation::tableName() . '.event_id', $event->getKey())
                ->count();
            return $docs == 0;
        }
        return true;
    }

    private function checkBadge($data)
    {
        \Log::debug('checking badge');
        $badge = $data['doc']['badge'] ?? '';
        if (strlen($badge) != 14) {
            return false;
        }
        $feid = substr($badge, 2, 7);
        if ($this->model->exists) {
            if ($this->model->accreditation->fe_id != $feid) {
                return false;
            }
        }
        else {
            $event = request()->get('eventObject');
            if (empty($event)) {
                return false;
            }
            $accreditation = Accreditation::where('event_id', $event->getKey())->where('fe_id', $feid)->first();
            if (empty($accreditation)) {
                return false;
            }
            $this->model->accreditation_id = $accreditation->getKey();
        }
        return true;
    }

    private function checkFencer($data)
    {
        $fencerId = $data['doc']['fencerId'] ?? 0;
        if (!isset($this->model) || empty($this->model->accreditation) || $this->model->accreditation->fencer_id != $fencerId) {
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
        \Log::debug("creating model using $id");
        $model = DocumentModel::find($id);
        if (empty($model)) {
            $model = new DocumentModel();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            if (!$this->model->exists) {
                $this->model->status = DocumentModel::STATUS_CREATED;
                $this->model->checkin = Carbon::now()->toDateTimeString();
                $this->model->card = isset($data['doc']['card']) ? intval($data['doc']['card']) : $this->model->card;
                $this->model->document_nr = isset($data['doc']['document']) ? intval($data['doc']['document']) : $this->model->document_nr;
            }

            if (isset($data['doc']['payload'])) {
                $payload = (array)$data['doc']['payload'];
                if (is_array($payload)) {
                    $this->model->payload = array_merge(is_array($this->model->payload) ? $this->model->payload : [], $payload);
                }
            }

            if (isset($data['doc']['status'])) {
                $this->model->status = $data['doc']['status'];
                switch ($this->model->status) {
                    case DocumentModel::STATUS_CREATED:
                        $this->model->process_start = null;
                        $this->model->process_end = null;
                        $this->model->checkout = null;
                        break;
                    case DocumentModel::STATUS_PROCESSING:
                        $this->model->process_start = Carbon::now()->toDateTimeString();
                        $this->model->process_end = null;
                        $this->model->checkout = null;
                        break;
                    case DocumentModel::STATUS_PROCESSED_GOOD:
                    case DocumentModel::STATUS_PROCESSED_ERROR:
                        $this->model->process_end = Carbon::now()->toDateTimeString();
                        $this->model->checkout = null;
                        break;
                    case DocumentModel::STATUS_CHECKOUT:
                        $this->model->checkout = Carbon::now()->toDateTimeString();
                        break;
                }
            }
        }
        return $this->model;
    }

    protected function postProcess()
    {
        parent::postProcess();
        if (!empty($this->model)) {
            switch ($this->model->status) {
                case DocumentModel::STATUS_CREATED:
                    CheckinEvent::dispatch(request()->get('eventObject'), $this->model);
                    break;
                case DocumentModel::STATUS_PROCESSING:
                    ProcessStartEvent::dispatch(request()->get('eventObject'), $this->model);
                    $this->model->process_start = Carbon::now()->toDateTimeString();
                    break;
                case DocumentModel::STATUS_PROCESSED_GOOD:
                case DocumentModel::STATUS_PROCESSED_ERROR:
                    ProcessEndEvent::dispatch(request()->get('eventObject'), $this->model);
                    $this->model->process_end = Carbon::now()->toDateTimeString();
                    break;
                case DocumentModel::STATUS_CHECKOUT:
                    CheckoutEvent::dispatch(request()->get('eventObject'), $this->model);
                    $this->model->checkout = Carbon::now()->toDateTimeString();
                    break;
            }
        }
    }

}
