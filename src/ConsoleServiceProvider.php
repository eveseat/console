<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Console;

use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->commands([
            'Seat\Console\Commands\Eve\QueueKey',           // eve:queue-keys {key_id}
            'Seat\Console\Commands\Eve\QueueKeys',          // eve:queue-keys
            'Seat\Console\Commands\Eve\UpdateApiCallList',  // eve:update-api-call-list
            'Seat\Console\Commands\Eve\UpdateEve',          // eve:update-eve
            'Seat\Console\Commands\Eve\UpdateMap',          // eve:update-map
            'Seat\Console\Commands\Eve\UpdateServerStatus', // eve:update-server-status

            'Seat\Console\Commands\Seat\Keys\Show',         // seat:keys:show
            'Seat\Console\Commands\Seat\Queue\Status',      // seat:queue:status

            'Seat\Console\Commands\Seat\Version',           // seat:version
        ]);

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__ . '/Config/console.config.php', 'console.config');
    }
}
