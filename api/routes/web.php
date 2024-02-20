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
require('pollcast.php');

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::get('/', function () {
    return env('APP_VERSION');
});

Route::get('/basic', '\App\Http\Controllers\Basic@index');
