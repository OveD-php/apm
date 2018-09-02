<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use OveD\Apm\Sampling\Chance;
use OveD\Apm\ServiceProvider\ApmServiceProvider;

class ApmTestCase extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Define this since running tests in laravel does not set that
        if (!defined('LARAVEL_START')){
            define('LARAVEL_START', microtime(true));
        }

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('apm', [
            'sampler'   => new Chance(100),
        ]);
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ApmServiceProvider::class,
        ];
    }

    /**
     * Migrate the in memory database
     */
    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench']);
    }
}