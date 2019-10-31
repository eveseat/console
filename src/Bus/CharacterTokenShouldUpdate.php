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

use Seat\Eveapi\Jobs\Assets\Character\Assets;
use Seat\Eveapi\Jobs\Assets\Character\Locations;
use Seat\Eveapi\Jobs\Assets\Character\Names;
use Seat\Eveapi\Jobs\Bookmarks\Character\Bookmarks;
use Seat\Eveapi\Jobs\Bookmarks\Character\Folders;
use Seat\Eveapi\Jobs\Calendar\Attendees;
use Seat\Eveapi\Jobs\Calendar\Detail;
use Seat\Eveapi\Jobs\Calendar\Events;
use Seat\Eveapi\Jobs\Character\AgentsResearch;
use Seat\Eveapi\Jobs\Character\Blueprints;
use Seat\Eveapi\Jobs\Character\CorporationHistory;
use Seat\Eveapi\Jobs\Character\Fatigue;
use Seat\Eveapi\Jobs\Character\Info;
use Seat\Eveapi\Jobs\Character\Medals;
use Seat\Eveapi\Jobs\Character\Notifications;
use Seat\Eveapi\Jobs\Character\Roles;
use Seat\Eveapi\Jobs\Character\Standings;
use Seat\Eveapi\Jobs\Character\Titles;
use Seat\Eveapi\Jobs\Clones\Clones;
use Seat\Eveapi\Jobs\Clones\Implants;
use Seat\Eveapi\Jobs\Contacts\Character\Contacts;
use Seat\Eveapi\Jobs\Contacts\Character\Labels as ContactLabels;
use Seat\Eveapi\Jobs\Contracts\Character\Bids;
use Seat\Eveapi\Jobs\Contracts\Character\Contracts;
use Seat\Eveapi\Jobs\Contracts\Character\Items;
use Seat\Eveapi\Jobs\Fittings\Character\Fittings;
use Seat\Eveapi\Jobs\Industry\Character\Jobs;
use Seat\Eveapi\Jobs\Industry\Character\Mining;
use Seat\Eveapi\Jobs\Killmails\Character\Detail as KillmailDetail;
use Seat\Eveapi\Jobs\Killmails\Character\Recent;
use Seat\Eveapi\Jobs\Location\Character\Location;
use Seat\Eveapi\Jobs\Location\Character\Online;
use Seat\Eveapi\Jobs\Location\Character\Ship;
use Seat\Eveapi\Jobs\Mail\Labels;
use Seat\Eveapi\Jobs\Mail\MailingLists;
use Seat\Eveapi\Jobs\Mail\Mails;
use Seat\Eveapi\Jobs\Market\Character\Orders;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Character\PlanetDetail;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Character\Planets;
use Seat\Eveapi\Jobs\Skills\Character\Attributes;
use Seat\Eveapi\Jobs\Skills\Character\Queue;
use Seat\Eveapi\Jobs\Skills\Character\Skills;
use Seat\Eveapi\Jobs\Universe\Structures;
use Seat\Eveapi\Jobs\Wallet\Character\Balance;
use Seat\Eveapi\Jobs\Wallet\Character\Journal;
use Seat\Eveapi\Jobs\Wallet\Character\Transactions;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class CharacterShouldUpdate.
 * @package Seat\Console\Bus
 */
class CharacterTokenShouldUpdate extends BusCommand
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
     * CharacterTokenShouldUpdate constructor.
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
     * @return mixed|void
     */
    public function fire()
    {

        // Assets
        Assets::withChain([
            new Locations($this->token), new Names($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        // Bookmarks
        Bookmarks::withChain([
            new Folders($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        // Calendar
        Events::withChain([
            new Detail($this->token), new Attendees($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        // Character
        Info::dispatch($this->token)->onQueue($this->queue);
        AgentsResearch::dispatch($this->token)->onQueue($this->queue);
        Blueprints::dispatch($this->token)->onQueue($this->queue);
        CorporationHistory::dispatch($this->token)->onQueue($this->queue);
        Fatigue::dispatch($this->token)->onQueue($this->queue);
        Medals::dispatch($this->token)->onQueue($this->queue);
        Notifications::dispatch($this->token)->onQueue($this->queue);
        Roles::dispatch($this->token)->onQueue($this->queue);
        Standings::dispatch($this->token)->onQueue($this->queue);
        Titles::dispatch($this->token)->onQueue($this->queue);

        // Clones
        Clones::withChain([
            new Implants($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        // Contacts
        Contacts::withChain([
            new ContactLabels($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        // Contracts
        Contracts::withChain([
            new Items($this->token), new Bids($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);

        // Fittings
        Fittings::dispatch($this->token)->onQueue($this->queue);

        // Industry
        Jobs::dispatch($this->token)->onQueue($this->queue);
        Mining::dispatch($this->token)->onQueue($this->queue);

        // Killmails
        Recent::withChain([
            new KillmailDetail($this->token), ]
        )->dispatch($this->token)->onQueue($this->queue);

        // Location
        Location::dispatch($this->token)->onQueue($this->queue);
        Online::dispatch($this->token)->onQueue($this->queue);
        Ship::dispatch($this->token)->onQueue($this->queue);

        // Mail
        Mails::withChain([
            new Labels($this->token),
        ])->dispatch($this->token)->onQueue($this->queue);
        MailingLists::dispatch($this->token)->onQueue($this->queue);

        // Market
        Orders::dispatch($this->token)->onQueue($this->queue);

        // Planetary Interactions
        Planets::withChain([
            new PlanetDetail($this->token), ]
        )->dispatch($this->token)->onQueue($this->queue);

        // Skills
        Attributes::dispatch($this->token)->onQueue($this->queue);
        Queue::dispatch($this->token)->onQueue($this->queue);
        Skills::dispatch($this->token)->onQueue($this->queue);

        // Structures
        Structures::dispatch($this->token)->onQueue($this->queue);

        // Wallet
        Balance::dispatch($this->token)->onQueue($this->queue);
        Journal::dispatch($this->token)->onQueue($this->queue);
        Transactions::dispatch($this->token)->onQueue($this->queue);
    }
}
