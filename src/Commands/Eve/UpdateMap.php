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

namespace Seat\Console\Commands\Eve;

use Illuminate\Console\Command;
use Seat\Eveapi\Helpers\JobContainer;
use Seat\Eveapi\Jobs\UpdatePublic;
use Seat\Eveapi\Traits\JobManager;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

class UpdateMap extends Command
{

    use JobManager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eve:update-map';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates EVE Map Information';

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
     * @param \Seat\Eveapi\Helpers\JobContainer $job
     *
     * @return mixed
     */
    public function handle(JobContainer $job)
    {

        $job->scope = 'Eve';
        $job->api = 'Map';

        $job_id = $this->addUniqueJob(UpdatePublic::class, $job);

        $this->info('Job ' . $job_id . ' dispatched!');

        // Analytics
        $this->dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'event')
            ->set('ec', 'queues')
            ->set('ea', 'update_map')
            ->set('el', 'console')))
            ->onQueue('medium'));
    }
}
