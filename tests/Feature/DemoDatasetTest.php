<?php

use App\Enums\Level;
use App\Enums\Role;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ScoreObject;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

it('boots the application', function () {
    $this->get('/')->assertStatus(200);
});

it('seeds the demo dataset', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);

    expect(Event::count())->toBe(1)
        ->and(Category::count())->toBe(3)
        ->and(Team::count())->toBe(24)
        ->and(Arena::count())->toBe(4);
});

it('creates all seven roles and nothing more', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Spatie\Permission\Models\Role::count())->toBe(7)
        ->and(Spatie\Permission\Models\Role::pluck('name')->sort()->values()->all())
        ->toBe(collect(Role::values())->sort()->values()->all());
});

it('encrypts NISN and hides it from serialization', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);

    $participant = Participant::first();
    $raw = DB::table('participants')->where('id', $participant->id)->value('nisn');

    expect($raw)->not->toBe($participant->nisn)
        ->and($participant->toArray())->not->toHaveKey('nisn');
});

it('assigns each demo user exactly one role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);

    foreach (Role::cases() as $role) {
        $user = User::where('email', match ($role) {
            Role::SuperAdmin => 'admin@nanorobotic.test',
            Role::EventAdmin => 'eventadmin@nanorobotic.test',
            Role::ArenaOperator => 'operator@nanorobotic.test',
            Role::Judge => 'juri@nanorobotic.test',
            Role::ChiefJudge => 'chief@nanorobotic.test',
            Role::Coach => 'coach@nanorobotic.test',
            Role::Public => 'publik@nanorobotic.test',
        })->first();

        expect($user)->not->toBeNull()
            ->and($user->hasRole($role->value))->toBeTrue();
    }
});

it('seeds dynamic challenges and score objects per level', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);

    foreach (Level::cases() as $level) {
        $category = Category::where('level', $level)->first();
        expect($category->challenges()->count())->toBeGreaterThan(0)
            ->and(ScoreObject::whereIn('challenge_id', $category->challenges()->pluck('id'))->count())->toBeGreaterThan(0);
    }
});
