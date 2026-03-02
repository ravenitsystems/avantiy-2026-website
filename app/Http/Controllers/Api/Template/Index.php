<?php

namespace App\Http\Controllers\Api\Template;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Index extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to view templates.', 'templates' => [], 'categories' => [], 'meta' => []];
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(12, (int) $request->input('per_page', 24)));
        $categoryId = $request->input('category_id');
        $hasStore = $request->input('has_store');
        $hasBlog = $request->input('has_blog');
        $hasBooking = $request->input('has_booking');
        $aiEnabled = $request->input('ai_enabled');

        $query = DB::table('template')
            ->where(function ($q) {
                $q->where('deleted', false)->orWhereNull('deleted');
            })
            ->orderBy('order_offset')
            ->orderByDesc('date_created')
            ->orderByDesc('template_id');

        if ($categoryId !== null && $categoryId !== '') {
            $query->whereIn('id', function ($q) use ($categoryId) {
                $q->select('template_id')
                    ->from('template_category_link')
                    ->where('template_category_id', $categoryId);
            });
        }

        if ($hasStore !== null && $hasStore !== '') {
            $query->where('has_store', filter_var($hasStore, FILTER_VALIDATE_BOOLEAN));
        }
        if ($hasBlog !== null && $hasBlog !== '') {
            $query->where('has_blog', filter_var($hasBlog, FILTER_VALIDATE_BOOLEAN));
        }
        if ($hasBooking !== null && $hasBooking !== '') {
            $query->where('has_booking', filter_var($hasBooking, FILTER_VALIDATE_BOOLEAN));
        }
        if ($aiEnabled !== null && $aiEnabled !== '') {
            $query->where('ai_enabled', filter_var($aiEnabled, FILTER_VALIDATE_BOOLEAN));
        }

        $total = $query->count();
        $rows = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        $templates = $rows->map(function ($row) {
            $ext = $row->thumbnail_ext ?? 'png';
            if (strpos($ext, '.') !== 0) {
                $ext = '.' . $ext;
            }

            return [
                'id' => (int) $row->id,
                'template_id' => (int) $row->template_id,
                'name' => $row->name,
                'preview_url' => $row->preview_url ?? '',
                'thumbnail_url' => '/media/templates/c_' . $row->template_id . $ext,
                'has_store' => (bool) $row->has_store,
                'has_blog' => (bool) $row->has_blog,
                'has_booking' => (bool) $row->has_booking,
                'ai_enabled' => (bool) $row->ai_enabled,
            ];
        })->values()->all();

        $categories = DB::table('template_category')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name])
            ->values()->all();

        return [
            'templates' => $templates,
            'categories' => $categories,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }
}
