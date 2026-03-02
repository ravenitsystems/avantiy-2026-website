<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Templateadminindex extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.', 'templates' => []];
        }

        if (! Templatecategoryindex::canAccessTemplateAdmin()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission to manage templates.', 'templates' => []];
        }

        $rows = DB::table('template')
            ->where(function ($q) {
                $q->where('deleted', false)->orWhereNull('deleted');
            })
            ->orderBy('order_offset')
            ->orderByDesc('date_created')
            ->orderByDesc('template_id')
            ->get(['id', 'template_id', 'enabled', 'order_offset', 'front_page', 'name', 'thumbnail_ext']);

        $links = DB::table('template_category_link')
            ->get(['template_id', 'template_category_id'])
            ->groupBy('template_id');

        $templates = $rows->map(function ($row) use ($links) {
            $ext = $row->thumbnail_ext ?? 'png';
            if (strpos($ext, '.') !== 0) {
                $ext = '.' . $ext;
            }
            $linkList = $links->get((string) $row->id) ?? collect();
            $categoryIds = $linkList->map(fn ($l) => (int) $l->template_category_id)->values()->all();
            return [
                'id' => (int) $row->id,
                'template_id' => (int) $row->template_id,
                'enabled' => (bool) $row->enabled,
                'order_offset' => (int) $row->order_offset,
                'front_page' => (bool) $row->front_page,
                'name' => $row->name ?? '',
                'thumbnail_url' => '/media/templates/c_' . $row->template_id . $ext,
                'category_ids' => $categoryIds,
            ];
        })->values()->all();

        return ['templates' => $templates];
    }
}
