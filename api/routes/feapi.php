<?php

// Special unauthenticated routes
// These will probably disappear at some point
Route::group(
    [
        'prefix' => '/fe',
    ],
    function () {
        Route::get('/{weapon}/{category}', 'Ranking\Get@index')->name('ranking.get');
    }
);

Route::group(
    [
        'prefix' => '/fe',
        'middleware' => 'auth:wp'
    ],
    function () {

        Route::post('/ranking/create', 'FE\CreateRanking@index')->name('fe.ranking.create');

        Route::post('/countries', 'FE\Countries\Index@index')->name('fe.countries.index');
        Route::post('/countries/save', 'FE\Countries\Save@index')->name('fe.countries.save');
        Route::post('/countries/delete', 'FE\Countries\Delete@index')->name('fe.countries.delete');

        Route::post('/fencers/view', 'FE\Fencers\View@index')->name('fe.fencers.view');
        Route::post('/fencers/save', 'FE\Fencers\Save@index')->name('fe.fencers.save');
        Route::post('/fencers/presavecheck', 'FE\Fencers\Presave@index')->name('fe.fencers.presave');
        Route::post('/fencers/delete', 'FE\Fencers\Delete@index')->name('fe.fencers.delete');
        Route::post('/fencers/merge', 'FE\Fencers\Merge@index')->name('fe.fencers.merge');
        Route::post('/fencers/upload', 'FE\Fencers\Upload@index')->name('fe.fencers.upload');
        Route::get('/fencers/{fencerId}/photo', 'Fencers\Photo@index')->name('fe.fencers.photo');
        Route::post('/fencers', 'FE\Fencers\Index@index')->name('fe.fencers.index');

        Route::post('/roletypes', 'FE\RoleTypes\Index@index')->name('fe.roletypes.index');
        Route::post('/roletypes/save', 'FE\RoleTypes\Save@index')->name('fe.roletypes.save');
        Route::post('/roletypes/delete', 'FE\RoleTypes\Delete@index')->name('fe.roletypes.delete');

        Route::post('/roles', 'FE\Roles\Index@index')->name('fe.roles.index');
        Route::post('/roles/save', 'FE\Roles\Save@index')->name('fe.roles.save');
        Route::post('/roles/delete', 'FE\Roles\Delete@index')->name('fe.roles.delete');

        Route::post('/registrars', 'FE\Registrars\Index@index')->name('fe.registrars.index');
        Route::post('/registrars/save', 'FE\Registrars\Save@index')->name('fe.registrars.save');
        Route::post('/registrars/delete', 'FE\Registrars\Delete@index')->name('fe.registrars.delete');

        Route::post('/users', 'FE\Users\Index@index')->name('fe.users.index');
    }
);
