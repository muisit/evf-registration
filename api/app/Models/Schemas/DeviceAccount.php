<?php

namespace App\Models\Schemas;

use App\Support\Contracts\EVFUser;
use App\Models\DeviceUser;

class DeviceAccount
{
    public string $id;
    public string $device;
    public string $email;
    public bool $isVerified;
    public ?string $verificationSent;
    public FencerPrivate $fencer;
    public array $preferences;

    public array $followers;
    public array $following;
}
