<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019, 2020  Leon Jacobs
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
use Seat\Eveapi\Jobs\Contracts\Character\Bids as CharacterBids;
use Seat\Eveapi\Jobs\Contracts\Character\Contracts as CharacterContracts;
use Seat\Eveapi\Jobs\Contracts\Character\Items as CharacterItems;
use Seat\Eveapi\Jobs\Contracts\Corporation\Bids as CorporationBids;
use Seat\Eveapi\Jobs\Contracts\Corporation\Contracts as CorporationContracts;
use Seat\Eveapi\Jobs\Contracts\Corporation\Items as CorporationItems;
use Seat\Eveapi\Models\Contracts\CorporationContract;
use Seat\Eveapi\Models\Contracts\CharacterContract;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Contracts.
 *
 * @package Seat\Console\Commands\Esi\Update
 */
class Contracts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'esi:update:contracts {contract_ids?* : Optional contract_ids to update}';

    /**
     * @var string
     */
    protected $description = 'Schedule update jobs for contracts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // collect optional contracts ID from arguments
        $contract_ids = $this->argument('contract_ids') ?: [];

        $character_contracts = CharacterContract::whereHas('detail', function ($query) {
            $query->where('status', '<>', 'deleted');
        });

        $corporation_contracts = CorporationContract::whereHas('detail', function ($query) {
            $query->where('status', '<>', 'deleted');
        });

        // in case at least one ID has been provided, filter contracts on arguments
        if (! empty($contract_ids)) {
            $character_contracts->whereIn('contract_id', $contract_ids);
            $corporation_contracts->whereIn('contract_id', $contract_ids);
        }

        // loop over character contracts and queue detailed jobs
        // if we don't have any contracts registered -> queue character and corporation jobs to collect them
        $character_contracts->get()->each(function ($contract) {
            if ($contract->detail->status != 'deleted') {

                $token = RefreshToken::find($contract->character_id);

                if ($contract->detail->type == 'auction')
                    CharacterBids::dispatch($token);

                if ($contract->detail->type != 'courier' && $contract->volume > 0)
                    CharacterItems::dispatch($token);
            }
        });

        RefreshToken::all()->each(function ($token) {
            CharacterContracts::dispatch($token);
        });

        // loop over character contracts and queue detailed jobs
        // if we don't have any contracts registered -> queue character and corporation jobs to collect them
        $corporation_contracts->get()->each(function ($contract) {
            if ($contract->detail->status != 'deleted') {

                $token = RefreshToken::with('affiliation')->whereHas('character.corporation_roles', function ($query) {
                    $query->where('role', 'Director');
                })->whereHas('character.affiliation', function ($query) use ($contract) {
                    $query->where('corporation_id', $contract->corporation_id);
                })->first();

                if (! is_null($token)) {
                    if ($contract->detail->type == 'auction')
                        CorporationBids::dispatch($token->character->affiliation->corporation_id, $token);

                    if ($contract->detail->type != 'courier' && $contract->volume > 0)
                        CorporationItems::dispatch($token->character->affiliation->corporation_id, $token);
                }
            }
        });

        RefreshToken::with('character.affiliation')->whereHas('character.corporation_roles', function ($query) {
            $query->where('role', 'Director');
        })->get()->each(function ($token) {
            CorporationContracts::dispatch($token->character->affiliation->corporation_id, $token);
        });
    }
}
