<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(
    [
        'prefix' => '/accreditations',
        'middleware' => 'auth'
    ],
    function () use ($router) {
        $router->get(
            '/{accreditationId}/badge',
            [
                'as' => 'accreditations.badge',
                'uses' => 'Accreditations\Badge@index'
            ]
        );
    }
);
