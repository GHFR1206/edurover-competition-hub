<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super Admin audit log browser. The audit trail is append-only: this page
 * can only read and filter it, never mutate it.
 */
class AuditController extends Controller
{
    /**
     * Map each auditable model to the design's audit category chip.
     * Anything unmapped falls back to "Sistem".
     *
     * @var array<string, string>
     */
    private const CATEGORIES = [
        'event' => 'Event',
        'category' => 'Event',
        'rulebook' => 'Event',
        'challenge' => 'Event',
        'arena' => 'Event',
        'heat_slot' => 'Event',
        'run' => 'Event',
        'score' => 'Skor',
        'score_line' => 'Skor',
        'score_object' => 'Skor',
        'result_approval' => 'Skor',
        'category_publication' => 'Skor',
        'team' => 'Tim',
        'registration' => 'Tim',
        'participant' => 'Tim',
        'coach' => 'Tim',
        'school' => 'Tim',
        'user' => 'Peran',
        'arena_assignment' => 'Peran',
    ];

    public function __invoke(Request $request): View
    {
        $category = $request->query('kategori');
        $search = $request->query('q');

        $logs = AuditLog::query()
            ->with('user')
            ->when($search, function ($query, $term) {
                $query->where(fn ($w) => $w
                    ->where('action', 'like', "%{$term}%")
                    ->orWhere('actor_name', 'like', "%{$term}%")
                    ->orWhere('auditable_type', 'like', "%{$term}%"));
            })
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        // Category filtering is done in PHP because it depends on a model→chip
        // mapping rather than a stored column.
        if ($category && $category !== 'Semua') {
            $filtered = $logs->getCollection()
                ->filter(fn (AuditLog $log) => $this->categoryOf($log) === $category)
                ->values();
            $logs->setCollection($filtered);
        }

        return view('super-admin.audit', [
            'logs' => $logs,
            'categories' => ['Semua', 'Event', 'Skor', 'Tim', 'Peran', 'Sistem'],
            'activeCategory' => $category ?? 'Semua',
            'search' => $search,
            'page' => 'audit',
        ]);
    }

    /** Resolve the design chip for a given audit entry. */
    private function categoryOf(AuditLog $log): string
    {
        $type = class_basename($log->auditable_type ?? '');

        return self::CATEGORIES[strtolower($type)] ?? 'Sistem';
    }
}
