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

namespace Seat\Console\Bus;

use Seat\Eveapi\Jobs\Assets\Corporation\Assets;
use Seat\Eveapi\Jobs\Assets\Corporation\Locations;
use Seat\Eveapi\Jobs\Assets\Corporation\Names;
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
use Seat\Eveapi\Jobs\Universe\CorporationStructures;
use Seat\Eveapi\Jobs\Wallet\Corporation\Balances;
use Seat\Eveapi\Jobs\Wallet\Corporation\Journals;
use Seat\Eveapi\Jobs\Wallet\Corporation\Transactions;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class CorporationBus.
 * @package Seat\Console\Bus
 */
class CorporationBus extends BusCommand
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

        Info::withChain([
            new AllianceHistory($this->corporation_id),
            new Divisions($this->corporation_id, $this->token),

            (new Roles($this->corporation_id, $this->token))->chain([
                new RoleHistories($this->corporation_id, $this->token),
            ]),

            (new Titles($this->corporation_id, $this->token))->chain([
                new MembersTitles($this->corporation_id, $this->token),
            ]),

            (new Medals($this->corporation_id, $this->token))->chain([
                new IssuedMedals($this->corporation_id, $this->token),
            ]),

            (new MembersLimit($this->corporation_id, $this->token))->chain([
                new Members($this->corporation_id, $this->token),
                new MemberTracking($this->corporation_id, $this->token),
            ]),

            // collect military informations
            new Recent($this->corporation_id, $this->token),

            // collect industrial informations
            (new Blueprints($this->corporation_id, $this->token))->chain([
                new Facilities($this->corporation_id, $this->token),
                new Jobs($this->corporation_id, $this->token),
                (new Observers($this->corporation_id, $this->token))->chain([
                    new ObserverDetails($this->corporation_id, $this->token),
                ]),
            ]),

            // collect financial informations
            new Orders($this->corporation_id, $this->token),
            new Contracts($this->corporation_id, $this->token),
            new Shareholders($this->corporation_id, $this->token),
            (new Balances($this->corporation_id, $this->token))->chain([
                new Journals($this->corporation_id, $this->token),
                new Transactions($this->corporation_id, $this->token),
            ]),

            // collect intel informations
            new Standings($this->corporation_id, $this->token),
            (new Contacts($this->corporation_id, $this->token))->chain([
                new Labels($this->corporation_id, $this->token),
            ]),

            // structures
            (new Starbases($this->corporation_id, $this->token))->chain([
                new StarbaseDetails($this->corporation_id, $this->token),
            ]),
            (new Structures($this->corporation_id, $this->token))->chain([
                new Extractions($this->corporation_id, $this->token),
            ]),
            (new CustomsOffices($this->corporation_id, $this->token))->chain([
                new CustomsOfficeLocations($this->corporation_id, $this->token),
            ]),

            // assets
            (new Assets($this->corporation_id, $this->token))->chain([
                new ContainerLogs($this->corporation_id, $this->token),
                new Locations($this->corporation_id, $this->token),
                new Names($this->corporation_id, $this->token),
                new CorporationStructures($this->corporation_id, $this->token),
            ]),
        ])->dispatch($this->corporation_id);
    }
}
