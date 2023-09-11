<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(
    [
        'prefix' => '/fencers',
        'middleware' => 'auth'
    ],
    function () use ($router) {
        $router->get(
            '/autocomplete',
            [
                'as' => 'events.ac',
                'uses' => 'Fencers\Autocomplete@index'
            ]
        );
    }
);
