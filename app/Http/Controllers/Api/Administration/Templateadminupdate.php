<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Templateadminupdate extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.'];
        }

        if (! Templatecategoryindex::canAccessTemplateAdmin()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission to manage templates.'];
        }

        $id1 = $request->route('id1');
        $id = $id1 !== null ? (int) $id1 : 0;
        if ($id <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid template ID.'];
        }

        $template = DB::table('template')->where('id', $id)->first();
        if ($template === null) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Template not found.'];
        }
        if (! empty($template->deleted)) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Template not found.'];
        }

        $updates = [];

        if ($request->has('enabled')) {
            $updates['enabled'] = filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('order_offset')) {
            $updates['order_offset'] = (int) $request->input('order_offset');
        }
        if ($request->has('front_page')) {
            $updates['front_page'] = filter_var($request->input('front_page'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('name')) {
            $name = trim((string) $request->input('name', ''));
            $updates['name'] = strlen($name) > 128 ? substr($name, 0, 128) : $name;
        }

        if (count($updates) === 0) {
            return ['message' => 'No changes.', 'template' => [
                'id' => $id,
                'enabled' => (bool) $template->enabled,
                'order_offset' => (int) $template->order_offset,
                'front_page' => (bool) $template->front_page,
                'name' => $template->name ?? '',
            ]];
        }

        DB::table('template')->where('id', $id)->update($updates);

        $updated = DB::table('template')->where('id', $id)->first();

        return ['message' => 'Template updated.', 'template' => [
            'id' => $id,
            'enabled' => (bool) ($updated->enabled ?? $template->enabled),
            'order_offset' => (int) ($updated->order_offset ?? $template->order_offset),
            'front_page' => (bool) ($updated->front_page ?? $template->front_page),
            'name' => $updated->name ?? $template->name ?? '',
        ]];
    }
}
