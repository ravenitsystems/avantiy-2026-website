<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base for all API handlers. Every response is returned in the same shape:
 * { "status": "PASS"|"FAIL", "data": <payload> }
 * handle() must return only the payload (array); status and data wrapper are applied here.
 */
abstract class ApiBase
{
    public abstract function handle(Request $request): array;

    private string $status = 'PASS';

    private int $response_code = 200;

    public function processCall(Request $request, ?string $id1, ?string $id2, ?string $id3, ?string $id4): JsonResponse
    {
        $output = $this->handle($request);
        $response = [
            'status' => $this->status,
            'data' => $output,
        ];

        return response()->json($response, $this->response_code);
    }

    protected function setPass(): void
    {
        $this->status = 'PASS';
    }

    protected function setFail(): void
    {
        $this->status = 'FAIL';
    }

    protected function setResponseCode(int $code): void
    {
        $this->response_code = $code;
    }



}
