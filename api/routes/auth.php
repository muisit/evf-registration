<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(['middleware' => 'throttle:2,1'], function () use ($router) {
    $router->get('/auth/me', ['as' => 'auth.me', 'uses' => 'Auth\Me@index']);
    $router->post('/auth/login', ['as' => 'auth.login', 'uses' => 'Auth\Login@index']);
});