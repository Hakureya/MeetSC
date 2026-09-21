<x-layouts.guest title="Masuk">
    <div class="grid grid-cols-1 items-center gap-14 lg:grid-cols-[minmax(0,460px)_1fr]">

        {{-- Kolom kiri: pesan sambutan --}}
        <div>
            <h1 class="text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">
                Satu sistem untuk
                ruang rapat, Zoom,
                dan kehadiran.
            </h1>

            <p class="mt-5 max-w-md text-slate-500">
                Reservasi ruang meeting, jadwalkan Zoom, dan kelola presensi kehadiran
                dalam satu sistem yang terintegrasi.
            </p>
        </div>

        {{-- Kolom kanan: form login --}}
        <div class="mx-auto w-full max-w-md rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/60">
            <h2 class="text-xl font-bold text-slate-900">Selamat Datang</h2>
            <p class="mt-1 text-sm text-slate-500">
                Silakan masuk ke akun Anda untuk melanjutkan pada sistem MeetSC.
            </p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-busy-border bg-busy-bg px-4 py-3 text-sm text-busy-text">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <div class="mt-1.5 flex items-center gap-2 rounded-lg border border-brand-100 bg-white px-3 py-2.5 focus-within:border-brand-500">
                        <svg class="h-4 w-4 text-brand-500" viewBox="0 0 20 20" fill="none"><path d="M3 5h14v10H3V5Z" stroke="currentColor" stroke-width="1.5"/><path d="m3 5 7 6 7-6" stroke="currentColor" stroke-width="1.5"/></svg>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="nama@plnsc.co.id"
                               class="w-full border-0 p-0 text-sm text-slate-800 placeholder:text-brand-500/70 focus:outline-none focus:ring-0">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <div class="mt-1.5 flex items-center gap-2 rounded-lg border border-brand-100 bg-white px-3 py-2.5 focus-within:border-brand-500">
                        <svg class="h-4 w-4 text-brand-500" viewBox="0 0 20 20" fill="none"><rect x="4" y="8.5" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 8.5V6a3.5 3.5 0 0 1 7 0v2.5" stroke="currentColor" stroke-width="1.5"/></svg>
                        <input id="password" type="password" name="password" required
                               placeholder="Masukkan kata sandi"
                               class="w-full border-0 p-0 text-sm text-slate-800 placeholder:text-brand-500/70 focus:outline-none focus:ring-0">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-500">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                    Ingat saya
                </label>

                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 py-3 font-semibold text-white transition hover:bg-brand-600">
                    Masuk
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M4 10h12M12 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>
    </div>
</x-layouts.guest>
