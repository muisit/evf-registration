<?php

// unauthenticated routes
Route::group(
    ['prefix' => '/fe'],
    function () {
        Route::post('/ranking/list', 'FE\Ranking\Index@index')->name('fe.ranking.index');
        Route::post('/ranking/detail', 'FE\Ranking\Detail@index')->name('fe.detail.index');
        Route::post('/events', 'FE\Events\Index@index')->name('fe.events.index');
        Route::post('/events/competitions', 'FE\Events\Competitions@index')->name('fe.events.competitions');
        Route::post('/results/{competitionId}', 'FE\Results\Index@index')->name('fe.results.index')->where('competitionId', '[0-9]+');
        Route::post('/categories', 'FE\Categories@index')->name('fe.categories.index');
        Route::post('/weapons', 'FE\Weapons@index')->name('fe.weapons.index');
    }
);

Route::group(
    [
        'prefix' => '/fe',
        'middleware' => 'auth:wp'
    ],
    function () {
        Route::post('/apidata', 'FE\ApiData@index')->name('fe.apidata');
        Route::post('/ranking/reset', 'FE\Ranking\Reset@index')->name('fe.ranking.reset');

        Route::post('/countries', 'FE\Countries\Index@index')->name('fe.countries.index');
        Route::post('/countries/save', 'FE\Countries\Save@index')->name('fe.countries.save');
        Route::post('/countries/delete', 'FE\Countries\Delete@index')->name('fe.countries.delete');

        Route::post('/events/save', 'FE\Events\Save@index')->name('fe.events.save');
        Route::post('/events/delete', 'FE\Events\Delete@index')->name('fe.events.delete');
        Route::post('/events/ranking', 'FE\Events\Ranking@index')->name('fe.events.ranking');

        Route::post('/results/{competitionId}/save', 'FE\Results\Save@index')->name('fe.results.save')->where('competitionId', '[0-9]+');
        Route::post('/results/{competitionId}/delete', 'FE\Results\Delete@index')->name('fe.results.delete')->where('competitionId', '[0-9]+');
        Route::post('/results/{competitionId}/clear', 'FE\Results\Clear@index')->name('fe.results.clear')->where('competitionId', '[0-9]+');
        Route::post('/results/{competitionId}/recalculate', 'FE\Results\Recalculate@index')->name('fe.results.recalculate')->where('competitionId', '[0-9]+');
        Route::post('/results/{competitionId}/import', 'FE\Results\Import@index')->name('fe.results.import')->where('competitionId', '[0-9]+');
        Route::post('/results/{competitionId}/check', 'FE\Results\Check@index')->name('fe.results.check')->where('competitionId', '[0-9]+');

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

        Route::post('/types', 'FE\EventTypes@index')->name('fe.types.index');
        Route::post('/users', 'FE\Users@index')->name('fe.users.index');

        Route::post('/workflow/upload', 'FE\Workflow\Upload@index')->name('fe.workflow.upload');
        Route::post('/workflow/step', 'FE\Workflow\Step@index')->name('fe.workflow.step');
    }
);

// Special unauthenticated routes
// These will probably disappear at some point
Route::group(
    [
        'prefix' => '/fe',
    ],
    function () {
        Route::get('/ranking/{weapon}/{category}', 'Ranking\Get@index')->name('ranking.get');
    }
);
