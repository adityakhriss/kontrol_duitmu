<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->call(IntegrationSeeder::class);

        $testUser = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'user',
                'is_active' => true,
            ],
        );

        $adminUser = User::query()->updateOrCreate(
            ['email' => 'admin@kontrolduitmu.test'],
            [
                'name' => 'Admin Kontrol Duitmu',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        foreach ([$testUser, $adminUser] as $user) {
            foreach ([
                ['name' => 'Tunai', 'slug' => 'tunai', 'type' => 'cash', 'sort_order' => 1],
                ['name' => 'Bank', 'slug' => 'bank', 'type' => 'bank', 'sort_order' => 2],
                ['name' => 'E-wallet', 'slug' => 'e-wallet', 'type' => 'e_wallet', 'sort_order' => 3],
            ] as $account) {
                $user->paymentAccounts()->updateOrCreate(
                    ['slug' => $account['slug']],
                    $account,
                );
            }
        }

    }
}
