<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td,
        th {
            border: 1px solid #000;
            vertical-align: middle;
        }

        /* ========================================
           TABEL HEADER / INFORMASI RAPAT
        ======================================== */

        .meta-table {
            width: 100%;
            margin-bottom: 28px;
        }

        .meta-table td {
            padding: 4px 8px;
            font-size: 11px;
            height: 20px;
        }

        /* Judul DAFTAR HADIR RAPAT */
        .title-cell {
            background-color: #b7d7f0;
            font-size: 16px !important;
            font-weight: bold;
            text-align: center;
            padding: 4px !important;
            height: 22px;
        }

        /* Label seperti Pimpinan Rapat: */
        .label {
            font-weight: bold;
            white-space: nowrap;
        }

        /* Kolom isi informasi */
        .value {
            font-weight: normal;
        }


        /* ========================================
           TABEL DAFTAR PESERTA
        ======================================== */

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table th {
            background-color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            padding: 5px 6px;
            height: 22px;
        }

        .data-table td {
            font-size: 11px;
            padding: 4px 6px;
            height: 19px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* Kolom nomor */
        .no-column {
            width: 10%;
            text-align: center;
        }

        /* Kolom waktu */
        .time-column {
            width: 15%;
            text-align: center;
        }

        /* Rata tengah */
        .text-center {
            text-align: center !important;
        }

        /* Mencegah baris terpotong ketika PDF */
        tr {
            page-break-inside: avoid;
        }
    </style>

</head>

<body>

    {{-- =====================================================
         TABEL HEADER DAN INFORMASI RAPAT
    ====================================================== --}}

    <table class="meta-table">

        {{-- Judul --}}
        <tr>
            <td colspan="6" class="title-cell">
                DAFTAR HADIR RAPAT
            </td>
        </tr>


        {{-- Pimpinan Rapat + Hari/Tanggal --}}
        <tr>

            <td class="label" style="width: 15%;">
                Pimpinan Rapat:
            </td>

            <td class="value" colspan="2" style="width: 33%;">
                {{ $form->pic ?? '-' }}
            </td>

            <td class="label" style="width: 15%;">
                Hari/Tanggal:
            </td>

            <td class="value" colspan="2" style="width: 37%;">
                {{ $form->created_at
                    ? $form->created_at->format('d/m/Y')
                    : now()->format('d/m/Y') }}
            </td>

        </tr>


        {{-- Tempat + Waktu --}}
        <tr>

            <td class="label">
                Tempat:
            </td>

            <td class="value" colspan="2">
                {{ $form->tempat ?? '-' }}
            </td>

            <td class="label">
                Waktu:
            </td>

            <td class="value" colspan="2">
                {{ $form->created_at
                    ? $form->created_at->format('H:i')
                    : now()->format('H:i') }}
            </td>

        </tr>


        {{-- Rapat / Pertemuan --}}
        <tr>

            <td class="label">
                Rapat/Pertemuan:
            </td>

            <td class="value" colspan="5">
                {{ $form->rapat_pertemuan ?? '-' }}
            </td>

        </tr>


        {{-- Divisi PIC --}}
        <tr>

            <td class="label">
                Divisi PIC:
            </td>

            <td class="value" colspan="5">
                {{ $form->divisi_pic ?? '-' }}
            </td>

        </tr>

    </table>


    {{-- =====================================================
         TABEL DAFTAR HADIR PESERTA
    ====================================================== --}}

    <table class="data-table">

        <thead>

            <tr>

                {{-- Nomor --}}
                <th class="no-column">
                    No.
                </th>


                {{-- Field yang dibuat pada form --}}
                @foreach ($form->fields as $field)

                    <th>
                        {{ $field['nama'] }}
                    </th>

                @endforeach


                {{-- Waktu pengisian --}}
                <th class="time-column">
                    Waktu Isi
                </th>

            </tr>

        </thead>


        <tbody>

            {{-- =================================================
                 DATA PESERTA
                 Maksimal 10 peserta
            ================================================== --}}

            @forelse ($responses->take(10) as $i => $response)

                <tr>

                    {{-- Nomor otomatis berdasarkan peserta --}}
                    <td class="text-center">
                        {{ $i + 1 }}
                    </td>


                    {{-- Jawaban setiap field --}}
                    @foreach ($form->fields as $field)

                        <td>
                            {{ $response->answers[$field['nama']] ?? '-' }}
                        </td>

                    @endforeach


                    {{-- Waktu mengisi form --}}
                    <td class="text-center">
                        {{ $response->created_at->format('d/m/Y H:i') }}
                    </td>

                </tr>

            @empty

                {{-- Jika belum ada peserta --}}
                <tr>

                    <td
                        colspan="{{ count($form->fields) + 2 }}"
                        class="text-center"
                    >
                        Belum ada peserta yang mengisi daftar hadir ini.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</body>

</html>