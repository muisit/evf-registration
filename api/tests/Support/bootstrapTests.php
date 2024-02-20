<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Console\Kernel;
use Tests\Support\Data\Fixture;

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Kernel::class)->bootstrap();
Artisan::call("config:clear"); // make sure we do not use the development configuration, but the testing environment
$kernel = $app->make(Kernel::class)->bootstrap();
Artisan::call("migrate");
Artisan::call("db:seed", ["--class" => "TestSeeder"]);
//Model::setConnectionResolver($app->make('db'));
Fixture::loadAll();
