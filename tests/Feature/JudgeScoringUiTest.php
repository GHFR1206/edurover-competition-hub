<?php

use App\Enums\CategoryStatus;
use App\Enums\RunStatus;
use App\Enums\ScoreObjectType;
use App\Models\Arena;
use App\Models\ArenaAssignment;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Event;
use App\Models\Rulebook;
use App\Models\Run;
use App\Models\ScoreObject;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->event = Event::factory()->create(['status' => 'running']);
    $this->category = Category::factory()->create(['event_id' => $this->event->id]);
    $this->arena = Arena::factory()->create(['event_id' => $this->event->id]);
    $this->team = Team::factory()->create(['category_id' => $this->category->id]);

    $this->challenge = Challenge::factory()->create(['category_id' => $this->category->id]);

    ScoreObject::factory()->create([
        'challenge_id' => $this->challenge->id,
        'name' => 'Bergerak maju',
        'type' => ScoreObjectType::Achievement,
    ]);

    ScoreObject::factory()->penalty()->create([
        'challenge_id' => $this->challenge->id,
        'name' => 'Penalti tabrakan',
        'points_per_unit' => 15,
    ]);

    $this->judge = User::factory()->create();
    $this->judge->assignRole('judge');

    ArenaAssignment::factory()->create([
        'arena_id' => $this->arena->id,
        'user_id' => $this->judge->id,
        'role' => 'judge',
        'is_active' => true,
    ]);

    $this->run = Run::factory()->create([
        'event_id' => $this->event->id,
        'category_id' => $this->category->id,
        'arena_id' => $this->arena->id,
        'team_id' => $this->team->id,
        'status' => RunStatus::Finished,
    ]);
});

it('renders the judge scoring form with separate manual and penalty sections', function () {
    $response = $this->actingAs($this->judge)
        ->get(route('judge.scores.edit', $this->run))
        ->assertOk();

    $html = $response->getContent();

    // Both sections exist and are clearly labelled.
    expect($html)->toContain('Penilaian manual')
        ->and($html)->toContain('Penalti &amp; pelanggaran')
        ->and($html)->toContain('Bergerak maju')
        ->and($html)->toContain('Penalti tabrakan');

    // Penalty lives inside its own section, not mixed with achievements.
    expect(substr_count($html, 'class="parameter"'))->toBe(2);
});

it('lets the judge submit a penalty that reduces the total', function () {
    $objects = $this->challenge->scoreObjects()->get();

    $achievement = $objects->firstWhere('type', ScoreObjectType::Achievement);
    $penalty = $objects->firstWhere('type', ScoreObjectType::Penalty);

    $this->actingAs($this->judge)
        ->post(route('judge.scores.store', $this->run), [
            'entries' => [
                ['score_object_id' => $achievement->id, 'quantity' => 2, 'achieved' => 1],
                ['score_object_id' => $penalty->id, 'quantity' => 1, 'achieved' => 1],
            ],
        ])
        ->assertRedirect();

    $score = $this->run->fresh()->scores()->firstOrFail();

    expect($score->penalty_total)->toBe(15)
        ->and($score->total)->toBe($score->subtotal - 15);
});

it('keeps judge pages visible on phones (no display:none regression)', function () {
    $css = file_get_contents(resource_path('css/roborace/juri.css'));

    // The mobile block must not hide the page container that every judge
    // view uses. (The original React design did, which broke scoring on phones.)
    preg_match('/@media \(max-width: 600px\)\s*\{(.*?)\n\}/s', $css, $match);

    expect($match)->not->toBeEmpty();

    $mobileBlock = $match[1];

    expect($mobileBlock)->not->toContain(".page {\n    display: none;")
        ->and($css)->toContain('@media (max-width: 600px)')
        ->and($css)->toContain('.score-section')
        ->and($css)->toContain('.penalty-section');
});

/* -------------------------------------------- event admin scoring mode */

it('lets an event admin switch a category to time scoring', function () {
    $admin = User::factory()->create();
    $admin->assignRole('event_admin');

    Rulebook::factory()->create(['category_id' => $this->category->id]);

    $this->actingAs($admin)
        ->get(route('eventadmin.setup.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->patch(route('eventadmin.setup.scoring', $this->category), ['scoring_mode' => 'time'])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($this->category->fresh()->scoring_mode)->toBe('time');
});

it('rejects an invalid scoring mode', function () {
    $admin = User::factory()->create();
    $admin->assignRole('event_admin');

    $this->actingAs($admin)
        ->patch(route('eventadmin.setup.scoring', $this->category), ['scoring_mode' => 'vibes'])
        ->assertSessionHasErrors('scoring_mode');
});

it('locks the scoring mode once the category is published', function () {
    $admin = User::factory()->create();
    $admin->assignRole('event_admin');

    $this->category->update(['status' => CategoryStatus::Published]);

    $this->actingAs($admin)
        ->patch(route('eventadmin.setup.scoring', $this->category), ['scoring_mode' => 'time'])
        ->assertSessionHasErrors('scoring_mode');

    expect($this->category->fresh()->scoring_mode)->toBe('jury');
});
