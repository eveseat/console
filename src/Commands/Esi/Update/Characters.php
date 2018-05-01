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

namespace Seat\Console\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\Assets\Character\Assets;
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
use Seat\Eveapi\Jobs\Character\Stats;
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
use Seat\Eveapi\Jobs\Mail\Bodies;
use Seat\Eveapi\Jobs\Mail\Headers;
use Seat\Eveapi\Jobs\Mail\Labels;
use Seat\Eveapi\Jobs\Mail\MailingLists;
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

class Characters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:characters {character_id? : Optional character_id to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for characters';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $tokens = RefreshToken::all()
            ->when($this->argument('character_id'), function ($tokens) {

                return $tokens->where('character_id', $this->argument('character_id'));
            })
            ->each(function ($token) {

                // Assets
                Assets::withChain([new Location($token), new Names($token)])->dispatch($token);

                // Bookmarks
                Bookmarks::withChain([new Folders($token)])->dispatch($token);

                // Calendar
                Events::withChain([new Detail($token), new Attendees($token)])->dispatch($token);

                // Character
                Info::dispatch($token);
                AgentsResearch::dispatch($token);
                Blueprints::dispatch($token);
                CorporationHistory::dispatch($token);
                Fatigue::dispatch($token);
                Medals::dispatch($token);
                Notifications::dispatch($token);
                Roles::dispatch($token);
                Standings::dispatch($token);
                Stats::dispatch($token);
                Titles::dispatch($token);

                // Clones
                Clones::withChain([new Implants($token)])->dispatch($token);

                // Contacts
                Contacts::withChain([new ContactLabels($token)])->dispatch($token);

                // Contracts
                Contracts::withChain([new Items($token), new Bids($token)])->dispatch($token);

                // Fittings
                Fittings::dispatch($token);

                // Industry
                Jobs::dispatch($token);
                Mining::dispatch($token);

                // Killmails
                Recent::withChain([new KillmailDetail($token)])->dispatch($token);

                // Location
                Location::dispatch($token);
                Online::dispatch($token);
                Ship::dispatch($token);

                // Mail
                Headers::withChain([new Bodies($token), new Labels($token)])->dispatch($token);
                MailingLists::dispatch($token);

                // Market
                Orders::dispatch($token);

                // Planetary Interactions
                Planets::withChain([new PlanetDetail($token)])->dispatch($token);

                // Skills
                Attributes::dispatch($token);
                Queue::dispatch($token);
                Skills::dispatch($token);

                // Structures
                Structures::dispatch($token);

                // Wallet
                Balance::dispatch($token);
                Journal::dispatch($token);
                Transactions::dispatch($token);

            });

        $this->info('Processed ' . $tokens->count() . ' refresh tokens.');
    }
}
