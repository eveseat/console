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
 * Class CharacterBus.
 * @package Seat\Console\Bus
 */
class CharacterBus extends BusCommand
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
        // Character
        Info::withChain([
            // collect information related to current character state
            new CorporationHistory($this->token->character_id),
            new Roles($this->token),
            new Titles($this->token),
            (new Clones($this->token))->chain([
                new Implants($this->token),
            ]),

            (new Location($this->token))->chain([
                new Online($this->token),
                new Ship($this->token),
            ]),

            (new Attributes($this->token))->chain([
                new Queue($this->token),
                new Skills($this->token),
            ]),

            // collect military informations
            new Fittings($this->token),
            new Recent($this->token),

            new Fatigue($this->token),
            new Medals($this->token),

            // collect industrial informations
            (new Blueprints($this->token))->chain([
                new Jobs($this->token),
                new Mining($this->token),
                new AgentsResearch($this->token),
            ]),

            // collect financial informations
            new Orders($this->token),
            new Contracts($this->token),
            new Planets($this->token),
            (new Balance($this->token))->chain([
                new Journal($this->token),
                new Transactions($this->token),
            ]),

            // collect intel informations
            new Standings($this->token),
            (new Contacts($this->token))->chain([
                new ContactLabels($this->token),
            ]),

            (new Mails($this->token))->chain([
                new MailLabels($this->token),
                new MailingLists($this->token),
            ]),

            // calendar events
            (new Events($this->token))->chain([
                new Detail($this->token),
                new Attendees($this->token),
            ]),

            // assets
            (new Assets($this->token))->chain([
                new Names($this->token),
                new Locations($this->token),
                new CharacterStructures($this->token),
            ]),
        ])->dispatch($this->token->character_id);
    }
}
