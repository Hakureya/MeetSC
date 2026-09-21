<?php

namespace App\Support;

/**
 * Definisi terpusat 15 slot waktu yang dipakai seluruh form reservasi
 * (Ruang Meeting maupun Zoom), supaya sisi server dan sisi klien selalu sinkron.
 *
 * Seluruh slot dapat dipilih bebas, termasuk rentang yang melewati jam makan siang.
 * Pemesanan boleh hanya 1 slot (30 menit) maupun rentang beberapa slot sekaligus.
 */
class TimeSlots
{
    /**
     * Daftar slot: label tampilan + jam mulai/selesai yang disimpan ke database.
     *
     * @return array<int, array{label: string, start: string, end: string}>
     */
    public static function all(): array
    {
        return [
            ['label' => '08.00 – 08.30', 'start' => '08:00', 'end' => '08:30'],
            ['label' => '08.30 – 09.00', 'start' => '08:30', 'end' => '09:00'],
            ['label' => '09.00 – 09.30', 'start' => '09:00', 'end' => '09:30'],
            ['label' => '09.30 – 10.00', 'start' => '09:30', 'end' => '10:00'],
            ['label' => '10.00 – 10.30', 'start' => '10:00', 'end' => '10:30'],
            ['label' => '10.30 – 11.00', 'start' => '10:30', 'end' => '11:00'],
            ['label' => '11.00 – 11.30', 'start' => '11:00', 'end' => '11:30'],
            ['label' => '11.30 – 12.00', 'start' => '11:30', 'end' => '12:00'],
            ['label' => '13.00 – 13.30', 'start' => '13:00', 'end' => '13:30'],
            ['label' => '13.30 – 14.00', 'start' => '13:30', 'end' => '14:00'],
            ['label' => '14.00 – 14.30', 'start' => '14:00', 'end' => '14:30'],
            ['label' => '14.30 – 15.00', 'start' => '14:30', 'end' => '15:00'],
            ['label' => '15.00 – 15.30', 'start' => '15:00', 'end' => '15:30'],
            ['label' => '15.30 – 16.00', 'start' => '15:30', 'end' => '16:00'],
            ['label' => '16.00 – 16.30', 'start' => '16:00', 'end' => '16:30'],
        ];
    }

    /**
     * Seluruh jam mulai yang sah.
     *
     * @return array<int, string>
     */
    public static function startTimes(): array
    {
        return array_column(static::all(), 'start');
    }

    /**
     * Seluruh jam selesai yang sah.
     *
     * @return array<int, string>
     */
    public static function endTimes(): array
    {
        return array_column(static::all(), 'end');
    }

    /**
     * Validasi rentang jam yang dikirim dari form.
     * Mengembalikan pesan error, atau null bila rentangnya sah.
     */
    public static function validateRange(string $jamMulai, string $jamSelesai): ?string
    {
        $startIndex = array_search($jamMulai, static::startTimes(), true);
        $endIndex = array_search($jamSelesai, static::endTimes(), true);

        if ($startIndex === false || $endIndex === false) {
            return 'Jam yang dipilih tidak valid. Silakan pilih ulang slot waktu.';
        }

        if ($endIndex < $startIndex) {
            return 'Jam selesai tidak boleh lebih awal dari jam mulai.';
        }

        // Minimal 1 slot: satu slot waktu (30 menit) sudah dianggap sah.
        if (($endIndex - $startIndex + 1) < 1) {
            return 'Pilih minimal 1 slot waktu.';
        }

        return null;
    }
}