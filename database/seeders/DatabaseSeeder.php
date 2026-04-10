<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $adminUser = User::factory()->create([
            'name' => 'Admin',
            'email' => 'skarandanis@gmail.com',
            'password' => bcrypt('7ujm&UJM'),
        ]);

        $this->call(PaymentMethodSeeder::class);
    }
}
