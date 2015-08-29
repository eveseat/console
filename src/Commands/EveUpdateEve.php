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
use Seat\Eveapi\Traits\JobManager;

class EveUpdateEve extends Command
{

    use JobManager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eve:update-eve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates EVE Universe Specific Information';

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

        $this->line('This is just a simple test to add a job!');

        $job_id = $this->addUniqueJob(
            'Seat\Eveapi\Jobs\UpdateEve',
            [
                'scope' => 'Eve',
                'api'   => 'Eve'
            ]
        );

        $this->info('Job ' . $job_id . ' dispatched!');
    }
}
