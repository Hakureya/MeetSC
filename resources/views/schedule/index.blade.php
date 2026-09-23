<x-layouts.app title="Breakout Room Zoom">
<style>
    /* ------------------------------------------------------------------
       Halaman utama Jadwal Breakout Room Zoom. Ditulis dengan CSS sendiri (prefix .sch-) dan satuan em
       pada papan jadwal, supaya seluruh isi tabel ikut membesar rapi saat mode Full Screen.
       ------------------------------------------------------------------ */
    .sch-noscroll { overflow: hidden; }

    .sch-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
    .sch-btn {
        display: inline-flex; align-items: center; gap: .5rem; cursor: pointer;
        border: 1px solid #e2e8f0; background: #fff; color: #334155;
        border-radius: .7rem; padding: .6rem 1.1rem; font-size: .9rem; font-weight: 600;
        transition: background .15s, border-color .15s;
    }
    .sch-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
    .sch-btn-primary { background: #1e6feb; border-color: #1e6feb; color: #fff; }
    .sch-btn-primary:hover { background: #0f5fe0; border-color: #0f5fe0; }
    .sch-btn svg { width: 1.05rem; height: 1.05rem; }

    .sch-layout { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); align-items: start; margin-top: 1.5rem; }
    @media (min-width: 1180px) { .sch-layout { grid-template-columns: minmax(0, 1fr) 380px; } }

    /* ---------- Papan jadwal (tabel) ---------- */
    .sch-board {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; overflow: hidden;
        box-shadow: 0 1px 2px rgb(15 23 42 / .04); color: #0f172a; font-size: 14px;
    }
    .sch-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; }
    .sch-head-title { display: flex; align-items: center; gap: .6rem; font-weight: 700; font-size: 1.05em; }
    .sch-head-title svg { width: 1.25em; height: 1.25em; color: #1e6feb; }
    .sch-pill { background: #d1fae5; color: #047857; border-radius: 999px; padding: .25em .8em; font-size: .82em; font-weight: 600; white-space: nowrap; }

    .sch-scroll { overflow-x: auto; }
    .sch-table { min-width: 620px; }
    .sch-cols, .sch-row { display: grid; grid-template-columns: minmax(150px, 1fr) minmax(0, 3.2fr) minmax(112px, .9fr); }
    .sch-cols { background: #f1f5f9; color: #475569; font-size: .78em; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; }
    .sch-cols > div { padding: .85em 1.15em; }

    .sch-row { border-top: 1px solid #e2e8f0; align-items: start; transition: background .2s, box-shadow .2s; }
    .sch-row > div { padding: .95em 1.15em; }
    .sch-row.is-lit { background: #ecfdf5; box-shadow: inset 4px 0 0 #10b981; }

    .sch-room-name { font-weight: 700; font-size: 1.05em; line-height: 1.3; }

    .sch-booked { position: relative; background: #059669; color: #fff; border-radius: .85em; padding: .8em 1.1em; display: flex; flex-direction: column; gap: .18em; }
    .sch-row.is-lit .sch-booked { background: #047857; }
    .sch-booked .sch-t { display: flex; align-items: flex-start; justify-content: space-between; gap: .75em; font-weight: 700; font-size: 1.05em; line-height: 1.35; }
    .sch-booked .sch-l { font-size: .88em; line-height: 1.4; opacity: .95; }
    .sch-live { display: inline-flex; align-items: center; gap: .4em; flex-shrink: 0; background: rgb(255 255 255 / .2); border-radius: 999px; padding: .1em .65em; font-size: .72em; font-weight: 700; white-space: nowrap; }
    .sch-live i { width: .55em; height: .55em; border-radius: 50%; background: #fff; animation: sch-pulse 1.4s ease-in-out infinite; }
    @keyframes sch-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .25; } }

    .sch-free { background: #f1f5f9; color: #475569; border-radius: .75em; padding: .75em 1.1em; font-size: .95em; }

    .sch-time-main { color: #475569; font-weight: 500; padding-top: .1em; }
    .sch-time-next { margin-top: .75em; color: #64748b; font-size: .85em; line-height: 1.5; }
    .sch-time-next small { display: block; font-size: .85em; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }

    /* ------------------------------------------------------------------
       Mode Full Screen: "Portal Breakout Room & Jadwal Kegiatan" — layar kiosk untuk
       ditampilkan di TV/monitor dekat area meeting, bukan lagi tabel biasa yang dibesarkan.
       Elemen #schKiosk disembunyikan sampai tombol Full Screen ditekan, lalu dijadikan target
       requestFullscreen (native, dengan fallback CSS fixed-inset kalau browser menolak).
       ------------------------------------------------------------------ */
    #schKiosk { display: none; }
    #schKiosk.is-fs {
        display: flex; flex-direction: column; position: fixed; inset: 0; z-index: 9999;
        background: #e2e8f0; padding: 1.4vh 1.4vw; font-size: clamp(12px, 1.15vw, 22px);
    }

    .kiosk-frame { flex: 1; min-height: 0; display: flex; flex-direction: column; border-radius: 1.6em; overflow: hidden; box-shadow: 0 25px 60px -20px rgb(15 23 42 / .35); }

    .kiosk-head {
        flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; gap: 1.5em;
        padding: 1.6em 2em; color: #fff; background: linear-gradient(120deg, #1d4ed8, #4338ca 65%, #4f46e5);
    }
    .kiosk-live { display: inline-flex; align-items: center; gap: .55em; background: rgb(255 255 255 / .16); border: 1px solid rgb(255 255 255 / .3); border-radius: 999px; padding: .35em 1em .35em .8em; font-size: .8em; font-weight: 700; }
    .kiosk-live i { width: .6em; height: .6em; border-radius: 50%; background: #4ade80; flex-shrink: 0; }
    .kiosk-live.is-idle { background: rgb(255 255 255 / .1); }
    .kiosk-live.is-idle i { background: rgb(255 255 255 / .5); animation: none; }
    .kiosk-live.is-live i { animation: sch-pulse 1.4s ease-in-out infinite; }
    .kiosk-title { margin-top: .3em; font-size: 1.9em; font-weight: 800; line-height: 1.25; }
    .kiosk-right { display: flex; align-items: center; gap: 1.4em; flex-shrink: 0; }
    .kiosk-clockbox { text-align: center; background: rgb(15 23 42 / .28); border: 1px solid rgb(255 255 255 / .18); border-radius: .9em; padding: .55em 1.3em; }
    .kiosk-clockbox .d { font-size: .68em; font-weight: 700; letter-spacing: .04em; opacity: .85; }
    .kiosk-clockbox .t { margin-top: .1em; display: flex; align-items: center; justify-content: center; gap: .5em; font-size: 1.55em; font-weight: 800; font-variant-numeric: tabular-nums; color: #4ade80; }
    .kiosk-clockbox .t span { background: rgb(255 255 255 / .12); border-radius: .3em; padding: .15em .55em; }
    .kiosk-exit { display: inline-flex; align-items: center; gap: .5em; background: rgb(255 255 255 / .16); border: 1px solid rgb(255 255 255 / .4); color: #fff; border-radius: .7em; padding: .6em 1.1em; font-size: .82em; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .kiosk-exit:hover { background: rgb(255 255 255 / .28); }
    .kiosk-exit svg { width: 1.1em; height: 1.1em; }

    .kiosk-body { flex: 1; min-height: 0; display: grid; grid-template-columns: minmax(260px, 1.05fr) minmax(0, 1.7fr); gap: 1.4em; padding: 1.4em; background: #f1f5f9; overflow: hidden; }
    .kiosk-col { min-height: 0; overflow-y: auto; }

    .kiosk-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1.1em; padding: 1.4em; }
    .kiosk-guide-head { display: flex; align-items: flex-start; gap: .8em; }
    .kiosk-guide-icon { flex-shrink: 0; width: 2.6em; height: 2.6em; border-radius: .8em; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; }
    .kiosk-guide-icon svg { width: 1.35em; height: 1.35em; }
    .kiosk-guide-head h2 { font-size: 1.15em; font-weight: 800; color: #0f172a; }
    .kiosk-guide-head p { margin-top: .2em; font-size: .82em; color: #64748b; }

    .kiosk-steps { margin-top: 1.2em; display: flex; flex-direction: column; gap: .9em; }
    .kiosk-step { border: 1px solid #e2e8f0; border-radius: 1em; padding: 1em 1.1em; }
    .kiosk-step-head { display: flex; align-items: flex-start; gap: .7em; }
    .kiosk-num { flex-shrink: 0; width: 1.7em; height: 1.7em; border-radius: 50%; background: #dbeafe; color: #1d4ed8; font-weight: 800; font-size: .85em; display: flex; align-items: center; justify-content: center; }
    .kiosk-step-head b { display: block; font-size: .92em; font-weight: 800; color: #0f172a; letter-spacing: .01em; }
    .kiosk-step-head span { display: block; margin-top: .3em; font-size: .82em; line-height: 1.5; color: #475569; }

    /* Mockup visual toolbar Zoom (langkah 1) */
    .kiosk-mockbar { margin: .9em 0 0 2.4em; background: #0f172a; border-radius: .8em; padding: .8em 1.1em; display: flex; align-items: center; gap: 1.3em; }
    .kiosk-mockbtn { display: flex; flex-direction: column; align-items: center; gap: .3em; color: #cbd5e1; font-size: .68em; font-weight: 600; }
    .kiosk-mockbtn i { width: 1.5em; height: 1.5em; border-radius: 50%; border: 1.5px solid #64748b; display: block; }
    .kiosk-mockbtn.active { color: #fff; }
    .kiosk-mockbtn.active .breakout-box { width: 2.6em; height: 1.9em; border-radius: .4em; background: #2563eb; display: flex; align-items: center; justify-content: center; position: relative; }
    .kiosk-mockbtn.active .breakout-box::after { content: ''; position: absolute; top: -.15em; right: -.15em; width: .5em; height: .5em; border-radius: 50%; background: #4ade80; border: 2px solid #0f172a; }
    .kiosk-grid4 { width: .85em; height: .85em; display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: .12em; }
    .kiosk-grid4 span { background: #fff; border-radius: 1px; }

    /* Mockup pop-up pilih room (langkah 2) */
    .kiosk-mockpopup { margin: .9em 0 0 2.4em; display: flex; flex-direction: column; gap: .5em; }
    .kiosk-mockroom { display: flex; align-items: center; justify-content: space-between; gap: .8em; border: 1px solid #e2e8f0; border-radius: .7em; padding: .55em .9em; font-size: .8em; color: #334155; }
    .kiosk-mockroom span.cnt { color: #94a3b8; font-size: .85em; }
    .kiosk-mockroom.hl { border-color: #93c5fd; background: #eff6ff; }
    .kiosk-mockjoin { background: #2563eb; color: #fff; font-weight: 700; font-size: .85em; border-radius: .45em; padding: .3em .9em; }

    .kiosk-note { margin-top: 1.2em; display: flex; gap: .7em; background: #fffbeb; border: 1px solid #fde68a; border-radius: 1em; padding: 1em 1.1em; }
    .kiosk-note svg { flex-shrink: 0; width: 1.3em; height: 1.3em; color: #d97706; margin-top: .1em; }
    .kiosk-note p { font-size: .82em; line-height: 1.55; color: #78350f; }

    .kiosk-table-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1em; }
    .kiosk-table-head h2 { font-size: 1.15em; font-weight: 800; color: #0f172a; }
    .kiosk-table-head p { margin-top: .2em; font-size: .82em; color: #64748b; }
    .kiosk-total { flex-shrink: 0; background: #f1f5f9; color: #334155; border-radius: 999px; padding: .4em 1em; font-size: .78em; font-weight: 700; white-space: nowrap; }

    .kiosk-cols, .kiosk-row { display: grid; grid-template-columns: minmax(90px, .8fr) minmax(0, 2.6fr) minmax(120px, .85fr); align-items: center; gap: 1em; }
    .kiosk-cols { margin-top: 1.2em; padding: 0 .3em .8em; border-bottom: 1px solid #e2e8f0; font-size: .72em; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .kiosk-rows-wrap { margin-top: .2em; }
    .kiosk-row { padding: .95em .3em; border-bottom: 1px solid #f1f5f9; }
    .kiosk-room { display: flex; align-items: center; gap: .55em; font-weight: 700; color: #1d4ed8; font-size: .92em; }
    .kiosk-room i { width: .5em; height: .5em; border-radius: 50%; background: #60a5fa; flex-shrink: 0; }
    .kiosk-row.is-live .kiosk-room i { background: #10b981; }
    .kiosk-agenda { font-size: .92em; color: #1e293b; line-height: 1.4; }
    .kiosk-row.is-free .kiosk-agenda { color: #94a3b8; font-style: italic; }
    .kiosk-time { text-align: right; font-size: .88em; font-weight: 600; color: #475569; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .kiosk-badge-live { display: inline-block; margin-left: .5em; background: #d1fae5; color: #047857; border-radius: 999px; padding: .1em .6em; font-size: .68em; font-weight: 700; vertical-align: middle; }

    .kiosk-foot { flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; gap: 1em; padding: .9em 2em; background: #fff; border-top: 1px solid #e2e8f0; font-size: .74em; color: #94a3b8; }

    /* ---------- Panel Agenda ---------- */
    .sch-panel {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.35rem 1.25rem 1.25rem;
        display: flex; flex-direction: column; max-height: calc(100vh - 2rem);
    }
    @media (min-width: 1180px) { .sch-panel { position: sticky; top: 1rem; } }
    .sch-panel h2 { font-size: 1.2rem; font-weight: 800; color: #0f172a; }
    .sch-panel-hint { margin-top: .5rem; font-size: .85rem; line-height: 1.45; color: #64748b; }
    .sch-panel-scope { margin-top: .35rem; font-size: .75rem; font-weight: 600; color: #1e6feb; }
    .sch-list { margin-top: 1rem; display: flex; flex-direction: column; gap: .8rem; overflow-y: auto; padding-right: .35rem; min-height: 0; }
    .sch-empty { border: 1px dashed #cbd5e1; border-radius: .9rem; padding: 2rem 1rem; text-align: center; font-size: .875rem; color: #94a3b8; }

    .sch-card {
        border: 1px solid #e2e8f0; border-left: 4px solid #e2e8f0; border-radius: .9rem; padding: .85rem 1rem; background: #fff; outline: none;
        transition: background .18s, border-color .18s, box-shadow .18s;
    }
    .sch-card:hover, .sch-card:focus-visible, .sch-card.is-hover { background: #ecfdf5; border-color: #a7f3d0; border-left-color: #10b981; }
    .sch-card:focus-visible { box-shadow: 0 0 0 3px rgb(16 185 129 / .3); }

    .sch-card-top { display: flex; align-items: center; justify-content: space-between; gap: .6rem; font-size: .88rem; color: #0f172a; }
    .sch-card-room { display: inline-flex; align-items: center; gap: .4rem; font-weight: 700; min-width: 0; }
    .sch-card-room svg { width: 1rem; height: 1rem; color: #1e6feb; flex-shrink: 0; }
    .sch-card-when { margin-top: .25rem; padding-left: 1.4rem; font-size: .8rem; font-weight: 600; color: #475569; }
    .sch-badge { border-radius: 999px; padding: .12rem .6rem; font-size: .7rem; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
    .sch-badge.today { background: #d1fae5; color: #047857; }
    .sch-badge.live { background: #059669; color: #fff; }
    .sch-badge.soon { background: #dbeafe; color: #1d4ed8; }

    .sch-card-title { margin-top: .55rem; font-size: 1.02rem; font-weight: 700; color: #0f172a; line-height: 1.35; }
    .sch-card-pic { margin-top: .5rem; display: flex; align-items: center; gap: .55rem; font-size: .85rem; color: #475569; }
    .sch-avatar { width: 1.6rem; height: 1.6rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .65rem; font-weight: 800; flex-shrink: 0; }
    .sch-card-foot { margin-top: .7rem; padding-top: .6rem; border-top: 1px solid #eef2f7; display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .sch-card-id { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; color: #94a3b8; }
    .sch-detail-btn { background: #0f172a; color: #fff; border: 0; border-radius: .5rem; padding: .35rem .95rem; font-size: .8rem; font-weight: 700; cursor: pointer; }
    .sch-detail-btn:hover { background: #1e293b; }

    /* ---------- Modal detail ---------- */
    .sch-modal-card { max-height: 85vh; }
</style>

@php
    $icons = [
        'expand' => '<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
        'plus' => '<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    ];
@endphp

<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Jadwal Reservasi Breakout Room Zoom</h1>
            <p class="mt-1 text-sm text-slate-500">Hari ini: <span id="schToday" class="font-semibold text-slate-800"></span></p>
        </div>

        <div class="sch-actions">
            <button type="button" id="schFullscreenBtn" class="sch-btn">
                <svg viewBox="0 0 24 24" fill="none">{!! $icons['expand'] !!}</svg>
                Full Screen
            </button>
            <a href="{{ route('zoom.create') }}" class="sch-btn sch-btn-primary">
                <svg viewBox="0 0 24 24" fill="none">{!! $icons['plus'] !!}</svg>
                Buat Reservasi
            </a>
        </div>
    </div>

    <div class="sch-layout">
        {{-- ================= Tabel Ruangan ================= --}}
        <section id="schBoard" class="sch-board" aria-label="Tabel breakout room Zoom jadwal hari ini">
            <div class="sch-scroll">
                <div class="sch-table" role="table">
                    <div class="sch-cols" role="row">
                        <div role="columnheader">Room</div>
                        <div role="columnheader">Agenda</div>
                        <div role="columnheader">Time</div>
                    </div>
                    <div class="sch-rows" id="schRows" role="rowgroup"></div>
                </div>
            </div>
        </section>

        {{-- ================= Agenda Hari Ini / Akan Datang ================= --}}
        <aside class="sch-panel">
            <h2>Agenda Hari Ini / Akan Datang</h2>
            <p class="sch-panel-hint">Arahkan kursor ke agenda untuk melihat sinkronisasi lokasi ruangan di tabel.</p>
            <p class="sch-panel-scope">{{ auth()->user()->isAdmin() ? 'Menampilkan agenda dari semua akun' : 'Menampilkan agenda Anda' }}</p>
            <div class="sch-list" id="schList"></div>
        </aside>
    </div>
</div>

{{-- ================= Mode Full Screen: Portal Breakout Room & Jadwal Kegiatan ================= --}}
<div id="schKiosk" aria-label="Portal Breakout Room dan jadwal kegiatan, tampilan layar penuh">
    <div class="kiosk-frame">
        <div class="kiosk-head">
            <div>
                <span class="kiosk-live" id="kioskLive"><i></i><span id="kioskLiveText">Memuat…</span></span>
                <div class="kiosk-title">Portal Breakout Room &amp; Jadwal Kegiatan</div>
            </div>
            <div class="kiosk-right">
                <div class="kiosk-clockbox">
                    <div class="d" id="kioskDate">-</div>
                    <div class="t"><span id="kioskClock">00:00:00</span> WIB</div>
                </div>
                <button type="button" class="kiosk-exit" id="kioskExit">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M9 4H4v5M15 20h5v-5M4 4l6 6M20 20l-6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Exit Full Screen
                </button>
            </div>
        </div>

        <div class="kiosk-body">
            <div class="kiosk-col">
                <div class="kiosk-card flex flex-col h-full">
                    {{-- Header / Judul Tetap Ada --}}
                    <div class="kiosk-guide-head shrink-0">
                        <span class="kiosk-guide-icon" style="background: transparent; padding: 0;">
                            <img 
                                src="{{ asset('storage/images/zoom-icon.png') }}" 
                                alt="Zoom Icon" 
                                style="width: 100%; height: 100%; object-fit: contain;"
                            >
                        </span>
                        <div>
                            <h2>Panduan Breakout Room</h2>
                            <p>Ikuti tahapan visual di bawah ini</p>
                        </div>
                    </div>

                    {{-- Pengganti Langkah-langkah: Gambar Panduan --}}
                    <div class="mt-3 flex-1 min-h-0 flex items-center justify-center overflow-hidden">
                        <img 
                            src="{{ asset('storage/images/panduan-breakout.jpeg') }}" 
                            alt="Panduan Bergambar Masuk Breakout Room Zoom" 
                            class="w-full h-full max-h-[58vh] object-contain rounded-xl border border-slate-200 shadow-sm"
                            loading="lazy"
                        >
                    </div>

                    {{-- Catatan Tambahan di Bawah --}}
                    <div class="kiosk-note shrink-0 mt-3">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v5M12 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <p><b>Catatan:</b> Jika tombol <i>Breakout Rooms</i> tidak terlihat, klik opsi <b>More (…)</b> di kanan bawah layar Zoom Anda.</p>
                    </div>
                </div>
            </div>

            <div class="kiosk-col">
                <div class="kiosk-card">
                    {{-- Elemen tersembunyi agar JavaScript tidak error //yang bikin jamnya jalan kioskTotal--}} 
                    <span id="kioskTotal" style="display: none;"></span>

                    <div class="kiosk-cols">
                        <div>Room</div>
                        <div>Agenda</div>
                        <div style="text-align:right">Time</div>
                    </div>
                    <div class="kiosk-rows-wrap" id="kioskRows"></div>
                </div>
            </div>
        </div>

        <div class="kiosk-foot">
            <span>*Zona Waktu: WIB (Waktu Indonesia Barat)</span>
            <span>Waktu otomatis tersinkronisasi</span>
        </div>
    </div>
</div>

{{-- ================= Modal Detail ================= --}}
<div id="schModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-3 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="schModalTitle">
    <div class="sch-modal-card bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden border">

        {{-- Header Modal --}}
        <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50 shrink-0">
            <div>
                <h4 id="schModalTitle" class="font-bold text-gray-800 text-sm">Detail Reservasi</h4>
                <p id="schModalId" class="mt-0.5 text-xs text-gray-400"></p>
            </div>
            <button type="button" data-close-modal class="text-gray-400 hover:text-gray-600 text-xl leading-none" aria-label="Tutup detail">&times;</button>
        </div>

        {{-- Body Modal (Scrollable) --}}
        <div id="schModalBody" class="px-4 py-3 overflow-y-auto flex-1 space-y-3">
            <!-- Konten diisi lewat JS -->
        </div>

        {{-- Footer Modal --}}
        <div id="schModalActions" class="px-4 py-3 border-t bg-gray-50 shrink-0">
            <!-- Tombol aksi diisi lewat JS -->
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';

    const DATA_URL = @js(route('zoom.data'));
    const state = {
        data: @js($payload),
        offset: 0,          // selisih (ms) jam server - jam perangkat
        hoverRid: null,
        rowSig: null,       // null = belum pernah dirender (string kosong bisa jadi hasil sah)
        listSig: null,
        lastRefresh: Date.now(),
        refreshing: false,
        fallbackFs: false,
        kioskRowSig: null,
    };
    state.offset = state.data.now_ms - Date.now();

    const $ = (sel) => document.querySelector(sel);
    const els = {
        board: $('#schBoard'), rows: $('#schRows'), list: $('#schList'), today: $('#schToday'),
        fsBtn: $('#schFullscreenBtn'), modal: $('#schModal'),
        kiosk: $('#schKiosk'), kioskExit: $('#kioskExit'),
        kioskDate: $('#kioskDate'), kioskClock: $('#kioskClock'),
        kioskLive: $('#kioskLive'), kioskLiveText: $('#kioskLiveText'),
        kioskTotal: $('#kioskTotal'), kioskRows: $('#kioskRows'),
    };

    // ---------------------------------------------------------------- utilitas
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));
    const pad = (n) => String(n).padStart(2, '0');
    const toSec = (hhmm) => { const [h, m] = hhmm.split(':').map(Number); return h * 3600 + m * 60; };
    const range = (a, b) => `${a} - ${b}`;

    // Jam & tanggal selalu mengikuti zona waktu server (Asia/Jakarta), bukan zona perangkat.
    const clockFmt = new Intl.DateTimeFormat('en-CA', {
        timeZone: state.data.timezone, hourCycle: 'h23',
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    });
    function nowParts() {
        const p = Object.fromEntries(
            clockFmt.formatToParts(new Date(Date.now() + state.offset)).map((x) => [x.type, x.value])
        );
        const h = +p.hour, m = +p.minute, s = +p.second;
        return { date: `${p.year}-${p.month}-${p.day}`, h, m, s, sec: h * 3600 + m * 60 + s };
    }

    const isoToDate = (iso) => new Date(`${iso}T12:00:00Z`);
    const fmtLong = (iso) => isoToDate(iso).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC',
    });
    const fmtShort = (iso) => isoToDate(iso).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC',
    });
    const addDays = (iso, n) => {
        const d = isoToDate(iso); d.setUTCDate(d.getUTCDate() + n);
        return d.toISOString().slice(0, 10);
    };

    const AVATARS = [['#dbeafe', '#1d4ed8'], ['#fce7f3', '#be185d'], ['#dcfce7', '#15803d'], ['#fef3c7', '#b45309'], ['#ede9fe', '#6d28d9'], ['#cffafe', '#0e7490']];
    function avatarHtml(name) {
        const words = String(name || '?').trim().split(/\s+/).slice(0, 2);
        const initials = words.map((w) => w.charAt(0)).join('').toUpperCase();
        let hash = 0; for (const ch of String(name)) hash = (hash * 31 + ch.charCodeAt(0)) >>> 0;
        const [bg, fg] = AVATARS[hash % AVATARS.length];
        return `<span class="sch-avatar" style="background:${bg};color:${fg}">${esc(initials)}</span>`;
    }

    // ---------------------------------------------------------------- tabel ruangan
    /** Agenda yang tampil di baris ruangan: yang sedang berjalan, atau — kalau belum ada —
     *  yang paling dekat berikutnya. Begitu sebuah agenda selesai, baris langsung ganti ke agenda berikutnya. */
    function currentIndex(entries, nowSec) {
        return entries.findIndex((e) => toSec(e.end) > nowSec);
    }

    function rowHtml(room, idx, nowSec) {
        const cur = idx >= 0 ? room.entries[idx] : null;
        const roomCell = `<div class="sch-room-name">${esc(room.name)}</div>`;

        if (!cur) {
            return `<div class="sch-row" role="row" data-room="${room.id}" data-rid="">
                <div role="cell">${roomCell}</div>
                <div role="cell"><div class="sch-free">Tersedia</div></div>
                <div role="cell"><div class="sch-time-main">-</div></div>
            </div>`;
        }

        const live = toSec(cur.start) <= nowSec;
        const statusLabel = cur.status === 'menunggu_pembatalan' ? 'Menunggu Pembatalan' : 'Booked';
        const rest = room.entries.slice(idx + 1);
        const nextHtml = rest.length
            ? `<div class="sch-time-next"><small>Selanjutnya</small>${rest.slice(0, 2).map((e) => esc(range(e.start, e.end))).join('<br>')}${rest.length > 2 ? `<br>+${rest.length - 2} lagi` : ''}</div>`
            : '';

        return `<div class="sch-row" role="row" data-room="${room.id}" data-rid="${esc(cur.rid)}">
            <div role="cell">${roomCell}</div>
            <div role="cell">
                <div class="sch-booked">
                    <div class="sch-t"><span>Nama Agenda: ${esc(cur.agenda)}</span>${live ? '<span class="sch-live"><i></i>Berlangsung</span>' : ''}</div>
                    <div class="sch-l sch-l-time">Waktu: ${esc(range(cur.start, cur.end))}</div>
                    <div class="sch-l">Status: ${statusLabel} (${esc(cur.pic)})</div>
                </div>
            </div>
            <div role="cell">
                <div class="sch-time-main">${esc(range(cur.start, cur.end))}</div>
                ${nextHtml}
            </div>
        </div>`;
    }

    /** Agenda saat ini per ruangan, dipakai bersama oleh tabel biasa dan tabel kiosk Full Screen.
     *  Kalau tanggal di perangkat sudah lewat tengah malam tetapi data belum diperbarui, jangan
     *  tampilkan agenda kemarin: kosongkan dulu sampai data baru masuk. */
    function roomPlan(p) {
        const stale = p.date !== state.data.today;
        return state.data.rooms.map((room) => {
            const r = stale ? { ...room, entries: [] } : room;
            const idx = currentIndex(r.entries, p.sec);
            const cur = idx >= 0 ? r.entries[idx] : null;
            const sig = cur
                ? `${room.id}|${cur.rid}|${cur.start}|${cur.status}|${toSec(cur.start) <= p.sec}|${r.entries.length - idx - 1}`
                : `${room.id}|free`;
            return { r, idx, cur, sig };
        });
    }

    function renderRows(p, plan) {
        const sig = plan.map((x) => x.sig).join('#');
        if (sig === state.rowSig) return;          // tidak ada perubahan → jangan sentuh DOM
        state.rowSig = sig;

        els.rows.innerHTML = plan.map((x) => rowHtml(x.r, x.idx, p.sec)).join('');
        applyHighlight();
    }

    // ---------------------------------------------------------------- kiosk (Full Screen)
    function kioskRowHtml(room, cur, live) {
        if (!cur) {
            return `<div class="kiosk-row is-free" role="row">
                <div class="kiosk-room"><i></i>Room ${esc(String(room.id).padStart(2, '0'))}</div>
                <div class="kiosk-agenda">Belum ada agenda</div>
                <div class="kiosk-time">-</div>
            </div>`;
        }
        return `<div class="kiosk-row${live ? ' is-live' : ''}" role="row">
            <div class="kiosk-room"><i></i>Room ${esc(String(room.id).padStart(2, '0'))}</div>
            <div class="kiosk-agenda">${esc(cur.agenda)}${live ? '<span class="kiosk-badge-live">Berlangsung</span>' : ''}</div>
            <div class="kiosk-time">${esc(range(cur.start, cur.end))}</div>
        </div>`;
    }

    function renderKioskRows(p, plan) {
        const sig = plan.map((x) => `${x.sig}|${x.cur && toSec(x.cur.start) <= p.sec}`).join('#');
        if (sig !== state.kioskRowSig) {
            state.kioskRowSig = sig;
            els.kioskRows.innerHTML = plan.map((x) => kioskRowHtml(x.r, x.cur, x.cur && toSec(x.cur.start) <= p.sec)).join('');
        }

        const anyLive = plan.some((x) => x.cur && toSec(x.cur.start) <= p.sec);
        els.kioskLive.classList.toggle('is-live', anyLive);
        els.kioskLive.classList.toggle('is-idle', !anyLive);
        els.kioskLiveText.textContent = anyLive ? 'Sesi Langsung Aktif' : 'Belum Ada Sesi Aktif';

        els.kioskTotal.textContent = `Total: ${state.data.rooms.length} Ruangan`;
    }

    // ---------------------------------------------------------------- panel agenda
    function renderList(p) {
        const tomorrow = addDays(p.date, 1);
        const visible = state.data.agendas
            .filter((a) => a.date > p.date || (a.date === p.date && toSec(a.end) > p.sec))
            .map((a) => {
                let kind = 'soon', label = 'Akan Datang';
                if (a.date === p.date) {
                    const live = toSec(a.start) <= p.sec;
                    kind = live ? 'live' : 'today'; label = live ? 'Berlangsung' : 'Hari Ini';
                } else if (a.date === tomorrow) {
                    label = 'Besok';
                }
                return { a, kind, label };
            });

        const sig = visible.map((v) => `${v.a.rid}|${v.kind}`).join('#');
        if (sig === state.listSig) return;
        state.listSig = sig;

        if (!visible.length) {
            els.list.innerHTML = `<div class="sch-empty">${state.data.is_admin
                ? 'Belum ada agenda hari ini maupun yang akan datang.'
                : 'Anda belum memiliki agenda hari ini maupun yang akan datang.'}</div>`;
            return;
        }

        els.list.innerHTML = visible.map(({ a, kind, label }) => `
            <article class="sch-card" tabindex="0" data-rid="${esc(a.rid)}">
                <div class="sch-card-top">
                    <span class="sch-card-room">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 1 1 13 0c0 5.4-6.5 11-6.5 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.8"/></svg>
                        ${esc(a.room)}
                    </span>
                    <span class="sch-badge ${kind}">${label}</span>
                </div>
                <div class="sch-card-when">${esc(fmtShort(a.date))} &middot; ${esc(range(a.start, a.end))}</div>
                <div class="sch-card-title">${esc(a.agenda)}</div>
                <div class="sch-card-pic">${avatarHtml(a.pic)}<span>${esc(a.pic)}</span></div>
                <div class="sch-card-foot">
                    <span class="sch-card-id">ID: ${esc(a.rid)}</span>
                    <button type="button" class="sch-detail-btn" data-detail="${esc(a.rid)}">Detail</button>
                </div>
            </article>`).join('');
        rehoverFromPointer();
        applyHighlight();
    }

    // ---------------------------------------------------------------- sinkronisasi hover
    /** Kartu agenda yang di-hover → baris ruangannya menyala hijau, tapi HANYA bila agenda itu
     *  memang sedang tampil di tabel. Agenda kedua di ruangan yang sama baru menyala setelah
     *  agenda pertamanya selesai (baris berganti ke agenda kedua). */
    function applyHighlight() {
        const rid = state.hoverRid;
        els.rows.querySelectorAll('.sch-row').forEach((row) => {
            row.classList.toggle('is-lit', !!rid && row.dataset.rid === rid);
        });
        els.list.querySelectorAll('.sch-card').forEach((card) => {
            card.classList.toggle('is-hover', !!rid && card.dataset.rid === rid);
        });
    }
    function setHover(rid) {
        if (state.hoverRid === rid) return;
        state.hoverRid = rid;
        applyHighlight();
    }
    // Posisi kursor terakhir di dalam panel. Dipakai untuk menghitung ulang kartu yang sedang
    // di-hover setelah daftar tersusun ulang (mis. kartu yang sudah selesai hilang dan kartu
    // di bawahnya bergeser naik), tanpa menunggu kursor digerakkan lagi.
    let pointer = null;
    function rehoverFromPointer() {
        if (!pointer) return;
        const under = document.elementFromPoint(pointer.x, pointer.y);
        setHover(under?.closest?.('.sch-card')?.dataset.rid ?? null);
    }
    els.list.addEventListener('mousemove', (e) => { pointer = { x: e.clientX, y: e.clientY }; });
    els.list.addEventListener('mouseover', (e) => setHover(e.target.closest('.sch-card')?.dataset.rid ?? null));
    els.list.addEventListener('mouseleave', () => { pointer = null; setHover(null); });
    els.list.addEventListener('focusin', (e) => setHover(e.target.closest('.sch-card')?.dataset.rid ?? null));
    els.list.addEventListener('focusout', () => setHover(null));

    // ---------------------------------------------------------------- modal detail
    // Gaya kartu & seksi di bawah ini disamakan dengan modal Detail Reservasi
    // pada halaman Semua Pemesanan (Admin).
    const statusLabels = { mendatang: 'Mendatang', menunggu_pembatalan: 'Menunggu Pembatalan' };
    const statusClasses = { mendatang: 'bg-blue-100 text-blue-700', menunggu_pembatalan: 'bg-amber-100 text-amber-700' };

    const escapeHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const row = (label, value) => {
        if (value === null || value === undefined || value === '') return '';
        return `
            <div class="flex justify-between gap-2 py-1 border-b border-gray-100 last:border-0 text-[11px]">
                <span class="text-gray-500 font-medium shrink-0">${label}</span>
                <span class="font-semibold text-gray-800 text-right truncate">${escapeHtml(value)}</span>
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

    function openDetail(rid) {
        const a = state.data.agendas.find((x) => x.rid === rid);
        if (!a) return;

        $('#schModalId').textContent = a.rid;

        const sections = [];

        if (state.data.is_admin) {
            sections.push(section('Informasi Pemesan', [
                row('Akun Pemesan', a.booker),
            ]));
        }

        sections.push(section('Informasi Reservasi', [
            row('Kode', a.rid),
            row('Jenis', 'Breakout Room Zoom'),
            row('Ruangan', a.room),
            row('Tanggal', fmtLong(a.date)),
            row('Waktu', range(a.start, a.end)),
            row('Nama Agenda', a.agenda),
        ]));

        sections.push(section('Penanggung Jawab', [
            row('Nama PIC', a.pic),
            row('No. Telp PIC', a.phone),
            row('Divisi PIC', a.divisi),
        ]));

        sections.push(section('Pemesanan', [
            row('Status', statusLabels[a.status] ?? a.status),
            row('Dibuat', a.created_at),
        ]));

        // Broadcast & Link Zoom — supaya teks undangan + link Zoom bisa
        // disalin ulang langsung dari halaman jadwal ini.
        const broadcastBlock = a.broadcast ? `
            <div class="rounded-lg border border-gray-200 bg-white p-2.5 shadow-xs sm:col-span-2">
                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">Broadcast &amp; Link Zoom</p>
                <div id="schBroadcastText" class="whitespace-pre-wrap rounded-md border border-gray-100 bg-gray-50 p-2 font-mono text-[11px] leading-relaxed text-gray-700 select-all">${escapeHtml(a.broadcast)}</div>
                <button
                    type="button"
                    onclick="copyScheduleBroadcast()"
                    id="schCopyBroadcastBtn"
                    class="mt-2 w-full rounded-lg bg-emerald-600 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                >
                    Salin Broadcast
                </button>
            </div>
        ` : '';

        $('#schModalBody').innerHTML = `
            <div class="mb-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    ${escapeHtml(statusLabels[a.status] ?? a.status)}
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                ${sections.join('')}
                ${broadcastBlock}
            </div>
        `;

        $('#schModalActions').innerHTML = `
            <div class="flex flex-col gap-2">
                <a href="${escapeHtml(a.zoom_url)}" target="_blank" rel="noopener noreferrer"
                   class="w-full block text-center py-2 border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg font-bold text-xs transition-colors">
                    Menuju Link Zoom
                </a>
                <a href="${escapeHtml(a.dashboard_url)}"
                   class="w-full block text-center py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg font-bold text-xs transition-colors shadow-sm">
                    ${escapeHtml(a.dashboard_label)}
                </a>
            </div>
        `;

        els.modal.classList.remove('hidden');
        els.modal.classList.add('flex');
    }
    function closeDetail() {
        els.modal.classList.add('hidden');
        els.modal.classList.remove('flex');
    }
    els.list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-detail]');
        if (btn) openDetail(btn.dataset.detail);
    });
    els.modal.addEventListener('click', (e) => {
        if (e.target === els.modal || e.target.closest('[data-close-modal]')) closeDetail();
    });

    // ------------------------------------------------------------------
    // Salin Broadcast Zoom (dari modal Detail halaman Breakout Room Zoom)
    // ------------------------------------------------------------------
    window.copyScheduleBroadcast = function () {
        const el = document.getElementById('schBroadcastText');
        const btn = document.getElementById('schCopyBroadcastBtn');
        if (!el || !btn) return;

        const text = el.textContent;

        function markCopied() {
            btn.innerText = 'Berhasil Disalin!';
            setTimeout(() => { btn.innerText = 'Salin Broadcast'; }, 2000);
        }

        function fallbackCopy() {
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
                    alert('Gagal menyalin otomatis. Silakan salin teks di atas secara manual.');
                }
            } catch (e) {
                alert('Gagal menyalin otomatis. Silakan salin teks di atas secara manual.');
            }
            document.body.removeChild(textarea);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(markCopied).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    };

    // ---------------------------------------------------------------- full screen (Portal kiosk)
    function syncFsClass() {
        const native = document.fullscreenElement === els.kiosk || document.webkitFullscreenElement === els.kiosk;
        const on = native || state.fallbackFs;
        els.kiosk.classList.toggle('is-fs', on);
        document.body.classList.toggle('sch-noscroll', on);
        if (on) { state.kioskRowSig = null; tick(true); }
    }
    async function enterFullscreen() {
        els.kiosk.classList.add('is-fs');   // tampilkan dulu: requestFullscreen menolak target display:none
        try {
            if (els.kiosk.requestFullscreen) await els.kiosk.requestFullscreen();
            else if (els.kiosk.webkitRequestFullscreen) els.kiosk.webkitRequestFullscreen();
            else state.fallbackFs = true;
        } catch (err) {
            state.fallbackFs = true;        // browser menolak → pakai tampilan layar penuh di dalam halaman
        }
        syncFsClass();
    }
    function exitFullscreen() {
        state.fallbackFs = false;
        if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
        else if (document.webkitFullscreenElement) document.webkitExitFullscreen();
        syncFsClass();
    }
    els.fsBtn.addEventListener('click', enterFullscreen);
    els.kioskExit.addEventListener('click', exitFullscreen);
    document.addEventListener('fullscreenchange', syncFsClass);
    document.addEventListener('webkitfullscreenchange', syncFsClass);
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeDetail();
        if (state.fallbackFs) exitFullscreen();
    });

    // ---------------------------------------------------------------- refresh data & tick
    async function refresh() {
        if (state.refreshing) return;
        state.refreshing = true;
        try {
            const res = await fetch(DATA_URL, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin', cache: 'no-store',
            });
            if (res.redirected) { location.reload(); return; }     // sesi habis → ke halaman login
            if (!res.ok) return;
            const json = await res.json();
            state.data = json;
            state.offset = json.now_ms - Date.now();
            state.rowSig = null; state.listSig = null;
            tick(true);
        } catch (err) {
            /* offline / gangguan sesaat: pertahankan tampilan terakhir, coba lagi di siklus berikutnya */
        } finally {
            state.refreshing = false;
            state.lastRefresh = Date.now();
        }
    }

    let lastClock = '', lastDate = '';
    function tick(force = false) {
        const p = nowParts();

        const clock = `${pad(p.h)}:${pad(p.m)}:${pad(p.s)}`;
        if (force || clock !== lastClock) { els.kioskClock.textContent = clock; lastClock = clock; }

        if (force || p.date !== lastDate) {
            const label = fmtLong(p.date);
            els.today.textContent = label;
            els.kioskDate.textContent = label.toUpperCase();
            lastDate = p.date;
        }

        // Reset harian: lewat tengah malam → ambil jadwal hari baru.
        if (p.date !== state.data.today && Date.now() - state.lastRefresh > 5000) refresh();

        const plan = roomPlan(p);
        renderRows(p, plan);
        renderList(p);
        renderKioskRows(p, plan);
    }

    tick(true);
    setInterval(tick, 1000);
    setInterval(refresh, 60000);        // jaring pengaman kalau koneksi realtime sempat putus
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });

    // ---------------------------------------------------------------- realtime (Reverb)
    // Begitu ada pengguna lain membuat/membatalkan reservasi breakout room Zoom,
    // server menyiarkan event "rooms.updated" lewat WebSocket (lihat app/Events/ZoomRoomsUpdated.php
    // dan routes/channels.php). Di sini kita cukup memicu refresh() yang sudah ada supaya
    // tabel jadwal ter-update seketika, tanpa menunggu siklus polling 60 detik di atas.
    function subscribeRealtime() {
        if (!window.Echo) return;
        window.Echo.private('zoom-schedule').listen('.rooms.updated', () => refresh());
    }

    if (window.Echo) {
        subscribeRealtime();
    } else {
        // app.js (yang menyiapkan window.Echo) dimuat sebagai modul Vite yang di-defer,
        // jadi bisa saja belum siap ketika script inline ini jalan. Tunggu sinyalnya.
        window.addEventListener('echo:ready', subscribeRealtime, { once: true });
    }
})();
</script>
</x-layouts.app>
