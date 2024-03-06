<?php

namespace App\Providers;

use App\Support\Services\PDFGenerator;
use App\Support\Services\RegistrationCSVService;
use App\Support\Services\RegistrationXMLService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use App\Notifications\JobFailure;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Routing\ResponseFactory;

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
        $this->app->singleton(RegistrationCSVService::class, function (Application $app) {
            return new RegistrationCSVService();
        });
        $this->app->singleton(RegistrationXMLService::class, function (Application $app) {
            return new RegistrationXMLService();
        });
    }

    public function boot()
    {
        DB::listen(function ($query) {
            \Log::debug(
                $query->sql,
                $query->bindings,
                $query->time
            );
        });
        Queue::failing(function (JobFailed $event) {
            \Log::debug("received JobFailed event" . json_encode($event));
            $notification = new JobFailure($event);
            Notification::route('mail', 'webmaster@veteransfencing.eu')->notify($notification);
        });
    }
}
