<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Templatecategoryupdate extends ApiBase
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
            return ['message' => 'You do not have permission to manage template categories.'];
        }

        $id1 = $request->route('id1');
        $id = $id1 !== null ? (int) $id1 : 0;
        if ($id <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid category ID.'];
        }

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Name is required.'];
        }
        if (strlen($name) > 128) {
            $name = substr($name, 0, 128);
        }

        $category = DB::table('template_category')->where('id', $id)->first();
        if ($category === null) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Category not found.'];
        }

        $existing = DB::table('template_category')->where('name', $name)->where('id', '!=', $id)->first();
        if ($existing !== null) {
            $this->setFail();
            $this->setResponseCode(409);
            return ['message' => 'A category with this name already exists.'];
        }

        DB::table('template_category')->where('id', $id)->update(['name' => $name]);

        return ['message' => 'Category updated.', 'category' => ['id' => $id, 'name' => $name]];
    }
}
