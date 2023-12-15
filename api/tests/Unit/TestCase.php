<?php

namespace Tests\Unit;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Session;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Support\Data\Fixture;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        Fixture::clear();
        parent::setUp();
        if (method_exists($this, 'fixtures')) {
            $this->fixtures();
        }
        $request = request();
        app()->instance('request', $request);
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../../bootstrap/app.php';
    }

    public function session(array $data)
    {
        $this->startSession();

        foreach ($data as $key => $value) {
            $this->app['session']->put($key, $value);
        }

        return $this;
    }
    protected function startSession()
    {
        if (! $this->app['session']->isStarted()) {
            $this->app['session']->start();
        }

        return $this;
    }
    public function flushSession()
    {
        $this->startSession();

        $this->app['session']->flush();

        return $this;
    }

    public function assertOk()
    {
        return $this->assertStatus(200);
    }

    public function assertStatus($code)
    {
        if (!empty($this->response)) {
            $this->assertEquals($code, $this->response->getStatusCode());
        }

        return $this;
    }

    public function assertHeader($key, $value)
    {
        if (!empty($this->response)) {
            $this->assertTrue($this->response->headers->has($key));
            $header = $this->response->headers->get($key);
            $this->assertEquals($value, $header);
        }

        return $this;
    }
}
