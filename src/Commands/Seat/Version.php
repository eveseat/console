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

namespace Seat\Console\Commands\Seat;

use Illuminate\Console\Command;

class Version extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all of the SeAT component versions';

    /**
     * Create a new command instance.
     *
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

        $this->line('api version: ' . config('api.config.version'));
        $this->line('console version: ' . config('console.config.version'));
        $this->line('eveapi version: ' . config('eveapi.config.version'));
        $this->line('notifications version: ' . config('notifications.config.version'));
        $this->line('web version: ' . config('web.config.version'));
        $this->line('services version: ' . config('services.config.version'));
    }
}
