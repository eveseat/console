<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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
use Seat\Eveapi\Jobs\Contracts\Corporation\Bids;
use Seat\Eveapi\Jobs\Contracts\Corporation\Contracts;
use Seat\Eveapi\Jobs\Contracts\Corporation\Items;
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
use Seat\Eveapi\Jobs\Corporation\OutpostDetails;
use Seat\Eveapi\Jobs\Corporation\Outposts;
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
use Seat\Eveapi\Jobs\Killmails\Corporation\Detail;
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
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    private $token;

    /**
     * @var string
     */
    private $queue;

    /**
     * CorporationCharacterShouldUpdate constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken $token
     * @param string                           $queue
     */
    public function __construct(RefreshToken $token, string $queue = 'default')
    {

        $this->token = $token;
        $this->queue = $queue;
    }

    /**
     * Fires the command.
     *
     * @return mixed
     */
    public function fire()
    {

        Assets::withChain([
            new Locations($this->token), new Names($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Bookmarks::withChain([
            new Folders($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Contacts::dispatch($this->token)->onQueue($this->queue);

        Contracts::withChain([
            new Items($this->token), new Bids($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Info::dispatch($this->token)->onQueue($this->queue);
        AllianceHistory::dispatch($this->token)->onQueue($this->queue);
        Blueprints::dispatch($this->token)->onQueue($this->queue);
        ContainerLogs::dispatch($this->token)->onQueue($this->queue);
        Divisions::dispatch($this->token)->onQueue($this->queue);
        Facilities::dispatch($this->token)->onQueue($this->queue);
        IssuedMedals::dispatch($this->token)->onQueue($this->queue);
        Medals::dispatch($this->token)->onQueue($this->queue);
        Members::dispatch($this->token)->onQueue($this->queue);
        MembersLimit::dispatch($this->token)->onQueue($this->queue);
        MemberTracking::dispatch($this->token)->onQueue($this->queue);

        Outposts::withChain([
            new OutpostDetails($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Roles::withChain([
                new RoleHistories($this->token), ]
        )->dispatch($this->token)->onQueue($this->queue);

        Shareholders::dispatch($this->token)->onQueue($this->queue);
        Standings::dispatch($this->token)->onQueue($this->queue);

        Starbases::withChain([
            new StarbaseDetails($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Structures::dispatch($this->token)->onQueue($this->queue);

        CustomsOffices::withChain([
            new CustomsOfficeLocations($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Titles::withChain([
            new MembersTitles($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Jobs::dispatch($this->token)->onQueue($this->queue);
        Extractions::dispatch($this->token)->onQueue($this->queue);

        Observers::withChain([
            new ObserverDetails($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Recent::withChain([
            new Detail($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        Orders::dispatch($this->token)->onQueue($this->queue);

        Balances::dispatch($this->token)->onQueue($this->queue);
        Journals::dispatch($this->token)->onQueue($this->queue);
        Transactions::dispatch($this->token)->onQueue($this->queue);
    }
}
