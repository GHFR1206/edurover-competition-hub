<?php

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);
});

it('renders public pages', function () {
    $this->get('/')->assertOk();
    $this->get('/hasil')->assertOk();
});

it('renders every role dashboard and page', function () {
    $pages = [
        'coach@nanorobotic.test' => ['/coach/dashboard', '/coach/tim', '/coach/hasil'],
        'juri@nanorobotic.test' => ['/juri/dashboard', '/juri/tugas', '/juri/skor-saya'],
        'operator@nanorobotic.test' => ['/operator/dashboard', '/operator/arena'],
        'chief@nanorobotic.test' => ['/chief/dashboard', '/chief/verifikasi', '/chief/publikasi'],
        'admin@nanorobotic.test' => ['/dashboard', '/admin'],
    ];

    foreach ($pages as $email => $urls) {
        $user = User::where('email', $email)->firstOrFail();

        foreach ($urls as $url) {
            $this->actingAs($user)->get($url)->assertSuccessful();
        }
    }
});

it('blocks a role from another role area', function () {
    $coach = User::where('email', 'coach@nanorobotic.test')->firstOrFail();
    $this->actingAs($coach)->get('/chief/verifikasi')->assertForbidden();
});
