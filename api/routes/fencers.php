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
            '/{fencerId}/photo',
            [
                'as' => 'fencers.photo',
                'uses' => 'Fencers\Photo@index'
            ]
        );

        $router->get(
            '/{fencerId}/accreditations',
            [
                'as' => 'fencers.accreditations',
                'uses' => 'Fencers\Accreditations@index'
            ]
        );

        $router->post(
            '/{fencerId}/photo',
            [
                'as' => 'fencers.photosave',
                'uses' => 'Fencers\PhotoSave@index'
            ]
        );

        $router->post(
            '/{fencerId}/photostate',
            [
                'as' => 'fencers.photostate',
                'uses' => 'Fencers\PhotoState@index'
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
