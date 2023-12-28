<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Model;
use Tests\Support\Data\Fixture;

$app = require __DIR__ . '/../../bootstrap/app.php';
Artisan::call("migrate");
Artisan::call("db:seed", ["--class" => "TestSeeder"]);
Model::setConnectionResolver($app['db']);
Fixture::loadAll();
