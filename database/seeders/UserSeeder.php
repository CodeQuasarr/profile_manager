<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrator
        User::create([
            'first_name' => 'Samuel',
            'last_name' => 'Oncle',
            'status' => User::STATUS_ACTIVE,
            'email' => 'oncle_sam@hellocse.fr',
            'coach_id' => null,
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        // Coach
        $coaches = [
            [ 'first_name' => 'coach', 'last_name' => 'first', ],
            [ 'first_name' => 'coach', 'last_name' => 'second', ],
        ];

        foreach ($coaches as $key => $coach) {
            User::create(
                array_merge(
                    $coach,
                    [
                        'coach_id' => null,
                        'status' => User::STATUS_ACTIVE,
                        'email' => $coach['last_name'] . '_'. $coach['first_name'] .'@team.eu',
                        'email_verified_at' => Carbon::now(),
                        'password' => Hash::make('password'),
                        'remember_token' => Str::random(10),
                    ]
                )
            );// players
            User::factory(10)->create([ 'coach_id' => $key + 1 ]);


        }


    }
}
