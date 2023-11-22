<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\WPUser;
use App\Models\Requests\Base;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Tests\Unit\TestCase;

class BaseTest extends TestCase
{
    public function testCreate()
    {
        $stubController = $this->createStub(Controller::class);
        $stubController->method('validate')->willReturn(['a' => 12]);
        $stubController->method('authorize')->willReturn(true);

        $request = new Base($stubController);

        $stub = $this->createMock(Request::class);
        $stub->expects($this->any())->method('user')->willReturn(new WPUser());
        $stub->expects($this->any())->method('all')->willReturn(['a' => 12]);
        $stub->expects($this->any())->method('only')->willReturn(['a' => 12]);
        $model = $request->validate($stub);

        $this->assertEmpty($model);
    }
}
