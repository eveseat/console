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

namespace Seat\Console\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\Alliances\Alliances;
use Seat\Eveapi\Jobs\Alliances\Members;
use Seat\Eveapi\Jobs\Character\Affiliation;
use Seat\Eveapi\Jobs\Character\PublicInfo as PublicInfoJob;
use Seat\Eveapi\Jobs\Market\Prices;
use Seat\Eveapi\Jobs\Sovereignty\Map;
use Seat\Eveapi\Jobs\Sovereignty\Structures;
use Seat\Eveapi\Jobs\Universe\Names;
use Seat\Eveapi\Jobs\Universe\Stations;

/**
 * Class PublicInfo.
 * @package Seat\Console\Commands\Esi\Update
 */
class PublicInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for public information';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Map::dispatch();
        Structures::withChain([new Stations])->dispatch();
        Affiliation::withChain([new Names])->dispatch();
        Alliances::withChain([new Members])->dispatch();
        Prices::dispatch();
        PublicInfoJob::dispatch();
    }
}
