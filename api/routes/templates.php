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

        $router->get(
            '/fonts',
            [
                'as' => 'templates.fonts',
                'uses' => 'Templates\Fonts@index'
            ]
        );

        $router->post(
            '/',
            [
                'as' => 'templates.save',
                'uses' => 'Templates\Save@index'
            ]
        );

        $router->get(
            '/{templateId}/picture/{pictureId}',
            [
                'as' => 'templates.picture',
                'uses' => 'Templates\Picture@index'
            ]
        );

        $router->post(
            '/{templateId}/picture',
            [
                'as' => 'templates.picturesave',
                'uses' => 'Templates\PictureSave@index'
            ]
        );
        $router->post(
            '/{templateId}/picture/{pictureId}/remove',
            [
                'as' => 'templates.picturedelete',
                'uses' => 'Templates\PictureDelete@index'
            ]
        );
    }
);
