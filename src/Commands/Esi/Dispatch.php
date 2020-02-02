<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

namespace Seat\Console\Commands\Esi;

use Illuminate\Console\Command;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Dispatch.
 *
 * @package Seat\Console\Commands\Esi
 */
class Dispatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:job:dispatch {job_class} {character_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches an ESI update job class for a specific character id.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        if (is_null($this->argument('character_id')))
            $this->warn('No character id specified. Using null token.');
        else
            $refresh_token = RefreshToken::findOrFail($this->argument('character_id'));

        $this->argument('job_class')::dispatch($refresh_token ?? null);
        $this->info('Job dispatched!');
    }
}
