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
use Seat\Eveapi\Jobs\Character\Roles;
use Seat\Eveapi\Jobs\Character\Standings;
use Seat\Eveapi\Jobs\Character\Titles;
use Seat\Eveapi\Jobs\Clones\Clones;
use Seat\Eveapi\Jobs\Clones\Implants;
use Seat\Eveapi\Jobs\Contacts\Character\Contacts;
use Seat\Eveapi\Jobs\Contacts\Character\Labels as ContactLabels;
use Seat\Eveapi\Jobs\Contracts\Character\Contracts;
use Seat\Eveapi\Jobs\Fittings\Character\Fittings;
use Seat\Eveapi\Jobs\Industry\Character\Jobs;
use Seat\Eveapi\Jobs\Industry\Character\Mining;
use Seat\Eveapi\Jobs\Killmails\Character\Recent;
use Seat\Eveapi\Jobs\Location\Character\Location;
use Seat\Eveapi\Jobs\Location\Character\Online;
use Seat\Eveapi\Jobs\Location\Character\Ship;
use Seat\Eveapi\Jobs\Mail\Labels as MailLabels;
use Seat\Eveapi\Jobs\Mail\MailingLists;
use Seat\Eveapi\Jobs\Mail\Mails;
use Seat\Eveapi\Jobs\Market\Character\Orders;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Character\Planets;
use Seat\Eveapi\Jobs\Skills\Character\Attributes;
use Seat\Eveapi\Jobs\Skills\Character\Queue;
use Seat\Eveapi\Jobs\Skills\Character\Skills;
use Seat\Eveapi\Jobs\Universe\CharacterStructures;
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
     * CharacterTokenShouldUpdate constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function __construct(RefreshToken $token)
    {

        $this->token = $token;
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
        ])->dispatch($this->token);

        // Bookmarks
        Bookmarks::withChain([
            new Folders($this->token),
        ])->dispatch($this->token);

        // Calendar
        Events::withChain([
            new Detail($this->token), new Attendees($this->token),
        ])->dispatch($this->token);

        // Character
        Info::dispatch($this->token->character_id);
        AgentsResearch::dispatch($this->token);
        Blueprints::dispatch($this->token);
        CorporationHistory::dispatch($this->token->character_id);
        Fatigue::dispatch($this->token);
        Medals::dispatch($this->token);
        Roles::dispatch($this->token);
        Standings::dispatch($this->token);
        Titles::dispatch($this->token);

        // Clones
        Clones::withChain([
            new Implants($this->token),
        ])->dispatch($this->token);

        // Contacts
        Contacts::withChain([
            new ContactLabels($this->token),
        ])->dispatch($this->token);

        // Contracts
        Contracts::dispatch($this->token);

        // Fittings
        Fittings::dispatch($this->token);

        // Industry
        Jobs::dispatch($this->token);
        Mining::dispatch($this->token);

        // Killmails
        Recent::dispatch($this->token);

        // Location
        Location::dispatch($this->token);
        Online::dispatch($this->token);
        Ship::dispatch($this->token);

        // Mail
        Mails::withChain([
            new MailLabels($this->token),
        ])->dispatch($this->token);
        MailingLists::dispatch($this->token);

        // Market
        Orders::dispatch($this->token);

        // Planetary Interactions
        Planets::dispatch($this->token);

        // Skills
        Attributes::dispatch($this->token);
        Queue::dispatch($this->token);
        Skills::dispatch($this->token);

        // Structures
        CharacterStructures::dispatch($this->token);

        // Wallet
        Balance::dispatch($this->token);
        Journal::dispatch($this->token);
        Transactions::dispatch($this->token);
    }
}
