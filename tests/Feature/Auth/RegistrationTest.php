<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'school' => 'SMP Garuda A',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('a registered school account becomes a coach with a school profile', function () {
    $this->post('/register', [
        'name' => 'Rani Wulandari',
        'school' => 'SMP Garuda A',
        'email' => 'rani@garuda.contoh',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'rani@garuda.contoh')->firstOrFail();

    expect($user->hasRole('coach'))->toBeTrue()
        ->and($user->coachProfile)->not->toBeNull()
        ->and($user->coachProfile->school->name)->toBe('SMP Garuda A');

    $this->get('/dashboard')->assertRedirect('/coach/dashboard');
});
