<?php

namespace App\Http\Controllers\Api\Translation;

use App\Http\Controllers\ApiBase;
use App\Services\Translations;
use Illuminate\Http\Request;

class TranslationsIndex extends ApiBase
{
    public function handle(Request $request): array
    {
        $locale = (string) $request->route('id1') ?: (string) $request->query('locale', config('app.locale'));
        $locale = strtolower(trim($locale));

        if ($locale === '') {
            $locale = (string) config('app.locale');
        }

        $messages = Translations::getForLocale($locale);

        return [
            'locale' => $locale,
            'messages' => $messages,
        ];
    }
}

