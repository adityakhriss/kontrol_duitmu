<?php

use App\Models\Debt;
use App\Models\Investment;
use App\Models\SavingGoal;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('user can reset all financial data without deleting account', function () {
    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();

    $account->update(['balance' => 500000]);
    $user->transactions()->create([
        'payment_account_id' => $account->id,
        'type' => 'income',
        'transaction_date' => now()->toDateString(),
        'amount' => 500000,
    ]);
    $user->bills()->create([
        'payment_account_id' => $account->id,
        'name' => 'Internet',
        'category_name' => 'Tagihan',
        'amount' => 250000,
        'due_date' => now()->addDay()->toDateString(),
    ]);
    SavingGoal::query()->create([
        'user_id' => $user->id,
        'name' => 'Dana darurat',
        'target_amount' => 1000000,
        'current_amount' => 100000,
    ]);
    Debt::query()->create([
        'user_id' => $user->id,
        'name' => 'Kartu kredit',
        'total_amount' => 1000000,
        'remaining_amount' => 750000,
        'monthly_payment' => 250000,
    ]);
    Investment::query()->create([
        'user_id' => $user->id,
        'name' => 'BBCA',
        'type' => 'Saham',
        'units' => 1,
        'buy_price' => 10000,
        'current_price' => 10000,
        'total_cost' => 10000,
        'current_value' => 10000,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.reset-data'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
    $this->assertSame(0, $user->fresh()->transactions()->count());
    $this->assertSame(0, $user->fresh()->bills()->count());
    $this->assertSame(0, $user->fresh()->savingGoals()->count());
    $this->assertSame(0, $user->fresh()->debts()->count());
    $this->assertSame(0, $user->fresh()->investments()->count());
    $this->assertSame(0.0, (float) $account->fresh()->balance);
});
