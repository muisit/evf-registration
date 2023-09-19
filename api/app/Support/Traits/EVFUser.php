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
        \Log::debug("testing for " . json_encode($role) . " in " . json_encode($this->_role_cache));
        return count(array_intersect($role, $this->_role_cache)) > 0;
    }

    public function rolesLike(string $rolePart): array
    {
        if (count($this->_role_cache) == 0) {
            $this->_role_cache = $this->getAuthRoles();
        }
        return array_values(array_filter($this->_role_cache, fn ($role) => strpos($role, $rolePart) !== false));
    }
}
