<?php

namespace App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\SideEvent as SideEventModel;
use Illuminate\Database\Eloquent\Model;
use App\Support\Contracts\EVFUser;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTimeImmutable;

class SideEvent extends Base
{
    public function rules(): array
    {
        return [
            'sides.*.id' => ['required', 'integer'], // allow negative values for new events
            'sides.*.title' => ['nullable', 'string', 'max:255'], // allow empty to delete side events
            'sides.*.descriptipon' => ['nullable', 'string'],
            'sides.*.starts' => ['required', 'date_format:Y-m-d'],
            'sides.*.costs' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!parent::authorize($user, $data)) {
            return false;
        }

        if (empty($this->model) || !$this->model->exists) {
            return false;
        }
        return true;
    }

    protected function createModel(Request $request): ?Model
    {
        $model = $request->get('eventObject');
        if (empty($model)) {
            // always return a model, but we check for existance in authorize()
            $model = new Event();
        }
        return $model;
    }

    protected function updateModel(array $data): ?Model
    {
        if ($this->model) {
            $sideEvents = collect($this->model->sides)->filter(fn ($e) => $e->competition_id === null)->keyBy('id');

            foreach ($data['sides'] as $newdata) {
                $model = new SideEventModel();
                $model->event_id = $this->model->getKey();
                $model->competition_id = null;

                if ($newdata['id']) {
                    $id = intval($newdata['id']);
                    if (isset($sideEvents[$id])) {
                        $model = $sideEvents[$id];
                    }
                }
                $model->title = $newdata['title'] ?? '';
                $model->description = $newdata['description'] ?? 0;
                $model->costs = $newdata['costs'] ?? null;
                $model->starts = $this->safeDate($newdata['starts'] ?? '');

                if (strlen($model->title) > 0) {
                    $model->save();
                }
                else {
                    $model->delete();
                }
            }
        }
        return $this->model;
    }

    private function safeDate($dt)
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dt);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
        return null;
    }
}
