<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComingSoonController extends Controller
{
    /**
     * Halaman placeholder untuk menu yang belum dibangun
     * (Ruang Meeting, Link Zoom, Form Kehadiran, dst).
     * Ganti dengan controller sungguhan saat fitur tersebut dikerjakan.
     */
    public function show(Request $request, string $title)
    {
        return view('coming-soon', [
            'title' => $title,
        ]);
    }
}
