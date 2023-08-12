<?php

namespace App\Support\Traits;

use App\Models\Event;

trait EVFUser {
    public function getAuthName(): string
    {
        return $this->name ?? '';
    }

    public function getAuthSessionName(): string
    {
        $els = explode('\\', get_class($this));
        return strtolower(end($els));
    }

    public function getAuthRoles(Event $event): array
    {
        return ["user"];
    }
}
