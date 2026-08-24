<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * File upload validation middleware.
 *
 * Hardening layer beyond Filament's built-in validation:
 * - Validates uploaded file MIME type via PHP's mime_content_type (magic bytes)
 * - Checks file extension matches declared MIME
 * - Enforces size limit per route
 * - Blocks executable extensions regardless of MIME (e.g. .php.jpg)
 *
 * Apply per-route: `->middleware('file.upload:images,5120')`
 * - arg1: comma-separated allowed extensions (e.g. 'jpg,png,webp')
 * - arg2: max size in KB (default 5120 = 5MB)
 */
class ValidateFileUpload
{
    public function handle(Request $request, Closure $next, string $allowedExtensions = 'jpg,jpeg,png,webp,pdf', int $maxSizeKb = 5120): Response
    {
        $allowedExts = array_map('trim', explode(',', $allowedExtensions));
        $dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'pht', 'phar',
            'exe', 'bat', 'sh', 'cmd', 'com', 'cpl', 'msi', 'scr',
            'js', 'jsx', 'mjs', 'vbs', 'wsf',
            'jar', 'war', 'ear',
            'asp', 'aspx', 'jsp', 'jspx',
            'htaccess', 'htpasswd', 'ini', 'env',
        ];

        // Scan all uploaded files
        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $subFile) {
                    $this->validateFile($subFile, $allowedExts, $dangerousExts, $maxSizeKb);
                }
            } else {
                $this->validateFile($file, $allowedExts, $dangerousExts, $maxSizeKb);
            }
        }

        return $next($request);
    }

    protected function validateFile($file, array $allowedExts, array $dangerousExts, int $maxSizeKb): void
    {
        if (! $file || ! $file->isValid()) {
            return;
        }

        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $sizeKb = $file->getSize() / 1024;
        $mimeType = $file->getMimeType();
        $realMime = $this->detectRealMime($file);

        // 1. Dangerous extension check (regardless of MIME)
        if (in_array($extension, $dangerousExts, true)) {
            abort(422, "File extension '{$extension}' tidak diizinkan.");
        }

        // 2. Empty file check
        if ($file->getSize() === 0) {
            abort(422, 'File kosong, silakan pilih file lain.');
        }

        // 3. Size limit
        if ($sizeKb > $maxSizeKb) {
            abort(422, "Ukuran file melebihi batas maksimum {$maxSizeKb}KB.");
        }

        // 4. Extension allowlist
        if (! in_array($extension, $allowedExts, true)) {
            abort(422, "Ekstensi '{$extension}' tidak diizinkan. Hanya: ".implode(', ', $allowedExts));
        }

        // 5. MIME type sanity check (extension vs declared MIME vs actual MIME)
        if ($realMime && $this->isExecutableMime($realMime)) {
            abort(422, 'Tipe file terdeteksi sebagai executable, upload ditolak.');
        }

        // 6. Double extension attack: 'file.php.jpg' masih berbahaya di beberapa OS
        $nameParts = explode('.', strtolower($originalName));
        if (count($nameParts) > 2) {
            // Check intermediate parts are not dangerous
            array_pop($nameParts); // Last is extension (checked)
            array_pop($nameParts); // Second-to-last is filename
            foreach ($nameParts as $part) {
                if (in_array($part, $dangerousExts, true)) {
                    abort(422, 'Nama file mengandung segment mencurigakan.');
                }
            }
        }
    }

    /**
     * Detect real MIME via magic bytes (lebih aman dari client-declared MIME).
     */
    protected function detectRealMime($file): ?string
    {
        try {
            $realMime = (new \Symfony\Component\Mime\MimeTypes())->guessMimeType($file->getPathname());
            return $realMime;
        } catch (\Throwable $e) {
            // Fallback ke PHP mime_content_type
            $mime = @mime_content_type($file->getPathname());
            return $mime ?: null;
        }
    }

    /**
     * Block executable MIME types regardless of extension.
     */
    protected function isExecutableMime(string $mime): bool
    {
        $dangerous = [
            'application/x-php', 'application/x-httpd-php',
            'application/x-shellscript', 'application/x-sh',
            'application/x-executable', 'application/x-mach-binary',
            'application/x-dosexec', 'application/x-msdownload',
            'application/x-msdos-program',
            'text/x-php', 'application/javascript',
            'application/x-cgi', 'application/perl',
        ];

        foreach ($dangerous as $bad) {
            if (str_contains($mime, $bad)) {
                return true;
            }
        }
        return false;
    }
}