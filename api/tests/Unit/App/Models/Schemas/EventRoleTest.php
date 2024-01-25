<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\EventRole as Model;
use App\Models\Schemas\EventRole as Schema;
use Tests\Unit\TestCase;

class EventRoleTest extends TestCase
{
    public function testCreate()
    {
        $model = new Model();
        $model->id = 1;
        $model->user_id = 12;
        $model->role_type = 'abracadabra';
        $schema = new Schema($model);
        $this->assertEquals(1, $schema->id);
        $this->assertEquals(12, $schema->userId);
        $this->assertEquals("abracadabra", $schema->role);
    }
}
