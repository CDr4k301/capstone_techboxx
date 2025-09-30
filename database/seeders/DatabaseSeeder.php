<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\User;
use App\Models\UserBuild;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // generate records traditionally and factory seeders
        User::factory(10)->create();

        $this->call([
            UserVerificationSeeder::class, 
            // UserBuildSeeder::class,

            // UserBuildSeeder::class,
            // OrderedBuildSeeder::class,
            // SupplierSeeder::class,
            ShoppingCartSeeder::class,
            CartItemSeeder::class,
            CheckoutSeeder::class,
        ]);
    }
}