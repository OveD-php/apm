<?php

namespace OveD\Apm\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use OveD\Apm\Request\ApmContext;

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
