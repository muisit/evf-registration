<?php

namespace App\Models\Requests;

use App\Support\Contracts\EVFUser;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Base
{
    protected Controller $controller;
    protected ?Model $model = null;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function validate(Request $request): ?Model
    {
        $this->model = $this->createModel($request);
        $data = $this->controller->validate($request, $this->rules(), $this->messages(), $this->customAttributes());

        if (!$this->authorize($request->user(), $data)) {
            $this->model = null;
        }
        else {
            $model = $this->updateModel($data);

            if (!empty($this->model)) {
                $this->model->save();
            }
        }

        return $this->model;
    }

    protected function authorize(EVFUser $user, array $data): bool
    {
        if (!empty($this->model)) {
            if (!$this->model->exists) {
                $this->controller->authorize('create', get_class($this->model));
            }
            else {
                $this->controller->authorize('update', $this->model);
            }
        }
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    protected function createModel(Request $request): ?Model
    {
        return null;
    }

    protected function updateModel(array $data): ?Model
    {
        return $this->model;
    }

    protected function messages(): array
    {
        return [];
    }

    protected function customAttributes(): array
    {
        return [];
    }
}
