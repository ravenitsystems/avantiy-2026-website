<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Dudaapilogindex extends ApiBase
{
    public static function canAccessDudaApiLog(): bool
    {
        $code = (string) (CurrentUser::getAdminCode() ?? '');
        return str_contains($code, 'A') || str_contains($code, 'D');
    }

    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.', 'entries' => [], 'meta' => []];
        }

        if (! self::canAccessDudaApiLog()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission to view Duda API logs.', 'entries' => [], 'meta' => []];
        }

        if (! Schema::hasTable('duda_api_log')) {
            return ['entries' => [], 'meta' => ['total' => 0, 'per_page' => 25, 'current_page' => 1, 'last_page' => 1]];
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(10, (int) $request->input('per_page', 25)));
        $method = $request->input('method');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $uriFilter = $request->input('uri');
        $codeFilter = $request->input('code');
        $searchFilter = $request->input('search');

        $orderBy = $request->input('order_by', 'timestamp_request');
        $allowedOrder = ['id', 'timestamp_request', 'method', 'uri', 'response_code', 'response_time'];
        if (! in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'timestamp_request';
        }
        $orderDir = strtolower((string) $request->input('order_dir', 'desc'));
        if ($orderDir !== 'asc' && $orderDir !== 'desc') {
            $orderDir = 'desc';
        }

        $query = DB::table('duda_api_log');

        if ($method !== null && $method !== '') {
            $query->where('method', $method);
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            if (str_contains($dateFrom, 'T')) {
                $query->where('timestamp_request', '>=', str_replace('T', ' ', substr($dateFrom, 0, 16)) . ':00');
            } else {
                $query->whereDate('timestamp_request', '>=', $dateFrom);
            }
        }
        if ($dateTo !== null && $dateTo !== '') {
            if (str_contains($dateTo, 'T')) {
                $query->where('timestamp_request', '<=', str_replace('T', ' ', substr($dateTo, 0, 16)) . ':59');
            } else {
                $query->whereDate('timestamp_request', '<=', $dateTo);
            }
        }
        if ($uriFilter !== null && $uriFilter !== '') {
            $query->where('uri', 'like', '%' . addcslashes($uriFilter, '%_\\') . '%');
        }
        if ($codeFilter !== null && $codeFilter !== '') {
            $query->where('response_code', (int) $codeFilter);
        }
        if ($searchFilter !== null && $searchFilter !== '') {
            $searchTerm = '%' . addcslashes($searchFilter, '%_\\') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('payload', 'like', $searchTerm)
                    ->orWhere('response_data', 'like', $searchTerm);
            });
        }

        $query->orderBy($orderBy, $orderDir);

        $total = $query->count();
        $rows = (clone $query)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $entries = $rows->map(function ($row) {
            return [
                'id' => (int) $row->id,
                'timestamp_request' => $row->timestamp_request ? (string) $row->timestamp_request : null,
                'timestamp_response' => $row->timestamp_response ? (string) $row->timestamp_response : null,
                'host' => $row->host ?? null,
                'uri' => $row->uri ?? null,
                'method' => $row->method ?? null,
                'payload' => $row->payload ?? null,
                'response_code' => $row->response_code !== null ? (int) $row->response_code : null,
                'response_data' => $row->response_data ?? null,
                'response_time' => $row->response_time !== null ? (int) $row->response_time : null,
                'valid_json' => (bool) $row->valid_json,
                'curl_error' => $row->curl_error ?? null,
                'class_and_method' => $row->class_and_method ?? null,
                'backtrace' => $row->backtrace ?? null,
            ];
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
