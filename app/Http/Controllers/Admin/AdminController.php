<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use ZipArchive;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function downloadChromeExtension()
    {
        $extensionPath = resource_path('chrome-extension/instagram');
        $zipFileName = 'chrome-extension-instagram.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        if (File::exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $files = File::allFiles($extensionPath);

            foreach ($files as $file) {
                $relativePath = substr($file->getPathname(), strlen($extensionPath) + 1);
                $zip->addFile($file->getPathname(), $relativePath);
            }

            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
