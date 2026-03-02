<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Templatecategoryindex extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.', 'categories' => []];
        }

        if (! self::canAccessTemplateAdmin()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission to manage template categories.', 'categories' => []];
        }

        $categories = DB::table('template_category')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name])
            ->values()->all();

        return ['categories' => $categories];
    }

    public static function canAccessTemplateAdmin(): bool
    {
        $code = (string) (CurrentUser::getAdminCode() ?? '');
        return str_contains($code, 'A') || str_contains($code, 'T');
    }
}
