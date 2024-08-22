<?php

namespace App\Providers;

use Illuminate\Events\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\AccreditationHandoutEvent::class => [\App\Listeners\SendHandoutFeed::class],
        \App\Events\CheckinEvent::class => [\App\Listeners\SnedCheckinFeed::class],
        \App\Events\CheckoutEvent::class => [\App\Listeners\SendCheckoutFeed::class],
        \App\Events\ProcessEndEvent::class => [\App\Listeners\SendBagFinishedFeed::class],
        \App\Events\ProcessStartEvent::class => [\App\Listeners\SendBagStartFeed::class],
        \App\Events\RankingUpdate::class => [\App\Listeners\SendRankingFeed::class],
        \App\Events\RegisterForEvent::class => [\App\Listeners\SendRegistrationFeed::class],
        \App\Events\ResultUpdate::class => [\App\Listeners\SendResultFeed::class],
        \App\Events\BlockEvent::class => [\App\Listeners\SendBlockFeed::class],
        \App\Events\FollowEvent::class => [\App\Listeners\SendFollowFeed::class]
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
