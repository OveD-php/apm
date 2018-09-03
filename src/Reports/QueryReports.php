<?php

namespace OveD\Apm\Reports;

use OveD\Apm\Models\Query;

class QueryReports
{

    public function getSlowest(int $count)
    {
        $queries = Query
            ::with('request')
            ->groupBy('sql')
            ->orderBy('time_ms', 'DESC')
            ->limit($count)
            ->get();

        return $queries;
    }
}