<?php

namespace Vistik\Apm;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    public $timestamps = false;

    protected $table = 'apm_queries';
    protected $guarded = ['id'];
    protected $dates = ['executed_at'];
}
