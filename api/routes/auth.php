<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(['middleware' => 'throttle:2,1'], function () use ($router) {
    $router->post('/auth/login', ['as' => 'auth.login', 'uses' => 'Auth\Login@index']);
});
$router->get('/auth/me', ['as' => 'auth.me', 'uses' => 'Auth\Me@index']);
$router->get('/auth/logout', ['as' => 'auth.logout', 'uses' => 'Auth\Logout@index']);
