<?php

namespace Vistik\Apm\Reports;

use Vistik\Apm\Models\Request;

class RequestReports
{

    public function getSlowest(int $count)
    {
        return Request::groupBy(['url', 'method'])
            ->with(['queries' => function ($q) {
                $q->orderBy('time_ms', 'DESC');
            }])
            ->orderBy('response_time_ms', 'DESC')
            ->limit($count)
            ->get();
    }
}