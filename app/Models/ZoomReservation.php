<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomReservation extends Model
{
    use HasFactory;

    /** Link Zoom tetap yang dipakai semua breakout room (dipakai halaman sukses & Dashboard). */
    public const MEETING_URL = 'https://zoom.us/j/5639933613?pwd=ck1LVi9vQ2owcmRKUGdGNW5Gb0VMZz09';

    /** @deprecated Alias lama untuk MEETING_URL, dipakai fitur broadcast di Dashboard. */
    public const ZOOM_LINK = self::MEETING_URL;

    /** Jumlah breakout room virtual (Ruang 1 – Ruang 9). */
    public const ROOM_COUNT = 9;

    protected $fillable = [
        'user_id',
        'nama_agenda',
        'nama_pic',
        'no_telp_pic',
        'divisi_pic',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'room_number',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getJamRangeAttribute(): string
    {
        return substr($this->jam_mulai, 0, 5).' – '.substr($this->jam_selesai, 0, 5);
    }

    /**
     * Teks broadcast/undangan rapat siap salin untuk reservasi ini.
     * Formatnya disamakan dengan yang tampil di halaman sukses reservasi Zoom,
     * supaya teks yang disalin dari Dashboard, Breakout Room Zoom, maupun
     * Semua Pemesanan selalu konsisten.
     */
    public function getBroadcastTextAttribute(): string
    {
        $hariTanggal = $this->tanggal->locale('id')->isoFormat('dddd, D MMMM Y');
        $link = self::MEETING_URL;

        return <<<TEXT
Dengan Hormat,

Sehubung dengan adanya {$this->nama_agenda}. Bersama ini kami sampaikan undangan pada,

Hari, tanggal : {$hariTanggal}
Jam  : {$this->jam_range}
Join Zoom Meeting
Meeting ID : 563 993 3613
Room : Ruang {$this->room_number}
Pass : AKHLAK
Link :
{$link}

Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih
TEXT;
    }

    /**
     * Tandai otomatis reservasi "mendatang" yang jam selesainya sudah lewat menjadi "selesai".
     * Dipanggil di awal controller yang menampilkan data reservasi, supaya statusnya selalu
     * mutakhir setiap kali halaman dibuka — tanpa perlu scheduler/cron terpisah.
     */
    public static function syncExpiredStatuses(): void
    {
        $now = now();

        static::where('status', 'mendatang')
            ->where(function ($query) use ($now) {
                $query->whereDate('tanggal', '<', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('tanggal', $now->toDateString())
                          ->where('jam_selesai', '<=', $now->format('H:i:s'));
                    });
            })
            ->update(['status' => 'selesai']);
    }
}