<?php

namespace App\Models;

use App\Models\Concerns\SyncsExpiredStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomReservation extends Model
{
    use HasFactory, SyncsExpiredStatuses;

    protected $fillable = [
        'room_id',
        'parent_reservation_id',
        'auto_generated',
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'keperluan',
        'nama_pic',
        'no_telp_pic',
        'divisi_pic',
        'jumlah_peserta',
        'konsumsi',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'auto_generated' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Reservasi induk (jika baris ini adalah reservasi turunan otomatis dari ruang gabungan).
     */
    public function parentReservation()
    {
        return $this->belongsTo(RoomReservation::class, 'parent_reservation_id');
    }

    /**
     * Reservasi turunan pada ruang-ruang komponen (jika baris ini adalah reservasi ruang gabungan).
     */
    public function childReservations()
    {
        return $this->hasMany(RoomReservation::class, 'parent_reservation_id');
    }

    public function getJamRangeAttribute(): string
    {
        return substr($this->jam_mulai, 0, 5).' – '.substr($this->jam_selesai, 0, 5);
    }
}