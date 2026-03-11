<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Countryindex extends ApiBase
{
    public static function canAccessSiteAdmin(): bool
    {
        $code = (string) (CurrentUser::getAdminCode() ?? '');
        return str_contains($code, 'A') || str_contains($code, 'S');
    }

    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.', 'entries' => [], 'meta' => []];
        }

        if (! self::canAccessSiteAdmin()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission.', 'entries' => [], 'meta' => []];
        }

        if (! Schema::hasTable('country')) {
            return ['entries' => [], 'meta' => ['total' => 0, 'per_page' => 25, 'current_page' => 1, 'last_page' => 1]];
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(10, (int) $request->input('per_page', 25)));
        $search = trim((string) $request->input('search', ''));
        $enabledFilter = $request->input('enabled');

        $orderBy = $request->input('order_by', 'name');
        $allowedOrder = ['id', 'name', 'alpha_2', 'alpha_3', 'dial_code', 'order_index', 'enabled'];
        if (! in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'name';
        }
        $orderDir = strtolower((string) $request->input('order_dir', 'asc'));
        if ($orderDir !== 'asc' && $orderDir !== 'desc') {
            $orderDir = 'asc';
        }

        $query = DB::table('country');

        if (Schema::hasColumn('country', 'enabled') && $enabledFilter !== null && $enabledFilter !== '') {
            $query->where('enabled', (bool) $enabledFilter);
        }
        if ($search !== '') {
            $term = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('alpha_2', 'like', $term)
                    ->orWhere('alpha_3', 'like', $term)
                    ->orWhere('dial_code', 'like', $term);
            });
        }

        $query->orderBy($orderBy, $orderDir);

        $total = $query->count();
        $select = ['id', 'name', 'alpha_2', 'alpha_3', 'dial_code', 'order_index'];
        if (Schema::hasColumn('country', 'enabled')) {
            $select[] = 'enabled';
        }
        if (Schema::hasColumn('country', 'currency')) {
            $select[] = 'currency';
        }
        if (Schema::hasColumn('country', 'flag_svg')) {
            $select[] = 'flag_svg';
        }
        $rows = (clone $query)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get($select);

        $entries = $rows->map(function ($row) {
            $item = [
                'id' => (int) $row->id,
                'name' => $row->name ?? '',
                'alpha_2' => $row->alpha_2 ?? '',
                'alpha_3' => $row->alpha_3 ?? '',
                'dial_code' => $row->dial_code ?? '',
                'order_index' => (int) ($row->order_index ?? 100),
                'enabled' => Schema::hasColumn('country', 'enabled') ? (bool) ($row->enabled ?? true) : true,
            ];
            if (isset($row->currency)) {
                $item['currency'] = $row->currency !== null ? (int) $row->currency : null;
            }
            if (isset($row->flag_svg) && $row->flag_svg !== null && $row->flag_svg !== '') {
                $item['flag_svg'] = $row->flag_svg;
            }
            return $item;
        })->values()->all();

        return [
            'entries' => $entries,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
    }
}
