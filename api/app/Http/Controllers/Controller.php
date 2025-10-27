<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function getOrigin()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) return $_SERVER['HTTP_ORIGIN'];
        if (isset($_SERVER['HTTP_HOST'])) return $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER['HTTP_HOST'];
        return '*';
    }
}
