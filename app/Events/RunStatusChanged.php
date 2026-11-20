<?php

namespace App\Events;

use App\Models\Run;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired whenever an operator changes a run's lifecycle status. Public
 * watchers (schedule) and the run's arena staff get it live.
 */
class RunStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Run $run) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        $channels = [
            // Public schedule board for the run's level.
            new Channel("event.{$this->run->event_id}"),
        ];

        if ($this->run->arena_id) {
            $channels[] = new PrivateChannel("arena.{$this->run->arena_id}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'run.status';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'run_id' => $this->run->id,
            'arena_id' => $this->run->arena_id,
            'status' => $this->run->status->value,
            'label' => $this->run->status->label(),
            'team' => $this->run->team?->name,
            'arena' => $this->run->arena?->name,
            'category_id' => $this->run->category_id,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
