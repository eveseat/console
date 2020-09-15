<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Console\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Eveapi\Models\FailedJob;
use Seat\Eveapi\Models\Status\EsiStatus;
use Seat\Eveapi\Models\Status\ServerStatus;

/**
 * Class Maintenance.
 * @package Seat\Console\Jobs
 */
class Maintenance implements ShouldQueue
{

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Perform the maintenance job.
     */
    public function handle()
    {

        $this->cleanup_tables();
    }

    /**
     * Partially truncates tables that typically contain
     * a lot of data.
     */
    public function cleanup_tables()
    {

        logger()->info('Performing table maintenance');

        // Prune the failed jobs table
        Failed_Jobs::where('id', '<', (Failed_Jobs::max('id') - 100))->delete();

        // Prune the server statuses older than a week.
        ServerStatus::where('created_at', '<', carbon('now')->subWeek(1))->delete();

        // Prune ESI statuses older than a week
        EsiStatus::where('created_at', '<', carbon('now')->subWeek(1))->delete();
    }
}
