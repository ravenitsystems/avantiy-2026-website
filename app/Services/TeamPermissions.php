<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\TeamRole;
use Illuminate\Support\Facades\DB;

class TeamPermissions
{
    public static function seedPermissions(): void
    {
        $now = now();

        foreach (config('permissions.permission_groups', []) as $group) {
            PermissionGroup::updateOrCreate(
                ['key' => $group['key']],
                array_merge($group, ['updated_at' => $now])
            );
        }

        $groupIds = PermissionGroup::pluck('id', 'key')->all();

        foreach (config('permissions.permissions', []) as $perm) {
            $groupKey = $perm['group_key'] ?? null;
            $permissionGroupId = $groupKey ? ($groupIds[$groupKey] ?? null) : null;

            $data = array_merge($perm, [
                'permission_group_id' => $permissionGroupId,
                'updated_at' => $now,
            ]);
            unset($data['group_key']);

            Permission::updateOrCreate(
                ['key' => $perm['key']],
                $data
            );
        }
    }

    /**
     * Seed global preset roles (team_id = null). Idempotent.
     */
    public static function seedPresetRoles(): void
    {
        $presets = config('permissions.preset_roles', []);

        $ownerRole = TeamRole::firstOrCreate(
            ['team_id' => null, 'is_owner' => true],
            [
                'name' => $presets['owner']['name'] ?? 'Owner',
                'description' => $presets['owner']['description'] ?? 'Full access to all team features.',
                'is_preset' => true,
                'sort_order' => 0,
            ]
        );

        $adminRole = TeamRole::firstOrCreate(
            ['team_id' => null, 'name' => $presets['admin']['name'] ?? 'Admin', 'is_preset' => true],
            [
                'description' => $presets['admin']['description'] ?? 'Can manage team members, clients, and websites.',
                'is_owner' => false,
                'sort_order' => 1,
            ]
        );
        self::syncRolePermissions($adminRole, $presets['admin']['permissions'] ?? []);

        $memberRole = TeamRole::firstOrCreate(
            ['team_id' => null, 'name' => $presets['member']['name'] ?? 'Member', 'is_preset' => true],
            [
                'description' => $presets['member']['description'] ?? 'Basic access to view assets and participate in conversations.',
                'is_owner' => false,
                'sort_order' => 2,
            ]
        );
        self::syncRolePermissions($memberRole, $presets['member']['permissions'] ?? []);
    }

    public static function getOwnerRole(): TeamRole
    {
        return TeamRole::whereNull('team_id')->where('is_owner', true)->firstOrFail();
    }

    public static function syncRolePermissions(TeamRole $role, array $permissionKeys): void
    {
        $permissionIds = Permission::whereIn('key', $permissionKeys)->pluck('id')->all();

        DB::table('team_role_permission')->where('team_role_id', $role->id)->delete();

        $now = now();
        $rows = array_map(fn ($pid) => [
            'team_role_id' => $role->id,
            'permission_id' => $pid,
            'created_at' => $now,
            'updated_at' => $now,
        ], $permissionIds);

        if ($rows) {
            DB::table('team_role_permission')->insert($rows);
        }
    }
}
