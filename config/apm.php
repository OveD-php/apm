<?php

use Vistik\Apm\Sampling\AlwaysOn;

return [
    /*
      |--------------------------------------------------------------------------
      | Save the requestedAt attributes on requests in this timezone. Default: UTC
      |--------------------------------------------------------------------------
    */
    'timezone' => 'UTC',

    /*
      |--------------------------------------------------------------------------
      | If you get alot of requests log logging could take up alot of disk space
      | sampling enables you to record e.g. 10% of the requests
      | Valid values: 0 (off) to 100 (always on)
      |--------------------------------------------------------------------------
    */
    'sampling' => new AlwaysOn(),

    /*
      |--------------------------------------------------------------------------
      | Check if queries are full scan queries. Default: false
      |--------------------------------------------------------------------------
    */
    'trackFullScanQueries' => false,

    /*
      |--------------------------------------------------------------------------
      | This will save the full sql's including values (password etc). Default: false
      |--------------------------------------------------------------------------
    */
    'showBindings' => false,

    /*
      |--------------------------------------------------------------------------
      | Log database queries to log file in addition to the database. Default: false
      |--------------------------------------------------------------------------
    */
    'saveQueriesToLog' => false,

    /*
      |--------------------------------------------------------------------------
      | Log HTTP requests to log file in addition to the database. Default: false
      |--------------------------------------------------------------------------
    */
    'saveRequestsToLog' => false,

    /*
      |--------------------------------------------------------------------------
      | Clean up database after x days (both request & queries)
      |--------------------------------------------------------------------------
    */
    'keepLogFor' => 30,
];