<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;

test('new user gets the three default payment accounts', function () {
    $user = User::factory()->create();

    expect($user->paymentAccounts()->orderBy('sort_order')->pluck('name')->all())
        ->toBe(['Tunai', 'Bank', 'E-wallet']);
});

test('default transaction categories can be seeded', function () {
    app(CategorySeeder::class)->run();

    expect(Category::query()->where('is_default', true)->count())->toBeGreaterThanOrEqual(14)
        ->and(Category::query()->where('type', 'expense')->exists())->toBeTrue()
        ->and(Category::query()->where('type', 'income')->exists())->toBeTrue();
});
