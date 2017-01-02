<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Console\Commands\Eve;

use Illuminate\Console\Command;
use Seat\Eveapi\Helpers\JobPayloadContainer;
use Seat\Eveapi\Jobs\UpdatePublic;
use Seat\Eveapi\Traits\JobManager;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

/**
 * Class UpdateServerStatus.
 * @package Seat\Console\Commands
 */
class UpdateServerStatus extends Command
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
     */
    public function __construct()
    {

        parent::__construct();

    }

    /**
     * @param \Seat\Eveapi\Helpers\JobPayloadContainer $job
     */
    public function handle(JobPayloadContainer $job)
    {

        $job->scope = 'Server';
        $job->api = 'Server';
        $job->queue = 'high';

        $job_id = $this->addUniqueJob(UpdatePublic::class, $job);

        $this->info('Job ' . $job_id . ' dispatched!');

        // Analytics
        dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'event')
            ->set('ec', 'queues')
            ->set('ea', 'update_server_status')
            ->set('el', 'console')))
            ->onQueue('medium'));
    }
}
