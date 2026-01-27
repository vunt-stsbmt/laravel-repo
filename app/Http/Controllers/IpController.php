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
                        $mapUrl = "https://www.openstreetmap.org/export/embed.html?layer=mapnik&marker={$lat},{$lon}";
                        $mapLink = "https://www.openstreetmap.org/?mlat={$lat}&mlon={$lon}#map=12/{$lat}/{$lon}";
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

        return view('index', [
            'ip' => $ip,
            'isV6' => $isV6,
            'ipData' => $ipData,
            'error' => $error,
            'mapUrl' => $mapUrl,
            'mapLink' => $mapLink,
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
}
