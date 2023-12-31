<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(
    [
        'prefix' => '/registrations',
        'middleware' => 'auth'
    ],
    function () use ($router) {
        $router->get(
            '/',
            [
                'as' => 'registrations.list',
                'uses' => 'Registrations\Index@index'
            ]
        );

        $router->post(
            '/',
            [
                'as' => 'registrations.save',
                'uses' => 'Registrations\Save@index'
            ]
        );

        $router->post(
            '/delete',
            [
                'as' => 'registrations.delete',
                'uses' => 'Registrations\Delete@index'
            ]
        );

        $router->post(
            '/pay',
            [
                'as' => 'registrations.pay',
                'uses' => 'Registrations\Pay@index'
            ]
        );
    }
);
