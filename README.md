### apm
APM package for laravel

### Install

git clone locally

add to composer.json:

`"repositories": [{
        "type": "vcs",
        "url": "/path/to/package"
    }],`


`composer require vistik/apm:dev-master`

`php artisan migrate`

add `ApmMiddleware::class` to middleware in kernel.php

Publish config: `php artisan vendor:publish` - select `Provider: Vistik\Apm\ServiceProvider\ApmServiceProvider`

Make a few requests

Look in the database :)
