<?php

namespace Database\Seeders;

use App\Enums\CategoryStatus;
use App\Enums\Level;
use App\Enums\RegistrationStatus;
use App\Enums\Role as RoleEnum;
use App\Enums\RunStatus;
use App\Enums\ScoreObjectType;
use App\Enums\ScoreStatus;
use App\Models\Arena;
use App\Models\ArenaAssignment;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Coach;
use App\Models\Event;
use App\Models\HeatSlot;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Run;
use App\Models\School;
use App\Models\Score;
use App\Models\ScoreObject;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo dataset ONLY. Score objects are examples, not a final rulebook.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::create([
            'name' => 'NanoRobotic Competition 2026 (Demo)',
            'slug' => 'nanorobotic-competition-2026-demo',
            'description' => 'Data demo untuk pengembangan. Bukan event resmi.',
            'venue' => 'Gedung Robotika Nasional',
            'starts_at' => now()->addWeek()->toDateString(),
            'ends_at' => now()->addWeek()->addDays(1)->toDateString(),
            'status' => 'scheduled',
        ]);

        // One staff/coach user per role.
        $users = $this->seedUsers();

        $arenas = collect(range(1, 4))->map(fn (int $i) => Arena::create([
            'event_id' => $event->id,
            'name' => "Arena {$i}",
            'code' => "ARENA-{$i}",
            'location' => 'Hall Utama',
            'status' => 'idle',
            'capacity_per_slot' => 1,
            'slot_minutes' => 15,
            'is_active' => true,
        ]));

        foreach (Level::cases() as $level) {
            $category = Category::create([
                'event_id' => $event->id,
                'level' => $level,
                'name' => $level->label().' — '.$level->persona(),
                'persona' => $level->persona(),
                'description' => $level->challengeName(),
                'status' => CategoryStatus::Scheduled,
                'min_team_members' => 1,
                'max_team_members' => 3,
                'settings' => ['aggregation' => ['attempt' => 'best', 'judges' => 'average']],
            ]);

            $category->rulebooks()->create([
                'version' => 1,
                'title' => 'Rulebook v1 (CONTOH, bukan final rulebook)',
                'summary' => 'Aturan contoh. Jangan dipakai untuk penilaian resmi.',
                'aggregation' => ['attempt' => 'best', 'judges' => 'average'],
                'tie_break_order' => ['primary_achievement', 'penalty', 'time', 'extra_run'],
                'retry_rules' => ['allow_re_run' => true, 'max_attempts' => 2],
                'status' => 'draft',
            ]);

            $challenge = Challenge::create([
                'category_id' => $category->id,
                'name' => $level->challengeName(),
                'theme' => $level->persona(),
                'description' => 'Tantangan '.$level->label(),
                'mission_brief' => 'Data contoh untuk '.$level->challengeName().'.',
                'position' => 0,
                'is_active' => true,
            ]);

            foreach (ExampleChallengeData::capabilityTags($level) as $i => $tag) {
                $challenge->category->capabilityTags()->firstOrCreate(
                    ['code' => $tag['code']],
                    ['name' => $tag['name'], 'position' => $i],
                );
            }

            $capabilityMap = $challenge->category->capabilityTags()->get()->keyBy('code');

            foreach (ExampleChallengeData::scoreObjects($level) as $i => $obj) {
                ScoreObject::create([
                    'challenge_id' => $challenge->id,
                    'capability_tag_id' => $this->guessCapabilityId($obj['code'], $capabilityMap),
                    'code' => $obj['code'],
                    'name' => $obj['name'],
                    'type' => ScoreObjectType::from($obj['type']),
                    'points_per_unit' => $obj['points'],
                    'max_quantity' => $obj['max'],
                    'requires_evidence' => $obj['evidence'],
                    'position' => $i,
                ]);
            }

            $this->seedTeams($event, $category, 8, $users['coach']);
        }

        $this->seedRuns($arenas);
    }

    /** @return array<string, User> */
    private function seedUsers(): array
    {
        $definitions = [
            RoleEnum::SuperAdmin->value => 'admin@nanorobotic.test',
            RoleEnum::EventAdmin->value => 'eventadmin@nanorobotic.test',
            RoleEnum::ArenaOperator->value => 'operator@nanorobotic.test',
            RoleEnum::Judge->value => 'juri@nanorobotic.test',
            RoleEnum::ChiefJudge->value => 'chief@nanorobotic.test',
            RoleEnum::Coach->value => 'coach@nanorobotic.test',
            RoleEnum::Public->value => 'publik@nanorobotic.test',
        ];

        $users = [];

        foreach ($definitions as $role => $email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => RoleEnum::from($role)->label(), 'password' => 'password', 'is_active' => true],
            );
            $user->syncRoles([$role]);
            $users[$role] = $user;
        }

        // A few extra judges for multi-arena parallel operations.
        foreach (range(1, 3) as $i) {
            $judge = User::firstOrCreate(
                ['email' => "juri{$i}@nanorobotic.test"],
                ['name' => "Juri {$i}", 'password' => 'password', 'is_active' => true],
            );
            $judge->syncRoles([RoleEnum::Judge->value]);
        }

        return $users;
    }

    private function guessCapabilityId(string $code, $capabilityMap): ?int
    {
        $prefix = strtolower(substr($code, 0, 3));

        $guess = match (true) {
            str_contains($code, 'COLOR') || str_contains($code, 'RGB') => 'sensor.color',
            str_contains($code, 'LINE') => 'sensor.line',
            str_contains($code, 'OBSTACLE') => 'sensor.ultrasonic',
            str_contains($code, 'ENCODER') => 'sensor.encoder',
            str_contains($code, 'HAZARD') => 'sensor.hazard',
            str_contains($code, 'PID') => 'control.pid',
            str_contains($code, 'DELIVER') || str_contains($code, 'SUPPLY') || str_contains($code, 'PICK') => null,
            default => null,
        };

        return $guess ? ($capabilityMap[$guess]->id ?? null) : null;
    }

    private function seedTeams(Event $event, Category $category, int $count, User $coach): void
    {
        $names = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel',
            'India', 'Juliet', 'Kilo', 'Lima', 'Mike', 'November', 'Oscar', 'Papa'];

        // The demo coach's school owns the first two teams of every category so a
        // coach login has visible data. Remaining teams belong to other schools.
        $coachSchool = School::firstOrCreate(
            ['npsn' => '10000001'],
            ['name' => 'Sekolah Demo NanoRobotic', 'level' => $category->level, 'city' => 'Jakarta'],
        );

        Coach::firstOrCreate(
            ['user_id' => $coach->id],
            ['school_id' => $coachSchool->id, 'name' => $coach->name, 'email' => $coach->email],
        );

        for ($i = 0; $i < $count; $i++) {
            $name = $names[$i % count($names)].' '.$category->level->label().' '.($i + 1);

            $school = $i < 2
                ? $coachSchool
                : School::factory()->create(['level' => $category->level]);

            $team = Team::create([
                'school_id' => $school->id,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => str()->slug($name),
                'short_name' => strtoupper(substr($names[$i % count($names)], 0, 3)),
                'status' => RegistrationStatus::Verified,
            ]);

            foreach (range(1, 2) as $p) {
                $nisn = (string) fake()->unique()->numberBetween(1000000000, 9999999999);
                $team->participants()->create([
                    'name' => fake()->name(),
                    'nisn' => $nisn,
                    'nisn_hash' => Participant::hashNisn($nisn),
                    'level' => $category->level,
                    'grade' => (string) fake()->numberBetween(1, 12),
                    'role' => $p === 1 ? 'leader' : 'member',
                ]);
            }

            Registration::create([
                'event_id' => $event->id,
                'category_id' => $category->id,
                'team_id' => $team->id,
                'coach_id' => Coach::where('user_id', $coach->id)->value('id'),
                'status' => RegistrationStatus::Verified,
                'consent_parent' => true,
                'consent_media' => true,
                'consent_at' => now(),
                'submitted_at' => now(),
                'verified_at' => now(),
            ]);
        }
    }

    private function seedRuns($arenas): void
    {
        $judge = User::where('email', 'juri@nanorobotic.test')->first();
        $operator = User::where('email', 'operator@nanorobotic.test')->first();

        // Assign the demo judge and operator to every arena so their dashboards
        // and scoring queues are populated out of the box.
        foreach ($arenas as $arena) {
            if ($operator) {
                ArenaAssignment::firstOrCreate(
                    ['arena_id' => $arena->id, 'user_id' => $operator->id, 'role' => 'operator'],
                    ['is_active' => true, 'starts_at' => now()->subHour()],
                );
            }

            if ($judge) {
                ArenaAssignment::firstOrCreate(
                    ['arena_id' => $arena->id, 'user_id' => $judge->id, 'role' => 'judge'],
                    ['is_active' => true, 'starts_at' => now()->subHour()],
                );
            }
        }

        $index = 0;

        foreach (Category::with('teams', 'event')->get() as $category) {
            $arena = $arenas[$index % $arenas->count()];
            $slot = HeatSlot::create([
                'arena_id' => $arena->id,
                'category_id' => $category->id,
                'slot_no' => 1,
                'label' => 'Heat 1 '.$category->level->label(),
                'starts_at' => now()->addHour(),
                'ends_at' => now()->addHour()->addMinutes(15),
                'status' => 'scheduled',
            ]);

            foreach ($category->teams->take(2) as $slotIndex => $team) {
                $status = $slotIndex === 0 ? RunStatus::Finished : RunStatus::Scheduled;

                $run = Run::create([
                    'event_id' => $category->event_id,
                    'category_id' => $category->id,
                    'arena_id' => $arena->id,
                    'team_id' => $team->id,
                    'heat_slot_id' => $slot->id,
                    'attempt_no' => 1,
                    'status' => $status,
                    'scheduled_at' => now()->addHour(),
                    'operator_id' => $operator?->id,
                ]);

                if ($status === RunStatus::Finished && $judge) {
                    Score::create([
                        'run_id' => $run->id,
                        'judge_id' => $judge->id,
                        'version' => 1,
                        'status' => ScoreStatus::Submitted,
                        'subtotal' => 120,
                        'penalty_total' => 0,
                        'total' => 120,
                        'submitted_at' => now(),
                    ]);
                }
            }

            $index++;
        }
    }
}
