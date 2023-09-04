<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(
    [
        'prefix' => '/events',
        'middleware' => 'auth'
    ],
    function () use ($router) {
        $router->get(
            '/',
            [
                'as' => 'events.list',
                'uses' => 'Events\Index@index'
            ]
        );

        $router->get(
            '/{event}/overview',
            [
                'as' => 'events.overview',
                'uses' => 'Events\Overview@index'
            ]
        );

        $router->get(
            '/{event}/registrations',
            [
                'as' => 'events.registrations',
                'uses' => 'Events\Registrations@index'
            ]
        );
    }
);
