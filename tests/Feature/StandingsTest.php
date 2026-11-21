<?php

use App\Enums\RunStatus;
use App\Enums\ScoreObjectType;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Event;
use App\Models\Rulebook;
use App\Models\Run;
use App\Models\Score;
use App\Models\ScoreObject;
use App\Models\Team;
use App\Models\User;
use App\Services\StandingsService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->event = Event::factory()->create(['status' => 'running']);
    $this->category = Category::factory()->create(['event_id' => $this->event->id]);
    $this->arena = Arena::factory()->create(['event_id' => $this->event->id]);

    $this->rulebook = Rulebook::factory()->create([
        'category_id' => $this->category->id,
        'aggregation' => ['attempt' => 'best', 'judges' => 'average'],
        'tie_break_order' => ['primary_achievement', 'penalty', 'time', 'extra_run'],
    ]);

    $this->challenge = Challenge::factory()->create(['category_id' => $this->category->id]);

    $this->achievement = ScoreObject::factory()->create([
        'challenge_id' => $this->challenge->id,
        'type' => ScoreObjectType::Achievement,
        'points_per_unit' => 10,
    ]);

    $this->penalty = ScoreObject::factory()->penalty()->create([
        'challenge_id' => $this->challenge->id,
        'points_per_unit' => 10,
    ]);

    $this->judges = User::factory()->count(3)->create();
});

/**
 * Create a finished run for a team with one approved score per judge.
 *
 * @param  array<int, int>  $judgeTotals
 */
function runWithScores(
    Team $team,
    array $judgeTotals,
    ?int $timeMs = null,
    int $attempt = 1,
    ?int $achievements = 0,
    int $penaltyTotal = 0,
): Run {
    $run = Run::factory()->create([
        'event_id' => $team->category->event_id,
        'category_id' => $team->category_id,
        'arena_id' => test()->arena->id,
        'team_id' => $team->id,
        'attempt_no' => $attempt,
        'status' => RunStatus::Finished,
        'official_time_ms' => $timeMs,
        'lap_count' => $timeMs !== null ? 1 : null,
    ]);

    foreach ($judgeTotals as $index => $total) {
        $judge = test()->judges[$index];

        $score = Score::factory()->approved()->create([
            'run_id' => $run->id,
            'judge_id' => $judge->id,
            'subtotal' => $total + $penaltyTotal,
            'penalty_total' => $penaltyTotal,
            'total' => $total,
        ]);

        // Each achievement line needs its own score object (unique index).
        for ($i = 0; $i < $achievements; $i++) {
            $object = ScoreObject::factory()->create([
                'challenge_id' => test()->challenge->id,
                'type' => ScoreObjectType::Achievement,
                'points_per_unit' => 10,
            ]);

            $score->lines()->create([
                'score_object_id' => $object->id,
                'quantity' => 1,
                'achieved' => true,
                'points_per_unit_snapshot' => 10,
                'computed_points' => 10,
            ]);
        }
    }

    return $run->fresh();
}

/* ------------------------------------------------- judge aggregation */

it('averages judge scores by default', function () {
    $team = Team::factory()->create(['category_id' => $this->category->id]);

    // 90 + 60 + 30 -> average 60, not the max (90).
    runWithScores($team, [90, 60, 30]);

    $row = app(StandingsService::class)->forCategory($this->category->fresh())->first();

    expect($row['best_total'])->toBe(60);
});

it('takes the best judge score when the rulebook says best', function () {
    $this->rulebook->update(['aggregation' => ['attempt' => 'best', 'judges' => 'best']]);

    $team = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($team, [90, 60, 30]);

    $row = app(StandingsService::class)->forCategory($this->category->fresh())->first();

    expect($row['best_total'])->toBe(90);
});

it('sums judge scores when the rulebook says total', function () {
    $this->rulebook->update(['aggregation' => ['attempt' => 'best', 'judges' => 'total']]);

    $team = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($team, [90, 60, 30]);

    $row = app(StandingsService::class)->forCategory($this->category->fresh())->first();

    expect($row['best_total'])->toBe(180);
});

it('uses the best attempt by default', function () {
    $team = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($team, [40], attempt: 1);
    runWithScores($team, [80], attempt: 2);

    $row = app(StandingsService::class)->forCategory($this->category->fresh())->first();

    expect($row['best_total'])->toBe(80)
        ->and($row['runs'])->toBe(2);
});

it('sums attempts when the rulebook says total', function () {
    $this->rulebook->update(['aggregation' => ['attempt' => 'total', 'judges' => 'average']]);

    $team = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($team, [40], attempt: 1);
    runWithScores($team, [80], attempt: 2);

    $row = app(StandingsService::class)->forCategory($this->category->fresh())->first();

    expect($row['best_total'])->toBe(120);
});

/* ------------------------------------------------------- tie-breaks */

it('breaks a points tie on primary achievement count', function () {
    $moreAchievements = Team::factory()->create(['category_id' => $this->category->id]);
    $fewerAchievements = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($moreAchievements, [100], achievements: 3);
    runWithScores($fewerAchievements, [100], achievements: 1);

    $standings = app(StandingsService::class)->forCategory($this->category->fresh());

    expect($standings[0]['team']->id)->toBe($moreAchievements->id)
        ->and($standings[0]['best_total'])->toBe(100)
        ->and($standings[1]['team']->id)->toBe($fewerAchievements->id);
});

it('breaks a points tie on fewer penalties when achievements are equal', function () {
    $clean = Team::factory()->create(['category_id' => $this->category->id]);
    $penalised = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($clean, [100], achievements: 1, penaltyTotal: 0);
    runWithScores($penalised, [100], achievements: 1, penaltyTotal: 20);

    $standings = app(StandingsService::class)->forCategory($this->category->fresh());

    expect($standings[0]['team']->id)->toBe($clean->id)
        ->and($standings[1]['team']->id)->toBe($penalised->id);
});

it('falls back to arena time when points, achievements and penalties tie', function () {
    $fast = Team::factory()->create(['category_id' => $this->category->id]);
    $slow = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($fast, [100], timeMs: 45_000, achievements: 1);
    runWithScores($slow, [100], timeMs: 60_000, achievements: 1);

    $standings = app(StandingsService::class)->forCategory($this->category->fresh());

    expect($standings[0]['team']->id)->toBe($fast->id)
        ->and($standings[0]['time_ms'])->toBe(45_000)
        ->and($standings[1]['team']->id)->toBe($slow->id);
});

it('honours a custom tie-break order', function () {
    // Time first: the slower team on points can win if it is faster.
    $this->rulebook->update(['tie_break_order' => ['time', 'primary_achievement']]);

    $fast = Team::factory()->create(['category_id' => $this->category->id]);
    $slow = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($fast, [100], timeMs: 30_000);
    runWithScores($slow, [100], timeMs: 90_000);

    $standings = app(StandingsService::class)->forCategory($this->category->fresh());

    expect($standings[0]['team']->id)->toBe($fast->id)
        ->and($standings[0]['tie_break'])->toBe('time>primary_achievement');
});

it('never ranks an unapproved score as official', function () {
    $team = Team::factory()->create(['category_id' => $this->category->id]);

    $run = Run::factory()->create([
        'event_id' => $this->event->id,
        'category_id' => $this->category->id,
        'arena_id' => $this->arena->id,
        'team_id' => $team->id,
        'status' => RunStatus::Finished,
    ]);

    Score::factory()->submitted()->create([
        'run_id' => $run->id,
        'judge_id' => $this->judges[0]->id,
        'total' => 999,
    ]);

    $row = app(StandingsService::class)->forCategory($this->category->fresh())->first();

    expect($row['best_total'])->toBe(0)
        ->and($row['approved'])->toBeFalse();
});

/* --------------------------------------------------------- time mode */

it('ranks time-mode categories by official time and ignores judge points', function () {
    $this->category->update(['scoring_mode' => 'time']);

    $fast = Team::factory()->create(['category_id' => $this->category->id]);
    $slow = Team::factory()->create(['category_id' => $this->category->id]);

    // The slower team has the higher judge score; time mode must ignore it.
    runWithScores($slow, [500], timeMs: 80_000);
    runWithScores($fast, [10], timeMs: 50_000);

    $standings = app(StandingsService::class)->forCategory($this->category->fresh());

    expect($standings[0]['team']->id)->toBe($fast->id)
        ->and($standings[0]['best_total'])->toBe(0)
        ->and($standings[0]['mode'])->toBe('time')
        ->and($standings[1]['team']->id)->toBe($slow->id);
});

it('uses a team best time across attempts in time mode', function () {
    $this->category->update(['scoring_mode' => 'time']);

    $team = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($team, [], timeMs: 70_000, attempt: 1);
    runWithScores($team, [], timeMs: 55_000, attempt: 2);

    $row = app(StandingsService::class)->forCategory($this->category->fresh())->first();

    expect($row['time_ms'])->toBe(55_000);
});

it('never ranks a disqualified run by its stopwatch reading in time mode', function () {
    $this->category->update(['scoring_mode' => 'time']);

    $clean = Team::factory()->create(['category_id' => $this->category->id]);
    $disqualified = Team::factory()->create(['category_id' => $this->category->id]);

    runWithScores($clean, [], timeMs: 90_000);

    // The device stopped the clock, but the run was disqualified: the fast
    // reading must be ignored so the DQ team cannot win the category.
    $dq = runWithScores($disqualified, [], timeMs: 10_000);
    $dq->update(['status' => RunStatus::Dq]);

    $service = app(StandingsService::class);
    $standings = $service->forCategory($this->category->fresh());

    expect($standings[0]['team']->id)->toBe($clean->id)
        ->and($standings[0]['time_ms'])->toBe(90_000)
        ->and($standings[1]['team']->id)->toBe($disqualified->id)
        ->and($standings[1]['time_ms'])->toBeNull()
        ->and($standings[1]['approved'])->toBeFalse();
});

it('does not treat a disqualified stopwatch reading as a public result', function () {
    $this->category->update(['scoring_mode' => 'time']);

    $team = Team::factory()->create(['category_id' => $this->category->id]);
    $run = runWithScores($team, [], timeMs: 10_000);
    $run->update(['status' => RunStatus::Dq]);

    $service = app(StandingsService::class);

    expect($service->hasApprovedScores($this->category->fresh()))->toBeFalse();

    $run->update(['status' => RunStatus::Finished]);

    expect($service->hasApprovedScores($this->category->fresh()))->toBeTrue();
});

it('formats stopwatch values for display', function () {
    expect(StandingsService::formatMs(65_432))->toBe('01:05.432')
        ->and(StandingsService::formatMs(1_500))->toBe('00:01.500')
        ->and(StandingsService::formatMs(null))->toBe('—');
});
