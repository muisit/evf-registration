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

        $router->get(
            '/overview',
            [
                'as' => 'acrreditations.overview',
                'uses' => 'Accreditations\Overview@index'
            ]
        );
   
        $router->get(
            '/regenerate',
            [
                'as' => 'acrreditations.regenerate',
                'uses' => 'Accreditations\Regenerate@index'
            ]
        );

        $router->get(
            '/summary/{summaryId}',
            [
                'as' => 'acrreditations.download',
                'uses' => 'Accreditations\Download@index'
            ]
        );

        $router->post(
            '/summary',
            [
                'as' => 'acrreditations.summary',
                'uses' => 'Accreditations\Summary@index'
            ]
        );
    }
);
