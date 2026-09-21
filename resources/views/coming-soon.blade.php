<x-layouts.app :title="$title">
    <div class="flex min-h-[60vh] flex-col items-center justify-center rounded-xl border border-dashed border-brand-100 bg-white text-center">
        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-500">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M12 8v4l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/></svg>
        </span>
        <h1 class="mt-4 text-xl font-bold text-slate-900">{{ $title }}</h1>
        <p class="mt-1 max-w-sm text-sm text-slate-500">
            Halaman ini sedang dalam pengembangan tahap berikutnya. Untuk saat ini, silakan lanjut ke Dashboard.
        </p>
        <a href="{{ route('dashboard') }}" class="mt-5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
            Kembali ke Dashboard
        </a>
    </div>
</x-layouts.app>
