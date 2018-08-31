<?php

namespace Vistik\Apm\Reports;

use Vistik\Apm\Models\Query;

class QueryReports
{

    public function getTopSlowest(int $count)
    {
        // select * from apm_queries  group by sql order by time_ms DESC limit 5

        $queries = Query::groupBy('sql')
            ->orderBy('time_ms', 'DESC')
            ->limit($count)
            ->get();

        foreach ($queries as $query){
            dump($query->toArray());
        }
    }
}