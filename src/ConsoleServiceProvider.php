<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Console;

use Illuminate\Support\ServiceProvider;
use Seat\Console\Commands\Esi\Dispatch;
use Seat\Console\Commands\Esi\Ping;
use Seat\Console\Commands\Esi\Update\Characters as CharactersUpdater;
use Seat\Console\Commands\Eve\UpdateSde;
use Seat\Console\Commands\Seat\Admin\Diagnose;
use Seat\Console\Commands\Seat\Admin\Email;
use Seat\Console\Commands\Seat\Admin\Generate;
use Seat\Console\Commands\Seat\Admin\Reset;
use Seat\Console\Commands\Seat\Cache\Clear;
use Seat\Console\Commands\Seat\Queue\ClearExpired;
use Seat\Console\Commands\Seat\Queue\Status;
use Seat\Console\Commands\Seat\Version;


class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateSde::class,
                Diagnose::class,
                Reset::class,
                Email::class,
                Generate::class,
                Clear::class,
                ClearExpired::class,
                Status::class,
                Version::class,

                // Esi
                Ping::class,
                CharactersUpdater::class,
                Dispatch::class,
            ]);
        }
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
