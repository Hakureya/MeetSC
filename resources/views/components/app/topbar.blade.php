@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<div class="flex items-center justify-end">
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2.5 rounded-full bg-white py-1.5 pl-1.5 pr-3.5 shadow-sm shadow-slate-200/60">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-600">
                {{ $initials }}
            </span>
            <span class="text-sm font-semibold text-slate-700">{{ $user->name }}</span>
        </div>
    </div>
</div>