{{--
    Modal "Detail Reservasi" bersama: Ruang Rapat, Breakout Room Zoom, dan Form Kehadiran.
    Dipakai oleh Dashboard dan Semua Pemesanan (Admin), jadi isi & gayanya selalu sama.

    Pemakaian:
      1. @include('partials.reservation-detail-modal')
      2. Tombol memanggil: openReservationDetail(@js(ReservationDetail::room(...)))
         (data dibentuk oleh App\Support\ReservationDetail).
      3. Bila data memuat `cancel`, modal menampilkan tombol pembatalan dan memanggil
         fungsi reservationDetailCancel(res) yang WAJIB didefinisikan oleh halaman pemanggil.

    Perbedaan antar halaman ditentukan oleh data, bukan oleh file ini:
      - `pemesan` terisi  -> kartu "Informasi Pemesan" tampil (hanya Semua Pemesanan).
      - `cancel` terisi   -> tombol pembatalan tampil.
--}}
<div
    id="reservationDetailModal"
    class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-3 sm:p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="reservationDetailTitle"
>
    <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden border">

        {{-- Header Modal --}}
        <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50 shrink-0">
            <h4 id="reservationDetailTitle" class="font-bold text-gray-800 text-sm">Detail Reservasi</h4>

            <button
                type="button"
                onclick="closeReservationDetail()"
                class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                aria-label="Tutup detail"
            >
                &times;
            </button>
        </div>

        {{-- Body Modal (Scrollable) --}}
        <div id="reservationDetailBody" class="px-4 py-3 overflow-y-auto flex-1 space-y-3">
            <!-- Konten diisi lewat JS -->
        </div>

        {{-- Footer Modal --}}
        <div id="reservationDetailActions" class="px-4 py-3 border-t bg-gray-50 shrink-0">
            <!-- Tombol aksi diisi lewat JS -->
        </div>
    </div>
</div>

<script>
    (function () {
        const esc = (str) => String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        const row = (label, value) => {
            if (value === null || value === undefined || value === '') return '';
            return `
                <div class="flex justify-between gap-2 py-1 border-b border-gray-100 last:border-0 text-[11px]">
                    <span class="text-gray-500 font-medium shrink-0">${label}</span>
                    <span class="font-semibold text-gray-800 text-right truncate">${esc(value)}</span>
                </div>
            `;
        };

        const section = (title, rowsHtml) => {
            const filled = rowsHtml.filter(Boolean).join('');
            if (!filled) return '';
            return `
                <div class="rounded-lg border border-gray-200 bg-white p-2.5 shadow-xs flex flex-col justify-between">
                    <div>
                        <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">${title}</p>
                        <div class="space-y-0.5">${filled}</div>
                    </div>
                </div>
            `;
        };

        // Kotak teks yang bisa disalin (Broadcast Zoom / Link Formulir).
        const copyBlock = (title, text, buttonLabel) => `
            <div class="rounded-lg border border-gray-200 bg-white p-2.5 shadow-xs sm:col-span-2">
                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">${title}</p>
                <div id="reservationDetailCopyText" class="whitespace-pre-wrap rounded-md border border-gray-100 bg-gray-50 p-2 font-mono text-[11px] leading-relaxed text-gray-700 select-all">${esc(text)}</div>
                <button
                    type="button"
                    onclick="copyReservationDetailText()"
                    id="reservationDetailCopyBtn"
                    data-label="${esc(buttonLabel)}"
                    class="mt-2 w-full rounded-lg bg-emerald-600 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                >
                    ${esc(buttonLabel)}
                </button>
            </div>
        `;

        const statusLabels = {
            room: { mendatang: 'Mendatang', selesai: 'Selesai', menunggu_pembatalan: 'Menunggu Pembatalan', dibatalkan: 'Dibatalkan' },
            zoom: { mendatang: 'Mendatang', selesai: 'Selesai', menunggu_pembatalan: 'Menunggu Pembatalan', dibatalkan: 'Dibatalkan' },
            form: { mendatang: 'Formulir Aktif', selesai: 'Sudah Kedaluwarsa' },
        };

        window.openReservationDetail = function (res) {
            const isRoom = res.jenis === 'room';
            const isZoom = res.jenis === 'zoom';
            const isForm = res.jenis === 'form';
            const pemesan = res.pemesan || null;
            const form = res.form || null;
            const statusText = (statusLabels[res.jenis] || {})[res.status] ?? res.status;

            document.getElementById('reservationDetailTitle').innerText =
                (isForm ? 'Detail Pembuatan Form ' : 'Detail Reservasi ') + res.kode;

            // Badge status: hijau untuk yang masih berjalan, abu-abu untuk lainnya.
            const active = res.status === 'mendatang';
            const badgeClass = active
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-slate-50 text-slate-700 border-slate-200';
            const dotClass = active ? 'bg-emerald-500' : 'bg-slate-500';

            const sections = [
                // Informasi Pemesan hanya tampil bila halaman mengirim datanya.
                pemesan ? section('Informasi Pemesan', [
                    row('Nama', pemesan.name),
                    row('NIP', pemesan.nip || '-'),
                    row('Divisi', pemesan.divisi || '-'),
                    row('Jabatan', pemesan.jabatan || 'Staff'),
                    row('Email', pemesan.email),
                ]) : '',

                isForm
                    ? section('Informasi Form', [
                        row('Kode', res.kode),
                        row('Judul Form', form?.judul),
                        row('Tempat', form?.tempat),
                        row('Rapat / Pertemuan', form?.rapat),
                        row('Kedaluwarsa', form?.expires_label),
                    ])
                    : section('Informasi Reservasi', [
                        row('Kode', res.kode),
                        row('Jenis', res.jenis_label),
                        row('Ruangan', res.ruang),
                        row('Lantai', res.lantai),
                        row('Kapasitas', res.kapasitas),
                        row('Ruang Gabungan', res.ruang_gabungan),
                        row('Tanggal', res.tanggal),
                        row('Waktu', res.waktu),
                        // Peserta & Konsumsi hanya relevan untuk Ruang Rapat.
                        isRoom ? row('Peserta', res.jumlah_peserta ? res.jumlah_peserta + ' orang' : null) : '',
                        isRoom ? row('Konsumsi', res.konsumsi) : '',
                        row(isZoom ? 'Agenda' : 'Keperluan', res.keperluan),
                    ]),

                section('Penanggung Jawab', [
                    row('Nama PIC', res.nama_pic || '-'),
                    row('No. Telp PIC', res.no_telp_pic),
                    row('Divisi PIC', res.divisi_pic),
                ]),

                section('Pemesanan', [
                    row('Status', statusText),
                    row('Dibuat', res.created_at),
                ]),
            ];

            // Kotak salin: Broadcast & Link Zoom (Zoom) atau Link Formulir (Form).
            let copyHtml = '';
            if (isZoom && res.broadcast) {
                copyHtml = copyBlock('Broadcast &amp; Link Zoom', res.broadcast, 'Salin Broadcast');
            } else if (isForm && form?.link) {
                copyHtml = copyBlock('Link Formulir', form.link, 'Salin Link Formulir');
            }

            document.getElementById('reservationDetailBody').innerHTML = `
                <div class="mb-2">
                    <span class="inline-flex items-center gap-1 rounded-full ${badgeClass} px-2.5 py-0.5 text-[11px] font-bold border">
                        <span class="h-1.5 w-1.5 rounded-full ${dotClass}"></span>
                        ${esc(statusText)}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    ${sections.join('')}
                    ${copyHtml}
                </div>
            `;

            // Footer: tombol menuju link + tombol pembatalan (bila ada).
            const linkUrl = isZoom ? res.zoom_link : (isForm ? form?.link : null);
            const linkLabel = isZoom ? 'Menuju Link Zoom' : 'Buka Formulir';

            const linkButton = linkUrl ? `
                <a
                    href="${esc(linkUrl)}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full block text-center py-2 border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg font-bold text-xs transition-colors"
                >
                    ${linkLabel}
                </a>
            ` : '';

            const cancelButton = res.cancel ? `
                <button
                    type="button"
                    onclick="reservationDetailCancel(window.__reservationDetail)"
                    class="w-full py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold text-xs transition-colors shadow-sm"
                >
                    ${esc(res.cancel.label)}
                </button>
            ` : '';

            window.__reservationDetail = res;

            document.getElementById('reservationDetailActions').innerHTML = (linkButton || cancelButton)
                ? `<div class="flex flex-col gap-2">${linkButton}${cancelButton}</div>`
                : '';

            const modal = document.getElementById('reservationDetailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.closeReservationDetail = function () {
            const modal = document.getElementById('reservationDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        window.copyReservationDetailText = function () {
            const el = document.getElementById('reservationDetailCopyText');
            const btn = document.getElementById('reservationDetailCopyBtn');
            if (!el || !btn) return;

            const text = el.textContent;
            const label = btn.dataset.label;

            function markCopied() {
                btn.textContent = 'Berhasil Disalin!';
                setTimeout(() => { btn.textContent = label; }, 2000);
            }

            function fallback() {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    if (document.execCommand('copy')) {
                        markCopied();
                    } else {
                        alert('Gagal menyalin otomatis. Silakan salin teks secara manual.');
                    }
                } catch (e) {
                    alert('Gagal menyalin otomatis. Silakan salin teks secara manual.');
                }
                document.body.removeChild(textarea);
            }

            // navigator.clipboard hanya tersedia di HTTPS atau localhost.
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(markCopied).catch(fallback);
            } else {
                fallback();
            }
        };

        document.getElementById('reservationDetailModal').addEventListener('click', function (event) {
            if (event.target === this) closeReservationDetail();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeReservationDetail();
        });
    })();
</script>
