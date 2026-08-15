<?php

use App\Models\User;

test('it generates a token for an existing user', function () {
    $user = User::factory()->create(['email' => 'existing@example.com']);

    $this->artisan('token:generate', ['email' => 'existing@example.com'])
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(1);
});

test('it creates the user when the email does not exist yet', function () {
    expect(User::where('email', 'new@example.com')->exists())->toBeFalse();

    $this->artisan('token:generate', ['email' => 'new@example.com'])
        ->assertSuccessful();

    $user = User::where('email', 'new@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->tokens()->count())->toBe(1);
});
