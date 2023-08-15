<?php

namespace Tests\Unit\App\Support;

use App\Models\Event;
use App\Support\Traits\EVFUser;
use Tests\Unit\TestCase;

class EVFUserTest extends TestCase
{
    use EVFUser;

    public string $name;

    public function testAuthName()
    {
        $this->name = 'Aøïter';
        $this->assertEquals('Aøïter', $this->getAuthName());
    }

    public function testSessionName()
    {
        $this->assertEquals('evfusertest', $this->getAuthSessionName());
    }

    public function testRoles()
    {
        $this->assertEquals(['user'], $this->getAuthRoles(new Event()));
    }
}
