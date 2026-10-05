# Dokumentasi MeetSC

Dokumen ini menjelaskan cara kerja aplikasi: peran pengguna, aturan bisnis, route, struktur data, fitur realtime, konfigurasi, deployment, dan hal yang perlu diwaspadai. Untuk instalasi cepat lihat [README](../README.md).

## Daftar isi

1. [Gambaran umum](#1-gambaran-umum)
2. [Peran dan akses](#2-peran-dan-akses)
3. [Ruang rapat](#3-ruang-rapat)
4. [Breakout room Zoom](#4-breakout-room-zoom)
5. [Status reservasi dan pembatalan](#5-status-reservasi-dan-pembatalan)
6. [Form kehadiran](#6-form-kehadiran)
7. [Realtime (Reverb)](#7-realtime-reverb)
8. [Daftar route](#8-daftar-route)
9. [Struktur data](#9-struktur-data)
10. [Konfigurasi](#10-konfigurasi)
11. [Deployment](#11-deployment)
12. [Pengujian](#12-pengujian)
13. [Catatan performa](#13-catatan-performa)
14. [Keterbatasan dan hal yang perlu diperhatikan](#14-keterbatasan-dan-hal-yang-perlu-diperhatikan)

---

## 1. Gambaran umum

| Aspek | Detail |
|---|---|
| Framework | Laravel `^13.17`, PHP `^8.3` |
| Tampilan | Blade, Tailwind CSS 4, Alpine.js, Vite |
| Realtime | Laravel Reverb + Laravel Echo + pusher-js |
| Ekspor | `barryvdh/laravel-dompdf` (PDF), `phpoffice/phpword` (Word) |
| Database | MySQL (produksi), SQLite (default `.env.example` dan test) |
| Session / cache / queue | Database (default `.env.example`) |
| Bahasa antarmuka | Indonesia |

Aplikasi **tidak membutuhkan scheduler/cron maupun queue worker**: perubahan status otomatis dijalankan oleh middleware (lihat [bagian 5](#5-status-reservasi-dan-pembatalan)) dan event broadcast memakai `ShouldBroadcastNow`.

## 2. Peran dan akses

Setiap user punya `role` (`admin` / `user`) dan `status` (`aktif` / `nonaktif`).

| Halaman | Tamu | User | Admin |
|---|:-:|:-:|:-:|
| Landing (`/`) | ✔ | ✔ | ✔ |
| Isi form kehadiran publik (`/hadir/{uuid}`) | ✔ | ✔ | ✔ |
| Dashboard, Ruang Meeting, Breakout Room Zoom, Form Kehadiran | – | ✔ | ✔ |
| Semua Pemesanan, Manajemen User | – | – | ✔ |

- **Login** memakai email + kata sandi. Akun `nonaktif` ditolak dengan pesan "Akun Anda telah dinonaktifkan". Waktu login terakhir disimpan di `last_login_at`.
- Hak admin dicek lewat Gate `admin` (tidak peka huruf besar/kecil pada kolom `role`), dipakai sebagai middleware `can:admin`.
- Admin **tidak bisa** menonaktifkan atau menghapus akunnya sendiri.
- Form kehadiran: user hanya melihat/ekspor miliknya sendiri; admin bisa semuanya.

## 3. Ruang rapat

### Ruang tersedia (dari seeder)

| Lantai | Ruang |
|---|---|
| 1 | Solution, Chain, Supply |
| 2 | Adaptif, Kompeten, Amanah |
| 3 | Prima, Layanan |
| Gabungan | **Adaptif + Kompeten** (lantai 2), **Prima + Layanan** (lantai 3) |

Ruang gabungan tidak tampil di landing page, tetapi tampil di halaman pemesanan.

### Slot waktu

Didefinisikan di `App\Support\TimeSlots`: **15 slot @30 menit, 08.00–16.30**, tanpa slot 12.00–13.00 (istirahat). Pemesanan boleh 1 slot atau rentang beberapa slot. Server memvalidasi jam mulai/selesai terhadap daftar slot yang sama dengan yang dipakai form di sisi klien.

### Aturan pemesanan

- Tanggal tidak boleh sebelum hari ini.
- Wajib: keperluan, nama PIC, no. telp PIC, divisi PIC, jumlah peserta, konsumsi.
- **Cek bentrok** memakai aturan overlap yang benar (`mulai < selesai_lain` dan `selesai > mulai_lain`), sehingga 08.00–09.00 dan 09.00–11.00 boleh dipesan dua orang berbeda.
- Bentrok bersifat dua arah untuk ruang gabungan: memesan *Ruang Adaptif* mengunci *Adaptif + Kompeten* di jam yang sama, dan sebaliknya.
- Memesan ruang gabungan otomatis membuat **reservasi turunan** (`auto_generated = true`, `parent_reservation_id` terisi) di tiap ruang komponen. Reservasi turunan **tidak ditampilkan** di daftar/riwayat supaya satu pemesanan tidak terhitung dua kali, dan ikut berubah status saat induknya diajukan pembatalan atau dibatalkan.

### Pilihan konsumsi

Dari `App\Support\KonsumsiOptions`: Snack · Makan Siang · Makan Malam · Snack dan Makan Siang · Tanpa Konsumsi · Disiapkan PLN NP/PLN IP/Eksternal · **Other** (teks bebas, wajib diisi bila dipilih; yang tersimpan adalah teks yang diketik). Nilai lama "Disiapkan PLN NP/IP/AP" tetap diterima agar data lama valid.

### Indikator "sedang dipakai"

Sebuah ruang ditandai sedang dipakai hanya bila **tanggal yang dilihat adalah hari ini** dan jam sekarang berada di dalam salah satu jadwalnya (`jam_mulai ≤ sekarang < jam_selesai`), bukan sekadar "ada jadwal hari ini".

## 4. Breakout room Zoom

- Ada **9 breakout room virtual** (Ruang 1–9) yang memakai **satu tautan Zoom tetap**.
- Menu *Breakout Room Zoom* (`/link-zoom`) menampilkan tabel jadwal hari ini per ruang plus panel agenda: **admin** melihat agenda semua akun, **user** hanya agenda miliknya. Tombol "Buat Reservasi" mengarah ke `/link-zoom/buat`.
- Aturan pemesanan sama dengan ruang rapat (slot waktu, cek overlap), per `room_number` 1–9.
- Setiap reservasi menghasilkan **teks undangan siap salin** (`ZoomReservation::broadcast_text`): hari/tanggal, jam, Meeting ID, nomor ruang, kata sandi, dan tautan. Format yang sama dipakai di halaman sukses, Dashboard, jadwal, dan Semua Pemesanan.
- Tautan, Meeting ID, dan kata sandi Zoom **ditulis langsung di kode** (`ZoomReservation::MEETING_URL` dan template `broadcast_text`). Untuk menggantinya, ubah di `app/Models/ZoomReservation.php`.

## 5. Status reservasi dan pembatalan

| Status | Arti |
|---|---|
| `mendatang` | Aktif, belum lewat |
| `menunggu_pembatalan` | User sudah mengajukan pembatalan; ruangan masih terkunci |
| `dibatalkan` | Dibatalkan admin; ruangan bebas lagi |
| `selesai` | Jam selesai sudah lewat |

Alur pembatalan:

1. **User** menekan "Ajukan Pembatalan" di Dashboard (hanya untuk status `mendatang`) → status menjadi `menunggu_pembatalan`.
2. **Admin** menekan "Batalkan Reservasi" di Semua Pemesanan (untuk status selain `selesai`/`dibatalkan`) → status menjadi `dibatalkan`.
3. Reservasi `selesai` tidak dapat dibatalkan.

Form kehadiran tidak punya kolom status: aktif = tampil sebagai `mendatang`, kedaluwarsa = `selesai`.

### Perubahan otomatis `mendatang` → `selesai`

Karena tidak ada cron, middleware `SyncReservationStatuses` (terdaftar di grup `web`) menandai reservasi yang jam selesainya sudah lewat. Pengecekan dibatasi **paling sering sekali per 60 detik** (kunci cache `reservation-status-sync`), sehingga status bisa terlambat hingga sekitar satu menit. Ubah konstanta `INTERVAL` di middleware bila perlu.

## 6. Form kehadiran

1. Pembuat mengisi judul, PIC, divisi PIC, tempat, rapat/pertemuan, **waktu kedaluwarsa**, dan satu atau lebih kolom (`text`, `number`, `email`, `date`, `textarea`; masing-masing bisa wajib).
2. Sistem membuat UUID dan tautan publik `/hadir/{uuid}` (tanpa login).
3. Peserta mengisi form. Validasi dibangun dinamis dari definisi kolom. Jawaban disimpan sebagai JSON bersama alamat IP. Form yang sudah kedaluwarsa menolak isian.
4. Pembuat/admin melihat respons di `/form-kehadiran/{id}/respons`.
5. Ekspor:
   - **PDF** (dompdf) — tata letak mengikuti `attendance/pdf.blade.php`, dibatasi agar muat satu halaman A4.
   - **Word** (`.docx`, PhpWord) — memuat **seluruh** peserta; tata letak diatur di `App\Support\Attendance\AttendanceWordDocument`.

   Bila package ekspor tidak terpasang, pengguna melihat pesan agar menjalankan `composer require` untuk package terkait.

## 7. Realtime (Reverb)

```
Reservasi Zoom dibuat / diajukan batal / dibatalkan admin
        │  broadcast(new ZoomRoomsUpdated)         (ShouldBroadcastNow, tanpa queue)
        ▼
Reverb ── private channel "zoom-schedule" ── event ".rooms.updated"
        ▼
Browser yang membuka /link-zoom  →  fetch GET /link-zoom/data  →  render ulang tabel
```

- Payload event sengaja minimal (id reservasi + tanggal). Browser memanggil ulang `/link-zoom/data`, yang sudah menyaring data sesuai hak akses (admin vs user), sehingga sumber kebenaran tetap di server.
- Channel `zoom-schedule` hanya mensyaratkan user sudah login.
- **Cadangan polling:** halaman juga memuat ulang data tiap 60 detik (dilewati saat tab tersembunyi) dan saat tab kembali terlihat. Jadi bila Reverb mati, jadwal tetap ter-update, hanya tidak instan.
- Variabel `.env` terkait: `BROADCAST_CONNECTION=reverb`, `REVERB_*`, `VITE_REVERB_*`. Jalankan server dengan `php artisan reverb:start`.

## 8. Daftar route

Semua route di `routes/web.php`.

**Publik**

| Method | URL | Nama | Fungsi |
|---|---|---|---|
| GET | `/` | `landing` | Landing page |
| GET / POST | `/hadir/{uuid}` | `attendance.public` / `attendance.submit` | Isi form kehadiran |
| GET / POST | `/login` | `login` | Login (hanya tamu) |

**Login (user & admin)**

| Method | URL | Nama | Fungsi |
|---|---|---|---|
| POST | `/logout` | `logout` | Keluar |
| GET | `/dashboard` | `dashboard` | Dashboard |
| GET | `/reservasi-saya` | `my-reservations.index` | Daftar reservasi milik sendiri |
| POST | `/reservasi/request-batal` | `reservations.request-cancel` | Ajukan pembatalan |
| GET | `/ruang-meeting` | `rooms.index` | Jadwal ruang per tanggal |
| GET | `/ruang-meeting/pesan/{room}` | `rooms.create` | Form pesan ruang |
| POST | `/ruang-meeting` | `rooms.store` | Simpan reservasi ruang |
| GET | `/ruang-meeting/sukses/{id}` | `rooms.success` | Halaman sukses |
| GET | `/link-zoom` | `zoom.index` | Jadwal breakout room |
| GET | `/link-zoom/data` | `zoom.data` | Data JSON jadwal (polling/realtime) |
| GET | `/link-zoom/buat` | `zoom.create` | Form pesan breakout room |
| GET | `/link-zoom/ketersediaan?tanggal=` | `zoom.availability` | JSON ketersediaan per tanggal |
| POST | `/link-zoom` | `zoom.store` | Simpan reservasi Zoom |
| GET | `/link-zoom/sukses/{id}` | `zoom.success` | Halaman sukses |
| GET/POST | `/form-kehadiran` | `attendance.index` / `.store` | Daftar & buat form |
| GET | `/form-kehadiran/{id}/respons` | `attendance.show` | Lihat respons |
| GET | `/form-kehadiran/{id}/export-pdf` · `/export-word` | `attendance.export-*` | Ekspor |

**Khusus admin** (`can:admin`)

| Method | URL | Nama | Fungsi |
|---|---|---|---|
| GET | `/semua-pemesanan` | `reservations.index` | Semua reservasi + form |
| POST | `/semua-pemesanan/batalkan/{jenis}/{id}` | `reservations.cancel` | Batalkan (`jenis` = `Ruang` atau `Zoom`) |
| GET/POST | `/manajemen-user` | `users.index` / `.store` | Daftar & tambah user |
| GET | `/manajemen-user/{id}/detail` | `users.show` | Detail JSON |
| PUT | `/manajemen-user/{id}` | `users.update` | Ubah user |
| POST | `/manajemen-user/{id}/toggle-status` | `users.toggle` | Aktif/nonaktif |
| POST | `/manajemen-user/{id}/reset-password` | `users.reset` | Reset kata sandi |
| DELETE | `/manajemen-user/{id}` | `users.destroy` | Hapus |

Filter Dashboard dan Semua Pemesanan (query string): `search`, `jenis`, `status`, `tanggal_mulai`, `tanggal_akhir`, `sort` (`terbaru` · `terlama` · `tanggal_asc` · `tanggal_desc`), `page`. Nilai `jenis` di Dashboard: `room` / `zoom` / `form`; di Semua Pemesanan: `Ruang` / `Zoom` / `Form`.

## 9. Struktur data

```
users 1──* room_reservations *──1 rooms
  │  1──* zoom_reservations
  │  1──* attendance_forms 1──* attendance_responses
room_reservations 1──* room_reservations   (parent_reservation_id → reservasi turunan)
```

| Tabel | Kolom penting |
|---|---|
| `users` | `name`, `email`, `password`, `nip`, `divisi`, `jabatan`, `role` (admin/user), `status` (aktif/nonaktif), `last_login_at` |
| `rooms` | `name`, `floor`, `capacity_min`, `capacity_max`, `is_combined`, `combined_room_ids` (JSON id ruang penyusun), `image` |
| `room_reservations` | `room_id`, `user_id`, `tanggal`, `jam_mulai`, `jam_selesai`, `keperluan`, `nama_pic`, `no_telp_pic`, `divisi_pic`, `jumlah_peserta`, `konsumsi` (string), `status`, `parent_reservation_id`, `auto_generated` |
| `zoom_reservations` | `user_id`, `nama_agenda`, `nama_pic`, `no_telp_pic`, `divisi_pic`, `tanggal`, `jam_mulai`, `jam_selesai`, `room_number` (1–9), `status` |
| `attendance_forms` | `user_id`, `uuid` (unik), `judul`, `pic`, `divisi_pic`, `tempat`, `rapat_pertemuan`, `fields` (JSON), `expires_at` |
| `attendance_responses` | `attendance_form_id`, `answers` (JSON), `ip_address` |

Tabel `jobs`, `cache`, dan `sessions` dipakai oleh driver database bawaan Laravel.

## 10. Konfigurasi

Salin `.env.example` menjadi `.env` lalu sesuaikan. Yang terpenting:

| Variabel | Keterangan |
|---|---|
| `APP_KEY` | Dibuat oleh `php artisan key:generate` |
| `APP_URL`, `APP_ENV`, `APP_DEBUG` | Produksi: `APP_ENV=production`, **`APP_DEBUG=false`** |
| `DB_CONNECTION` + `DB_*` | `sqlite` (dev) atau `mysql` (produksi) |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Default `database` |
| `BROADCAST_CONNECTION` | `reverb` untuk realtime |
| `REVERB_APP_ID/KEY/SECRET`, `REVERB_HOST/PORT/SCHEME` | **Ganti nilai contoh** (`meetscreverbkey`, `meetscreverbsecret`) di produksi |
| `VITE_REVERB_*` | Diteruskan ke frontend saat `npm run build` |
| Zona waktu | Di-hardcode `Asia/Jakarta` di `config/app.php` (bukan dari `.env`); menentukan "hari ini" dan "jam sekarang" untuk status/indikator ruang |
| `APP_LOCALE` | Default kode `id`; teks tanggal (mis. nama bulan) memakai locale ini. `.env.example` mengisi `en`, jadi set `APP_LOCALE=id` agar nama bulan/hari berbahasa Indonesia |

## 11. Deployment

1. Pastikan server memakai PHP 8.3+, dan **filesystem peka huruf besar/kecil (Linux)** — nama file harus sama persis dengan nama class (mis. `TimeSlots.php`).
2. `composer install --no-dev --optimize-autoloader`
3. Siapkan `.env` produksi, lalu `php artisan key:generate` (sekali).
4. `php artisan migrate --force`, lalu `php artisan db:seed` **hanya pada instalasi pertama**, dan segera ganti kata sandi admin.
5. `npm ci && npm run build`
6. `php artisan storage:link` (gambar ruangan dan ikon dimuat dari `public/storage`).
7. Jalankan Reverb sebagai service (mis. Supervisor): `php artisan reverb:start`, dan arahkan reverse proxy (WebSocket) ke port `REVERB_SERVER_PORT`.
8. Cache: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
9. Document root web server → folder `public/`.

## 12. Pengujian

```bash
composer test         # atau: php artisan test
```

| File | Cakupan |
|---|---|
| `RoomReservationChangesTest` | Validasi slot, divisi PIC, konsumsi "Other", reservasi turunan ruang gabungan, tampilan kapasitas |
| `ScheduleTest` | Halaman jadwal Zoom, hak akses admin vs user, pemfilteran agenda, tautan detail |
| `EfficiencyTest` | Throttle sinkronisasi status, jumlah query landing/dashboard tidak bertambah seiring data |

Test memakai SQLite in-memory (`phpunit.xml`).

**Kondisi saat dokumen ini ditulis:** dua test lama masih gagal dan tidak terkait fitur —
`ExampleTest` (memanggil `/` tanpa menyiapkan database) dan `ScheduleTest::test_zoom_menu_now_shows_the_room_schedule` (mencari teks "Reset Harian Aktif" yang sudah tidak ada di view). Perbaiki dengan mengaktifkan `RefreshDatabase` di `ExampleTest` dan memperbarui ekspektasi teks pada test kedua.

## 13. Catatan performa

- Middleware sinkronisasi status dibatasi sekali per 60 detik (sebelumnya 2 query UPDATE di setiap request).
- Dashboard dan Semua Pemesanan melakukan filter/urut/pagination di PHP pada baris ringan, dan hanya membangun data detail untuk 10 baris yang tampil. Cukup untuk ribuan baris; bila data mencapai puluhan ribu, pindahkan filter dan pagination ke SQL (query `UNION` ketiga tabel).
- Landing page memakai satu query untuk semua ruang.
- Tidak ada index pada `status`/`tanggal` di tabel reservasi. Pertimbangkan index `(status, tanggal)` pada `room_reservations` dan `zoom_reservations` seiring bertambahnya data.

## 14. Keterbatasan dan hal yang perlu diperhatikan

- **Status `menunggu_pembatalan` untuk Zoom** ditambahkan lewat migrasi yang hanya berjalan di **MySQL** (`ALTER TABLE ... MODIFY`). Di SQLite status ini ditolak oleh constraint, jadi lingkungan SQLite tidak bisa menyimpannya.
- **Kredensial Zoom di kode** (tautan, Meeting ID, kata sandi) — lihat [bagian 4](#4-breakout-room-zoom).
- **Akun admin seeder** berkata sandi `password` — ganti segera.
- **Berkas lokal sensitif:** `.env.realbak`, `.env.realbak2`, dan file SQLite `meetsc` di root proyek tidak diabaikan oleh `.gitignore` bawaan. Jangan di-commit (lihat README).
- Tidak ada pembatasan percobaan login (rate limiting) khusus; pertimbangkan menambahkannya bila aplikasi diakses dari internet.
- Form kehadiran publik menyimpan alamat IP peserta; pertimbangkan pemberitahuan privasi bila diperlukan.
