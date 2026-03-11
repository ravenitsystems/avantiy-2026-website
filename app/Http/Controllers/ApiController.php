<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ApiRequestLoggingMiddleware;
use App\Http\Middleware\DudaApiSecurityMiddleware;
use App\Http\Middleware\EpicurusApiSecurityMiddleware;
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

        if (strcasecmp($module, 'Epicurus') === 0) {
            $loggingMiddleware = app(ApiRequestLoggingMiddleware::class);

            $securityMiddleware = (!str_starts_with(strtolower($call), 'notify')) ? app(EpicurusApiSecurityMiddleware::class) : app(DudaApiSecurityMiddleware::class);

            $pipeline = static function (Request $req) use ($api_handler, $id1, $id2, $id3, $id4): JsonResponse {
                return $api_handler->processCall($req, $id1, $id2, $id3, $id4);
            };

            $pipeline = static function (Request $req) use ($securityMiddleware, $pipeline): JsonResponse {
                return $securityMiddleware->handle($req, $pipeline);
            };

            $pipeline = static function (Request $req) use ($loggingMiddleware, $pipeline): JsonResponse {
                return $loggingMiddleware->handle($req, $pipeline);
            };

            return $pipeline($request);
        }

        return $api_handler->processCall($request, $id1, $id2, $id3, $id4);
    }
}
