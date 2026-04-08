<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect();

        $users->push(User::firstOrCreate(
            ['email' => 'test@wallet.com'],
            ['name' => 'Test User', 'password' => Hash::make('password')]
        ));

        User::factory(4)->create()->each(function (User $user) use ($users) {
            $users->push($user);
        });

        $users->each(function (User $user) {
            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 1000.00]
            );
        });
    }
}
