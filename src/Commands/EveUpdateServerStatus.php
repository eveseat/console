<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

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

namespace Seat\Console\Commands;

use Illuminate\Console\Command;
use Seat\Eveapi\Helpers\JobContainer;
use Seat\Eveapi\Traits\JobManager;

/**
 * Class EveUpdateServerStatus
 * @package Seat\Console\Commands
 */
class EveUpdateServerStatus extends Command
{

    use JobManager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eve:update-server-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the EVE Online Server Status';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {

        parent::__construct();

    }

    /**
     * @param \Seat\Eveapi\Helpers\JobContainer $job
     */
    public function handle(JobContainer $job)
    {

        $job->scope = 'Server';
        $job->api = 'ServerStatus';

        $job_id = $this->addUniqueJob(
            'Seat\Eveapi\Jobs\UpdateServerStatus', $job);

        $this->info('Job ' . $job_id . ' dispatched!');
    }
}
