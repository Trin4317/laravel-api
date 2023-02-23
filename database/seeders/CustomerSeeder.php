<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::factory()
            ->count(20)
            ->hasInvoices(10)
            ->create();

        Customer::factory()
            ->count(50)
            ->hasInvoices(5)
            ->create();

        Customer::factory()
            ->count(70)
            ->hasInvoices(3)
            ->create();

        Customer::factory()
            ->count(5)
            ->create();
    }
}
