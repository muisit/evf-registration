<?php

namespace App\Models\Requests;

use App\Models\Event;
use App\Models\Schemas\Code;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Codes extends Base
{
    public array $codes = [];
    public string $action;

    public function rules(): array
    {
        return [
            'codes.*.original' => ['required', 'string', 'size:14'],
            'codes.*.baseFunction' => ['required', 'integer', 'min:0', 'max:9'],
            'codes.*.addFunction' => ['required', 'integer', 'min:0', 'max:9'],
            'codes.*.id1' => ['required', 'integer', 'min:101', 'max:999'],
            'codes.*.id2' => ['required', 'integer', 'min:101', 'max:999'],
            'codes.*.validation' => ['required', 'integer'],
            'codes.*.payload' => ['required', 'string'],
            'action' => ['required','max:50'],
        ];
    }


    public function validate(Request $request): ?Model
    {
        $this->model = $this->createModel($request);
        $validator = $this->createValidator($request);
        if ($validator->fails()) {
            throw new ValidationException(
                $validator,
                new JsonResponse($validator->errors()->getMessages(), 422)
            );
        }

        $data = $this->extractInputFromRules($request);
        // no authorization, we do that in the CodeServices
        $model = $this->updateModel($data);
        // $this->postProcess(); // no postprocessing, we do not adjust the model

        return $this->model;
    }

    protected function createModel(Request $request): ?Model
    {
        // if we are logged out and do not have an event query parameter to start, there is no event object (yet)
        return $request->get('eventObject') ?? new Event();
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            foreach ($data['codes'] as $code) {
                $schema = new Code();
                $schema->original = $code['original'] ?? '';
                $schema->baseFunction = $code['baseFunction'] ?? 0;
                $schema->addFunction = $code['addFunction'] ?? 0;
                $schema->id1 = $code['id1'] ?? 0;
                $schema->id2 = $code['id2'] ?? 0;
                $schema->validation = $code['validation'] ?? 0;
                $schema->payload = $code['payload'] ?? 0;

                if ($schema->validate()) {
                    $this->codes[] = $schema;
                }
            }
            $this->action = $data['action'];
        }
        return $this->model;
    }
}
