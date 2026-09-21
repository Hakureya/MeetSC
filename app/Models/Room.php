<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'floor',
        'capacity_min',
        'capacity_max',
        'is_combined',
        'combined_room_ids',
        'image',
    ];

    protected $casts = [
        'combined_room_ids' => 'array',
        'is_combined' => 'boolean',
    ];

    /**
     * Hanya ruang individu (bukan gabungan). Dipakai di landing page —
     * ruang gabungan (mis. "Ruang Adaptif + Kompeten") tidak ditampilkan di sana.
     */
    public function scopeIndividual(Builder $query): Builder
    {
        return $query->where('is_combined', false);
    }

    public function scopeCombined(Builder $query): Builder
    {
        return $query->where('is_combined', true);
    }

    /**
     * Label kapasitas siap tampil: "6 orang" atau "10 hingga 12 orang".
     */
    public function getCapacityLabelAttribute(): string
    {
        if ($this->capacity_min === $this->capacity_max) {
            return "{$this->capacity_min} orang";
        }

        return "{$this->capacity_min} hingga {$this->capacity_max} orang";
    }

    /**
     * Ruang-ruang dasar yang membentuk ruang gabungan ini (kosong jika bukan ruang gabungan).
     */
    public function baseRooms()
    {
        return static::whereIn('id', $this->combined_room_ids ?? []);
    }

    /**
     * Semua ruang gabungan yang memakai ruang ini sebagai salah satu komponennya.
     * Berguna nanti untuk cek bentrok jadwal: kalau "Ruang Adaptif" dipesan,
     * maka "Ruang Adaptif + Kompeten" otomatis tidak bisa dipesan di jam yang sama, dan sebaliknya.
     */
    public function combinedRoomsContainingThis()
    {
        return static::combined()->get()->filter(
            fn (Room $room) => in_array($this->id, $room->combined_room_ids ?? [])
        );
    }

    public function reservations()
    {
        return $this->hasMany(RoomReservation::class);
    }

    /**
     * ID ruangan ini beserta seluruh ruangan yang "berhubungan" dengannya untuk keperluan
     * pengecekan bentrok jadwal:
     * - Jika ruangan ini ruang gabungan, maka ruang-ruang komponennya ikut disertakan.
     * - Jika ruangan ini ruang individu, maka ruang gabungan yang memuatnya ikut disertakan.
     * Dipakai supaya memesan "Ruang Adaptif" otomatis mengunci "Ruang Adaptif + Kompeten"
     * di jam yang sama, dan sebaliknya.
     */
    public function relatedRoomIdsForConflictCheck(): array
    {
        if ($this->is_combined) {
            return array_merge([$this->id], $this->combined_room_ids ?? []);
        }

        $combinedIds = $this->combinedRoomsContainingThis()->pluck('id')->all();

        return array_merge([$this->id], $combinedIds);
    }

    /**
     * Reservasi ruangan ini untuk tanggal tertentu (default hari ini), diurutkan berdasarkan jam mulai.
     */
    public function reservationsForDate(?string $date = null)
    {
        return $this->reservations()
            ->whereDate('tanggal', $date ?? now()->toDateString())
            ->whereIn('status', ['mendatang', 'menunggu_pembatalan'])
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Apakah ruangan sedang dipakai pada jam saat ini (untuk hari ini).
     */
    public function isInUseNow(): bool
    {
        $now = now()->format('H:i:s');

        return $this->reservationsForDate()
            ->contains(fn ($r) => $r->jam_mulai <= $now && $r->jam_selesai > $now);
    }
}