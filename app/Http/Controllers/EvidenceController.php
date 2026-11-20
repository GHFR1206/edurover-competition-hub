<?php

namespace App\Http\Controllers;

use App\Models\EvidenceFile;
use App\Models\Run;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Serves private evidence files to authorised users only.
 *
 * Evidence lives on the private disk, so it is never linkable by URL alone.
 * Access is granted to staff (audit/evidence.view), to any judge or operator
 * assigned to the run's arena, to the run's own coach, and to the uploading
 * judge. Everyone else is denied.
 */
class EvidenceController extends Controller
{
    public function show(Request $request, EvidenceFile $evidence): Response
    {
        abort_unless($this->canView($request->user(), $evidence), 403);

        abort_unless(Storage::disk($evidence->disk)->exists($evidence->path), 404, 'Berkas bukti tidak ditemukan.');

        return Storage::disk($evidence->disk)->response(
            $evidence->path,
            $evidence->original_name ?: basename($evidence->path),
            ['Content-Type' => $evidence->mime_type ?: 'application/octet-stream'],
        );
    }

    private function canView(User $user, EvidenceFile $evidence): bool
    {
        if ($user->hasAnyPermission(['audit.view', 'evidence.view'])) {
            return true;
        }

        $run = Run::with('team')->find($evidence->run_id);

        if (! $run) {
            return false;
        }

        // The judge who uploaded it always keeps access to their own evidence.
        if ($evidence->uploaded_by === $user->id) {
            return true;
        }

        // The run's own coach (via their school).
        if ($user->coachProfile?->school_id && $user->coachProfile->school_id === $run->team?->school_id) {
            return true;
        }

        // Assigned arena staff (judge / operator).
        return $user->arenaAssignments()
            ->where('arena_id', $run->arena_id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Shared upload helper used by the judge and operator flows.
     *
     * @return array<string, mixed>|null
     */
    public static function store(Request $request, Run $run, ?int $scoreId = null): ?EvidenceFile
    {
        if (! $request->hasFile('evidence_file')) {
            return null;
        }

        $file = $request->file('evidence_file');

        $path = $file->store('evidence/'.now()->format('Y/m'), 'local');

        return EvidenceFile::create([
            'uuid' => (string) Str::uuid(),
            'run_id' => $run->id,
            'score_id' => $scoreId,
            'uploaded_by' => $request->user()->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'kind' => $request->input('evidence_kind', 'photo'),
            'caption' => $request->input('evidence_caption'),
        ]);
    }
}
