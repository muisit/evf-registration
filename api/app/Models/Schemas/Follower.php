<?php

namespace App\Models\Schemas;

use App\Models\Follow;
use App\Models\Schemas\FencerPublic;

class Follower
{
    public ?FencerPublic $fencer;
    public ?string $user;
    public array $preferences;

    public function __construct(Follow $model, bool $isFollower)
    {
        if ($isFollower) {
            // users that follow us, which may not be linked to a
            // fencer, but always to a device user
            $this->user = $model->user->uuid;
            if (!empty($model->user->fencer)) {
                $this->fencer = new FencerPublic($model->user->fencer);
            }
        }
        else {
            // fencers we follow, which may not be linked to
            // a device user, but always to a fencer
            $this->user = $model->fencer->user?->uuid ?? '';
            $this->fencer = new FencerPublic($model->fencer);
        }
        $this->preferences = array_keys($model->preferences);
    }
}
