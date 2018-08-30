<?php

namespace Vistik\Apm;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $table = 'apm_queries';
    protected $guarded = ['id'];
}
