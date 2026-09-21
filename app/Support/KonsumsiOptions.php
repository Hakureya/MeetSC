<?php

namespace App\Support;

/**
 * Definisi terpusat pilihan konsumsi untuk pemesanan ruang rapat,
 * supaya form (sisi klien) dan validasi (sisi server) selalu sinkron.
 *
 * Opsi terakhir, "Other", tidak memiliki teks baku: pemesan mengisinya sendiri
 * dan nilai yang tersimpan di database adalah teks yang mereka ketik.
 */
class KonsumsiOptions
{
    /**
     * Nilai khusus yang dikirim form ketika pemesan memilih opsi isian manual.
     */
    public const OTHER = 'Other';

    /**
     * Pilihan baku yang tampil sebagai radio button (belum termasuk "Other").
     *
     * @return array<int, string>
     */
    public static function preset(): array
    {
        return [
            'Snack',
            'Makan Siang',
            'Makan Malam',
            'Snack dan Makan Siang',
            'Tanpa Konsumsi',
            'Disiapkan PLN NP/PLN IP/Eksternal',
        ];
    }

    /**
     * Seluruh nilai yang sah dikirim pada field `konsumsi`,
     * yaitu pilihan baku ditambah penanda "Other".
     *
     * Nilai lama "Disiapkan PLN NP/IP/AP" tetap diterima agar data
     * reservasi yang sudah ada sebelumnya tidak menjadi tidak valid.
     *
     * @return array<int, string>
     */
    public static function acceptedValues(): array
    {
        return array_merge(static::preset(), [
            'Disiapkan PLN NP/IP/AP',
            static::OTHER,
        ]);
    }

    /**
     * Menentukan nilai konsumsi final yang disimpan ke database:
     * teks manual bila pemesan memilih "Other", selain itu pilihan bakunya.
     */
    public static function resolve(string $konsumsi, ?string $konsumsiLainnya): string
    {
        if ($konsumsi !== static::OTHER) {
            return $konsumsi;
        }

        return trim((string) $konsumsiLainnya);
    }
}
