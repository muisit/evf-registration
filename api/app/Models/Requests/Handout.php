<?php

namespace App\Models\Requests;

use App\Models\Accreditation;
use App\Models\AccreditationAudit;
use App\Models\AccreditationDocument as DocumentModel;
use App\Models\Role;
use App\Events\AccreditationHandoutEvent;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTimeImmutable;

class Handout extends Base
{
    public function rules(): array
    {
        return [
            'badge' => ['required', 'string', 'size:14'],
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!empty($this->model)) {
            // handout is a status update allowed for organiser and accreditors
            $this->controller->authorize('update', $this->model);
        }
        else {
            return false;
        }

        // the interface should both pass the event id as query parameter and in the form
        $event = request()->get('eventObject');
        if (empty($event) || $event->getKey() != $this->model->event_id) {
            return false;
        }
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $badge = $request->get('badge');
        $event = request()->get('eventObject');
        if (empty($event)) {
            return null;
        }
        $feid = substr($badge, 2, 7);
        $accreditation = Accreditation::where('event_id', $event->getKey())->where('fe_id', $feid)->first();
        if (empty($accreditation)) {
            return null;
        }
        return $accreditation;
    }

    protected function postProcess()
    {
        if (!empty($this->model)) {
            \Log::debug("Handout Request: sending handout event");
            AccreditationAudit::createFromAction("handout", $this->model, []);
            AccreditationHandoutEvent::dispatch($this->model);
        }
    }
}
