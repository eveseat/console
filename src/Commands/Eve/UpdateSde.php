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

namespace Seat\Console\Commands\Eve;

use File;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class UpdateServerStatus
 * @package Seat\Console\Commands
 */
class UpdateSde extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eve:update-sde
                            {--local : Check the local config file for the version string}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the EVE Online SDE Data';

    /**
     * The Guzzle Instance
     *
     * @var
     */
    protected $guzzle;

    /**
     * The response Json from the resources repo
     *
     * @var
     */
    protected $json;

    /**
     * The SDE file storage path
     *
     * @var
     */
    protected $storage_path;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {

        parent::__construct();

    }

    /**
     * Handle the calling of the required function to
     * update the EVE SDE data.
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

        if (!$this->confirm('Are you sure you want to update to the latest EVE SDE?', true)) {

            $this->warn('Exiting');

            return;
        }

        // Request the json from eveseat/resources
        $this->json = $this->getJsonResource();

        // Ensure we got a response, else fail.
        if (!$this->json) {

            $this->warn('Unable to reach the resources endpoint.');

            return;
        }

        // Check if we should attempt getting the
        // version string locally
        if ($this->option('local')) {

            $version_number = env('SDE_VERSION', null);

            if (!is_null($version_number)) {

                $this->comment('Using locally sourced version number of: ' . $version_number);
                $this->json->version = env('SDE_VERSION');

            } else {

                $this->warn('Unable to determine the version number override. ' .
                    'Using remote version: ' . $this->json->version);
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
            config('database.connections.mysql.host') . '/' .
            config('database.connections.mysql.database'));

        if (!$this->confirm('Does the above look OK?', true)) {

            $this->warn('Exiting');

            return;
        }

        if (!$this->isStorageOk()) {

            $this->error('Storage path is not OK. Please check permissions');

            return;
        }

        // Download the SDE's
        $this->getSde();

        $this->importSde();

        $this->line('SDE Update Command Complete');

        return;

    }

    /**
     * Get an instance of Guzzle
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
     * Get a new progress bar to display based on the
     * amount of iterations we expect to use
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
     * Query the eveseat/resources repository for SDE
     * related information
     *
     * @return \Psr\Http\Message\StreamInterface|void
     */
    public function getJsonResource()
    {

        $result = $this->getGuzzle()->request('GET',
            'https://raw.githubusercontent.com/eveseat/resources/master/sde.json', [
                'headers' => ['Accept' => 'application/json',]
            ]);

        if ($result->getStatusCode() != 200)
            return;

        return json_decode($result->getBody());
    }

    /**
     * Download the EVE Sde from Fuzzwork and save it
     * in the storage_path/sde folder
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
                'sink' => $file_handler]);

            fclose($file_handler);

            if ($result->getStatusCode() != 200)
                $this->error('Unable to download ' . $url .
                    '. The HTTP response was: ' . $result->getStatusCode());

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        return;
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
            if (!File::exists($storage))
                File::makeDirectory($storage, 0755, true);

            // Set the storage path
            $this->storage_path = $storage;

            return true;

        }

        return false;
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

            if (!File::exists($archive_path)) {

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
                // Check if the password is longer than 0. If not, dont specify the -p flag
                (strlen(config('database.connections.mysql.password')) ? ' -p' : '')
                // Append this regardless
                . config('database.connections.mysql.password') .
                ' -h ' . config('database.connections.mysql.host') .
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

        return;

    }

}
