<?php

if (! function_exists('prdump')) {
    function prdump($value)
    {
        return print_r($value, true);
    }
}

if (! function_exists('validate_string')) {
    function validate_string($value)
    {
        if ($value === null) {
            return "";
        }
        if (mb_check_encoding($value, 'utf-8') === true) {
            return $value;
        }
        return "";
    }
}

if (! function_exists('validate_int')) {
    function validate_int($value)
    {
        if (empty($value)) {
            return -1;
        }
        if (mb_check_encoding($value, 'utf-8') === true) {
            return intval($value);
        }
        return -1;
    }
}

if (! function_exists('validate_trim')) {
    function validate_trim($value)
    {
        $value = validate_string($value);
        return preg_replace("/(^\s+)|(\s+$)/u", "", $value);
    }
}
    
if (! function_exists('validate_name')) {
    function validate_name($value)
    {
        $value = validate_trim($value);
        // only allow alphanumeric, -, apostrophe and spaces
        return preg_replace("/[^-'\s\p{L}\p{N}]/u", "", $value);
    }
}

if (! function_exists('validate_email')) {
    function validate_email($value)
    {
        $value = validate_trim($value);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        return $value;
    }
}

if (! function_exists('validate_intlist')) {
    function validate_intlist($value)
    {
        if (!is_array($value)) {
            $value = validate_trim($value);
            // see if this is a json encoded list
            $lst = json_decode($value, false);
            if ($lst === false || !is_array($lst)) {
                $lst = explode(",", $value);
            }
            $value = $lst;
        }
        if (!is_array($value)) {
            return [];
        }
        return array_map(fn ($itm) => intval($itm), $value);
    }
}

if (! function_exists('base64_encode_url')) {
    function base64_encode_url($string)
    {
        return str_replace(['+','/','='], ['-','_',''], base64_encode($string));
    }
}

if (! function_exists('base64_decode_url')) {
    function base64_decode_url($string)
    {
        return base64_decode(str_replace(['-','_'], ['+','/'], $string));
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    function csrf_token()
    {
        $session = app('session');

        if (isset($session)) {
            return $session->token();
        }

        throw new RuntimeException('Application session store not set.');
    }
}

if (!function_exists('object_implements')) {
    /**
     * Check that an object recursively implements one or more interfaces
     */
    function object_implements(object $object, string | array $interfaces)
    {
        if (!is_array($interfaces)) {
            $interfaces = [$interfaces];
        }
    
        return count(array_intersect($interfaces, class_uses_recursive(get_class($object)))) > 0;
    }
}