<?php

// Unauthenticated routes
$router->group(
    [
        'prefix' => '/events'
    ],
    function () use ($router) {
        $router->get(
            '/{eventId:[1-9][0-9]*}',
            [
                'as' => 'events.get',
                'uses' => 'Events\Get@index'
            ]
        );
    }
);

// Authenticated routes
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

        $router->post(
            '/',
            [
                'as' => 'events.save',
                'uses' => 'Events\Save@index'
            ]
        );

        $router->post(
            '/config',
            [
                'as' => 'events.saveconfig',
                'uses' => 'Events\SaveConfig@index'
            ]
        );

        $router->post(
            '/sides',
            [
                'as' => 'events.savesides',
                'uses' => 'Events\SaveSides@index'
            ]
        );

        $router->get(
            '/roles',
            [
                'as' => 'events.roles',
                'uses' => 'Events\Roles@index'
            ]
        );

        $router->post(
            '/roles',
            [
                'as' => 'events.saveroles',
                'uses' => 'Events\SaveRoles@index'
            ]
        );

        $router->get(
            '/overview',
            [
                'as' => 'events.overview',
                'uses' => 'Events\Overview@index'
            ]
        );

        $router->get(
            '/statistics',
            [
                'as' => 'events.statistics',
                'uses' => 'Events\Statistics@index'
            ]
        );

        $router->get(
            '/generate',
            [
                'as' => 'events.generate',
                'uses' => 'Events\Generate@index'
            ]
        );

        $router->get(
            '/xml/{sideEventId}',
            [
                'as' => 'events.downloadxml',
                'uses' => 'Events\Download@asXML'
            ]
        );

        $router->get(
            '/csv/{sideEventId}',
            [
                'as' => 'events.downloadcsv',
                'uses' => 'Events\Download@asCSV'
            ]
        );
    }
);
