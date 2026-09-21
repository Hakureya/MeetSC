<x-layouts.guest title="Beranda">
    <div class="grid grid-cols-1 gap-14 lg:grid-cols-[minmax(0,420px)_1fr]">

        {{-- Kolom kiri: judul, deskripsi, CTA --}}
        <div class="flex flex-col justify-center">
            <p class="text-4xl font-semibold tracking-wide text-brand-500">MeetSC</p>

            <h1 class="mt-3 text-sm font-extrabold leading-tight text-slate-900 sm:text-5xl">
                Ruang, Zoom,
                dan kehadiran,
                tanpa ribet.
            </h1>

            <p class="mt-5 max-w-md text-slate-500">
                Reservasi ruang, link Zoom, dan daftar hadir terintegrasi dalam satu sistem.
            </p>

            <div class="mt-8">
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-6 py-3.5 font-semibold text-white shadow-sm shadow-brand-500/30 transition hover:bg-brand-600">
                    Mulai
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M4 10h12M12 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>

        {{-- Kolom kanan: status 9 ruang rapat --}}
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800">Status ruang rapat saat ini</h2>
                <p class="text-sm">
                    <span class="font-bold text-free-text">{{ $availableRooms }} dari {{ $totalRooms }} ruang</span>
                    <span class="text-slate-500">tersedia</span>
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($rooms as $room)
                    <div class="overflow-hidden rounded-xl border {{ $room['in_use'] ? 'border-busy-border bg-busy-bg' : 'border-free-border bg-free-bg' }}">
                        <div class="aspect-[4/3] w-full bg-slate-200">
                            @if ($room['image'])
                                <img src="{{ asset('storage/'.$room['image']) }}" alt="{{ $room['name'] }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xs text-slate-400">Foto ruangan</div>
                            @endif
                        </div>
                        <div class="p-3">
                            <p class="font-semibold text-slate-800">{{ $room['name'] }}</p>
                            <p class="text-xs text-slate-400">Lantai {{ $room['floor'] }} · {{ $room['capacity_label'] }}</p>

                            @if (count($room['schedules']) > 0)
                                <p class="mt-1 text-xs font-medium {{ $room['in_use'] ? 'text-busy-text' : 'text-slate-500' }}">
                                    {{ $room['in_use'] ? 'Dipakai sekarang:' : 'Jadwal hari ini:' }}
                                </p>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($room['schedules'] as $schedule)
                                        <li class="flex items-center gap-1.5 text-xs {{ $room['in_use'] ? 'text-busy-text' : 'text-slate-500' }}">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $room['in_use'] ? 'bg-busy-text' : 'bg-slate-400' }}"></span>
                                            {{ $schedule }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-1 text-xs text-free-text">Belum ada reservasi untuk hari ini</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center gap-2 rounded-xl border border-brand-100 bg-white px-4 py-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-50 text-violet-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><rect x="3" y="7" width="13" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="m16.5 10 4-2.5v9l-4-2.5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                </span>
                <p class="text-sm text-slate-600">
                    Breakout Room Zoom yang tersedia
                    <span class="font-bold text-slate-900">{{ $availableZoomRooms }}/{{ $totalZoomRooms }}</span>
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>