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

namespace Seat\Console\Commands\Seat\Admin;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;
use Seat\Services\Repositories\Configuration\UserRespository;
use Seat\Web\Acl\AccessManager;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\User;

class Reset extends Command
{
    use UserRespository, AccessManager, DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:admin:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the administrator user\'s roles and password.';

    /**
     * Create a new command instance.
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

        $this->line('SeAT Admin Reset Tool');

        $admin = User::firstOrNew(['name' => 'admin']);

        if (! $admin->exists)
            $this->warn('User \'admin\' does not exist. It will be created.');

        $password = null;
        while (strlen($password) < 6)
            $password = $this->secret('Please enter a min 6 character password for the \'admin\' user');

        $this->line('Setting password');

        $admin->fill([
            'name'     => 'admin',
            'email'    => 'admin@seat.local',
            'password' => bcrypt($password),
        ])->save();

        $this->line('Checking if \'admin\' is a super user');

        if (! $admin->has('superuser')) {

            $this->line('Searching for the \'Superuser\' role');
            $role = Role::where('title', 'Superuser')->first();

            if (! $role) {

                $this->comment('Creating the Superuser role');
                $role = Role::create(['title' => 'Superuser']);

            }

            $this->line('Checking if the Superuser role has the superuser permission');
            $role_permissions = $this->getCompleteRole($role->id)->permissions;

            if (! $role_permissions->contains('superuser')) {

                $this->comment('Adding the superuser permission to the role');
                $this->giveRolePermission($role->id, 'superuser', false);

            }

            $this->comment('Adding \'admin\' to the Superuser role');
            $this->giveUserRole($admin->id, $role->id);

        }

        $this->line('Ensuring the \'admin\' user is enabled.');

        if (! $admin->active) {

            $admin->active = true;
            $admin->save();
        }

        // Analytics
        $this->dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'event')
            ->set('ec', 'admin')
            ->set('ea', 'password_reset')
            ->set('el', 'console')))
            ->onQueue('medium'));

        $this->info('Done');

    }
}
