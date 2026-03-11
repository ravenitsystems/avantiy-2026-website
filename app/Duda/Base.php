<?php

namespace App\Duda;

use App\Services\StaticHelpers;
use Illuminate\Support\Facades\DB;

class Base
{
    private string $host;
    private string $user;
    private string $pass;
    private string $path;
    private bool $logs;

    public function __construct(string $path, ?string $host = null)
    {
        $this->host = ($host === null) ? strval(getenv("DUDA_API_HOST")) : $host;
        $this->user = strval(getenv("DUDA_API_USER"));
        $this->pass = strval(getenv("DUDA_API_PASS"));
        $this->path = trim($path, " /\n\r\t");
        $this->logs = StaticHelpers::getConfigFlag(getenv("DUDA_API_LOGS"));
    }

    /**
     * Executes an API call at a low level with many parameters effecting the call, this method is to be used with all
     * interactions with the Duda API. This method also logs every request into a database table if DUDA_API_LOGS is
     * switched on.
     * @param string $uri
     * @param string $method
     * @param array|null $header_list
     * @param array|null $params
     * @param array|null $payload
     * @param array|null $query
     * @return array
     */
    public function request(string $uri, string $method, ?array $header_list, ?array $params = null, ?array $payload = null, ?array $query = null): array
    {
        $uri = $this->buildUri($uri, $params, $query);
        $method = strtoupper($method);
        $url = $this->buildUrl($uri);
        $headers = ['Authorization: Basic ' . base64_encode("{$this->user}:{$this->pass}")];
        $headers = array_merge($headers, $header_list ?? []);
        $log_id = $this->logRequest($this->host, $uri, $method, $payload);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, 0);
        if (is_array($payload) && sizeof($payload) != 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response_json = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response_time = intval(floatval(curl_getinfo($ch, CURLINFO_TOTAL_TIME)) * 1000);
        $response_data = json_decode($response_json, true);
        $response_error = curl_error($ch);
        $this->logResponse($log_id, $response_code, $response_data, $response_time, $response_error);
        return [
            'code' => $response_code,
            'data' => $response_data,
            'time' => $response_time,
        ];
    }

    /**
     * Creates a log entry for a request and returns a request_log_id for the response log to use
     * @param string $host
     * @param string $uri
     * @param string $method
     * @param array|null $payload
     * @return int
     */
    private function logRequest(string $host, string $uri, string $method, ?array $payload = null): int
    {
        if (!$this->logs) {
            return 0;
        }
        $user_id = intval(session()->get('user_id'));
        $team_id = intval(session()->get('active_team_id'));

        $backtrace = json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $log_id = DB::table('duda_api_log')->insertGetId([
            'host' => $host,
            'uri' => $uri,
            'method' => $method,
            'class_and_method' => '',
            'backtrace' => $backtrace,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'user_id' => $user_id,
            'team_id' => $team_id,
        ]);
        return intval($log_id);
    }

    /**
     * Logs the response from the Duda API using a previously generated request_log_id, this fills the existing log
     * entry with the response information. This method will do nothing if DUDA_API_LOGS is off.
     * @param int $request_log_id
     * @param int $response_code
     * @param array|string|null $response_data
     * @param int $response_time
     * @param string $response_error
     * @return void
     */
    private function logResponse(int $request_log_id, int $response_code, array | string | null $response_data, int $response_time, string $response_error): void
    {
        if (!$this->logs || $request_log_id == 0) {
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
     * Generates a URL using an input URI, this simply adds the host name onto the URI to give a fully qualified URL.
     * There is no error checking on this method and it assumes secure http.
     * @param string $uri
     * @return string
     */
    private function buildUrl(string $uri): string
    {
        return 'https://' . $this->host . $uri;
    }

    /**
     * Generates a URI depending on input and ensures there is a predicatable slash pattern to the output, this method
     * supports uri substitution vai the params parameter and http queries through the query parameter. The
     * substitutions and query string are all url encoded and double slashes are eliminated.
     * @param string $uri
     * @param array|null $params
     * @param array|null $query
     * @return string
     */
    private function buildUri(string $uri, ?array $params = [], ?array $query = []): string
    {
        foreach (($params ?? []) as $key => $value) {
            $uri = str_replace('{' . $key . '}', urlencode($value), $uri);
        }
        $uri = trim($uri, '/');
        while (str_replace('//', '/', $uri) != $uri) {
            $uri = str_replace('//', '/', $uri);
        }
        if (sizeof(($query ?? [])) > 0) {
            $uri .= '?' . http_build_query($query);
        }
        return '/' . $this->path . '/' . $uri;
    }

    protected function getPermissionGroupFromMap(array $group_list): array
    {

    }

}
