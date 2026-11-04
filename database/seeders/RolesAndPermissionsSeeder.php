<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the seven platform roles and the granular permissions
 * referenced by policies. No role beyond these seven is allowed.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Event & rulebook management
            'event.manage', 'arena.manage', 'schedule.manage', 'challenge.manage', 'rulebook.manage', 'rulebook.lock',
            // Run control
            'run.control', 'run.assign',
            // Scoring
            'score.input', 'score.submit',
            // Verification & publication
            'score.review', 'score.approve', 'score.reject', 'score.reopen', 'category.publish',
            'appeal.manage', 'rerun.manage',
            // Results
            'results.view.all', 'results.view.arena', 'results.view.own_team', 'results.view.published',
            // Registration
            'registration.manage', 'registration.verify',
            // Evidence & audit
            'evidence.upload', 'evidence.view', 'audit.view',
            // Export
            'export.results', 'export.certificates', 'export.curation',
            // Telemetry ingest (gated by feature flag elsewhere)
            'telemetry.ingest',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $matrix = [
            RoleEnum::SuperAdmin->value => [
                'event.manage', 'arena.manage', 'schedule.manage', 'challenge.manage', 'rulebook.manage', 'rulebook.lock',
                'run.assign', 'results.view.all', 'registration.manage', 'registration.verify',
                'evidence.view', 'audit.view', 'export.results', 'export.certificates', 'export.curation',
            ],
            RoleEnum::EventAdmin->value => [
                'event.manage', 'arena.manage', 'schedule.manage', 'challenge.manage', 'rulebook.manage', 'rulebook.lock',
                'run.assign', 'results.view.all', 'registration.manage', 'registration.verify',
                'evidence.view', 'audit.view', 'export.results', 'export.certificates', 'export.curation',
            ],
            RoleEnum::ArenaOperator->value => [
                'run.control', 'results.view.arena', 'evidence.upload',
            ],
            RoleEnum::Judge->value => [
                'score.input', 'score.submit', 'evidence.upload', 'results.view.own_team',
            ],
            RoleEnum::ChiefJudge->value => [
                'score.review', 'score.approve', 'score.reject', 'score.reopen', 'category.publish',
                'appeal.manage', 'rerun.manage', 'results.view.all', 'evidence.view', 'audit.view',
                'export.results', 'export.certificates', 'export.curation',
            ],
            RoleEnum::Coach->value => [
                'results.view.own_team', 'export.certificates',
            ],
            RoleEnum::Public->value => [
                'results.view.published',
            ],
        ];

        foreach ($matrix as $role => $rolePermissions) {
            $model = Role::findOrCreate($role, 'web');
            $model->syncPermissions($rolePermissions);
        }
    }
}
