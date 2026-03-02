<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Templateadminupdatecategories extends ApiBase
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
        $templateId = $id1 !== null ? (int) $id1 : 0;
        if ($templateId <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid template ID.'];
        }

        $template = DB::table('template')->where('id', $templateId)->first();
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

        $categoryIds = $request->input('category_ids');
        if (! is_array($categoryIds)) {
            $categoryIds = [];
        }
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        $categoryIds = array_filter($categoryIds, fn ($id) => $id > 0);

        $validIds = DB::table('template_category')->whereIn('id', $categoryIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $categoryIds = array_values(array_intersect($categoryIds, $validIds));

        DB::table('template_category_link')->where('template_id', $templateId)->delete();

        foreach ($categoryIds as $catId) {
            DB::table('template_category_link')->insert([
                'template_id' => $templateId,
                'template_category_id' => $catId,
            ]);
        }

        return [
            'message' => 'Categories updated.',
            'category_ids' => $categoryIds,
        ];
    }
}
