<?php

namespace Tests\Unit\App\Http\Controllers;

use Tests\Unit\TestCase;

class BasicTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testRoute()
    {
        $response = $this->get('/basic');
        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(count($output['categories']) > 0);
        $this->assertTrue(count($output['roles']) > 0);
        $this->assertTrue(count($output['weapons']) > 0);
        $this->assertTrue(count($output['countries']) > 0);
    }
}
