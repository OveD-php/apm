<?php

namespace Vistik\Apm\Models;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $guarded = ['id'];
    protected $table = 'apm_queries';

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id', 'uuid');
    }
}
