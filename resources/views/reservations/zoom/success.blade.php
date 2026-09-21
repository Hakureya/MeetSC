<x-layouts.app title="Sukses Reservasi Link Zoom">
<div class="max-w-xl mx-auto py-12 px-4">
    <div class="bg-white border rounded-2xl shadow-sm p-8">
        <div class="text-center">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl font-black">✓</div>
            <h2 class="text-2xl font-bold text-gray-800">Breakout Room berhasil dipesan</h2>
            <p class="text-xs text-gray-500">Room {{ $reservation->room_number }} aktif untuk agenda Anda</p>
        </div>

        <div class="flex justify-center my-6">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode('https://zoom.us/j/5639933613?pwd=ck1LVi9vQ2owcmRKUGdGNW5Gb0VMZz09') }}" alt="QR Code Link Zoom" class="border p-2 rounded-xl bg-white shadow-sm">
        </div>

        <div class="bg-gray-50 border rounded-xl p-4 font-mono text-xs text-gray-700 whitespace-pre-wrap leading-relaxed select-all" id="zoomBroadcastPayload">Dengan Hormat,

Sehubung dengan adanya {{ $reservation->nama_agenda }}. Bersama ini kami sampaikan undangan pada,

Hari, tanggal : {{ \Carbon\Carbon::parse($reservation->tanggal)->isoFormat('dddd, D MMMM Y') }}
Jam  : {{ $reservation->jam_range }}
Join Zoom Meeting
Meeting ID : 563 993 3613
Room : Ruang {{ $reservation->room_number }}
Pass : AKHLAK
Link :
https://zoom.us/j/5639933613?pwd=ck1LVi9vQ2owcmRKUGdGNW5Gb0VMZz09

Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih</div>

        <div class="mt-4 text-left border rounded-xl p-4 bg-gray-50 space-y-2 text-sm text-gray-700">
            <p><span class="font-semibold w-32 inline-block">Nama PIC:</span> {{ $reservation->nama_pic }}</p>
            <p><span class="font-semibold w-32 inline-block">No. Telp PIC:</span> {{ $reservation->no_telp_pic }}</p>
            <p><span class="font-semibold w-32 inline-block">Divisi PIC:</span> {{ $reservation->divisi_pic ?? '-' }}</p>
        </div>

        <div class="mt-6 flex flex-col gap-3">
            <button onclick="copyBroadcast()" id="btnCopy" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow">Salin Broadcast</button>
            <a href="{{ route('zoom.index') }}" class="w-full text-center py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50">buat pemesanan lagi</a>
        </div>
    </div>
</div>

<script>
function copyBroadcast() {
    const text = document.getElementById('zoomBroadcastPayload').innerText;
    const b = document.getElementById('btnCopy');

    function markCopied() {
        b.innerText = "Berhasil Disalin!";
        b.classList.replace('bg-emerald-600', 'bg-emerald-800');
        setTimeout(() => {
            b.innerText = "Salin Broadcast";
            b.classList.replace('bg-emerald-800', 'bg-emerald-600');
        }, 2000);
    }

    // navigator.clipboard hanya tersedia di HTTPS atau localhost/127.0.0.1.
    // Saat diakses lewat IP LAN via HTTP biasa, ini bisa undefined dan gagal diam-diam.
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