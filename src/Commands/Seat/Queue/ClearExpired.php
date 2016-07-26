<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Console\Commands\Seat\Queue;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Seat\Eveapi\Models\FailedJob;
use Seat\Eveapi\Models\JobTracking;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

class ClearExpired extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:queue:clear-expired {--H|hours=24 : # hours after which a job if considered expired}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the job queue of jobs older than x amount of hours';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        // Determine the timestamp that we will be
        // comparing job age with. Jobs older than this
        // time will be considered expired.
        $oldest_jobs = Carbon::now()
            ->subHour($this->option('hours'))
            ->toDateTimeString();

        $this->info('Cleaning up job older than ' . $this->option('hours') .
            ' hours. Jobs submitted after ' . $oldest_jobs . ' will be kept.');

        // Clear the jobs older than the determined date that is
        // either Queued and or Working
        JobTracking::whereIn('status', ['Working', 'Queued'])
            ->where('created_at', '<=', $oldest_jobs)
            ->delete();

        $this->info('Cleaning up the failed_jobs table');

        // Truncate the failed_jobs table
        FailedJob::truncate();

        // Analytics
        $this->dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'event')
            ->set('ec', 'admin')
            ->set('ea', 'expired_jobs_clear')
            ->set('el', 'console')
            ->set('ev', $this->option('hours'))))
            ->onQueue('medium'));

    }
}
