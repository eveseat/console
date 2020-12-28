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

use Seat\Eveapi\Jobs\Alliances\Info;
use Seat\Eveapi\Jobs\Alliances\Members;
use Seat\Eveapi\Jobs\Contacts\Alliance\Contacts;
use Seat\Eveapi\Jobs\Contacts\Alliance\Labels;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Alliance.
 *
 * @package Seat\Console\Bus
 * @deprecated since 4.7.0 - this will be moved into eveapi package in a near future
 */
class Alliance extends BusCommand
{
    /**
     * @var int
     */
    private $alliance_id;

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    private $token;

    /**
     * Alliance constructor.
     *
     * @param int $alliance_id
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function __construct(int $alliance_id, RefreshToken $token)
    {
        $this->token = $token;
        $this->alliance_id = $alliance_id;
    }

    public function fire()
    {
        Info::withChain([
            new Members($this->alliance_id),
            new Labels($this->alliance_id, $this->token),
            new Contacts($this->alliance_id, $this->token),
        ])->dispatch($this->alliance_id)->delay(now()->addSeconds(rand(120, 600)));
        // in order to prevent ESI to receive massive income of all existing SeAT instances in the world
        // add a bit of randomize when job can be processed - we use seconds here, so we have more flexibility
        // https://github.com/eveseat/seat/issues/731
    }
}
