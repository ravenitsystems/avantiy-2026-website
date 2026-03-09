<?php

namespace App\Duda;

use Illuminate\Support\Facades\DB;

class BaseApi
{
    private string $api_url = 'https://api.duda.co';
    private string $api_user;
    private string $api_pass;

    public function __construct(string $api_user, string $api_pass)
    {
        $this->api_user = $api_user;
        $this->api_pass = $api_pass;
    }

    public function get(string $uri, array $params = [], array $query = []): array
    {
        return $this->apiRequest($uri, 'GET', $params, null, $query);
    }

    public function post(string $uri, array $payload, array $params = [], array $query = []): array
    {
        return $this->apiRequest($uri, 'POST', $params, $payload, $query);
    }

    public function apiRequest(string $uri, string $method, ?array $params = null, ?array $payload = null, ?array $query = null): array
    {
        $uri = $this->buildUri($uri, $params ?? [], $query ?? []);
        $method = strtoupper($method);
        $url = $this->buildUrl($uri);
        $headers = ['Authorization: Basic ' . base64_encode("{$this->api_user}:{$this->api_pass}")];
        $log_id = $this->logDudaRequest($this->api_url, $uri, $method, __METHOD__);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (is_array($payload) && sizeof($payload) != 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }
        $response_json = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response_time = intval(floatval(curl_getinfo($ch, CURLINFO_TOTAL_TIME)) * 1000);
        $response_data = json_decode($response_json, true);
        $response_error = curl_error($ch);
        $this->logDudaResponse($log_id, $response_code, $response_data, $response_time, $response_error);
        return [
            'code' => $response_code,
            'data' => is_array($response_data) ? $response_data : null,
            'time' => $response_time,
        ];
    }

    private function logDudaRequest(string $host, string $uri, string $method, string $class_and_method, array $payload = []): int
    {
        if (!$this->logDudaApiCalls()) {
            return 0;
        }
        $backtrace = json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return DB::table('duda_api_log')->insertGetId([
            'host' => $host,
            'uri' => $uri,
            'method' => $method,
            'class_and_method' => $class_and_method,
            'backtrace' => $backtrace,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);
    }

    private function logDudaResponse(int $request_log_id, int $response_code, array | string | null $response_data, int $response_time, string $response_error): void
    {
        if (!$this->logDudaApiCalls()) {
            return;
        }
        $valid_json = is_array($response_data);
        $response_data = ($response_data === null) ? '' : $response_data;
        $response_data = (is_array($response_data)) ? json_encode($response_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $response_data;
        DB::table('duda_api_log')->where('id', $request_log_id)->update([
            'timestamp_response' => date("Y-m-d H:i:s"),
            'response_code' => $response_code,
            'response_data' => $response_data,
            'response_time' => $response_time,
            'valid_json' => $valid_json,
            'curl_error' => $response_error,
        ]);
    }




    /**
     * Adds the host component onto a URI to form a fully qualified URL
     * @param string $uri
     * @return string
     */
    public function buildUrl(string $uri): string
    {
        return rtrim($this->api_url, '/') . '/' . trim($uri, '/');
    }

    /**
     * Builds a URI based on stored and given parameters using standard placeholder techniques
     * @param string $uri
     * @param array $params
     * @param array $query
     * @return string
     */
    public function buildUri(string $uri, array $params = [], array $query = []): string
    {
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', urlencode($value), $uri);
        }
        $uri = trim($uri, '/');
        if (sizeof($query) > 0) {
            $uri .= '?' . http_build_query($query);
        }
        return $uri;
    }

    /**
     * Establishes if the settings to log duda api calls is on or off, this is done by reading the environment variable,
     * the result is stored in a static variable so this process is not repeated in the same call.
     * @return bool
     */
    public function logDudaApiCalls(): bool
    {
        static $stored_result;
        if (isset($stored_result)) {
            return $stored_result;
        }

        $result = false;
        $log = strtolower(strval(getenv('DUDA_API_LOG')));
        if (in_array($log, ['1', 'yes', 'true', 'on'])) {
            $result = true;
        }
        $stored_result = $result;
        return $stored_result;
    }


}
