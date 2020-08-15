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

namespace Seat\Console\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\Alliances\Alliances;
use Seat\Eveapi\Jobs\Character\CorporationHistory;
use Seat\Eveapi\Jobs\Character\Info as CharacterInfoJob;
use Seat\Eveapi\Jobs\Corporation\AllianceHistory;
use Seat\Eveapi\Jobs\Corporation\Info as CorporationInfoJob;
use Seat\Eveapi\Jobs\Fittings\Insurances;
use Seat\Eveapi\Jobs\Market\Prices;
use Seat\Eveapi\Jobs\Sovereignty\Map;
use Seat\Eveapi\Jobs\Sovereignty\Structures;
use Seat\Eveapi\Jobs\Universe\Names;
use Seat\Eveapi\Jobs\Universe\Stations;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Universe\UniverseStation;

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
     * The requested stations.
     *
     * @var array
     */
    private $requestedStations = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // NPC stations
        CorporationInfo::whereNotIn('home_station_id', UniverseStation::FAKE_STATION_ID)
            ->select('home_station_id')
            ->distinct()
            ->chunk(100, function ($corporations) {
                foreach ($corporations as $corporation) {
                    if (! in_array($corporation->home_station_id, $this->requestedStations))
                        array_push($this->requestedStations, $corporation->home_station_id);
                }
            });

        // corporation assets
        CorporationAsset::where('location_type', 'station')
            ->select('location_id')
            ->distinct()
            ->chunk(100, function ($assets) {
                foreach ($assets as $asset) {
                    if (! in_array($asset->location_id, $this->requestedStations))
                        array_push($this->requestedStations, $asset->location_id);
                }
            });

        Map::dispatch();
        Structures::dispatch();
        Stations::dispatch($this->requestedStations);
        Names::dispatch();
        Alliances::dispatch();
        Prices::dispatch();
        Insurances::dispatch();

        CharacterInfo::doesntHave('refresh_token')->each(function ($character) {
            CharacterInfoJob::dispatch($character->character_id);
            CorporationHistory::dispatch($character->character_id);
        });

        CorporationInfo::all()->each(function ($corporation) {
            CorporationInfoJob::dispatch($corporation->corporation_id);
            AllianceHistory::dispatch($corporation->corporation_id);
        });
    }
}
