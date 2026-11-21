<?php

use App\Enums\CategoryStatus;
use App\Enums\RunStatus;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\Run;
use App\Models\Team;
use App\Models\User;
use App\Services\PublicationService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->event = Event::factory()->create(['status' => 'running']);
    $this->category = Category::factory()->create([
        'event_id' => $this->event->id,
        'scoring_mode' => 'time',
    ]);
    $this->arena = Arena::factory()->create(['event_id' => $this->event->id]);

    $this->chief = User::factory()->create();
    $this->chief->assignRole('chief_judge');
});

/** One finished run for a fresh team. */
function finishedTimedRun(Category $category, Arena $arena, ?int $timeMs): Run
{
    $team = Team::factory()->create(['category_id' => $category->id]);

    return Run::factory()->create([
        'event_id' => $category->event_id,
        'category_id' => $category->id,
        'arena_id' => $arena->id,
        'team_id' => $team->id,
        'status' => RunStatus::Finished,
        'official_time_ms' => $timeMs,
    ]);
}

it('publishes a time-mode category without any judge score', function () {
    finishedTimedRun($this->category, $this->arena, 42_000);

    $service = app(PublicationService::class);

    expect($service->blockingReasons($this->category))->toBe([])
        ->and($service->canPublish($this->category))->toBeTrue();

    $reasons = $service->publish($this->category, $this->chief);

    expect($reasons)->toBe([])
        ->and($this->category->fresh()->status)->toBe(CategoryStatus::Published);
});

it('blocks a time-mode category when a run has no official time', function () {
    finishedTimedRun($this->category, $this->arena, null);

    $reasons = app(PublicationService::class)->blockingReasons($this->category);

    expect($reasons)->toHaveCount(1)
        ->and($reasons[0])->toContain('tanpa waktu resmi');
});

it('still requires approved judge scores in jury mode', function () {
    $this->category->update(['scoring_mode' => 'jury']);

    $run = finishedTimedRun($this->category, $this->arena, 42_000);
    $run->scores()->create([
        'judge_id' => User::factory()->create()->id,
        'version' => 1,
        'status' => 'submitted',
        'subtotal' => 10,
        'penalty_total' => 0,
        'total' => 10,
        'submitted_at' => now(),
    ]);

    $reasons = app(PublicationService::class)->blockingReasons($this->category);

    expect(collect($reasons)->contains(fn ($r) => str_contains($r, 'belum disetujui')))->toBeTrue();
});

it('does not block publication for a disqualified run without a stopwatch reading', function () {
    finishedTimedRun($this->category, $this->arena, 42_000);

    $dqTeam = Team::factory()->create(['category_id' => $this->category->id]);
    Run::factory()->create([
        'event_id' => $this->category->event_id,
        'category_id' => $this->category->id,
        'arena_id' => $this->arena->id,
        'team_id' => $dqTeam->id,
        'status' => RunStatus::Dq,
        'official_time_ms' => null,
    ]);

    $service = app(PublicationService::class);

    expect($service->blockingReasons($this->category))->toBe([])
        ->and($service->canPublish($this->category))->toBeTrue();
});
