<?php

namespace Tests\Unit\App\Http\Controllers;

use Tests\Unit\TestCase;

class IndexTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testRoute()
    {
        $response = $this->get('/');

        $this->assertEquals(
            env('APP_VERSION'),
            $response->getContent()
        );
    }
}
