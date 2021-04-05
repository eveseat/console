<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Console\Commands\Seat\Queue;

use Illuminate\Console\Command;
use Laravel\Horizon\Contracts\WorkloadRepository;

/**
 * Class Status.
 * @package Seat\Console\Commands\Seat\Queue
 * @deprecated since 4.8.0 - this class will be removed with no replacement
 */
class Status extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:queue:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the job queue status';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->table(['Name', 'Jobs', 'Avg Wait', '# Processes'],
            collect(resolve(WorkloadRepository::class)->get()));
    }
}
