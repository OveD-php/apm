<?php

namespace Vistik\Apm\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];
    protected $table = 'apm_requests';
    protected $dates = ['requested_at'];

    public function queries()
    {
        return $this->hasMany(Query::class, 'request_id', 'uuid');
    }
}
