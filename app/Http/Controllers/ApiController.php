<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $module = ucfirst(strval($request->route('module')));
        $call = ucfirst(strval($request->route("call")));
        $id1 = $request->route("id1");
        $id2 = $request->route("id2");
        $id3 = $request->route("id3");
        $id4 = $request->route("id4");
        if (!preg_match('/^[a-zA-Z0-9]{1,32}$/', $module)) {
            abort(404, '');
        }
        if (!preg_match('/^[a-zA-Z0-9]{1,32}$/', $call)) {
            abort(404, '');
        }
        if ($id1 !== null && !preg_match('/^[a-zA-Z0-9]{1,32}$/', $id1)) {
            abort(404, '');
        }
        if ($id2 !== null && !preg_match('/^[a-zA-Z0-9]{1,64}$/', $id2)) {
            abort(404, '');
        }
        if ($id3 !== null && !preg_match('/^[a-zA-Z0-9]{1,32}$/', $id3)) {
            abort(404, '');
        }
        if ($id4 !== null && !preg_match('/^[a-zA-Z0-9]{1,32}$/', $id4)) {
            abort(404, '');
        }
        $api_handler_class = "\\App\\Http\\Controllers\\Api\\{$module}\\{$call}";
        if (!class_exists($api_handler_class)) {
            abort(404, '');
        }
        $api_handler = new $api_handler_class();
        if ((!$api_handler instanceof ApiBase)) {
            abort(404, '');
        }
        return $api_handler->processCall($request, $id1, $id2, $id3, $id4);
    }
}
