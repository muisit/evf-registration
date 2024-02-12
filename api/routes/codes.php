<?php

// Unauthenticated route
$router->group(
    [
        'prefix' => '/codes'
    ],
    function () use ($router) {
        $router->post(
            '/',
            [
                'as' => 'codes.validate',
                'uses' => 'Codes\Validate@index'
            ]
        );
    }
);


// Authenticated routes
$router->group(
    [
        'prefix' => '/codes',
        'middleware' => 'auth'
    ],
    function () use ($router) {
        $router->get(
            '/users',
            [
                'as' => 'codeusers.list',
                'uses' => 'Codes\Users@index'
            ]
        );

        $router->post(
            '/users',
            [
                'as' => 'codeusers.save',
                'uses' => 'Codes\SaveUser@index'
            ]
        );
    }
);
