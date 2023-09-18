<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(
    [
        'prefix' => '/fencers',
        'middleware' => 'auth'
    ],
    function () use ($router) {
        $router->get(
            '/',
            [
                'as' => 'fencers.index',
                'uses' => 'Fencers\Index@index'
            ]
        );

        $router->get(
            '/autocomplete',
            [
                'as' => 'fencers.ac',
                'uses' => 'Fencers\Autocomplete@index'
            ]
        );

        $router->post(
            '/duplicate',
            [
                'as' => 'fencers.duplicate',
                'uses' => 'Fencers\Duplicate@index'
            ]
        );

        $router->post(
            '/',
            [
                'as' => 'fencers.save',
                'uses' => 'Fencers\Save@index'
            ]
        );
    }
);
