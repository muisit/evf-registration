<?php

Route::group(["namespace" => "App\Http\Controllers"], function () {
    require('auth.php');
    require('events.php');
    require('fencers.php');
    require('registrations.php');
    require('accreditations.php');
    require('templates.php');
    require('codes.php');
});

Route::get('/basic', '\App\Http\Controllers\Basic@index');
