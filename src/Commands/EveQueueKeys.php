<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to dea    l
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Console\Commands;

use Illuminate\Console\Command;
use Seat\Eveapi\Helpers\JobContainer;
use Seat\Eveapi\Models\EveApiKey;
use Seat\Eveapi\Traits\JobManager;

class EveQueueKeys extends Command
{

    use JobManager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eve:queue-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queues EVE API Keys for Update';

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
     * @param \Seat\Eveapi\Helpers\JobContainer $job
     *
     * @return mixed
     */
    public function handle(JobContainer $job)
    {

        // Query the API Keys from the database
        // and queue jobs for them 10 at a time.
        EveApiKey::chunk(10, function ($keys) use ($job) {

            foreach ($keys as $key) {

                $job->scope = 'Key';
                $job->api = 'Scheduler';
                $job->owner_id = $key->key_id;
                $job->eve_api_key = $key;

                $job_id = $this->addUniqueJob(
                    'Seat\Eveapi\Jobs\CheckAndQueueKey', $job);

                $this->info('Job ' . $job_id . ' dispatched!');
            }
        });
    }
}
