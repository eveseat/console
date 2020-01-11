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

namespace Seat\Console\Bus;

use Seat\Eveapi\Jobs\Assets\Corporation\Assets;
use Seat\Eveapi\Jobs\Assets\Corporation\Locations;
use Seat\Eveapi\Jobs\Assets\Corporation\Names;
use Seat\Eveapi\Jobs\Bookmarks\Corporation\Bookmarks;
use Seat\Eveapi\Jobs\Bookmarks\Corporation\Folders;
use Seat\Eveapi\Jobs\Contacts\Corporation\Contacts;
use Seat\Eveapi\Jobs\Contacts\Corporation\Labels;
use Seat\Eveapi\Jobs\Contracts\Corporation\Contracts;
use Seat\Eveapi\Jobs\Corporation\AllianceHistory;
use Seat\Eveapi\Jobs\Corporation\Blueprints;
use Seat\Eveapi\Jobs\Corporation\ContainerLogs;
use Seat\Eveapi\Jobs\Corporation\Divisions;
use Seat\Eveapi\Jobs\Corporation\Facilities;
use Seat\Eveapi\Jobs\Corporation\Info;
use Seat\Eveapi\Jobs\Corporation\IssuedMedals;
use Seat\Eveapi\Jobs\Corporation\Medals;
use Seat\Eveapi\Jobs\Corporation\Members;
use Seat\Eveapi\Jobs\Corporation\MembersLimit;
use Seat\Eveapi\Jobs\Corporation\MembersTitles;
use Seat\Eveapi\Jobs\Corporation\MemberTracking;
use Seat\Eveapi\Jobs\Corporation\RoleHistories;
use Seat\Eveapi\Jobs\Corporation\Roles;
use Seat\Eveapi\Jobs\Corporation\Shareholders;
use Seat\Eveapi\Jobs\Corporation\Standings;
use Seat\Eveapi\Jobs\Corporation\StarbaseDetails;
use Seat\Eveapi\Jobs\Corporation\Starbases;
use Seat\Eveapi\Jobs\Corporation\Structures;
use Seat\Eveapi\Jobs\Corporation\Titles;
use Seat\Eveapi\Jobs\Industry\Corporation\Jobs;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\Extractions;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\ObserverDetails;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\Observers;
use Seat\Eveapi\Jobs\Killmails\Corporation\Recent;
use Seat\Eveapi\Jobs\Market\Corporation\Orders;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation\CustomsOfficeLocations;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation\CustomsOffices;
use Seat\Eveapi\Jobs\Wallet\Corporation\Balances;
use Seat\Eveapi\Jobs\Wallet\Corporation\Journals;
use Seat\Eveapi\Jobs\Wallet\Corporation\Transactions;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class CorporationCharacterShouldUpdate.
 * @package Seat\Console\Bus
 */
class CorporationTokenShouldUpdate extends BusCommand
{
    /**
     * @var int
     */
    private $corporation_id;

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    private $token;

    /**
     * CorporationCharacterShouldUpdate constructor.
     *
     * @param int $corporation_id
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function __construct(int $corporation_id, RefreshToken $token)
    {

        $this->corporation_id = $corporation_id;
        $this->token = $token;
    }

    /**
     * Fires the command.
     *
     * @return mixed
     */
    public function fire()
    {

        Assets::withChain([
            new Locations($this->corporation_id, $this->token), new Names($this->corporation_id, $this->token),
        ])->dispatch($this->corporation_id, $this->token);

        Bookmarks::withChain([
            new Folders($this->corporation_id, $this->token),
        ])->dispatch($this->corporation_id, $this->token);

        Contacts::withChain([
            new Labels($this->corporation_id, $this->token),
        ])->dispatch($this->corporation_id, $this->token);

        Contracts::dispatch($this->corporation_id, $this->token);

        Info::dispatch($this->corporation_id);
        AllianceHistory::dispatch($this->corporation_id);
        Blueprints::dispatch($this->corporation_id, $this->token);
        ContainerLogs::dispatch($this->corporation_id, $this->token);
        Divisions::dispatch($this->corporation_id, $this->token);
        Facilities::dispatch($this->corporation_id, $this->token);
        IssuedMedals::dispatch($this->corporation_id, $this->token);
        Medals::dispatch($this->corporation_id, $this->token);
        Members::dispatch($this->corporation_id, $this->token);
        MembersLimit::dispatch($this->corporation_id, $this->token);
        MemberTracking::dispatch($this->corporation_id, $this->token);

        Roles::withChain([
                new RoleHistories($this->corporation_id, $this->token), ]
        )->dispatch($this->corporation_id, $this->token);

        Shareholders::dispatch($this->corporation_id, $this->token);
        Standings::dispatch($this->corporation_id, $this->token);

        Starbases::withChain([
            new StarbaseDetails($this->corporation_id, $this->token),
        ])->dispatch($this->corporation_id, $this->token);

        Structures::dispatch($this->corporation_id, $this->token);

        CustomsOffices::withChain([
            new CustomsOfficeLocations($this->corporation_id, $this->token),
        ])->dispatch($this->corporation_id, $this->token);

        Titles::withChain([
            new MembersTitles($this->corporation_id, $this->token),
        ])->dispatch($this->corporation_id, $this->token);

        Jobs::dispatch($this->corporation_id, $this->token);
        Extractions::dispatch($this->corporation_id, $this->token);

        Observers::withChain([
            new ObserverDetails($this->corporation_id, $this->token),
        ])->dispatch($this->corporation_id, $this->token);

        Recent::dispatch($this->corporation_id, $this->token);

        Orders::dispatch($this->corporation_id, $this->token);

        Balances::dispatch($this->corporation_id, $this->token);
        Journals::dispatch($this->corporation_id, $this->token);
        Transactions::dispatch($this->corporation_id, $this->token);
    }
}
