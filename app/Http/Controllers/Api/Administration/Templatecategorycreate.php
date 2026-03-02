<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Templatecategorycreate extends ApiBase
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

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Name is required.'];
        }
        if (strlen($name) > 128) {
            $name = substr($name, 0, 128);
        }

        $existing = DB::table('template_category')->where('name', $name)->first();
        if ($existing !== null) {
            $this->setFail();
            $this->setResponseCode(409);
            return ['message' => 'A category with this name already exists.', 'category' => ['id' => (int) $existing->id, 'name' => $existing->name]];
        }

        $id = DB::table('template_category')->insertGetId(['name' => $name]);

        return ['message' => 'Category created.', 'category' => ['id' => (int) $id, 'name' => $name]];
    }
}
