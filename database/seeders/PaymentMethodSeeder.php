<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            ['resource_key' => 'payment.method.cod'],
            ['resource_key' => 'payment.method.stripe'],
            ['resource_key' => 'payment.method.paypal'],
        ]);
    }
}