<x-layouts.app title="Jawaban: {{ $form->judul }}">
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('attendance.index') }}" class="text-sm text-brand-500 font-semibold hover:underline">&larr; Kembali ke Form Kehadiran</a>
            <h1 class="text-xl font-bold text-slate-900 mt-1">{{ $form->judul }}</h1>
            <p class="text-sm text-slate-500">{{ $responses->count() }} orang sudah mengisi formulir ini.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('attendance.export-word', $form->id) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Export Word
            </a>
            <a href="{{ route('attendance.export-pdf', $form->id) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                Export PDF
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-busy-border bg-busy-bg px-4 py-3 text-sm text-busy-text">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white border rounded-xl overflow-hidden shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b text-gray-600 uppercase text-[11px]">
                <tr>
                    <th class="p-3">#</th>
                    @foreach ($form->fields as $field)
                        <th class="p-3">{{ $field['nama'] }}</th>
                    @endforeach
                    <th class="p-3">Waktu Isi</th>
                </tr>
            </thead>
            <tbody class="divide-y text-gray-700">
                @forelse ($responses as $i => $response)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 text-gray-400">{{ $i + 1 }}</td>
                        @foreach ($form->fields as $field)
                            <td class="p-3">{{ $response->answers[$field['nama']] ?? '-' }}</td>
                        @endforeach
                        <td class="p-3 text-xs text-gray-500">{{ $response->created_at->translatedFormat('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($form->fields) + 2 }}" class="p-6 text-center text-gray-400">
                            Belum ada yang mengisi formulir ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-layouts.app>
