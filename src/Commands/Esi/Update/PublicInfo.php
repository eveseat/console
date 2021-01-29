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
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Universe\UniverseStation;

/**
 * Class PublicInfo.
 * @package Seat\Console\Commands\Esi\Update
 * @deprecated since 4.7.0 - this will be moved into eveapi package in a near future
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
        // NPC stations using HQ
        CorporationInfo::whereNotIn('home_station_id', UniverseStation::FAKE_STATION_ID)
            ->select('home_station_id')
            ->orderBy('home_station_id')
            ->distinct()
            ->chunk(100, function ($corporations) {
                Stations::dispatch($corporations->pluck('home_station_id')->toArray());
            });

        // NPC stations using character assets
        CharacterAsset::where('location_type', 'station')
            ->select('location_id')
            ->orderBy('location_id')
            ->distinct()
            ->chunk(100, function ($assets) {
                Stations::dispatch($assets->pluck('location_id')->toArray());
            });
        
         // NPC stations using corporation assets
        CorporationAsset::where('location_type', 'station')
            ->select('location_id')
            ->orderBy('location_id')
            ->distinct()
            ->chunk(100, function ($assets) {
                Stations::dispatch($assets->pluck('location_id')->toArray());
            });

        Map::dispatch();
        Structures::dispatch();
        Names::dispatch();
        Alliances::dispatch();
        Prices::dispatch();
        Insurances::dispatch();

        CharacterInfo::doesntHave('refresh_token')->each(function ($character) {
            CharacterInfoJob::withChain([
                new CorporationHistory($character->character_id),
            ])->dispatch($character->character_id)->delay(rand(10, 120));
            // in order to prevent ESI to receive massive income of all existing SeAT instances in the world
            // add a bit of randomize when job can be processed - we use seconds here, so we have more flexibility
            // https://github.com/eveseat/seat/issues/731
        });

        CorporationInfo::all()->each(function ($corporation) {
            CorporationInfoJob::withChain([
                new AllianceHistory($corporation->corporation_id),
            ])->dispatch($corporation->corporation_id)->delay(rand(120, 300));
            // in order to prevent ESI to receive massive income of all existing SeAT instances in the world
            // add a bit of randomize when job can be processed - we use seconds here, so we have more flexibility
            // https://github.com/eveseat/seat/issues/731
        });
    }
}
