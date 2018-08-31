<?php

namespace Vistik\Apm\Listeners;

use Vistik\Apm\Request\ApmContext;
use Illuminate\Database\Events\QueryExecuted;

class QueryListener
{
    /**
     * @var ApmContext
     */
    private $apmContext;

    /**
     * Create the event listener.
     *
     * @param ApmContext $apmContext
     */
    public function __construct(ApmContext $apmContext)
    {
        $this->apmContext = $apmContext;
    }

    /**
     * Handle the event.
     *
     * @param QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $this->apmContext->addQuery($event->sql, $event->time, $event->bindings, $event->connectionName);
    }
}
