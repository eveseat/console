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

namespace Seat\Console\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Console\Bus\CorporationTokenShouldUpdate;
use Seat\Eveapi\Models\RefreshToken;

class Corporations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:corporations {character_id? : Optional character_id to update ' .
    'corporation information for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for corporations';

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

                // Fire the class to update corporation information
                (new CorporationTokenShouldUpdate($token->character->affiliation->corporation_id, $token))->fire();
            });

        $this->info('Processed ' . $tokens->count() . ' refresh tokens.');

    }
}
