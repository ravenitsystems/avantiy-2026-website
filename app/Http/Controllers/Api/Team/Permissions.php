<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Permission;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Permissions extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in.'];
        }

        $permissions = Permission::with('permissionGroup')
            ->orderBy('sort_order')
            ->get();

        $grouped = [];
        foreach ($permissions as $p) {
            $group = $p->permissionGroup;
            $groupKey = $group ? (string) $group->id : 'ungrouped';
            if (! isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'id' => $group?->id,
                    'name' => $group?->name ?? 'Other',
                    'description' => $group?->description,
                    'is_high_impact' => $group?->is_high_impact ?? false,
                    'sort_order' => $group?->sort_order ?? 999,
                    'permissions' => [],
                ];
            }
            $grouped[$groupKey]['permissions'][] = [
                'id' => $p->id,
                'key' => $p->key,
                'label' => $p->label,
                'description' => $p->description,
                'is_high_impact' => $p->is_high_impact,
            ];
        }

        usort($grouped, fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        $result = array_values(array_map(fn ($g) => [
            'id' => $g['id'],
            'group' => $g['name'],
            'group_description' => $g['description'],
            'group_is_high_impact' => $g['is_high_impact'],
            'permissions' => $g['permissions'],
        ], $grouped));

        return ['permissions' => $result];
    }
}
