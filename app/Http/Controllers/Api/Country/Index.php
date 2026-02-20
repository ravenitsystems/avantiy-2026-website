<?php

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Index extends ApiBase
{
    public function handle(Request $request): array
    {
        $rows = DB::table('country')
            ->orderBy('order_index')
            ->orderBy('name')
            ->get(['id', 'name', 'alpha_2', 'dial_code']);

        $countries = $rows->map(fn ($r) => [
            'id' => (int) $r->id,
            'name' => $r->name,
            'alpha_2' => $r->alpha_2,
            'dial_code' => $r->dial_code,
        ])->values()->all();

        $detectedAlpha2 = $this->detectCountryAlpha2($request);
        if ($detectedAlpha2 !== null) {
            $countries = $this->putCountryFirst($countries, $detectedAlpha2);
        }

        return ['countries' => $countries];
    }

    private function detectCountryAlpha2(Request $request): ?string
    {
        $countryCode = $request->header('CF-IPCountry');
        if (!empty($countryCode) && $countryCode !== 'XX' && strlen($countryCode) === 2) {
            return strtoupper($countryCode);
        }

        $ip = $request->ip();
        if (empty($ip) || $this->isPrivateIp($ip)) {
            return null;
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,countryCode',
            ]);
            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success' && !empty($data['countryCode'])) {
                    return strtoupper($data['countryCode']);
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function isPrivateIp(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function putCountryFirst(array $countries, string $alpha2): array
    {
        $match = null;
        $rest = [];
        foreach ($countries as $c) {
            if (($c['alpha_2'] ?? '') === $alpha2) {
                $match = $c;
            } else {
                $rest[] = $c;
            }
        }
        if ($match !== null) {
            return array_merge([$match], $rest);
        }
        return $countries;
    }
}
