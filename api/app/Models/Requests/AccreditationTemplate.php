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
            'template.copy' => ['nullable', 'boolean']
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
            if (isset($data['template']['copy']) && $data['template']['copy']) {
                $this->model = $this->copyModel($this->model);
            }
            else {
                $this->model->name = $data['template']['name'];
                // convert to json back and forth to ensure a valid json entry
                $this->model->content = json_encode(json_decode($data['template']['content']));
                $this->model->is_default = $data['template']['isDefault'];

                if ($this->model->event_id === null) {
                    $event = request()->get('eventObject');
                    if (!empty($event)) {
                        $this->model->event_id = $event->getKey();
                    }
                }
            }
        }
        return $this->model;
    }

    private function copyModel(TemplateModel $model): TemplateModel
    {
        $retval = new TemplateModel();
        $retval->name = $model->name . ' (copy)';
        $event = request()->get('eventObject');
        $retval->event_id = $event?->getKey() ?? null;
        $retval->is_default = 'N';

        $content = json_decode($model->content);
        if (isset($content->pictures)) {
            $content->pictures = collect($content->pictures)->map(function ($picture) use ($model, $retval) {
                $path = $model->image($picture->file_id, $picture->file_ext);
                if (file_exists($path)) {
                    $copypath = $retval->image($picture->file_id, $picture->file_ext);
                    @copy($path, $copypath);
                    if (file_exists($copypath)) {
                        return $picture;
                    }
                }
                return null;
            })->filter(fn ($el) => $el !== null);
        }
        $retval->content = json_encode($content);
        return $retval;
    }
}
