<?php

namespace App\Models\Requests;

use App\Models\Event;
use App\Models\AccreditationTemplate as TemplateModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccreditationTemplate extends Base
{
    public function rules(): array
    {
        return [
            'template.id' => ['required', 'int', 'min:0'],
            'template.name' => ['required','string', 'max:200', 'min:1'],
            'template.content' => ['required', 'json'],
            //'template.eventId' => ['required','exists:TD_Event,event_id'], // you cannot update the associated event
            'template.isDefault' => ['required', Rule::in(['Y', 'N'])],
        ];
    }

    protected function createModel(Request $request): ?Model
    {
        $modeldata = $request->get('template');
        $id = 0;
        if (!empty($modeldata)) $id = $modeldata['id'] ?? 0;
        $id = intval($id);

        $model = TemplateModel::where('id', $id)->first();
        if (empty($model)) {
            $model = new TemplateModel();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $this->model->name = $data['template']['name'];
            // convert to json back and forth to ensure a valid json entry
            $this->model->content = json_encode(json_decode($data['template']['content']));
            $this->model->is_default = $data['template']['isDefault'];
            //$this->model->event_id = $data['template']['eventId']; // you cannot update the associated event
        }
        return $this->model;
    }
}
