<x-layouts.app title="Ruang Meeting">
<div class="max-w-6xl mx-auto py-8 px-4">
    {{-- Header & Filter Tanggal --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Ruang Rapat Hari Ini</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Lihat jadwal dan status ketersediaan ruangan rapat hari ini di kantor PLN Suku Cadang.
                </p>
            </div>
            
            <div class="flex items-center gap-2 self-start sm:self-center bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm">
                <span class="pl-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal:</span>
                <input type="date" 
                       value="{{ $selectedDate }}" 
                       onchange="window.location.href='?tanggal='+this.value"
                       class="border-0 bg-slate-50 text-slate-800 rounded-lg text-sm font-medium focus:ring-2 focus:ring-brand-500 cursor-pointer py-1.5 px-3">
            </div>
        </div>

    @foreach ([1 => 'Lantai 1', 2 => 'Lantai 2', 3 => 'Lantai 3'] as $floor => $floorName)
        @php $floorRooms = $rooms->where('floor', $floor); @endphp
        @if ($floorRooms->isNotEmpty())
            <div class="mb-8">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-brand-600">
                        <span class="h-4 w-1 rounded-full bg-brand-500"></span>
                        {{ $floorName }}
                    </h2>
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600">{{ $floorRooms->count() }} Ruangan</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($floorRooms as $room)
                        @php
                            $hasSchedule = $room->reservations->isNotEmpty();
                            $isBusyNow = $room->busy_now;
                        @endphp
                        <div class="overflow-hidden rounded-xl border {{ $isBusyNow ? 'border-busy-border bg-busy-bg' : ($hasSchedule ? 'border-amber-200 bg-amber-50' : 'border-free-border bg-free-bg') }}">
                            {{-- Gambar Ruangan --}}
                            <div class="relative aspect-[4/3] w-full bg-slate-200">
                                @if ($room->image)
                                    <img src="{{ asset('storage/'.$room->image) }}" alt="{{ $room->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xs text-slate-400">Foto ruangan</div>
                                @endif
                            </div>
                            {{-- Info Ruangan --}}
                            <div class="p-3.5">
                                {{-- Header Teks & Badge Status (Dibuat Flexbox agar sejajar & tidak menimpa) --}}
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800 leading-tight">{{ $room->name }}</p>
                                        {{-- Keterangan kapasitas ringkas di bawah nama ruangan. --}}
                                        <p class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-500">
                                            <svg class="h-3 w-3 shrink-0" viewBox="0 0 20 20" fill="none"><path d="M13.5 15.5v-1a3 3 0 0 0-3-3h-5a3 3 0 0 0-3 3v1M8 8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM17.5 15.5v-1a3 3 0 0 0-2.25-2.9M13 3.7a3 3 0 0 1 0 5.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Kapasitas {{ $room->capacity_label }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold text-white {{ $isBusyNow ? 'bg-red-600' : ($hasSchedule ? 'bg-amber-500' : 'bg-emerald-500') }}">
                                        {{ $isBusyNow ? 'Sedang Dipakai' : ($hasSchedule ? 'Ada Jadwal' : 'Tersedia') }}
                                    </span>
                                </div>

                                @if ($room->is_combined)
                                    <p class="mt-1.5 inline-flex items-center gap-1 rounded-md bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-600">
                                        Ruang Gabungan · memakai kedua ruangan sekaligus
                                    </p>
                                @endif

                                @if ($hasSchedule)
                                    <p class="mt-2 text-xs font-medium {{ $isBusyNow ? 'text-busy-text' : 'text-amber-700' }}">
                                        {{ $isBusyNow ? 'Rapat terjadwal hari ini:' : 'Rapat terjadwal hari ini:' }}
                                    </p>
                                    <ul class="mt-1 max-h-10 space-y-0.5 overflow-y-auto pr-1">
                                        @foreach ($room->reservations as $res)
                                            <li class="flex items-center gap-1.5 text-xs {{ $isBusyNow ? 'text-busy-text' : 'text-amber-700' }}">
                                                <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $isBusyNow ? 'bg-busy-text' : 'bg-amber-500' }}"></span>
                                                {{ substr($res->jam_mulai, 0, 5) }} – {{ substr($res->jam_selesai, 0, 5) }}
                                                @if ($res->room_id !== $room->id)
                                                    <span class="text-[10px] opacity-70">({{ $res->room->name }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-2 flex items-center gap-1.5 text-xs text-free-text">
                                        <span class="h-1.5 w-1.5 rounded-full bg-free-text"></span>
                                        Belum ada reservasi untuk hari ini
                                    </p>
                                @endif

                                <a href="{{ route('rooms.create', ['room' => $room->id, 'tanggal' => $selectedDate]) }}"
                                class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-lg bg-white py-2 text-sm font-semibold text-brand-600 shadow-sm hover:bg-brand-50">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6 2.5v3M14 2.5v3M3 8h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    Pesan Ruangan Ini
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
</x-layouts.app>