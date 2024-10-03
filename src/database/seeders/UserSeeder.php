<?php

namespace Fmcpay\BirthdayVoucher\database\seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $batchSize = 500;
        $totalRecords = 100000;

        for ($i = 0; $i < $totalRecords / $batchSize; $i++) {
            $users = [];
            $wallets = [];

            for ($j = 0; $j < $batchSize; $j++) {
                $userId = ($i * $batchSize) + $j + 1;
                $users[] = [
                    'id' => $userId,
                    'name' => $faker->userName(),
                    'email' => $faker->unique()->email(),
                    'password' => bcrypt($faker->password()),
                    'created_at' => $faker->dateTimeBetween('-10 years', 'now'),
                    'birth_day' => $faker->date('Y-m-d', '2015-10-10'),
                ];

                $wallets[] = [
                    'user_id' => $userId,
                    'balance' => $faker->randomFloat(2, 0, 1000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('users')->insert($users);
            DB::table('wallets')->insert($wallets);
        }
    }
}
