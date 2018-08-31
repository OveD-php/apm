<?php

namespace Vistik\Apm\Reports;

use Vistik\Apm\Models\Query;

class QueryReports
{

    public function getTopSlowest(int $count)
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