<?php

namespace Vistik\Apm\Listeners;

use Vistik\Apm\Request\ApmContext;
use Illuminate\Database\Events\QueryExecuted;

class QueryListener
{
    /**
     * @var ApmContext
     */
    private $requestContext;

    /**
     * Create the event listener.
     *
     * @param ApmContext $requestContext
     */
    public function __construct(ApmContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * Handle the event.
     *
     * @param QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $this->requestContext->addQuery($event->sql, $event->time, $event->bindings, $event->connectionName);
    }
}
