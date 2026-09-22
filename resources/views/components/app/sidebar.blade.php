@php
    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'match' => ['dashboard', 'dashboard.*']],
        ['route' => 'rooms.index', 'label' => 'Ruang Rapat', 'icon' => 'calendar'],
        ['route' => 'zoom.index', 'label' => 'Breakout Room Zoom', 'icon' => 'link'],
        ['route' => 'attendance.index', 'label' => 'Form Kehadiran', 'icon' => 'clipboard'],
    ];

    $adminItems = [
        ['route' => 'reservations.index', 'label' => 'Semua Pemesanan', 'icon' => 'list'],
        ['route' => 'users.index', 'label' => 'Manajemen Akun', 'icon' => 'users'],
    ];

    $icons = [
        'home' => '<path d="M4 10.5 12 4l8 6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 9.5V19h12V9.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 10h16M8 3.5v3M16 3.5v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'link' => '<path d="M9 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M10 8H8a4 4 0 0 0 0 8h2M14 8h2a4 4 0 0 1 0 8h-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'clipboard' => '<rect x="6" y="5" width="12" height="15" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M9 5V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1M9 11h6M9 15h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'list' => '<path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'users' => '<circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M16 8.5a2.5 2.5 0 1 0 0-5M18 20c0-2.6-1.4-4.8-3.5-5.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'settings' => '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M12 3.5v2M12 18.5v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M3.5 12h2M18.5 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'logout' => '<path d="M9 6V5a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5a2 2 0 0 1-2-2v-1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M3 12h11M11 8l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
    ];
@endphp

<aside
    x-data="{
        collapsed: window.localStorage.getItem('meetsc_sidebar_collapsed') === '1',
        toggle() {
            this.collapsed = !this.collapsed;
            window.localStorage.setItem('meetsc_sidebar_collapsed', this.collapsed ? '1' : '0');
        }
    }"
    :class="collapsed ? 'w-16 px-2' : 'w-64 px-5'"
    class="sticky top-0 flex h-screen shrink-0 flex-col overflow-y-auto overflow-x-hidden border-r border-brand-100 bg-white py-6 transition-all duration-200"
>
    {{-- Header: logo + tombol buka/tutup --}}
    <div class="flex items-center" :class="collapsed ? 'justify-center' : 'justify-between'">
        <x-brand-logo x-show="!collapsed" x-cloak />

        <button
            type="button"
            @click="toggle()"
            :title="collapsed ? 'Buka sidebar' : 'Tutup sidebar'"
            :aria-label="collapsed ? 'Buka sidebar' : 'Tutup sidebar'"
            :aria-expanded="(!collapsed).toString()"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-brand-50 hover:text-brand-600"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/>
                <path d="M9 4v16" stroke="currentColor" stroke-width="1.6"/>
                <path :d="collapsed ? 'm13 9 3 3-3 3' : 'm18 9-3 3 3 3'" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>

    <nav class="mt-8 flex flex-1 flex-col gap-1">
        @foreach ($navItems as $item)
            @php $active = request()->routeIs(...($item['match'] ?? [$item['route']])); @endphp
            <a href="{{ route($item['route']) }}"
               title="{{ $item['label'] }}"
               :class="collapsed ? 'justify-center' : 'justify-start'"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ $active ? 'bg-brand-500 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-brand-50' }}">
                <svg class="h-4.5 w-4.5 shrink-0" viewBox="0 0 24 24" fill="none">{!! $icons[$item['icon']] !!}</svg>
                <span x-show="!collapsed" x-cloak class="whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
        @endforeach

        @if (auth()->user()?->isAdmin())
            <p x-show="!collapsed" x-cloak class="mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Khusus Admin</p>
            <div x-show="collapsed" x-cloak class="mt-6 border-t border-brand-100 pt-2"></div>

            @foreach ($adminItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   title="{{ $item['label'] }}"
                   :class="collapsed ? 'justify-center' : 'justify-start'"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ $active ? 'bg-brand-500 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-brand-50' }}">
                    <svg class="h-4.5 w-4.5 shrink-0" viewBox="0 0 24 24" fill="none">{!! $icons[$item['icon']] !!}</svg>
                    <span x-show="!collapsed" x-cloak class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endif
    </nav>

    <div class="mt-auto flex flex-col gap-1 border-t border-brand-100 pt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    title="Keluar"
                    :class="collapsed ? 'justify-center' : 'justify-start'"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-busy-bg hover:text-busy-text">
                <svg class="h-4.5 w-4.5 shrink-0" viewBox="0 0 24 24" fill="none">{!! $icons['logout'] !!}</svg>
                <span x-show="!collapsed" x-cloak class="whitespace-nowrap">Keluar</span>
            </button>
        </form>
    </div>
</aside>