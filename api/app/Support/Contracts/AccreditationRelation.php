<?php

namespace App\Support\Contracts;

use App\Models\Event;

interface AccreditationRelation
{
    /**
     * Return related accreditations
     *
     * @return string
     */
    public function selectAccreditations(Event $event);
}
