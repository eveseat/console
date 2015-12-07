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

namespace Seat\Console\Commands\Seat\Cache;

use Exception;
use File;
use Illuminate\Console\Command;
use Predis\Client;
use Seat\Eveapi\Models\JobTracking;

/**
 * Class Clear
 * @package Seat\Console\Commands\Seat\Cache
 */
class Clear extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear caches used by SeAT.';

    /**
     * Create a new command instance.
     *
     * @return void
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

        $this->line('SeAT Cache Clearing Tool');
        $this->line('');

        if (!$this->confirm('Are you sure you want to clear ALL caches (file/redis/db)?', true)) {

            $this->warn('Exiting without clearing cache');

            return;
        }

        $this->clear_pheal_cache();
        $this->clear_redis_cache();
        $this->clear_database_jobs();

        return;

    }

    /**
     * Clear the Pheal Storage Cache
     */
    public function clear_pheal_cache()
    {

        // Pheal Cache Clearing
        $pheal_storage = storage_path() . '/app/pheal/';

        if (File::isWritable($pheal_storage)) {

            $this->info('Clearing the Pheal Cache at: ' . $pheal_storage);

            if (!File::deleteDirectory($pheal_storage, true))
                $this->error('Failed to clear the Pheal Cache directory. Check permissions.');

        } else {

            $this->warn('Pheal Cache directory at ' . $pheal_storage . ' is not writable');
        }

        return;
    }

    /**
     * Flush all keys in Redis
     */
    public function clear_redis_cache()
    {

        $redis_host = config('database.redis.default.host');
        $redis_port = config('database.redis.default.port');

        $this->info('Clearing the Redis Cache at: ' . $redis_host . ':' . $redis_port);

        try {

            $redis = new Client([
                'host' => $redis_host,
                'port' => $redis_port
            ]);
            $redis->flushall();
            $redis->disconnect();

        } catch (Exception $e) {

            $this->error('Failed to clear the Redis Cache. Error: ' . $e->getMessage());

        }

        return;
    }

    /**
     * Clear the database job tracking cache
     */
    public function clear_database_jobs()
    {

        $this->info('Clearing the database Job Tracking Cache');

        JobTracking::truncate();
    }
}
