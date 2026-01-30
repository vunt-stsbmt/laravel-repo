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

<body class="min-h-screen bg-neutral-950 text-slate-100">
    <div class="relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute -top-40 left-1/2 h-[28rem] w-[28rem] -translate-x-1/2 rounded-full bg-emerald-500/10 blur-[140px]"></div>
            <div class="absolute -bottom-24 left-10 h-72 w-72 rounded-full bg-blue-500/10 blur-[120px]"></div>
            <div class="absolute right-10 top-24 h-80 w-80 rounded-full bg-cyan-500/10 blur-[140px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(148,163,184,0.12),transparent_55%)]"></div>
        </div>

        <main class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-14">
            <header>
                <h1 class="text-3xl font-semibold text-white sm:text-4xl">Tra cứu địa chỉ IP cục bộ</h1>
                <p class="mt-3 max-w-2xl text-sm text-slate-300 sm:text-base">
                    Xem địa chỉ IP, thông tin vị trí và khả năng kết nối mạng hiện tại của bạn.
                </p>
            </header>

            <div class="mt-10 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="space-y-6">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">IPv4</p>
                                <p class="mt-2 text-2xl font-semibold text-white sm:text-3xl" id="ip-value">{{ $ip ?? '-' }}</p>
                            </div>
                            <button
                                type="button"
                                data-copy-target="ip-value"
                                class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/40 px-4 py-2 text-xs font-medium text-slate-200 transition hover:border-emerald-400/70 hover:text-white"
                            >
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Sao chép
                            </button>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-400">Vị trí</p>
                                <p class="mt-2 text-sm font-semibold text-white">
                                    {{ $ipData['city'] ?? '-' }}, {{ $ipData['country'] ?? '-' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">{{ $ipData['regionName'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Nhà cung cấp dịch vụ Internet</p>
                                <p class="mt-2 text-sm font-semibold text-white">{{ $ipData['isp'] ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $ipData['org'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">ASN</p>
                                <p class="mt-2 text-sm font-semibold text-white">{{ $ipData['as'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Loại</p>
                                <p class="mt-2 text-sm font-semibold text-white">{{ $isV6 ? 'IPv6' : 'IPv4' }}</p>
                            </div>
                        </div>

                        @if (!$isV6)
                            <div class="mt-6 rounded-xl border border-white/10 bg-black/30 p-4 text-xs text-slate-300">
                                Mạng của bạn có thể không hỗ trợ IPv6.
                                <button class="ml-3 rounded-full border border-white/10 px-3 py-1 text-[11px] text-white/80">
                                    Thử lại
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <h2 class="text-base font-semibold text-white">Tra cứu IP khác</h2>
                        <p class="mt-2 text-xs text-slate-300">Nhập IP để tra cứu nhanh (IPv4/IPv6).</p>
                        <form class="mt-4 flex flex-wrap gap-3" method="GET">
                            <input
                                type="text"
                                name="ip"
                                value="{{ $lookupIpInput ?? '' }}"
                                placeholder="Ví dụ: 8.8.8.8"
                                class="h-10 flex-1 rounded-xl border border-white/10 bg-black/40 px-3 text-sm text-slate-100 placeholder:text-slate-500"
                            />
                            <button
                                type="submit"
                                class="h-10 rounded-xl border border-white/10 bg-white/10 px-4 text-sm font-medium text-white transition hover:border-emerald-400/70"
                            >
                                Tra cứu
                            </button>
                        </form>
                        @if ($error)
                            <div class="mt-4 rounded-xl border border-rose-500/40 bg-rose-500/10 p-3 text-xs text-rose-100">
                                {{ $error }}
                            </div>
                        @endif
                    </div>
                </section>

                <aside class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="text-base font-semibold text-white">Nguồn tra cứu</h2>
                    <div class="mt-4 divide-y divide-white/10 text-sm">
                        <div class="flex items-start justify-between gap-4 py-4">
                            <div>
                                <p class="font-semibold text-white">ip138.com</p>
                                <p class="mt-2 text-xs text-slate-300">Địa chỉ IP: {{ $ip ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $ipData['country'] ?? '-' }} {{ $ipData['regionName'] ?? '-' }}</p>
                            </div>
                            <span class="rounded-full border border-blue-500/40 bg-blue-500/10 px-2 py-1 text-[10px] text-blue-200">nội địa</span>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-4">
                            <div>
                                <p class="font-semibold text-white">Trang web truy vấn IP.cn</p>
                                <p class="mt-2 text-xs text-slate-300">Địa chỉ IP: {{ $ip ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $ipData['country'] ?? '-' }} {{ $ipData['city'] ?? '-' }} {{ $ipData['isp'] ?? '-' }}</p>
                            </div>
                            <span class="rounded-full border border-blue-500/40 bg-blue-500/10 px-2 py-1 text-[10px] text-blue-200">nội địa</span>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-4">
                            <div>
                                <p class="font-semibold text-white">Cloudflare</p>
                                <p class="mt-2 text-xs text-slate-300">Địa chỉ IP: {{ $ip ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $ipData['country'] ?? '-' }} {{ $ipData['city'] ?? '-' }} {{ $ipData['as'] ?? '-' }}</p>
                            </div>
                            <span class="rounded-full border border-emerald-500/40 bg-emerald-500/10 px-2 py-1 text-[10px] text-emerald-200">tính quốc tế</span>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-4">
                            <div>
                                <p class="font-semibold text-white">IPinfo.io</p>
                                <p class="mt-2 text-xs text-slate-300">Địa chỉ IP: {{ $ip ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $ipData['country'] ?? '-' }} {{ $ipData['regionName'] ?? '-' }} {{ $ipData['org'] ?? '-' }}</p>
                            </div>
                            <span class="rounded-full border border-emerald-500/40 bg-emerald-500/10 px-2 py-1 text-[10px] text-emerald-200">tính quốc tế</span>
                        </div>
                    </div>
                    <p class="mt-4 text-[11px] text-slate-400">
                        Dữ liệu từ các nguồn có thể sai khác nhẹ do cập nhật khác thời điểm.
                    </p>
                </aside>
            </div>

            <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="flex flex-wrap items-center justify-center gap-6 text-xs text-slate-200 sm:justify-between">
                    <label class="flex items-center gap-3">
                        <span>Địa chỉ IP ẩn</span>
                        <input type="checkbox" class="h-5 w-9 rounded-full border border-white/10 bg-black/30 text-emerald-400" />
                    </label>
                    <label class="flex items-center gap-3">
                        <span>Che giấu thông tin trong nước</span>
                        <input type="checkbox" class="h-5 w-9 rounded-full border border-white/10 bg-black/30 text-emerald-400" />
                    </label>
                    <label class="flex items-center gap-3">
                        <span>Thông tin quốc tế bí mật</span>
                        <input type="checkbox" class="h-5 w-9 rounded-full border border-white/10 bg-black/30 text-emerald-400" />
                    </label>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-white">Kiểm tra kết nối</h2>
                    <span class="text-xs text-slate-400">Dữ liệu thời gian thực</span>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($latencyCards as $card)
                        <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                            <div class="flex items-center justify-between text-xs text-slate-300">
                                <span class="font-semibold text-white">{{ $card['name'] }}</span>
                                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] text-emerald-200">
                                    {{ $card['tag'] }}
                                </span>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-emerald-300">
                                @if ($card['latency_ms'])
                                    {{ $card['latency_ms'] }} ms
                                @else
                                    --
                                @endif
                            </p>
                            @if (!empty($card['error']))
                                <p class="mt-2 text-[11px] text-rose-300">{{ $card['error'] }}</p>
                            @endif
                            <div class="mt-3 flex gap-1">
                                @for ($i = 0; $i < 10; $i++)
                                    <span class="h-2 w-2 rounded-full {{ $i < ($card['score'] ?? 0) ? 'bg-emerald-500/70' : 'bg-emerald-500/10' }}"></span>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>
</body>

</html>
