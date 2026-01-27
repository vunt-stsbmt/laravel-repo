<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} · IP Check</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute -top-32 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-500/20 blur-3xl">
            </div>
            <div class="absolute -bottom-24 right-10 h-80 w-80 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="absolute left-10 top-20 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(148,163,184,0.15),transparent_55%)]">
            </div>
        </div>

        <main class="relative mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-16">
            <div class="mb-12 flex flex-wrap items-center gap-3 text-sm text-slate-300">
                <span class="rounded-full border border-slate-700/60 bg-slate-900/60 px-3 py-1">IP Checker</span>
                <span
                    class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-emerald-200">Online</span>
            </div>

            <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <section>
                    <h1 class="text-4xl font-semibold leading-tight text-white sm:text-5xl">
                        Kiểm tra IP của bạn trong vài giây
                    </h1>
                    <p class="mt-4 max-w-xl text-lg text-slate-300">
                        Dữ liệu được lấy từ ip-api.com để hiển thị vị trí và thông tin mạng liên quan.
                    </p>

                    <div
                        class="mt-8 rounded-2xl border border-slate-800/80 bg-slate-900/70 p-6 shadow-2xl shadow-cyan-500/10">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Địa chỉ IP</p>
                                <p class="mt-2 text-3xl font-semibold text-white sm:text-4xl" id="ip-value">
                                    {{ $ip }}</p>
                                <p class="mt-2 text-sm text-slate-400">
                                    Loại: <span class="font-medium text-slate-200">{{ $isV6 ? 'IPv6' : 'IPv4' }}</span>
                                </p>
                            </div>
                            <button type="button" data-copy-target="ip-value"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-700/70 bg-slate-950/70 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-cyan-400/60 hover:text-white">
                                <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                Sao chép IP
                            </button>
                        </div>
                    </div>

                    @if ($error)
                        <div
                            class="mt-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-100">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-800/70 bg-slate-900/50 p-5">
                            <p class="text-sm text-slate-400">User Agent</p>
                            <p class="mt-2 text-sm text-slate-200">{{ request()->userAgent() }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-800/70 bg-slate-900/50 p-5">
                            <p class="text-sm text-slate-400">Thời điểm</p>
                            <p class="mt-2 text-sm text-slate-200">{{ now()->format('H:i:s · d/m/Y') }}</p>
                        </div>
                    </div>

                    @if ($ipData)
                        <div class="mt-8 rounded-2xl border border-slate-800/80 bg-slate-900/60 p-6">
                            <h2 class="text-lg font-semibold text-white">Thông tin chi tiết</h2>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-800/60 bg-slate-950/60 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Khu vực</p>
                                    <p class="mt-2 text-sm text-slate-200">{{ $ipData['city'] ?? '-' }},
                                        {{ $ipData['regionName'] ?? '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $ipData['country'] ?? '-' }} ·
                                        {{ $ipData['zip'] ?? '-' }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-800/60 bg-slate-950/60 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">ISP</p>
                                    <p class="mt-2 text-sm text-slate-200">{{ $ipData['isp'] ?? '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $ipData['org'] ?? '-' }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-800/60 bg-slate-950/60 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Timezone</p>
                                    <p class="mt-2 text-sm text-slate-200">{{ $ipData['timezone'] ?? '-' }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-800/60 bg-slate-950/60 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">AS</p>
                                    <p class="mt-2 text-sm text-slate-200">{{ $ipData['as'] ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </section>

                <aside class="flex flex-col gap-6">
                    @if ($mapUrl)
                        <div class="overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60">
                            <div class="flex items-center justify-between px-5 py-4">
                                <h2 class="text-lg font-semibold text-white">Bản đồ vị trí</h2>
                                @if ($mapLink)
                                    <a class="text-sm text-cyan-300 hover:text-cyan-200" href="{{ $mapLink }}"
                                        target="_blank" rel="noreferrer">
                                        Mở lớn
                                    </a>
                                @endif
                            </div>
                            <iframe class="h-64 w-full border-0" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" src="{{ $mapUrl }}"></iframe>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-6">
                        <h2 class="text-lg font-semibold text-white">Mẹo nhanh</h2>
                        <ul class="mt-4 space-y-3 text-sm text-slate-300">
                            <li class="flex gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                                Nếu bạn đang dùng VPN, IP sẽ hiển thị theo máy chủ VPN.
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                                IP động có thể thay đổi theo thời gian hoặc khi reset modem.
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                                Dùng IP để whitelist truy cập vào hệ thống nội bộ.
                            </li>
                        </ul>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800/80 bg-gradient-to-br from-slate-900/80 to-slate-950/80 p-6">
                        <h2 class="text-lg font-semibold text-white">Bạn muốn gì tiếp theo?</h2>
                        <p class="mt-3 text-sm text-slate-300">Có thể mở rộng: kiểm tra DNS, ping server, hoặc hiển thị
                            vị trí ước tính.</p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <span
                                class="rounded-full border border-slate-700/60 bg-slate-900/60 px-3 py-1 text-xs text-slate-300">DNS
                                Lookup</span>
                            <span
                                class="rounded-full border border-slate-700/60 bg-slate-900/60 px-3 py-1 text-xs text-slate-300">Whois</span>
                            <span
                                class="rounded-full border border-slate-700/60 bg-slate-900/60 px-3 py-1 text-xs text-slate-300">Latency
                                Test</span>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>
</body>

</html>
