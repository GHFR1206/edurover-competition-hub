<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Records create/update/delete activity to the append-only audit log.
 * Models opt in by declaring an `$auditAttributes` allowlist (empty = all fillable).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->auditSnapshot());
        });

        static::updated(function ($model) {
            $before = array_intersect_key($model->getOriginal(), $model->auditSnapshot());
            $after = $model->auditSnapshot();
            $changed = $model->getChanges();

            if (empty($changed)) {
                return;
            }

            $model->writeAudit(
                'updated',
                array_intersect_key($before, $changed),
                array_intersect_key($after, $changed),
            );
        });

        static::deleted(function ($model) {
            $model->writeAudit('deleted', $model->auditSnapshot(), null);
        });
    }

    /** @return array<string, mixed> */
    protected function auditSnapshot(): array
    {
        $attributes = $this->getAttributes();

        if (! empty($this->auditAttributes ?? [])) {
            return array_intersect_key($attributes, array_flip($this->auditAttributes));
        }

        return $attributes;
    }

    public function writeAudit(string $action, ?array $before, ?array $after, ?string $reason = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'actor_name' => Auth::user()?->name,
            'action' => $action,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'before' => $before,
            'after' => $after,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
            'created_at' => now(),
        ]);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
