<?php

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(
    [
        'prefix' => '/templates',
        'middleware' => 'auth'
    ],
    function () use ($router) {
        $router->get(
            '/',
            [
                'as' => 'templates.index',
                'uses' => 'Templates\Index@index'
            ]
        );

        $router->post(
            '/',
            [
                'as' => 'templates.save',
                'uses' => 'Templates\Save@index'
            ]
        );
    }
);
