<?php

use App\Enums\RunStatus;
use App\Enums\ScoreStatus;
use App\Models\Category;
use App\Models\Run;
use App\Models\Score;
use App\Models\User;
use App\Services\PublicationService;
use App\Services\ScoringService;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);
});

it('lets a judge save and submit a score', function () {
    $judge = User::where('email', 'juri@nanorobotic.test')->firstOrFail();

    $run = Run::whereHas('category.challenges.scoreObjects')->firstOrFail();
    $objects = $run->category->challenges->first()->scoreObjects;

    $entries = $objects->map(fn ($o) => [
        'score_object_id' => $o->id,
        'quantity' => $o->type->isPenalty() ? 0 : 1,
        'achieved' => true,
    ])->all();

    $score = app(ScoringService::class)->saveDraft($run, $judge, $entries);

    expect($score->status)->toBe(ScoreStatus::Draft)
        ->and($score->lines)->toHaveCount($objects->count())
        ->and($score->total)->toBeInt();

    $submitted = app(ScoringService::class)->submit($score, $judge);
    expect($submitted->status)->toBe(ScoreStatus::Submitted);
});

it('enforces maker-checker on approval', function () {
    $judge = User::where('email', 'juri@nanorobotic.test')->firstOrFail();
    $chief = User::where('email', 'chief@nanorobotic.test')->firstOrFail();

    $score = Score::where('status', ScoreStatus::Submitted->value)->firstOrFail();

    expect($judge->can('approve', $score))->toBeFalse()
        ->and($chief->can('approve', $score))->toBeTrue();
});

it('publishes a category only when clean', function () {
    $chief = User::where('email', 'chief@nanorobotic.test')->firstOrFail();
    $service = app(PublicationService::class);

    $category = Category::whereHas('runs')->firstOrFail();

    // Fresh demo data has unfinished runs and unapproved scores -> blocked.
    $reasons = $service->blockingReasons($category);
    expect($reasons)->not->toBeEmpty();

    // Bring every run to a settled state and approve every score.
    foreach ($category->runs as $run) {
        $run->status = RunStatus::Finished;
        $run->save();

        if ($run->scores->isEmpty()) {
            $judge = User::where('email', 'juri@nanorobotic.test')->firstOrFail();
            $run->scores()->create([
                'judge_id' => $judge->id,
                'version' => 1,
                'status' => ScoreStatus::Submitted,
                'subtotal' => 0,
                'penalty_total' => 0,
                'total' => 0,
                'submitted_at' => now(),
            ]);
            $run->load('scores');
        }

        foreach ($run->scores as $score) {
            $service->approve($score, $chief);
        }
    }

    $blocked = $service->blockingReasons($category->fresh());
    expect($blocked)->toBeEmpty();

    expect($service->publish($category->fresh(), $chief))->toBeEmpty();
    expect($category->fresh()->isPublished())->toBeTrue();
});

it('blocks publication while scores are unapproved', function () {
    $chief = User::where('email', 'chief@nanorobotic.test')->firstOrFail();
    $service = app(PublicationService::class);

    $category = Category::whereHas('runs')->firstOrFail();
    $category->runs()->update(['status' => RunStatus::Finished->value]);

    $reasons = $service->blockingReasons($category->fresh());
    expect($reasons)->not->toBeEmpty();

    $result = $service->publish($category->fresh(), $chief);
    expect($result)->not->toBeEmpty();
    expect($category->fresh()->isPublished())->toBeFalse();
});
