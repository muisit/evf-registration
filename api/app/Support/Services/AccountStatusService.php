<?php

namespace App\Support\Services;

use App\Models\DeviceUser;
use App\Models\Device;
use App\Models\Follow;
use App\Models\Schemas\DeviceAccount;
use App\Models\Schemas\FencerPrivate;
use App\Models\Schemas\FencerPublic;
use App\Models\Schemas\Follower;

class AccountStatusService
{
    public function generate($user, $device)
    {
        $retval = new DeviceAccount();
        if ($user instanceof DeviceUser) {
            $retval->id = $user->uuid;
            $retval->device = $device->uuid;
            $retval->email = $user->email ?? '';
            $retval->isVerified = !empty($user->email_verified_at);

            if (!empty($user->fencer)) {
                $retval->fencer = new FencerPrivate($user->fencer);
            }

            // by default our preference options are to follow the
            // following feeds for people we follow: handout, ranking, result, register
            // Symmetrically, we also allow followers only those feeds
            $retval->preferences = [
                'follower' => ['handout', 'ranking', 'result', 'register'],
                'following' => ['handout', 'ranking', 'result', 'register']
            ];
            if (isset($user->preferences['account'])) {
                $retval->preferences = $user->preferences['account'];
            }

            if (!empty($device)) {
                if (!empty($device->verification_sent_at)) {
                    $retval->verificationSent = $device->verification_sent_at;
                }
            }

            $retval->followers = $this->getFollowers($user);
            $retval->following = $this->getFollowing($user);
        }
        \Log::debug("AccountStatus is " . json_encode($retval));
        return $retval;
    }

    private function getFollowers($user): array
    {
        // device-users that follow us
        // Always linked to a user, could be linked to a user.fencer
        $lst = Follow::with(['user', 'user.fencer'])->where('fencer_id', $user->fencer_id ?? 0)->get();
        \Log::debug("followers is " . json_encode($lst));
        $retval = [];
        foreach ($lst as $follower) {
            $retval[] = new Follower($follower, true);
        }
        return $retval;
    }

    private function getFollowing($user): array
    {
        // device-users/fencers that we follow (and that can block us)
        // Always linked to a fencer, could be linked to a fencer.user
        $retval = [];
        foreach (Follow::with(['fencer', 'fencer.user'])->where('device_user_id', $user->getKey())->get() as $follower) {
            $retval[] = new Follower($follower, false);
        }
        return $retval;
    }
}