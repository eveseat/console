<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019, 2020  Leon Jacobs
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

namespace Seat\Console\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\Alliances\Alliances as AlliancesJob;
use Seat\Eveapi\Jobs\Alliances\Info;
use Seat\Eveapi\Jobs\Alliances\Members;
use Seat\Eveapi\Models\Alliances\Alliance;

/**
 * Class Alliances.
 *
 * @package Seat\Console\Commands\Esi\Update
 */
class Alliances extends Command
{
    /**
     * @var string
     */
    protected $signature = 'esi:update:alliances {alliance_ids?* : Optional alliance_ids to update}';

    /**
     * @var string
     */
    protected $description = 'Schedule update jobs for alliances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // collect optional alliance ID from arguments
        $alliance_ids = $this->argument('alliance_ids') ?: [];

        $alliances = Alliance::query();

        // in case at least one ID has been provided, filter alliances on arguments
        if (! empty($alliance_ids))
            $alliances->whereIn('alliance_id', $alliance_ids);

        // loop over alliances and queue detailed jobs
        // if we don't have any alliance registered -> queue a global job to collect them
        if ($alliances->get()->each(function ($alliance) {
            Info::dispatch($alliance->alliance_id);
            Members::dispatch($alliance->alliance_id);
        })->isEmpty() && empty($alliance_ids)) AlliancesJob::dispatch();
    }
}
