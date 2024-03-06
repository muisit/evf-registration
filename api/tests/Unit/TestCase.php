<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\Data\Fixture;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        //Fixture::clear();
        parent::setUp();
        //if (method_exists($this, 'fixtures')) {
        //    $this->fixtures();
        //}
        $request = request();
        $this->app->instance('request', $request);
        $this->app->useStoragePath(realpath(__DIR__ . '/../_output'));

        // allow setting the session ID to log in users, outside full requests
        $request->setUserResolver(function ($guard = null) {
            return $this->app->make('auth')->guard($guard)->user();
        });
    }

    /**
     * Creates the application.
     *
     * @return Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function unsetUser()
    {
        return $this->app->make('auth')->guard()->forgetUser();
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
            request()->setLaravelSession($this->app['session']);
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

    public function assertException($callable, $exception)
    {
        $cls = '';
        try {
            $callable();
        }
        catch (\Exception $e) {
            $cls = get_class($e);
            if ($cls != $exception) {
                $this->assertEquals($exception, $cls . ':' . $e->getTraceAsString());
            }
        }
        $this->assertTrue($exception == $cls, "Expected exception $exception");
    }
}
