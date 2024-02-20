<?php

Route::group(
    [
        'prefix' => '/templates',
        'middleware' => 'auth'
    ],
    function () {
        Route::get(
            '/',
            'Templates\Index@index'
        )->name('templates.index');

        Route::get(
            '/fonts',
            'Templates\Fonts@index'
        )->name('templates.fonts');

        Route::post(
            '/',
            'Templates\Save@index'
        )->name('templates.save');

        Route::post(
            '/remove',
            'Templates\Remove@index'
        )->name('templates.remove');

        Route::get(
            '/{templateId}/picture/{pictureId}',
            'Templates\Picture@index'
        )->name('templates.picture');

        Route::post(
            '/{templateId}/picture',
            'Templates\PictureSave@index'
        )->name('templates.picturesave');

        Route::post(
            '/{templateId}/picture/{pictureId}/remove',
            'Templates\PictureDelete@index'
        )->name('templates.picturedelete');

        Route::get(
            '/{templateId}/print',
            'Templates\Example@index'
        )->name('templates.print');
    }
);
