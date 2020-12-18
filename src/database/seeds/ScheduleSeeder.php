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

namespace Seat\Console\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class ScheduleSeeder.
 * @package Seat\Console\database\seeds
 */
class ScheduleSeeder extends Seeder
{
    /**
     * @var array
     */
    protected $schedules = [

        [   // ESI Status | Every Minute
            'command'           => 'esi:update:status',
            'expression'        => '* 0-10,13-23 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Horizon Metrics | Every Five Minutes
            'command'           => 'horizon:snapshot',
            'expression'        => '*/5 * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // EVE Server Status | Every Minute
            'command'           => 'eve:update:status',
            'expression'        => '* 0-10,13-23 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // SDE Data | Monthly
            'command'           => 'eve:update:sde',
            'expression'        => '0 0 1 * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Public Data | Daily at 12am
            'command'           => 'esi:update:public',
            'expression'        => '0 0 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Characters | Hourly
            'command'           => 'esi:update:characters',
            'expression'        => '0 0-10,13-23 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Character Affiliation | Every two hours
            'command'           => 'esi:update:affiliations',
            'expression'        => '0 0-10/2,13-23/2 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Character Notifications | Every twenty minutes
            'command'           => 'esi:update:notifications',
            'expression'        => '*/20 0-10,13-23 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Corporations | Every two hours
            'command'           => 'esi:update:corporations',
            'expression'        => '0 0-10/2,13-23/2 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Killmails | Every fifteen minutes
            'command'           => 'esi:update:killmails',
            'expression'        => '*/15 0-10,13-23 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Contracts | Every fifteen minutes
            'command'           => 'esi:update:contracts',
            'expression'        => '*/15 0-10,13-23 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Prices | Once a day
            'command'           => 'esi:update:prices',
            'expression'        => '0 13 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Alliances | Once a day
            'command'           => 'esi:update:alliances',
            'expression'        => '0 14 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // add randomness to default schedules
        $this->seedRandomize();

        //
        // drop SeAT 3.x deprecated commands
        //
        DB::table('schedules')->where('command', 'alerts:run')->delete();
        DB::table('schedules')->where('command', 'esi:update:serverstatus')->delete();
        DB::table('schedules')->where('command', 'esi:update:esistatus')->delete();

        // Check if we have the schedules, else,
        // insert them
        foreach ($this->schedules as $job) {
            if (DB::table('schedules')->where('command', $job['command'])->exists()) {
                DB::table('schedules')->where('command', $job['command'])->update([
                    'expression' => $job['expression'],
                ]);
            } else {
                DB::table('schedules')->insert($job);
            }
        }
    }

    /**
     * To prevent massive request wave from all installed instances in the world,
     * we add some randomness to seeded schedules.
     *
     * @see https://github.com/eveseat/seat/issues/731
     */
    private function seedRandomize()
    {
        // except utc 11 and utc 12
        $hours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];

        foreach ($this->schedules as $key => $schedule) {
            switch ($schedule['command']) {
                // use random minute, from 12am up to 10am and from 1pm up to 11pm
                case 'esi:update:characters':
                    $this->schedules[$key]['expression'] = sprintf('%d 0-10,13-23 * * *', rand(0, 59));
                    break;
                // use random minute, from 12am up to 10am and from 1pm up to 11pm - every 2 hours
                case 'esi:update:affiliations':
                case 'esi:update:corporations':
                    $this->schedules[$key]['expression'] = sprintf('%d 0-10/2,13-23/2 * * *', rand(0, 59));
                    break;
                // use random minute and hour, once a day, between 12am up to 10am and from 1pm up to 11pm
                case 'esi:update:public':
                case 'esi:update:prices':
                case 'esi:update:alliances':
                    $this->schedules[$key]['expression'] = sprintf('%d %d * * *', rand(0, 59), Arr::random($hours));
                    break;
            }
        }
    }
}
