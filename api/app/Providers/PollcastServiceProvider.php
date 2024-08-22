<?php

namespace App\Providers;

use SupportPal\Pollcast\ServiceProvider as BaseServiceProvider;

class PollcastServiceProvider extends BaseServiceProvider
{
    protected function loadRoutesFrom($path)
    {
        // pass, do not load routes from the package
    }
}
