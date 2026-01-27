<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IpController extends Controller
{
    public function index(Request $request)
    {
        $ip = $request->ip();
        $isV6 = str_contains($ip ?? '', ':');

        $ipData = null;
        $error = null;
        $mapUrl = null;
        $mapLink = null;

        try {
            $response = Http::timeout(4)->get("http://ip-api.com/json/{$ip}", [
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
}
