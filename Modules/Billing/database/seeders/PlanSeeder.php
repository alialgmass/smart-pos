<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Basic',
            'price_monthly' => 19.00,
            'max_users' => 2,
            'max_products' => 100,
            'features' => [
                'Up to 100 products',
                '2 staff accounts',
                'Basic sales reports',
                'Email support',
            ],
        ]);

        Plan::create([
            'name' => 'Advanced',
            'price_monthly' => 49.00,
            'max_users' => 5,
            'max_products' => 1000,
            'features' => [
                'Up to 1,000 products',
                '5 staff accounts',
                'Advanced sales reports',
                'Inventory management',
                'Customer management',
                'Priority email support',
            ],
        ]);

        Plan::create([
            'name' => 'Pro',
            'price_monthly' => 99.00,
            'max_users' => 20,
            'max_products' => 10000,
            'features' => [
                'Unlimited products',
                '20 staff accounts',
                'Full analytics suite',
                'Inventory management',
                'Customer management',
                'Restaurant module',
                'Offline mode',
                'API access',
                'Dedicated support',
            ],
        ]);
    }
}
