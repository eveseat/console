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
use Seat\Eveapi\Jobs\Wallet\Corporation\Balances;
use Seat\Eveapi\Jobs\Wallet\Corporation\Journals;
use Seat\Eveapi\Jobs\Wallet\Corporation\Transactions;
use Seat\Eveapi\Models\RefreshToken;

class Corporations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:corporations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for all corporations';

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
     *
     * @return mixed
     */
    public function handle()
    {

        $tokens = RefreshToken::all()->each(function ($token) {

            Assets::withChain([new Locations($token), new Names($token)])->dispatch($token);

            Bookmarks::withChain([new Folders($token)])->dispatch($token);

            Contacts::dispatch($token);

            Contracts::withChain([new Items($token), new Bids($token)])->dispatch($token);

            Info::dispatch($token);
            AllianceHistory::dispatch($token);
            Blueprints::dispatch($token);
            ContainerLogs::dispatch($token);
            Divisions::dispatch($token);
            Facilities::dispatch($token);
            IssuedMedals::dispatch($token);
            Medals::dispatch($token);
            Members::dispatch($token);
            MembersLimit::dispatch($token);
            MemberTracking::dispatch($token);
            Outposts::withChain([new OutpostDetails($token)])->dispatch($token);
            Roles::withChain([new RoleHistories($token)])->dispatch($token);
            Shareholders::dispatch($token);
            Standings::dispatch($token);
            Starbases::withChain([new StarbaseDetails($token)])->dispatch($token);
            Structures::dispatch($token);
            Titles::withChain([new MembersTitles($token)])->dispatch($token);

            Jobs::dispatch($token);
            Extractions::dispatch($token);
            Observers::withChain([new ObserverDetails($token)])->dispatch($token);

            Recent::withChain([new Detail($token)])->dispatch($token);

            Orders::dispatch($token);

            Balances::dispatch($token);
            Journals::dispatch($token);
            Transactions::dispatch($token);

        });

        $this->info('Processed ' . $tokens->count() . ' refresh tokens.');

    }
}
