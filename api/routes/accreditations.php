<?php

Route::group(
    [
        'prefix' => '/accreditations',
        'middleware' => 'auth'
    ],
    function () {
        Route::get(
            '/{fencerId}/badge/{templateId}',
            'Accreditations\Badge@index'
        )->name('accreditations.badge');

        Route::get(
            '/overview',
            'Accreditations\Overview@index'
        )->name('accreditations.overview');
   
        Route::get(
            '/regenerate',
            'Accreditations\Regenerate@index'
        )->name('accreditations.regenerate');

        Route::get(
            '/summary/{summaryId}',
            'Accreditations\Download@index'
        )->name('accreditations.download');

        Route::post(
            '/summary',
            'Accreditations\Summary@index'
        )->name('accreditations.summary');

        Route::post(
            '/document',
            'Accreditations\SaveDocument@index'
        )->name('accreditations.document');

        Route::post(
            '/handout',
            'Accreditations\Handout@index'
        )->name('accreditations.handout');

        Route::get(
            '/documents',
            'Accreditations\Documents@index'
        )->name('accreditations.documents');

        Route::get(
            '/statistics',
            'Accreditations\Statistics@index'
        )->name('accreditations.statistics');
    }
);
