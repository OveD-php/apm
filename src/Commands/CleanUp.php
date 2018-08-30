<?php

namespace Vistik\Apm\Commands;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Console\Command;
use Vistik\Apm\Query;
use Vistik\Apm\Request;

class CleanUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apm:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old request log entries permanently.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = config('apm.keepRecordsForDays');
        $deleteOlderThan = Carbon::parse('-' . $days . ' days', new DateTimeZone('UTC'));

        Request::where('requested_at', '<', $deleteOlderThan)->delete();
        Query::where('created_at', '<', $deleteOlderThan)->delete();

    }
}
