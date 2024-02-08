<?php

namespace App\Models\Requests;

use App\Support\Contracts\EVFUser;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Auth\Access\AuthorizationException;

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
        $validator = $this->createValidator($request);
        if ($validator->fails()) {
            \Log::debug("validator fails" . json_encode($validator->errors()->getMessages()));
            throw new ValidationException(
                $validator,
                new JsonResponse($validator->errors()->getMessages(), 422)
            );
        }

        $data = $this->extractInputFromRules($request);

        if (empty($request->user())) {
            throw new AuthorizationException(); // should never occur, all routes guarded by authenticator
        }
        if (!$request->user() || !$this->authorize($request->user(), $data)) {
            $this->model = null;
        }
        else {
            $model = $this->updateModel($data);
            $this->postProcess();
        }

        return $this->model;
    }

    protected function postProcess()
    {
        if (!empty($this->model)) {
            $this->model->save();
        }
    }

    public function createValidator(Request $request)
    {
        return app('validator')->make($request->all(), $this->rules(), $this->messages(), $this->customAttributes());
    }

    protected function extractInputFromRules(Request $request)
    {
        return $request->only(collect($this->rules())->keys()->map(function ($rule) {
            return Str::contains($rule, '.') ? explode('.', $rule)[0] : $rule;
        })->unique()->toArray());
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
            return true;
        }
        return false;
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
