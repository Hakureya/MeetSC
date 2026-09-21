<x-layouts.guest title="Form Kehadiran">
    <div class="mx-auto max-w-lg">
        <div class="rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/60">
            <h1 class="text-xl font-bold text-slate-900">{{ $form->judul }}</h1>
            <p class="mt-1 text-sm text-slate-500">Isi data kehadiran Anda pada formulir di bawah ini.</p>

            @if ($form->pic || $form->divisi_pic || $form->tempat || $form->rapat_pertemuan)
                <dl class="mt-4 space-y-1.5 rounded-xl bg-slate-50 px-4 py-3 text-sm">
                    @if ($form->rapat_pertemuan)
                        <div class="flex gap-2">
                            <dt class="w-32 shrink-0 text-slate-500">Rapat/Pertemuan</dt>
                            <dd class="font-medium text-slate-800">{{ $form->rapat_pertemuan }}</dd>
                        </div>
                    @endif
                    @if ($form->tempat)
                        <div class="flex gap-2">
                            <dt class="w-32 shrink-0 text-slate-500">Tempat</dt>
                            <dd class="font-medium text-slate-800">{{ $form->tempat }}</dd>
                        </div>
                    @endif
                    @if ($form->pic)
                        <div class="flex gap-2">
                            <dt class="w-32 shrink-0 text-slate-500">PIC</dt>
                            <dd class="font-medium text-slate-800">{{ $form->pic }}</dd>
                        </div>
                    @endif
                    @if ($form->divisi_pic)
                        <div class="flex gap-2">
                            <dt class="w-32 shrink-0 text-slate-500">Divisi PIC</dt>
                            <dd class="font-medium text-slate-800">{{ $form->divisi_pic }}</dd>
                        </div>
                    @endif
                </dl>
            @endif

            @if ($isExpired)
                <div class="mt-6 rounded-lg border border-busy-border bg-busy-bg px-4 py-3 text-sm text-busy-text">
                    Formulir ini sudah kedaluwarsa dan tidak bisa diisi lagi.
                </div>
            @elseif (session('success'))
                <div class="mt-6 rounded-lg border border-free-border bg-free-bg px-4 py-3 text-sm text-free-text">
                    {{ session('success') }}
                </div>
            @else
                @if ($errors->any())
                    <div class="mt-6 rounded-lg border border-busy-border bg-busy-bg px-4 py-3 text-sm text-busy-text">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('attendance.submit', $form->uuid) }}" class="mt-6 space-y-4">
                    @csrf

                    @foreach ($form->fields as $field)
                        <div>
                            <label class="block text-sm font-medium text-slate-700">
                                {{ $field['nama'] }}
                                @if ($field['required'])
                                    <span class="text-busy-text">*</span>
                                @endif
                            </label>

                            @if ($field['tipe'] === 'textarea')
                                <textarea name="answers[{{ $field['nama'] }}]" rows="3"
                                          {{ $field['required'] ? 'required' : '' }}
                                          class="mt-1.5 w-full rounded-lg border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-0">{{ old("answers.{$field['nama']}") }}</textarea>
                            @else
                                <input type="{{ $field['tipe'] }}" name="answers[{{ $field['nama'] }}]"
                                       value="{{ old("answers.{$field['nama']}") }}"
                                       {{ $field['required'] ? 'required' : '' }}
                                       class="mt-1.5 w-full rounded-lg border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-0">
                            @endif
                        </div>
                    @endforeach

                    <button type="submit"
                            class="w-full rounded-xl bg-brand-500 py-3 font-semibold text-white transition hover:bg-brand-600">
                        Kirim Kehadiran
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.guest>