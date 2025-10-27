<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\Validator;

class Model extends BaseModel
{
    public static function tableName()
    {
        return with(new static())->getTable();
    }

    public static function rules()
    {
        return [];
    }

    public function validator($rules = null)
    {
        $data = [];
        $rules = $rules ?? self::rules();
        foreach ($rules as $key => $rule) {
            $data[$key] = $this->${$key};
        }
        return Validator::make($data, $rules);
    }

    public function validate($rules = null)
    {
        return !(self::validator($rules))->fails();
    }
}
