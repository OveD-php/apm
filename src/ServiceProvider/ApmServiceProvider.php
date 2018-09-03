<?php

namespace OveD\Apm\ServiceProvider;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use OveD\Apm\Commands\CleanUp;
use OveD\Apm\Commands\ShowSlowestRequests;
use OveD\Apm\Filters\Filters;
use OveD\Apm\Listeners\QueryListener;
use OveD\Apm\Request\ApmContext;

class ApmServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/apm.php' => config_path('apm.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanUp::class
            ]);
        }
    }

    public function register()
    {
        $this->app->singleton(ApmContext::class, function () {
            return new ApmContext(
                new Filters(config('apm.filters', []))
            );
        });

        DB::connection()->enableQueryLog();
        Event::listen(QueryExecuted::class, QueryListener::class);
    }
}