<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ExchangeRate
{
    private static ?array $data;

    public static function getRate(string $currency_code): float
    {
        if (!isset(self::$data) || self::$data === null) {
            self::getData();
        }
        if (!array_key_exists($currency_code, self::$data)) {
            throw new \Exception("Invalid currency code");
        }
        return floatval(self::$data[$currency_code]);
    }

    public static function cacheClearReload(): void
    {
        Cache::delete('exchange_data');
        self::$data = null;
        self::getData();
    }

    private static function getData(): void
    {
       if (Cache::has('exchange_data')) {
           self::$data = Cache::get('exchange_data');
           return;
       }
        if (($api_key = getenv('EXCHANGE_RATE_KEY')) === false) {
            throw new \Exception("The API key for exchange rates is not defined in the environment");
        }
        $url = 'https://v6.exchangerate-api.com/v6/'. $api_key .'/latest/GBP';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, 0);
        $json = strval(curl_exec($ch));
        $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $time = intval( floatval(curl_getinfo($ch, CURLINFO_TOTAL_TIME)) * 1000);
        if ($code != 200) {
            throw new \Exception("Incorrect response from Exchange Rate API");
        }
        $data = json_decode(trim($json), true);
        if (!is_array($data)) {
            throw new \Exception("Incorrect response from Exchange Rate API");
        }
        if (!array_key_exists('conversion_rates', $data)) {
            throw new \Exception("Incorrect response from Exchange Rate API");
        }
        self::$data = $data['conversion_rates'];
        Cache::put('exchange_data',self::$data, 60);
    }




}
