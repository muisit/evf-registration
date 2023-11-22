<?php
 
namespace App\Support\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

class ValidateTrim implements ValidatorAwareRule, ValidationRule
{
    protected $validator = null;

    public function setValidator(Validator $validator): ValidateTrim
    {
        $this->validator = $validator;
        return $this;
    }
    
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            $this->validator->setValue($attribute, trim($value ?? ''));
        }
    }
}
