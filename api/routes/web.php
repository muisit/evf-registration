<?php

/** @var \Laravel\Lumen\Routing\Router $router */

require('auth.php');
require('events.php');
require('fencers.php');
require('registrations.php');
require('accreditations.php');
require('templates.php');
require('codes.php');

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

$router->get('/', function () use ($router) {
    return env('APP_VERSION');
});

$router->get('/basic', 'Basic@index');
