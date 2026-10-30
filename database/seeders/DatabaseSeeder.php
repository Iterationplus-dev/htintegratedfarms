<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Enquiry;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Development accounts are only created outside production;
     * create real staff accounts with `php artisan app:create-user`.
     */
    public function run(): void
    {
        $this->call(ContentSeeder::class);

        if (app()->isProduction()) {
            return;
        }

        foreach ([UserRole::Admin, UserRole::Sales, UserRole::Editor] as $role) {
            User::updateOrCreate(['email' => "{$role->value}@htintegratedfarms.test"], [
                'name' => $role->getLabel().' (dev)',
                'password' => 'password',
                'role' => $role,
                'is_active' => true,
            ]);
        }

        if (Enquiry::query()->doesntExist()) {
            Enquiry::factory()->count(4)->create();
            Enquiry::factory()->partnership()->count(2)->create();
            Enquiry::factory()->quote()->count(3)->create()->each(
                fn (Enquiry $enquiry) => QuoteItem::factory()->count(2)->for($enquiry)->create(),
            );
        }
    }
}
