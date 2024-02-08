<?php

namespace App\Models\Schemas;

use App\Models\Accreditation;

/**
 * Code model
 *
 * @OA\Schema()
 */
class Code
{
    /**
     * Full code as a string
     *
     * @var string
     * @OA\Property()
     */
    public string $original;

    /**
     * Basic code function
     *
     * @var integer
     * @OA\Property()
     */
    public int $baseFunction;

    /**
     * Additional function for this code
     *
     * @var integer
     * @OA\Property()
     */
    public int $addFunction;

    /**
     * Randomised ID 1
     *
     * @var integer
     * @OA\Property()
     */
    public int $id1;

    /**
     * Randomised ID2
     *
     * @var integer
     * @OA\Property()
     */
    public int $id2;

    /**
     * Validation checksum over additional function, ID 1 and ID 2
     *
     * @var integer
     * @OA\Property()
     */
    public int $validation;

    /**
     * Additional payload
     *
     * @var string
     * @OA\Property()
     */
    public string $payload;

    public function validate()
    {
        \Log::debug("validating " . json_encode($this));
        if ($this->id1 < 101 || $this->id1 > 999) return false;
        if ($this->id2 < 101 || $this->id2 > 999) return false;
        if ($this->baseFunction < 1 || $this->baseFunction > 9) return false;
        if ($this->addFunction < 0 || $this->addFunction > 9) return false;
        if (!is_numeric($this->payload)) return false;
        if (intval($this->payload) < 0 || intval($this->payload) > 9999) return false;
        if ($this->validation < 0 || $this->validation > 9) return false;

        $code = sprintf("%d%d%d%3d%3d%d%s", $this->baseFunction, $this->baseFunction, $this->addFunction, $this->id1, $this->id2, $this->validation, $this->payload);
        // we allow for some padding before and after, depending on the barcode encoding and what the scanner returns
        if (strpos($this->original, $code) === false) {
            return false;
        }
        return $this->validateChecksum(sprintf("%d%3d%3d", $this->addFunction, $this->id1, $this->id2), $this->validation);
    }

    public function validateChecksum($value, $validation)
    {
        $control = Accreditation::createControlDigit($value);
        \Log::debug("checksum $control vs $validation");
        return $control == $validation;
    }

    public static function fromString(string $code)
    {
        \Log::debug("code from string $code");
        if (strlen($code) < 14) {
            \Log::debug("code is too short");
            return false;
        }
        $codeObject = new static();
        $codeObject->original = $code;
        $codeObject->baseFunction = intval($code[0]);
        $codeObject->addFunction = intval($code[2]);
        $codeObject->id1 = intval(substr($code, 3, 3));
        $codeObject->id2 = intval(substr($code, 6, 3));
        $codeObject->validation = intval($code[9]);
        $codeObject->payload = substr($code, 10, 4);

        return $codeObject->validate() ? $codeObject : false;
    }
}
