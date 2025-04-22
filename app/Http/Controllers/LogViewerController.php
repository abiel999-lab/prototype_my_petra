<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index()
    {
        $logFile = storage_path('logs/laravel.log');

        // Baca isi log
        $logContent = File::exists($logFile) ? File::get($logFile) : 'Log file not found.';

        // Potong log jadi baris dan dibalik agar terbaru di atas
        $logLines = array_reverse(explode("\n", $logContent));

        return view('admin.logs.index', compact('logLines'));
    }
}
