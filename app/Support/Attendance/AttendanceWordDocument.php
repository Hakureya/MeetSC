<?php

namespace App\Support\Attendance;

use App\Models\AttendanceForm;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\SimpleType\VerticalJc;

class AttendanceWordDocument
{
    private const COLOR_TITLE_BG = 'B7D7F0';

    private const MARGIN_SIDE = 1000;
    private const TABLE_WIDTH = 9906;

    private const LETTERHEAD_PATH = 'storage/images/kop-surat.png';

    /** Ukuran A4 dalam point (pt): 210 x 297 mm. */
    private const PAGE_WIDTH_PT = 595.28;
    private const PAGE_HEIGHT_PT = 841.89;

    /**
     * Margin halaman (twips) saat kop surat dipakai, agar teks tidak menimpa
     * logo di atas maupun alamat di bawah. Samakan dengan @page di pdf.blade.php.
     */
    private const MARGIN_TOP_LETTERHEAD = 1700;    // ~85 pt
    private const MARGIN_BOTTOM_LETTERHEAD = 2000; // ~100 pt

    /** Margin atas & bawah jika gambar kop surat tidak ditemukan. */
    private const MARGIN_TOP_PLAIN = 1000;
    private const MARGIN_BOTTOM_PLAIN = 1000;

    /** Padanan .no-column { width: 10%; } */
    private const NO_COLUMN_RATIO = 0.08;

    /** Padanan .time-column { width: 15%; } */
    private const TIME_COLUMN_RATIO = 0.20;

    public static function build(AttendanceForm $form, Collection $responses): PhpWord
    {
        $phpWord = new PhpWord();

        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(9);
        $phpWord->setDefaultParagraphStyle([
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'lineHeight' => 1.15,
        ]);

        $letterheadPath = public_path(self::LETTERHEAD_PATH);
        $hasLetterhead = file_exists($letterheadPath);

        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'paperSize' => 'A4',
            'headerHeight' => 734,
            'footerHeight' => 720,
            'marginTop' => $hasLetterhead ? self::MARGIN_TOP_LETTERHEAD : self::MARGIN_TOP_PLAIN,
            'marginBottom' => $hasLetterhead ? self::MARGIN_BOTTOM_LETTERHEAD : self::MARGIN_BOTTOM_PLAIN,
            'marginLeft' => self::MARGIN_SIDE,
            'marginRight' => self::MARGIN_SIDE,
        ]);

        // Pasang kop surat sebagai gambar A4 penuh di belakang teks (diulang tiap halaman).
        if ($hasLetterhead) {
            $section->addHeader()->addImage($letterheadPath, [
                'width' => self::PAGE_WIDTH_PT,
                'height' => self::PAGE_HEIGHT_PT,
                'positioning' => 'absolute',
                'posHorizontalRel' => 'page',
                'posVerticalRel' => 'page',
                'posHorizontal' => 'left',
                'posVertical' => 'top',
                'wrappingStyle' => 'behind',
            ]);
        }

        static::addMetaTable($phpWord, $section, $form);
        $section->addTextBreak(1, ['size' => 14]);
        static::addDataTable($phpWord, $section, $form, $responses);

        return $phpWord;
    }

    /**
     * Tabel judul + informasi rapat. Padanan dari <table class="meta-table"> pada pdf.blade.php.
     */
    private static function addMetaTable(PhpWord $phpWord, $section, AttendanceForm $form): void
    {
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMarginTop' => 60,
            'cellMarginBottom' => 60,
            'cellMarginLeft' => 120,
            'cellMarginRight' => 120,
            'alignment' => JcTable::CENTER,
            'width' => self::TABLE_WIDTH,
            'unit' => TblWidth::TWIP,
            'layout' => 'fixed',
        ];

        static::registerTableStyleOnce($phpWord, 'metaTable', $tableStyle);
        $table = $section->addTable('metaTable');

        $vCenter = ['valign' => VerticalJc::CENTER];
        $labelStyle = ['bold' => true];
        $rowHeight = 320;

        // 1. Baris judul: DAFTAR HADIR RAPAT (gridSpan 4)
        $table->addRow(360, ['cantSplit' => true]);
        $table->addCell(self::TABLE_WIDTH, array_merge($vCenter, [
            'gridSpan' => 4,
            'bgColor' => self::COLOR_TITLE_BG,
        ]))->addText(
            'DAFTAR HADIR RAPAT',
            ['bold' => true, 'size' => 12],
            ['alignment' => Jc::CENTER]
        );

        // Kolom label dilebarkan (area tabel lebih sempit karena margin kop surat)
        // agar "Pimpinan Rapat:" dan "Rapat/Pertemuan:" tidak turun ke baris kedua.
        $col1W = (int) round(self::TABLE_WIDTH * 0.19);
        $col2W = (int) round(self::TABLE_WIDTH * 0.31);
        $col3W = (int) round(self::TABLE_WIDTH * 0.19);
        $col4W = self::TABLE_WIDTH - $col1W - $col2W - $col3W; // 31%
        $span3W = self::TABLE_WIDTH - $col1W; // 81%

        // 2. Baris: Pimpinan Rapat + Hari/Tanggal
        $table->addRow($rowHeight, ['cantSplit' => true]);
        $table->addCell($col1W, $vCenter)->addText('Pimpinan Rapat:', $labelStyle);
        $table->addCell($col2W, $vCenter)->addText($form->pic ?: '-');
        $table->addCell($col3W, $vCenter)->addText('Hari/Tanggal:', $labelStyle);
        $table->addCell($col4W, $vCenter)->addText(
            $form->created_at ? $form->created_at->format('d/m/Y') : now()->format('d/m/Y')
        );

        // 3. Baris: Tempat + Waktu
        $table->addRow($rowHeight, ['cantSplit' => true]);
        $table->addCell($col1W, $vCenter)->addText('Tempat:', $labelStyle);
        $table->addCell($col2W, $vCenter)->addText($form->tempat ?: '-');
        $table->addCell($col3W, $vCenter)->addText('Waktu:', $labelStyle);
        $table->addCell($col4W, $vCenter)->addText(
            $form->created_at ? $form->created_at->format('H:i') : now()->format('H:i')
        );

        // 4. Baris: Rapat/Pertemuan (colspan="3")
        $table->addRow($rowHeight, ['cantSplit' => true]);
        $table->addCell($col1W, $vCenter)->addText('Rapat/Pertemuan:', $labelStyle);
        $table->addCell($span3W, array_merge($vCenter, ['gridSpan' => 3]))
            ->addText($form->rapat_pertemuan ?: '-');

        // 5. Baris: Divisi PIC (colspan="3")
        $table->addRow($rowHeight, ['cantSplit' => true]);
        $table->addCell($col1W, $vCenter)->addText('Divisi PIC:', $labelStyle);
        $table->addCell($span3W, array_merge($vCenter, ['gridSpan' => 3]))
            ->addText($form->divisi_pic ?: '-');
    }

    /**
     * Tabel daftar hadir peserta. Padanan dari <table class="data-table"> pada pdf.blade.php.
     *
     * Seluruh peserta ditampilkan. Baris header ('tblHeader') diulang otomatis di setiap
     * halaman, dan setiap baris ('cantSplit') tidak akan terpotong di batas halaman.
     */
    private static function addDataTable(PhpWord $phpWord, $section, AttendanceForm $form, Collection $responses): void
    {
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMarginTop' => 60,
            'cellMarginBottom' => 60,
            'cellMarginLeft' => 90,
            'cellMarginRight' => 90,
            'alignment' => JcTable::CENTER,
            'width' => self::TABLE_WIDTH,
            'unit' => TblWidth::TWIP,
            'layout' => 'fixed',
        ];

        static::registerTableStyleOnce($phpWord, 'dataTable', $tableStyle);
        $table = $section->addTable('dataTable');

        $fields = $form->fields ?? [];
        $fieldCount = max(count($fields), 1);

        $noColWidth = (int) round(self::TABLE_WIDTH * self::NO_COLUMN_RATIO);
        $timeColWidth = (int) round(self::TABLE_WIDTH * self::TIME_COLUMN_RATIO);
        $fieldColWidth = (int) floor((self::TABLE_WIDTH - $noColWidth - $timeColWidth) / $fieldCount);

        $vCenter = ['valign' => VerticalJc::CENTER];
        $headerFontStyle = ['bold' => true];
        $centerParagraph = ['alignment' => Jc::CENTER];

        $table->addRow(340, ['tblHeader' => true, 'cantSplit' => true]);
        $table->addCell($noColWidth, $vCenter)->addText('No.', $headerFontStyle, $centerParagraph);

        foreach ($fields as $field) {
            $table->addCell($fieldColWidth, $vCenter)->addText($field['nama'], $headerFontStyle, $centerParagraph);
        }

        $table->addCell($timeColWidth, $vCenter)->addText('Waktu Isi', $headerFontStyle, $centerParagraph);

        if ($responses->isEmpty()) {
            $table->addRow(300, ['cantSplit' => true]);
            $table->addCell(self::TABLE_WIDTH, array_merge($vCenter, ['gridSpan' => $fieldCount + 2]))
                ->addText('Belum ada peserta yang mengisi daftar hadir ini.', [], $centerParagraph);

            return;
        }

        foreach ($responses->values() as $i => $response) {
            $table->addRow(300, ['cantSplit' => true]);
            $table->addCell($noColWidth, $vCenter)->addText((string) ($i + 1), [], $centerParagraph);

            foreach ($fields as $field) {
                $table->addCell($fieldColWidth, $vCenter)
                    ->addText((string) ($response->answers[$field['nama']] ?? '-'));
            }

            $table->addCell($timeColWidth, $vCenter)
                ->addText($response->created_at->format('d/m/Y H:i'), [], $centerParagraph);
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
