<?php

namespace App\Http\Controllers;

use App\Models\AttendanceForm;
use App\Models\AttendanceResponse;
use App\Support\Attendance\AttendanceWordDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceFormController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Riwayat form ditampilkan maksimal 5 per halaman, dengan pagination
        // jika jumlah form lebih dari itu.
        $forms = ($user->role === 'admin')
            ? AttendanceForm::with(['user', 'responses'])->latest()->paginate(5)
            : AttendanceForm::with('responses')->where('user_id', $user->id)->latest()->paginate(5);

        return view('attendance.index', compact('forms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'divisi_pic' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
            'rapat_pertemuan' => 'required|string|max:255',
            'expires_at' => 'required|date',
            'fields' => 'required|array|min:1',
            'fields.*.nama' => 'required|string|max:100',
            'fields.*.tipe' => 'required|in:text,number,email,date,textarea',
            'fields.*.required' => 'nullable|boolean',
        ], [
            'divisi_pic.required' => 'Divisi PIC wajib diisi.',
        ]);

        // Normalisasi checkbox "required" (kalau tidak dicentang, key-nya tidak terkirim sama sekali).
        $fields = collect($validated['fields'])->map(fn ($f) => [
            'nama' => $f['nama'],
            'tipe' => $f['tipe'],
            'required' => (bool) ($f['required'] ?? false),
        ])->all();

        AttendanceForm::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'judul' => $validated['judul'],
            'pic' => $validated['pic'],
            'divisi_pic' => $validated['divisi_pic'],
            'tempat' => $validated['tempat'],
            'rapat_pertemuan' => $validated['rapat_pertemuan'],
            'fields' => $fields,
            'expires_at' => $validated['expires_at'],
        ]);

        return redirect()->route('attendance.index')->with('success', 'Form kehadiran berhasil dibuat.');
    }

    /**
     * Halaman publik (tanpa login) untuk mengisi form kehadiran.
     */
    public function showPublic($uuid)
    {
        $form = AttendanceForm::where('uuid', $uuid)->firstOrFail();

        return view('attendance.public', [
            'form' => $form,
            'isExpired' => $form->isExpired(),
        ]);
    }

    /**
     * Proses pengisian form publik.
     */
    public function submitPublic(Request $request, $uuid)
    {
        $form = AttendanceForm::where('uuid', $uuid)->firstOrFail();

        if ($form->isExpired()) {
            return back()->withErrors(['form' => 'Formulir ini sudah kedaluwarsa dan tidak bisa diisi lagi.']);
        }

        // Bangun aturan validasi secara dinamis berdasarkan field yang didefinisikan pembuat form.
        $rules = [];
        foreach ($form->fields as $field) {
            $key = "answers.{$field['nama']}";
            $typeRule = match ($field['tipe']) {
                'number' => 'numeric',
                'email' => 'email',
                'date' => 'date',
                default => 'string',
            };
            $rules[$key] = ($field['required'] ? 'required' : 'nullable').'|'.$typeRule;
        }

        $validated = $request->validate($rules);

        AttendanceResponse::create([
            'attendance_form_id' => $form->id,
            'answers' => $validated['answers'] ?? [],
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('attendance.public', $form->uuid)
            ->with('success', 'Terima kasih, kehadiran Anda berhasil dicatat.');
    }

    public function showResponses($id)
    {
        $form = AttendanceForm::with('user')->findOrFail($id);

        if (auth()->user()->role !== 'admin' && $form->user_id !== auth()->id()) {
            abort(403);
        }

        $responses = $form->responses()->latest()->get();

        return view('attendance.responses', compact('form', 'responses'));
    }

    /**
     * Ekspor daftar kehadiran (jawaban) ke PDF.
     * Butuh package: composer require barryvdh/laravel-dompdf
     */
    public function exportPdf($id)
    {
        $form = AttendanceForm::with('user')->findOrFail($id);

        if (auth()->user()->role !== 'admin' && $form->user_id !== auth()->id()) {
            abort(403);
        }

        $responses = $form->responses()->oldest()->get();

        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->withErrors([
                'export' => 'Fitur export PDF butuh package tambahan. Jalankan: composer require barryvdh/laravel-dompdf',
            ]);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance.pdf', compact('form', 'responses'))
            ->setPaper('a4', 'portrait');

        $filename = Str::slug($form->judul).'-daftar-hadir.pdf';

        return $pdf->download($filename);
    }

    /**
     * Ekspor daftar kehadiran (jawaban) ke Word (.docx).
     * Butuh package: composer require phpoffice/phpword
     * Tata letak dan gaya visualnya diatur di App\Support\Attendance\AttendanceWordDocument,
     * dibuat agar konsisten dengan tampilan resources/views/attendance/pdf.blade.php.
     */
    public function exportWord($id)
    {
        $form = AttendanceForm::with('user')->findOrFail($id);

        if (auth()->user()->role !== 'admin' && $form->user_id !== auth()->id()) {
            abort(403);
        }

        // Berbeda dengan export PDF (dibatasi 10 baris agar muat satu halaman
        // A4), export Word menampilkan SELURUH peserta karena dokumen Word
        // otomatis mengalir ke halaman berikutnya.
        $responses = $form->responses()->oldest()->get();

        if (! class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            return back()->withErrors([
                'export' => 'Fitur export Word butuh package tambahan. Jalankan: composer require phpoffice/phpword',
            ]);
        }

        $phpWord = AttendanceWordDocument::build($form, $responses);

        $filename = Str::slug($form->judul).'-daftar-hadir.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word').'.docx';

        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}