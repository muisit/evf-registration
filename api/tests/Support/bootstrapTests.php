<?php

use Illuminate\Support\Facades\Artisan;

$app = require __DIR__ . '/../../bootstrap/app.php';
Artisan::call("migrate");
Artisan::call("db:seed", ["--class" => "TestSeeder"]);
