<?php

namespace App\Http\Controllers\Coach;

use App\Enums\CategoryStatus;
use App\Enums\RegistrationStatus;
use App\Enums\ScoreStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Team;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(private readonly RegistrationService $registrations) {}

    public function dashboard(Request $request): View
    {
        $teams = $this->coachTeams($request);

        return view('coach.dashboard', [
            'teams' => $teams,
            'pending' => $teams->sum(fn (Team $t) => $t->runs->sum(
                fn ($r) => $r->scores->where('status', ScoreStatus::Submitted)->count()
            )),
        ]);
    }

    public function index(Request $request): View
    {
        return view('coach.teams.index', [
            'teams' => $this->coachTeams($request),
            'canRegister' => $this->registerableCategories()->isNotEmpty(),
            'page' => 'team',
        ]);
    }

    /** Team registration form (team + first participants). */
    public function create(Request $request): View
    {
        $categories = $this->registerableCategories();

        return view('coach.teams.create', [
            'categories' => $categories,
            'page' => 'team',
        ]);
    }

    /** Store a new team together with its participants and a draft registration. */
    public function store(Request $request): RedirectResponse
    {
        $schoolId = $request->user()->coachProfile?->school_id;

        abort_unless($schoolId, 403, 'Akun Anda belum terhubung ke sekolah.');

        // The form always renders four member rows; drop the blank ones so the
        // "min:1" rule reflects the members the coach actually filled in.
        $members = collect($request->input('members', []))
            ->filter(fn ($m) => filled($m['name'] ?? null))
            ->values()
            ->all();

        $request->merge(['members' => $members]);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'short_name' => ['nullable', 'string', 'max:10'],
            'members' => ['required', 'array', 'min:1', 'max:6'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.nisn' => ['nullable', 'digits:10'],
            'members.*.grade' => ['nullable', 'string', 'max:20'],
            'members.*.role' => ['nullable', 'in:leader,member,reserve'],
        ], [], [
            'members.*.name' => 'nama anggota',
            'members.*.nisn' => 'NISN',
        ]);

        $category = Category::findOrFail($validated['category_id']);

        // The coach may only register into a category of the running event.
        abort_unless(
            $this->registerableCategories()->contains('id', $category->id),
            403,
            'Kategori ini tidak sedang membuka pendaftaran.',
        );

        $this->assertTeamSize($category, count($validated['members']));
        $this->assertNisnUnique($validated['members'], $schoolId);

        $team = DB::transaction(function () use ($validated, $category, $schoolId, $request) {
            $team = Team::create([
                'school_id' => $schoolId,
                'category_id' => $category->id,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
                'short_name' => filled($validated['short_name'] ?? null) ? $validated['short_name'] : null,
                'status' => RegistrationStatus::Draft,
            ]);

            foreach (array_values($validated['members']) as $i => $member) {
                $team->participants()->create([
                    'name' => $member['name'],
                    'nisn' => filled($member['nisn'] ?? null) ? $member['nisn'] : null,
                    'nisn_hash' => Participant::hashNisn($member['nisn'] ?? null),
                    'level' => $category->level,
                    'grade' => filled($member['grade'] ?? null) ? $member['grade'] : null,
                    'role' => $member['role'] ?? ($i === 0 ? 'leader' : 'member'),
                ]);
            }

            Registration::create([
                'event_id' => $category->event_id,
                'category_id' => $category->id,
                'team_id' => $team->id,
                'coach_id' => $request->user()->coachProfile?->id,
                'status' => RegistrationStatus::Draft,
            ]);

            return $team;
        });

        return redirect()->route('coach.teams.show', $team)
            ->with('status', "Tim {$team->name} dibuat sebagai draft. Lengkapi data lalu ajukan verifikasi.");
    }

    /** Add one participant to an existing draft/rejected team. */
    public function storeParticipant(Request $request, Team $team): RedirectResponse
    {
        $this->authorizeDraft($request, $team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nisn' => ['nullable', 'digits:10'],
            'grade' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'in:leader,member,reserve'],
        ]);

        $this->assertNisnUnique([$validated], $team->school_id, $team->id);
        $this->assertTeamSize($team->category, $team->participants()->count() + 1);

        $team->participants()->create([
            'name' => $validated['name'],
            'nisn' => filled($validated['nisn'] ?? null) ? $validated['nisn'] : null,
            'nisn_hash' => Participant::hashNisn($validated['nisn'] ?? null),
            'level' => $team->category?->level,
            'grade' => filled($validated['grade'] ?? null) ? $validated['grade'] : null,
            'role' => $validated['role'] ?? 'member',
        ]);

        return back()->with('status', "Anggota {$validated['name']} ditambahkan.");
    }

    /** Remove a participant from a draft/rejected team. */
    public function destroyParticipant(Request $request, Team $team, Participant $participant): RedirectResponse
    {
        $this->authorizeDraft($request, $team);

        abort_unless($participant->team_id === $team->id, 404);

        $participant->delete();

        return back()->with('status', "Anggota {$participant->name} dihapus.");
    }

    /** Submit the team registration for committee verification. */
    public function submit(Request $request, Team $team): RedirectResponse
    {
        abort_unless($this->owns($request, $team), 403);

        $registration = $team->registrations()->latest()->first();

        abort_unless($registration, 404, 'Data pendaftaran tidak ditemukan.');

        if (! in_array($registration->status, [RegistrationStatus::Draft, RegistrationStatus::Rejected], true)) {
            return back()->withErrors(['submit' => 'Pendaftaran ini sudah diajukan atau sudah diverifikasi.']);
        }

        $validated = $request->validate([
            'consent_parent' => ['accepted'],
            'consent_media' => ['accepted'],
        ], [], [
            'consent_parent' => 'persetujuan orang tua',
            'consent_media' => 'izin publikasi media',
        ]);

        $registration->update([
            'consent_parent' => true,
            'consent_media' => true,
            'consent_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->registrations->submit($registration->fresh());

        return back()->with('status', 'Pendaftaran diajukan. Panitia akan memverifikasi data tim Anda.');
    }

    public function show(Request $request, Team $team): View
    {
        abort_unless($this->owns($request, $team), 403);

        $team->load([
            'school', 'category.event', 'participants', 'runs.arena', 'runs.scores',
            'registrations' => fn ($q) => $q->latest(),
        ]);

        $registration = $team->registrations->first();

        return view('coach.teams.show', [
            'team' => $team,
            'registration' => $registration,
            'editable' => $registration
                && in_array($registration->status, [RegistrationStatus::Draft, RegistrationStatus::Rejected], true),
            'page' => 'team',
        ]);
    }

    /** @return Collection<int, Team> */
    private function coachTeams(Request $request): Collection
    {
        $schoolId = $request->user()->coachProfile?->school_id;

        if (! $schoolId) {
            return collect();
        }

        return Team::with(['school', 'category', 'runs.scores', 'registrations'])
            ->where('school_id', $schoolId)
            ->get();
    }

    /** Categories that currently accept new teams for the active event. */
    private function registerableCategories(): Collection
    {
        $event = Event::with('categories')
            ->whereIn('status', ['scheduled', 'running'])
            ->inDisplayOrder()
            ->latest('starts_at')
            ->first();

        return $event
            ? $event->categories
                ->filter(fn (Category $c) => in_array(
                    $c->status,
                    [CategoryStatus::Registration, CategoryStatus::Scheduled],
                    true,
                ))
                ->sortBy('level')
                ->values()
            : collect();
    }

    private function owns(Request $request, Team $team): bool
    {
        return $request->user()->coachProfile?->school_id === $team->school_id;
    }

    private function authorizeDraft(Request $request, Team $team): void
    {
        abort_unless($this->owns($request, $team), 403);

        $status = $team->registrations()->latest()->value('status');

        abort_unless(
            in_array($status, [RegistrationStatus::Draft->value, RegistrationStatus::Rejected->value], true),
            403,
            'Tim yang sudah diajukan tidak dapat diubah.',
        );
    }

    private function assertTeamSize(?Category $category, int $size): void
    {
        if (! $category) {
            return;
        }

        $min = max(1, (int) $category->min_team_members);
        $max = max($min, (int) $category->max_team_members);

        if ($size < $min || $size > $max) {
            throw ValidationException::withMessages([
                'members' => "Kategori {$category->level->publicLabel()} memerlukan {$min}–{$max} anggota.",
            ]);
        }
    }

    /**
     * Reject a NISN that already belongs to another participant (globally, or
     * elsewhere in the same school). Uses the blind index, never decrypts.
     *
     * @param  array<int, array{nisn?:?string}>  $members
     */
    private function assertNisnUnique(array $members, int $schoolId, ?int $ignoreTeamId = null): void
    {
        foreach ($members as $member) {
            $nisn = $member['nisn'] ?? null;

            if (! $nisn) {
                continue;
            }

            $hash = Participant::hashNisn($nisn);

            $exists = Participant::where('nisn_hash', $hash)
                ->when($ignoreTeamId, fn ($q) => $q->where('team_id', '!=', $ignoreTeamId))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'members' => "NISN {$nisn} sudah terdaftar pada peserta lain.",
                ]);
            }
        }
    }
}
