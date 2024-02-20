<?php

use SupportPal\Pollcast\Http\Middleware\VerifySocketId;

Route::group([
    'prefix' => 'pollcast',
    'namespace' => '\SupportPal\Pollcast\Http\Controller'
], function () {
    Route::post('connect', ['ChannelController@connect'])->name('supportpal.pollcast.connect');

    Route::group(['middleware' => [VerifySocketId::class, 'auth']], function () {
        Route::post('channel/subscribe', 'ChannelController@subscribe')->name('supportpal.pollcast.subscribe');
        Route::post('channel/unsubscribe', 'ChannelController@unsubscribe')->name('supportpal.pollcast.unsubscribe');
        Route::post('subscribe/messages', 'SubscriptionController@messages')->name('supportpal.pollcast.receive');
        Route::post('publish', 'PublishController@publish')->name('supportpal.pollcast.publish');
    });
});
