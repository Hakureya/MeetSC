<x-layouts.app title="Sukses Reservasi Link Zoom">
<div class="max-w-xl mx-auto py-10 px-4">
    <div class="bg-white border rounded-2xl shadow-sm p-6 sm:p-8">
        
        <!-- Status Badge Atas -->
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md">
                Status: Terkonfirmasi
            </span>
            <span class="text-xs text-gray-400">ID: #{{ $reservation->id ?? '-' }}</span>
        </div>

        <!-- Banner Sukses -->
        <div class="text-center">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl font-bold shadow-xs">
                ✓
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Breakout Room Berhasil Dipesan</h2>
            <p class="text-xs text-gray-500 mt-1">Room {{ $reservation->room_number }} telah dialokasikan untuk agenda Anda</p>
        </div>

        <!-- QR Code -->
        <div class="flex flex-col items-center justify-center my-6">
            <div class="p-2.5 bg-white border border-gray-200 rounded-xl shadow-xs">
                <img 
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(\App\Models\ZoomReservation::MEETING_URL) }}" 
                    alt="QR Code Link Zoom" 
                    class="w-36 h-36 rounded-lg block"
                >
            </div>
            <span class="text-[11px] text-gray-400 mt-2">Scan untuk bergabung ke tautan Zoom</span>
        </div>

        <!-- Container Format Undangan Rapat + Tombol Salin -->
        <div class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50">
            <!-- Header Container -->
            <div class="flex items-center justify-between px-4 py-2.5 bg-gray-100/80 border-b border-gray-200">
                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Format Undangan Rapat</span>
                <button 
                    type="button" 
                    onclick="copyBroadcast()" 
                    id="btnCopy" 
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-medium text-xs rounded-lg transition shadow-xs"
                >
                    Salin Broadcast
                </button>
            </div>
            
            <!-- Isi Teks Undangan -->
            <div 
                id="zoomBroadcastPayload" 
                class="p-4 font-mono text-xs text-gray-700 whitespace-pre-wrap leading-relaxed select-all max-h-56 overflow-y-auto"
            >Dengan Hormat,

Sehubung dengan adanya {{ $reservation->nama_agenda }}. Bersama ini kami sampaikan undangan pada,

Hari, tanggal : {{ \Carbon\Carbon::parse($reservation->tanggal)->isoFormat('dddd, D MMMM Y') }}
Jam  : {{ $reservation->jam_range }}
Join Zoom Meeting
Meeting ID : 563 993 3613
Room : Ruang {{ $reservation->room_number }}
Pass : AKHLAK
Link :
{{ \App\Models\ZoomReservation::MEETING_URL }}

Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih</div>
        </div>

        <!-- Detail PIC Ringkas -->
        <div class="mt-4 border border-gray-200 rounded-xl p-3.5 bg-gray-50 text-xs text-gray-600 space-y-1.5">
            <div class="flex justify-between">
                <span class="text-gray-500">Penanggung Jawab (PIC):</span>
                <span class="font-medium text-gray-800">{{ $reservation->nama_pic }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Kontak WhatsApp/Telp:</span>
                <span class="font-medium text-gray-800">{{ $reservation->no_telp_pic }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Divisi / Unit:</span>
                <span class="font-medium text-gray-800">{{ $reservation->divisi_pic ?? '-' }}</span>
            </div>
        </div>

        <!-- 2 Tombol Navigasi Bawah -->
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a 
                href="{{ route('zoom.index') }}" 
                class="flex-1 py-2.5 px-4 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition border border-gray-200"
            >
                Daftar Reservasi
            </a>
            <a 
                href="{{ route('zoom.create') }}" 
                class="flex-1 py-2.5 px-4 text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl transition shadow-xs"
            >
                Pesan Ruangan Lain
            </a>
        </div>

    </div>
</div>

<script>
function copyBroadcast() {
    const text = document.getElementById('zoomBroadcastPayload').innerText;
    const b = document.getElementById('btnCopy');

    function markCopied() {
        b.innerText = "✓ Tersalin!";
        b.classList.replace('bg-emerald-600', 'bg-emerald-800');
        setTimeout(() => {
            b.innerText = "Salin Broadcast";
            b.classList.replace('bg-emerald-800', 'bg-emerald-600');
        }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(markCopied).catch(() => copyBroadcastFallback(text, markCopied));
    } else {
        copyBroadcastFallback(text, markCopied);
    }
}

function copyBroadcastFallback(text, onSuccess) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
        if (document.execCommand('copy')) {
            onSuccess();
        } else {
            alert('Gagal menyalin otomatis. Silakan salin teks di atas secara manual.');
        }
    } catch (e) {
        alert('Gagal menyalin otomatis. Silakan salin teks di atas secara manual.');
    }
    document.body.removeChild(textarea);
}
</script>
</x-layouts.app>