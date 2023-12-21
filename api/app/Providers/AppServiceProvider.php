<?php

namespace App\Providers;

use App\Support\Services\PDFGenerator;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PDFGenerator::class, function (Application $app) {
            return new PDFGenerator();
        });
    }
}
