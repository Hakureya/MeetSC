<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomReservation extends Model
{
    use HasFactory;

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