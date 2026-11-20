<?php

namespace App\Events;

use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a category's results change (score approved, corrected, or the
 * category published). The public leaderboard refreshes live.
 */
class StandingsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Category $category,
        public bool $published = false,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        // Public category scoreboard + the event-wide board.
        return [
            new Channel("category.{$this->category->id}"),
            new Channel("event.{$this->category->event_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'standings.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'category_id' => $this->category->id,
            'level' => $this->category->level->value,
            'published' => $this->published,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
