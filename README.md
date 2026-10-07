# MeetSC

Aplikasi web untuk **memesan ruang rapat fisik** dan **breakout room Zoom**, membuat **form kehadiran** dengan tautan publik, serta memantau seluruh pemesanan (khusus admin).

Dibangun dengan Laravel 13, Blade, Tailwind CSS 4, dan Laravel Reverb (WebSocket) untuk pembaruan jadwal Zoom secara real time.

> Dokumentasi lengkap (aturan bisnis, route, struktur data, realtime, deployment) ada di [`docs/DOKUMENTASI.md`](docs/DOKUMENTASI.md).

## Fitur

| Fitur | Siapa | Keterangan |
|---|---|---|
| Landing page | Publik | Status tiap ruang rapat (sedang dipakai / tersedia) dan jumlah breakout room Zoom yang kosong |
| Dashboard | Login | Reservasi hari ini, riwayat reservasi + form kehadiran milik sendiri (cari, filter, urut, pagination), ajukan pembatalan |
| Ruang Meeting | Login | Lihat jadwal per tanggal, pesan ruang (termasuk ruang gabungan) |
| Breakout Room Zoom | Login | Tabel jadwal Ruang 1–9 yang ter-update real time, pesan breakout room, salin teks undangan |
| Form Kehadiran | Login | Buat form dengan kolom kustom, bagikan tautan publik, lihat respons, ekspor PDF/Word |
| Semua Pemesanan | Admin | Seluruh reservasi + form dari semua akun, filter, batalkan reservasi |
| Manajemen User | Admin | Tambah/ubah/hapus user, aktif/nonaktifkan, reset kata sandi |

## Kebutuhan

- PHP **8.3+** dengan ekstensi umum Laravel (mbstring, xml, curl, zip, PDO sesuai database)
- Composer
- Node.js + npm
- MySQL (produksi) atau SQLite (pengembangan)

## Instalasi cepat

```bash
git clone <url-repo> meetsc && cd meetsc

composer setup          # install dependensi, buat .env, key:generate, migrate, npm install, npm run build
php artisan db:seed     # akun admin awal + 10 ruang rapat
```

Skrip `composer setup` memakai `.env.example` (default SQLite). Untuk MySQL, ubah `DB_*` di `.env` **sebelum** `migrate`.

> **Keamanan:** seeder membuat akun admin `admin@plnsc.co.id` dengan kata sandi `password`.
> **Segera ganti** kata sandi ini (menu Manajemen User → Reset Password) sebelum aplikasi dipakai di luar mesin lokal.

## Menjalankan (pengembangan)

```bash
composer dev            # menjalankan proses pengembangan (php artisan dev)
```

atau manual, di terminal terpisah:

```bash
php artisan serve               # http://localhost:8000
npm run dev                     # Vite
php artisan reverb:start        # WebSocket untuk jadwal Zoom real time
php artisan storage:link        # sekali saja, agar gambar ruangan tampil
```

Tanpa Reverb, halaman jadwal Zoom tetap berfungsi: ia memperbarui diri tiap 60 detik (polling).

## Menjalankan test

```bash
composer test
```

## Struktur singkat

```
app/
  Http/Controllers/     Dashboard, Landing, RoomReservation, ZoomReservation, Schedule,
                        AttendanceForm, MyReservations, Admin/*, Auth/*
  Http/Middleware/      SyncReservationStatuses (mendatang -> selesai otomatis)
  Models/               User, Room, RoomReservation, ZoomReservation, AttendanceForm, AttendanceResponse
  Support/              TimeSlots, KonsumsiOptions, ReservationDetail, Attendance/AttendanceWordDocument
  Events/               ZoomRoomsUpdated (broadcast Reverb)
resources/views/        Blade (dashboard, reservations, schedule, attendance, admin, landing)
routes/                 web.php, channels.php
database/               migrations, seeders
tests/Feature/          RoomReservationChangesTest, ScheduleTest, EfficiencyTest
```

## Sebelum push ke GitHub

`.gitignore` bawaan hanya mengabaikan `.env`. Tambahkan baris berikut agar file sensitif/lokal tidak ikut ter-commit:

```gitignore
.env.*
!.env.example
/meetsc
/database/*.sqlite
```

Lalu cek dengan `git status` bahwa `.env.realbak`, `.env.realbak2`, dan file database `meetsc` (SQLite) tidak muncul. Jika sudah pernah ter-commit, hapus dari riwayat dan **ganti semua kunci/kata sandi** yang ada di dalamnya.
