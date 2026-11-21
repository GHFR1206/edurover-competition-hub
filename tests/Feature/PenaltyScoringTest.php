<?php

use App\Enums\RunStatus;
use App\Enums\ScoreObjectType;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Event;
use App\Models\Run;
use App\Models\ScoreObject;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $event = Event::factory()->create(['status' => 'running']);
    $this->category = Category::factory()->create(['event_id' => $event->id]);
    $arena = Arena::factory()->create(['event_id' => $event->id]);

    $challenge = Challenge::factory()->create(['category_id' => $this->category->id]);

    $this->achievement = ScoreObject::factory()->create([
        'challenge_id' => $challenge->id,
        'type' => ScoreObjectType::Achievement,
        'points_per_unit' => 10,
        'max_quantity' => 5,
    ]);

    $this->penalty = ScoreObject::factory()->penalty()->create([
        'challenge_id' => $challenge->id,
        'points_per_unit' => 15,
        'max_quantity' => 3,
    ]);

    $team = Team::factory()->create(['category_id' => $this->category->id]);

    $this->run = Run::factory()->create([
        'event_id' => $event->id,
        'category_id' => $this->category->id,
        'arena_id' => $arena->id,
        'team_id' => $team->id,
        'status' => RunStatus::Finished,
    ]);

    $this->judge = User::factory()->create();
    $this->judge->assignRole('judge');
    $this->judge->arenaAssignments()->create([
        'arena_id' => $arena->id,
        'role' => 'judge',
        'is_active' => true,
    ]);
});

it('deducts a penalty that is ticked with a blank quantity', function () {
    $this->actingAs($this->judge)
        ->post(route('judge.scores.store', $this->run), [
            'entries' => [
                ['score_object_id' => $this->achievement->id, 'quantity' => 2, 'achieved' => 1],
                ['score_object_id' => $this->penalty->id, 'quantity' => 0, 'achieved' => 1],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $score = $this->run->fresh()->scores()->firstOrFail();

    expect($score->subtotal)->toBe(20)
        ->and($score->penalty_total)->toBe(15)
        ->and($score->total)->toBe(5);
});

it('multiplies a penalty by its quantity', function () {
    $this->actingAs($this->judge)
        ->post(route('judge.scores.store', $this->run), [
            'entries' => [
                ['score_object_id' => $this->achievement->id, 'quantity' => 5, 'achieved' => 1],
                ['score_object_id' => $this->penalty->id, 'quantity' => 2, 'achieved' => 1],
            ],
        ])
        ->assertRedirect();

    $score = $this->run->fresh()->scores()->firstOrFail();

    expect($score->penalty_total)->toBe(30)
        ->and($score->total)->toBe(20);
});

it('returns a validation error instead of a 500 when a quantity exceeds its limit', function () {
    $this->actingAs($this->judge)
        ->post(route('judge.scores.store', $this->run), [
            'entries' => [
                ['score_object_id' => $this->achievement->id, 'quantity' => 99, 'achieved' => 1],
            ],
        ])
        ->assertSessionHasErrors('entries.0.quantity');

    expect($this->run->fresh()->scores()->count())->toBe(0);
});

it('returns a validation error when a penalty exceeds its limit', function () {
    $this->actingAs($this->judge)
        ->post(route('judge.scores.store', $this->run), [
            'entries' => [
                ['score_object_id' => $this->penalty->id, 'quantity' => 9, 'achieved' => 1],
            ],
        ])
        ->assertSessionHasErrors('entries.0.quantity');
});
