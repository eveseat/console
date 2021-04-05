<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

use Seat\Eveapi\Bus\Alliance as AllianceBus;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Alliance.
 *
 * @package Seat\Console\Bus
 * @deprecated since 4.7.0 - will be replaced by Seat\Eveapi\Bus\Alliance
 */
class Alliance extends AllianceBus
{
    /**
     * Alliance constructor.
     *
     * @param int $alliance_id
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(int $alliance_id, ?RefreshToken $token = null)
    {
        parent::__construct($alliance_id, $token);
    }
}
