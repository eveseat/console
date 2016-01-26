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

use Illuminate\Console\Command;
use Seat\Services\Data\Queue;

/**
 * Class Status
 * @package Seat\Console\Commands\Seat\Queue
 */
class Status extends Command
{

    use Queue;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:queue:status {--live : Follow the progress live}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the job queue status';

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

        if ($this->option('live')) {

            $this->info('Live Progress:');
            $summaries = $this->count_summary();
            $bar = $this->new_bar($summaries);

            // Continuously update a progress bar with
            // the current progress. If we a change in the
            // total number of jobs, redraw the bar.
            while (($summaries['total_jobs'] - $summaries['queued_jobs']) > 0) {

                $new_summaries = $this->count_summary();
                if ($new_summaries['total_jobs'] <> $summaries['total_jobs'])
                    $bar = $this->new_bar($new_summaries);

                // Set the values to $summaries for
                // the while loop to eval
                $summaries = $new_summaries;

                $bar->setMessage(
                    '[Working: ' . $summaries['working_jobs'] .
                    ' | Done: ' . $summaries['done_jobs'] .
                    ' | Error: ' . $summaries['error_jobs'] .
                    '] Total:'
                );
                $bar->setProgress($summaries['total_jobs'] - $summaries['queued_jobs']);

                // Sleep for a second and update again.
                sleep(2);
            }

            return;
        }

        // Just throw a summary table of the jobs
        $summaries = $this->count_summary();
        $this->table(['Total', 'Working', 'Done', 'Error'], [
            [
                $summaries['total_jobs'],
                $summaries['working_jobs'],
                $summaries['done_jobs'],
                $summaries['error_jobs']
            ]
        ]);

    }

    /**
     * @param $summaries
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    public function new_bar($summaries)
    {

        $bar = $this->output->createProgressBar($summaries['total_jobs']);
        $bar->setFormat(
            '%message% %current%/%max% [%bar%] %percent:3s%%');

        return $bar;
    }

}
