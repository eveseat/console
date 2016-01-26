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

namespace Seat\Console\Commands\Seat;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

/**
 * Class Version
 * @package Seat\Console\Commands\Seat
 */
class Version extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:version {--offline : Skip Checking Github for latest versions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all of the SeAT component versions';

    /**
     * @var string
     */
    protected $base_url = 'https://api.github.com/repos/:repo/releases/latest';

    /**
     * @var array
     */
    protected $packages = [
        'api', 'console', 'eveapi', 'notifications', 'web', 'services'
    ];

    /**
     * Create a new command instance.
     *
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client)
    {

        $this->client = $client;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $offline = $this->option('offline');

        if ($offline)
            $this->info('Checking Local Versions Only');
        else
            $this->info('Checking Local and Github Versions. Please wait...');

        $client = $this->client;
        $base_url = $this->base_url;
        $headers = [
            'Accept' => 'application/json',
        ];

        $this->table(['Package Name', 'Local Version', 'Latest Github'],
            array_map(function ($package) use ($offline, $base_url, $client, $headers) {

                if ($offline) {

                    return [
                        ucfirst($package),
                        config($package . '.config.version'),
                        'Offline'
                    ];
                }

                $url = str_replace(':repo', 'eveseat/' . $package, $base_url);

                return [
                    ucfirst($package),
                    config($package . '.config.version'),
                    json_decode($client->get($url, $headers)->getBody())->tag_name
                ];

            }, $this->packages));

        return;

    }

}
