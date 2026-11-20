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
        'eventadmin@nanorobotic.test' => [
            '/event-admin/dashboard', '/event-admin/event', '/event-admin/setup',
            '/event-admin/staff', '/event-admin/jadwal', '/event-admin/laporan',
        ],
        'admin@nanorobotic.test' => [
            '/admin',
            '/super-admin/dashboard', '/super-admin/verifikasi', '/super-admin/pengguna',
            '/super-admin/audit', '/super-admin/pengaturan',
        ],
    ];

    foreach ($pages as $email => $urls) {
        $user = User::where('email', $email)->firstOrFail();

        foreach ($urls as $url) {
            $this->actingAs($user)->get($url)->assertSuccessful();
        }
    }
});

it('redirects every role from the shared dashboard', function () {
    $targets = [
        'coach@nanorobotic.test' => '/coach/dashboard',
        'juri@nanorobotic.test' => '/juri/dashboard',
        'operator@nanorobotic.test' => '/operator/dashboard',
        'chief@nanorobotic.test' => '/chief/dashboard',
        'eventadmin@nanorobotic.test' => '/event-admin/dashboard',
        'admin@nanorobotic.test' => '/super-admin/dashboard',
    ];

    foreach ($targets as $email => $target) {
        $user = User::where('email', $email)->firstOrFail();

        $this->actingAs($user)->get('/dashboard')->assertRedirect($target);
        $this->actingAs($user)->get($target)->assertSuccessful();
    }
});

it('renders public pages across the site', function () {
    foreach (['/', '/event', '/jadwal', '/leaderboard', '/hasil', '/arsip'] as $url) {
        $this->get($url)->assertSuccessful();
    }
});

it('blocks a role from another role area', function () {
    $coach = User::where('email', 'coach@nanorobotic.test')->firstOrFail();
    $this->actingAs($coach)->get('/chief/verifikasi')->assertForbidden();
});

it('renders every auth page', function () {
    foreach (['/login', '/register', '/forgot-password'] as $url) {
        $this->get($url)->assertSuccessful();
    }

    $user = User::where('email', 'coach@nanorobotic.test')->firstOrFail();
    $this->actingAs($user)->get('/confirm-password')->assertSuccessful();
});
