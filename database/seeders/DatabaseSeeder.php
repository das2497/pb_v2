<?php

namespace Database\Seeders;

use App\Models\Product_category;
use App\Models\Products;
use App\Models\Rep;
use App\Models\RepAssignShop;
use App\Models\Routes;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a realistic baseline so a fresh
     * install can actually exercise every role (order-admin, rep, shop).
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Order Admin',
            'email' => 'admin@example.com',
            'role' => 'o_admin',
        ]);

        $morningRoute = Routes::factory()->create(['name' => 'Colombo Morning Route', 'time' => 'Morning']);
        $eveningRoute = Routes::factory()->create(['name' => 'Colombo Evening Route', 'time' => 'Evening']);

        $cakeCategory = Product_category::factory()->create(['category' => 'CAKE ITEMS', 'main_category' => 'Bakery']);
        $breadCategory = Product_category::factory()->create(['category' => 'BREAD', 'main_category' => 'Bakery']);

        Products::factory()->count(5)->create(['category' => $cakeCategory->category]);
        Products::factory()->count(5)->create(['category' => $breadCategory->category]);

        $shops = Shops::factory()->count(5)->create([
            'morning_route' => $morningRoute->name,
            'evening_route' => $eveningRoute->name,
        ]);

        $shops->each(function (Shops $shop) {
            User::factory()->create([
                'name' => $shop->name,
                'email' => $shop->email,
                'role' => 'shop',
            ]);
        });

        $reps = Rep::factory()->count(2)->create();

        $reps->each(function (Rep $rep) use ($shops) {
            User::factory()->create([
                'name' => $rep->name,
                'email' => $rep->email,
                'role' => 'rep',
            ]);

            foreach ($shops->random(min(2, $shops->count())) as $shop) {
                RepAssignShop::factory()->create([
                    'rep_id' => (string) $rep->id,
                    'shop_id' => (string) $shop->id,
                ]);
            }
        });
    }
}
