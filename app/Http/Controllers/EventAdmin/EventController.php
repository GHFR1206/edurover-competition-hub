<?php

namespace App\Http\Controllers\EventAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Event Admin event list + creation + one-way status advance.
 */
class EventController extends Controller
{
    /** Mirrors the RoboRace one-way event flow. */
    private const FLOW = ['draft' => 'scheduled', 'scheduled' => 'running', 'running' => 'finished'];

    public function index(Request $request): View
    {
        return view('event-admin.events', [
            'events' => Event::withCount('categories')->latest('starts_at')->get(),
            'page' => 'events',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
        ]);

        Event::create($validated + [
            'slug' => str()->slug($validated['name']).'-'.str()->random(4),
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('eventadmin.events.index')
            ->with('status', "Event {$validated['name']} dibuat sebagai Draft.");
    }

    public function advance(Request $request, Event $event)
    {
        $next = self::FLOW[$event->status] ?? null;

        if (! $next) {
            return back()->withErrors(['advance' => 'Event sudah berada di tahap akhir.']);
        }

        // Guard: a draft can only open registration once its setup is complete.
        if ($event->status === 'draft' && $event->categories()->count() === 0) {
            return back()->withErrors(['advance' => 'Setup harus lengkap sebelum membuka registrasi.']);
        }

        $previous = $event->status;
        $event->update(['status' => $next]);

        return redirect()->route('eventadmin.events.index')
            ->with('status', "{$event->name} maju dari {$previous} ke {$next}.");
    }
}
