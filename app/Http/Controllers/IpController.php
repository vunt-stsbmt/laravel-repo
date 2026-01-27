<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpController extends Controller
{
    public function index(Request $request)
    {
        $ip = $this->resolveClientIp($request);
        $isV6 = str_contains($ip ?? '', ':');

        Log::info('IP_CHECK_REQUEST', [
            'ip' => $ip,
            'is_ipv6' => $isV6,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);

        $ipData = null;
        $error = null;
        $mapUrl = null;
        $mapLink = null;
        $pingHostInput = trim((string) $request->query('ping_host', ''));
        $pingResult = null;
        $pingError = null;

        try {
            if (!$ip) {
                throw new \RuntimeException('Không xác định được IP.');
            }

            $queryIp = rawurlencode($ip);
            $response = Http::timeout(4)->get("http://ip-api.com/json/{$queryIp}", [
                'fields' => 'status,message,country,regionName,city,zip,lat,lon,timezone,isp,org,as,query',
            ]);

            if ($response->ok()) {
                $payload = $response->json();

                if (($payload['status'] ?? null) === 'success') {
                    $ipData = $payload;
                    $lat = $payload['lat'] ?? null;
                    $lon = $payload['lon'] ?? null;

                    if ($lat !== null && $lon !== null) {
                        $zoom = 12;
                        $mapUrl = "https://www.openstreetmap.org/export/embed.html?layer=mapnik&marker={$lat},{$lon}&zoom={$zoom}";
                        $mapLink = "https://www.openstreetmap.org/?mlat={$lat}&mlon={$lon}#map={$zoom}/{$lat}/{$lon}";
                    }
                } else {
                    $error = $payload['message'] ?? 'Không lấy được thông tin IP.';
                }
            } else {
                $error = 'API không phản hồi hợp lệ.';
            }
        } catch (\Throwable $exception) {
            $error = 'Không thể kết nối API.';
        }

        if ($pingHostInput !== '') {
            [$pingResult, $pingError] = $this->pingHost($pingHostInput);
        }

        return view('index', [
            'ip' => $ip,
            'isV6' => $isV6,
            'ipData' => $ipData,
            'error' => $error,
            'mapUrl' => $mapUrl,
            'mapLink' => $mapLink,
            'pingHostInput' => $pingHostInput,
            'pingResult' => $pingResult,
            'pingError' => $pingError,
        ]);
    }

    private function resolveClientIp(Request $request): ?string
    {
        $candidates = [];

        $headerValues = [
            $request->header('CF-Connecting-IP'),
            $request->header('X-Real-IP'),
        ];

        foreach ($headerValues as $value) {
            if (is_string($value) && $value !== '') {
                $candidates[] = $value;
            }
        }

        $forwardedFor = $request->header('X-Forwarded-For');
        if (is_string($forwardedFor) && $forwardedFor !== '') {
            foreach (explode(',', $forwardedFor) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $candidates[] = $part;
                }
            }
        }

        $candidates[] = $request->ip();

        foreach ($candidates as $candidate) {
            if ($this->isPublicIp($candidate)) {
                return $candidate;
            }
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function isPublicIp(string $ip): bool
    {
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function pingHost(string $input): array
    {
        $host = $this->normalizeHost($input);
        if (!$host) {
            return [null, 'Host không hợp lệ.'];
        }

        $resolvedIp = $this->resolveHostIp($host);
        if (!$resolvedIp) {
            return [null, 'Không thể phân giải DNS.'];
        }

        if (!$this->isPublicIp($resolvedIp)) {
            return [null, 'Chỉ hỗ trợ ping tới IP public.'];
        }

        $ports = [443, 80];
        $timeout = 2.5;
        $start = microtime(true);
        $selectedPort = null;
        $connection = null;

        foreach ($ports as $port) {
            $errNo = 0;
            $errStr = '';
            $connection = @fsockopen($host, $port, $errNo, $errStr, $timeout);
            if ($connection) {
                $selectedPort = $port;
                break;
            }
        }

        if (!$connection) {
            return [null, 'Không thể kết nối tới host.'];
        }

        $latencyMs = (int) round((microtime(true) - $start) * 1000);
        fclose($connection);

        return [[
            'host' => $host,
            'ip' => $resolvedIp,
            'port' => $selectedPort,
            'latency_ms' => $latencyMs,
        ], null];
    }

    private function normalizeHost(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        if (str_starts_with($input, 'http://') || str_starts_with($input, 'https://')) {
            $parsed = parse_url($input);
            $input = $parsed['host'] ?? '';
        }

        $input = preg_replace('/:\d+$/', '', $input);
        if ($input === '' || strlen($input) > 255) {
            return null;
        }

        if (filter_var($input, FILTER_VALIDATE_IP)) {
            return $input;
        }

        if (filter_var($input, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return $input;
        }

        return null;
    }

    private function resolveHostIp(string $host): ?string
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        $ipv4 = gethostbyname($host);
        if ($ipv4 && $ipv4 !== $host) {
            return $ipv4;
        }

        $records = dns_get_record($host, DNS_AAAA);
        if (is_array($records)) {
            foreach ($records as $record) {
                if (!empty($record['ipv6'])) {
                    return $record['ipv6'];
                }
            }
        }

        return null;
    }
}
