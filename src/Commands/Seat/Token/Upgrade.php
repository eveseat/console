<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Console\Commands\Seat\Token;

use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Upgrade.
 * @package Seat\Console\Commands\Seat\Token
 */
class Upgrade extends Command
{

    const CURRENT_VERSION = 2;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:token:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade all tokens to latest sso version';

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
     */
    public function handle()
    {

        $this->line('SeAT Token Upgrader');

        $client = new Client([
            'timeout' => 30,
        ]);
        $authsite = 'https://login.eveonline.com/v2/oauth/token';

        $errors = 0;
        $perm = 0;
        $success = 0;

        $count = DB::table('refresh_tokens')
            // ->whereNotNull('deleted_at')
            ->count();
        $progress = $this->output->createProgressBar($count);

        RefreshToken::whereNotIn('version', [self::CURRENT_VERSION])
            ->chunk(100, function ($tokens) use ($client, &$errors, &$success, &$perm, $authsite, $progress) {
                foreach ($tokens as $token){
                    try{
                        $token_headers = [
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode(env('EVE_CLIENT_ID') . ':' . env('EVE_CLIENT_SECRET')),
                                'User-Agent' => 'Eve SeAT SSO v2 Migrator. Contact eveseat slack or github. https://github.com/eveseat/seat',
                                'Content-Type' => 'application/x-www-form-urlencoded',
                                'Host' => 'login.eveonline.com',
                            ],
                            'form_params' => [
                                // 'client_id' => env('EVE_CLIENT_ID'),
                                'grant_type' => 'refresh_token',
                                'refresh_token' => $token->refresh_token,
                            ],
                        ];

                        $result = $client->post($authsite, $token_headers);
                        $resp = json_decode($result->getBody());
                        $expires_new = Carbon::createFromTimestamp(time() + $resp->expires_in);

                        $token->token = $resp->access_token;
                        $token->refresh_token = $resp->refresh_token;
                        $token->expires_on = $expires_new;
                        $token->version = self::CURRENT_VERSION;

                        $token->save();

                        $success += 1;

                    } catch (RequestException $e) {
                        logger()->error('Error Migrating Refresh Token', [
                            'Character ID'   => $token->character_id,
                            'Message' => $e->getMessage(),
                            'Body' => (string) $e->getResponse()->getBody(),
                            'Headers' => $e->getResponse()->getHeaders(),
                        ]);

                        if (strpos((string) $e->getResponse()->getBody(), 'invalid_grant') !== false) {
                            $perm += 1;
                            $token->delete();
                        } else{
                            $errors += 1;
                        }
                    }
                    $progress->advance();
                }
            });

            $progress->finish();
            $this->line('');

            $this->info('SeAT SSO Token Migration Complete!');
            $this->info('Success: '. $success);
            $this->info('Temp Fail: '. $errors);
            $this->info('Perm Fail: '. $perm);

    }
}
