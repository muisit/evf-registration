<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Audit extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array'
    ];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->created_at = Carbon::now()->toDateTimeString();
        });
    }

    public static function createFromAction($model, $action, $oldvalues, $newvalues, $user = null)
    {
        $obj = new static();
        $obj->model_id = $model ? $model->getKey() : null;
        $obj->model_type = $model ? get_class($model) : '';
        $obj->action = $action;
        $obj->payload = [
            'old' => json_encode($oldvalues),
            'new' => json_encode($newvalues)
        ];
        $auditUser = $user ?? Auth::user();
        $obj->created_by = $user ? $user->getKey() : null;
        $obj->created_by_type = $user ? get_class($user) : '';
        $obj->save();
    }
}
