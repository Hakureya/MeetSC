<?php

namespace App\Support\Attendance;

use App\Models\AttendanceForm;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Membangun dokumen Word (.docx) untuk daftar hadir rapat.
 *
 * File ini adalah padanan Word dari `resources/views/attendance/pdf.blade.php`:
 * PDF dirender lewat Blade (HTML+CSS), sedangkan Word dibangun lewat PhpWord
 * (library ini tidak bisa membaca file Blade/HTML), jadi tata letak dan warnanya
 * ditulis ulang secara manual di sini agar hasilnya konsisten dengan versi PDF.
 * Controller cukup memanggil AttendanceWordDocument::build($form, $responses)
 * lalu menyimpan/mengunduh hasilnya, tanpa perlu tahu detail penyusunan tabelnya.
 */
class AttendanceWordDocument
{
    /**
     * Warna latar judul, disamakan dengan .title-cell pada pdf.blade.php (#b7d7f0).
     */
    private const COLOR_TITLE_BG = 'B7D7F0';

    /**
     * Total lebar tabel dalam twips (satuan PhpWord), dipakai untuk membagi
     * lebar kolom secara proporsional seperti lebar dalam persen pada CSS PDF.
     */
    private const TABLE_WIDTH = 15700;

    /** Padanan .no-column { width: 10%; } */
    private const NO_COLUMN_RATIO = 0.10;

    /** Padanan .time-column { width: 15%; } */
    private const TIME_COLUMN_RATIO = 0.15;

    public static function build(AttendanceForm $form, Collection $responses): PhpWord
    {
        $phpWord = new PhpWord();

        // body { font-family: sans-serif; font-size: 11px; } pada versi PDF.
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'marginTop' => 600,
            'marginBottom' => 600,
            'marginLeft' => 600,
            'marginRight' => 600,
        ]);

        static::addMetaTable($phpWord, $section, $form);
        $section->addTextBreak(1);
        static::addDataTable($phpWord, $section, $form, $responses);

        return $phpWord;
    }

    /**
     * Tabel judul + informasi rapat. Padanan dari <table class="meta-table"> pada pdf.blade.php.
     */
    private static function addMetaTable(PhpWord $phpWord, $section, AttendanceForm $form): void
    {
        // td, th { border: 1px solid #000; } pada versi PDF.
        $borderStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ];

        static::registerTableStyleOnce($phpWord, 'metaTable', $borderStyle);
        $table = $section->addTable('metaTable');

        // Baris judul: .title-cell (bg #b7d7f0, size 16px, bold, center), digabung 6 kolom.
        $table->addRow();
        $table->addCell(self::TABLE_WIDTH, ['gridSpan' => 6, 'bgColor' => self::COLOR_TITLE_BG])
            ->addText('DAFTAR HADIR RAPAT', ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);

        $labelStyle = ['bold' => true];

        // Baris: Pimpinan Rapat + Hari/Tanggal (proporsi lebar mengikuti 15% / 33% / 15% / 37%).
        $table->addRow();
        $table->addCell((int) round(self::TABLE_WIDTH * 0.15))->addText('Pimpinan Rapat:', $labelStyle);
        $table->addCell((int) round(self::TABLE_WIDTH * 0.33))->addText($form->pic ?: '-');
        $table->addCell((int) round(self::TABLE_WIDTH * 0.15))->addText('Hari/Tanggal:', $labelStyle);
        $table->addCell((int) round(self::TABLE_WIDTH * 0.37))->addText(
            $form->created_at ? $form->created_at->format('d/m/Y') : now()->format('d/m/Y')
        );

        // Baris: Tempat + Waktu.
        $table->addRow();
        $table->addCell((int) round(self::TABLE_WIDTH * 0.15))->addText('Tempat:', $labelStyle);
        $table->addCell((int) round(self::TABLE_WIDTH * 0.33))->addText($form->tempat ?: '-');
        $table->addCell((int) round(self::TABLE_WIDTH * 0.15))->addText('Waktu:', $labelStyle);
        $table->addCell((int) round(self::TABLE_WIDTH * 0.37))->addText(
            $form->created_at ? $form->created_at->format('H:i') : now()->format('H:i')
        );

        // Baris: Rapat/Pertemuan, digabung menutupi sisa lebar seperti colspan="5" pada PDF.
        $table->addRow();
        $table->addCell((int) round(self::TABLE_WIDTH * 0.15))->addText('Rapat/Pertemuan:', $labelStyle);
        $table->addCell((int) round(self::TABLE_WIDTH * 0.85), ['gridSpan' => 5])
            ->addText($form->rapat_pertemuan ?: '-');
    }

    /**
     * Tabel daftar hadir peserta. Padanan dari <table class="data-table"> pada pdf.blade.php.
     *
     * Berbeda dengan export PDF (dibatasi 10 baris agar muat satu halaman A4),
     * export Word menampilkan SELURUH peserta karena dokumen Word otomatis
     * mengalir ke halaman berikutnya.
     */
    private static function addDataTable(PhpWord $phpWord, $section, AttendanceForm $form, Collection $responses): void
    {
        $borderStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ];

        static::registerTableStyleOnce($phpWord, 'dataTable', $borderStyle);
        $table = $section->addTable('dataTable');

        $fields = $form->fields;
        $fieldCount = max(count($fields), 1);

        $noColWidth = (int) round(self::TABLE_WIDTH * self::NO_COLUMN_RATIO);
        $timeColWidth = (int) round(self::TABLE_WIDTH * self::TIME_COLUMN_RATIO);
        // Sisa lebar dibagi rata ke kolom field, mengikuti browser membagi rata
        // kolom tanpa lebar eksplisit pada table-layout: fixed di versi PDF.
        $fieldColWidth = (int) floor((self::TABLE_WIDTH - $noColWidth - $timeColWidth) / $fieldCount);

        // .data-table th { background-color: #ffffff; font-weight: bold; text-align: center; }
        // Header tetap putih (bukan biru) supaya sama persis dengan versi PDF.
        $headerFontStyle = ['bold' => true];
        $centerParagraph = ['alignment' => Jc::CENTER];

        $table->addRow(400, ['tblHeader' => true]);
        $table->addCell($noColWidth)->addText('No.', $headerFontStyle, $centerParagraph);

        foreach ($fields as $field) {
            $table->addCell($fieldColWidth)->addText($field['nama'], $headerFontStyle, $centerParagraph);
        }

        $table->addCell($timeColWidth)->addText('Waktu Isi', $headerFontStyle, $centerParagraph);

        if ($responses->isEmpty()) {
            $table->addRow();
            $table->addCell(self::TABLE_WIDTH, ['gridSpan' => $fieldCount + 2])
                ->addText('Belum ada peserta yang mengisi daftar hadir ini.', [], $centerParagraph);

            return;
        }

        foreach ($responses as $i => $response) {
            $table->addRow();
            $table->addCell($noColWidth)->addText((string) ($i + 1), [], $centerParagraph);

            foreach ($fields as $field) {
                $table->addCell($fieldColWidth)->addText((string) ($response->answers[$field['nama']] ?? '-'));
            }

            $table->addCell($timeColWidth)->addText($response->created_at->format('d/m/Y H:i'), [], $centerParagraph);
        }
    }

    /**
     * addTableStyle akan error jika nama style yang sama didaftarkan dua kali
     * pada objek PhpWord yang sama. Dibungkus try/catch agar aman dipanggil
     * berkali-kali, misalnya dari proses pengujian otomatis.
     */
    private static function registerTableStyleOnce(PhpWord $phpWord, string $name, array $style): void
    {
        try {
            $phpWord->addTableStyle($name, $style);
        } catch (\Throwable $e) {
            // Nama style sudah terdaftar sebelumnya — abaikan.
        }
    }
}
