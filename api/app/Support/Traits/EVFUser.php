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

    public function getAuthRoles(?Event $event = null): array
    {
        return ["user"];
    }

    private $_role_cache = [];
    public function hasRole(string | array $role): bool
    {
        if (count($this->_role_cache) == 0) {
            $this->_role_cache = $this->getAuthRoles();
        }
        if (!is_array($role)) $role = [$role];
        return count(array_intersect($role, $this->_role_cache)) > 0;
    }
}
