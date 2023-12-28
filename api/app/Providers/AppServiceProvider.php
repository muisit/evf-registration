<?php

namespace App\Providers;

use App\Support\Services\PDFGenerator;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use App\Notifications\JobFailure;
use Illuminate\Support\Facades\Notification;

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

    public function boot()
    {
        Queue::failing(function (JobFailed $event) {
            $notification = new JobFailure($event);
            Notification::route('mail', 'webmaster@veteransfencing.eu')->notify($notification);
        });
    }
}
