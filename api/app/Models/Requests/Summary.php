<?php

namespace App\Models\Requests;

use App\Models\AccreditationTemplate;
use App\Models\Country;
use App\Models\Event;
use App\Models\Role;
use App\Models\SideEvent;
use App\Support\Contracts\EVFUser;
use App\Support\Services\PDFService;
use App\Jobs\SetupSummary;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Summary extends Base
{
    private $job = null;

    public function rules(): array
    {
        return [
            'summary.typeId' => ['required', 'int'],
            'summary.type' => ['nullable', Rule::in(['Country','Role', 'Template', 'Event'])],
        ];
    }

    public function createValidator(Request $request)
    {
        $validator = parent::createValidator($request);
        $validator->after(function ($validator) use ($request) {
            $data = $request->only('summary.typeId', 'summary.type');
            if (!$this->checkModel($data)) {
                $validator->errors()->add(
                    'typeId',
                    'Invalid type id'
                );
            }
        });
        return $validator;
    }

    private function checkModel($data)
    {
        if (
            isset($data['summary'])
            && isset($data['summary']['typeId'])
            && isset($data['summary']['type'])
        ) {
            $model = PDFService::modelFactory($data['summary']['type'], intval($data['summary']['typeId']));
            if (!empty($model)) {
                return true;
            }
        }
        return false;
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        $event = request()->get('eventObject');
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            return false;
        }
        $this->controller->authorize('accredit', $event);
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        return new Role();
    }


    protected function updateModel(array $data): ?Model
    {
        $this->job = new SetupSummary(request()->get('eventObject'), $data['summary']['type'], intval($data['summary']['typeId']));
        return new Role();
    }

    protected function postProcess()
    {
        if (!empty($this->job)) {
            dispatch($this->job);
        }
    }
}
