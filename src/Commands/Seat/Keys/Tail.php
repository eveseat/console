<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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

namespace Seat\Console\Commands\Seat\Keys;


use Illuminate\Console\Command;
use Seat\Eveapi\Models\JobLog;

/**
 * Class Tail
 * @package Seat\Console\Commands\Seat\Keys
 */
class Tail extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:keys:tail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tail the logs for the API key updates';

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

        $this->warn('This command adds load to the db.');

        // Warn if the joblogs are disabled
        if (!config('eveapi.config.enable_joblog'))
            $this->warn('API Joblogs are disabled in the configuration.');

        // Get the latest logid
        $last_id = JobLog::max('id');
        $this->line('Starting tail from log id ' . $last_id);
        $this->line('^C stops the tail');

        while (true) {

            foreach (JobLog::where('id', '>', $last_id)->get() as $log) {

                if ($log->id > $last_id)
                    $last_id = $log->id;

                $this->line(
                    $log->created_at . ' - ' .
                    $log->key_id . ' ' .
                    '\'' . $log->type . '\' ' .
                    $log->message
                );
            }

            sleep(0.8);
        }

    }

}
