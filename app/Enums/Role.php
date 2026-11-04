<?php

namespace App\Enums;

/**
 * The seven platform roles. No others are allowed by product agreement.
 */
enum Role: string
{
    case SuperAdmin = 'super_admin';
    case EventAdmin = 'event_admin';
    case ArenaOperator = 'arena_operator';
    case Judge = 'judge';
    case ChiefJudge = 'chief_judge';
    case Coach = 'coach';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::EventAdmin => 'Event Admin',
            self::ArenaOperator => 'Operator Arena',
            self::Judge => 'Juri',
            self::ChiefJudge => 'Chief Judge',
            self::Coach => 'Coach / Sekolah',
            self::Public => 'Publik',
        };
    }

    /** Roles that access the Filament staff panel. */
    public static function staff(): array
    {
        return [self::SuperAdmin, self::EventAdmin, self::ArenaOperator, self::ChiefJudge];
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
