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
