<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;

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
