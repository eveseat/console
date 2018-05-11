<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Console\Commands\Eve;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;
use Seat\Services\Settings\Seat;

/**
 * Class UpdateServerStatus.
 * @package Seat\Console\Commands
 */
class Sde extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eve:update:sde
                            {--local : Check the local config file for the version string}
                            {--force : Force re-installation of an existing SDE version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the EVE Online SDE Data';

    /**
     * The Guzzle Instance.
     *
     * @var
     */
    protected $guzzle;

    /**
     * The response Json from the resources repo.
     *
     * @var
     */
    protected $json;

    /**
     * The SDE file storage path.
     *
     * @var
     */
    protected $storage_path;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {

        parent::__construct();

    }

    /**
     * Query the eveseat/resources repository for SDE
     * related information.
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function handle()
    {

        // Start by warning the user about the command that will be run
        $this->comment('Warning! This Laravel command uses exec() to execute a ');
        $this->comment('mysql shell command to import an extracted dump. Due');
        $this->comment('to the way the command is constructed, should someone ');
        $this->comment('view the current running processes of your server, they ');
        $this->comment('will be able to see your SeAT database users password.');
        $this->line('');
        $this->line('Ensure that you understand this before continuing.');

        // Test that we have valid Database details. An exception
        // will be thrown if this fails.
        DB::connection()->getDatabaseName();

        if (! $this->confirm('Are you sure you want to update to the latest EVE SDE?', true)) {

            $this->warn('Exiting');

            return;
        }

        // Request the json from eveseat/resources
        $this->json = $this->getJsonResource();

        // Ensure we got a response, else fail.
        if (! $this->json) {

            $this->warn('Unable to reach the resources endpoint.');

            return;
        }

        // Check if we should attempt getting the
        // version string locally
        if ($this->option('local')) {

            $version_number = env('SDE_VERSION', null);

            if (! is_null($version_number)) {

                $this->comment('Using locally sourced version number of: ' . $version_number);
                $this->json->version = env('SDE_VERSION');

            } else {

                $this->warn('Unable to determine the version number override. ' .
                    'Using remote version: ' . $this->json->version);
            }
        }

        // Avoid an existing SDE to be accidentally installed again
        // except if the user explicitly ask for it
        if ($this->json->version == Seat::get('installed_sde') &&
            $this->option('force') == false
        ) {

            $this->warn('You are already running the latest SDE version.');
            $this->warn('If you want to install it again, run this command with --force argument.');

            return;
        }

        // Ask for a confirmation before installing an existing SDE version
        if ($this->option('force') == true) {

            $this->warn('You will re-download and install the current SDE version.');

            if (! $this->confirm('Are you sure ?', true)) {

                $this->info('Nothing has been updated.');

                return;
            }
        }

        //TODO: Allow for tables to be specified in config file

        // Show a final confirmation with some info on what
        // we are going to be doing.
        $this->info('The local SDE data will be updated to ' . $this->json->version);
        $this->info(count($this->json->tables) . ' tables will be updated: ' .
            implode(', ', $this->json->tables));
        $this->info('Download format will be: ' . $this->json->format);
        $this->line('');
        $this->info('The SDE will be imported to mysql://' .
            config('database.connections.mysql.username') . '@' .
            config('database.connections.mysql.host') . ':' .
            config('database.connections.mysql.port') . '/' .
            config('database.connections.mysql.database'));

        if (! $this->confirm('Does the above look OK?', true)) {

            $this->warn('Exiting');

            return;
        }

        if (! $this->isStorageOk()) {

            $this->error('Storage path is not OK. Please check permissions');

            return;
        }

        // Download the SDE's
        $this->getSde();

        $this->importSde();

        Seat::set('installed_sde', $this->json->version);

        $this->line('SDE Update Command Complete');

        // Analytics
        dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'event')
            ->set('ec', 'queues')
            ->set('ea', 'update_sde')
            ->set('el', 'console')
            ->set('ev', $this->json->version)))
            ->onQueue('medium'));

    }

    /**
     * Query the eveseat/resources repository for SDE
     * related information.
     *
     * @return mixed
     */
    public function getJsonResource()
    {

        $result = $this->getGuzzle()->request('GET',
            'https://raw.githubusercontent.com/eveseat/resources/master/sde.json', [
                'headers' => ['Accept' => 'application/json'],
            ]);

        if ($result->getStatusCode() != 200)
            return json_encode([]);

        return json_decode($result->getBody());
    }

    /**
     * Get an instance of Guzzle.
     *
     * @return \GuzzleHttp\Client
     */
    public function getGuzzle()
    {

        if ($this->guzzle)
            return $this->guzzle;

        $this->guzzle = new Client();

        return $this->guzzle;

    }

    /**
     * Check that the storage path is ok. I needed it
     * will be automatically created.
     *
     * @return bool
     */
    public function isStorageOk()
    {

        $storage = storage_path() . '/sde/' . $this->json->version . '/';
        $this->info('Storage path is: ' . $storage);

        if (File::isWritable(storage_path())) {

            // Check that the path exists
            if (! File::exists($storage))
                File::makeDirectory($storage, 0755, true);

            // Set the storage path
            $this->storage_path = $storage;

            return true;

        }

        return false;
    }

    /**
     * Download the EVE Sde from Fuzzwork and save it
     * in the storage_path/sde folder.
     */
    public function getSde()
    {

        $this->line('Downloading...');
        $bar = $this->getProgressBar(count($this->json->tables));

        foreach ($this->json->tables as $table) {

            $url = str_replace(':version', $this->json->version, $this->json->url) .
                $table . $this->json->format;
            $destination = $this->storage_path . $table . $this->json->format;

            $file_handler = fopen($destination, 'w');

            $result = $this->getGuzzle()->request('GET', $url, [
                'sink' => $file_handler, ]);

            fclose($file_handler);

            if ($result->getStatusCode() != 200)
                $this->error('Unable to download ' . $url .
                    '. The HTTP response was: ' . $result->getStatusCode());

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

    }

    /**
     * Get a new progress bar to display based on the
     * amount of iterations we expect to use.
     *
     * @param $iterations
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    public function getProgressBar($iterations)
    {

        $bar = $this->output->createProgressBar($iterations);

        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %memory:6s%');

        return $bar;
    }

    /**
     * Extract the SDE files downloaded and run the MySQL command
     * to import them into the database.
     */
    public function importSde()
    {

        $this->line('Importing...');
        $bar = $this->getProgressBar(count($this->json->tables));

        foreach ($this->json->tables as $table) {

            $archive_path = $this->storage_path . $table . $this->json->format;
            $extracted_path = $this->storage_path . $table . '.sql';

            if (! File::exists($archive_path)) {

                $this->warn($archive_path . ' seems to be invalid. Skipping.');
                continue;
            }

            // Get 2 handles ready for both the in and out files
            $input_file = bzopen($archive_path, 'r');
            $output_file = fopen($extracted_path, 'w');

            // Write the $output_file in chunks
            while ($chunk = bzread($input_file, 4096))
                fwrite($output_file, $chunk, 4096);

            // Close the files
            bzclose($input_file);
            fclose($output_file);

            // With the output file ready, prepare the scary exec() command
            // that should be run. A sample $import_command is:
            // mysql -u root -h 127.0.0.1 seat < /tmp/sample.sql
            $import_command = 'mysql -u ' . config('database.connections.mysql.username') .
                // Check if the password is longer than 0. If not, don't specify the -p flag
                (strlen(config('database.connections.mysql.password')) ? ' -p' : '')
                // Append this regardless. Escape special chars in the password too.
                . escapeshellcmd(config('database.connections.mysql.password')) .
                ' -h ' . config('database.connections.mysql.host') .
                ' -P ' . config('database.connections.mysql.port') .
                ' ' . config('database.connections.mysql.database') .
                ' < ' . $extracted_path;

            // Run the command... (*scared_face*)
            exec($import_command, $output, $exit_code);

            if ($exit_code !== 0)
                $this->error('Warning: Import failed with exit code ' .
                    $exit_code . ' and command outut: ' . implode('\n', $output));

            $bar->advance();

        }

        $bar->finish();
        $this->line('');

    }
}
