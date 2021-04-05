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

namespace Seat\Console\database\seeds;

use Illuminate\Database\Seeder;

/**
 * Class ScheduleSeeder.
 * @package Seat\Console\database\seeds
 * @deprecated since 4.8.0 - this class has been split between Seat\Eveapi\database\seeds\ScheduleSeeder and Seat\Web\database\seeds\ScheduleSeeder
 */
class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        (new \Seat\Eveapi\database\seeds\ScheduleSeeder())->run();
        (new \Seat\Web\database\seeds\ScheduleSeeder())->run();
    }
}
