<?php

namespace App\Support\Traits;

use Illuminate\Support\Str;

/**
 * Input sanitization helpers.
 *
 * Use di FormRequest::prepareForValidation() atau controller:
 *   $this->sanitizeInput($request->all());
 *
 * - stripControlChars: hapus null bytes, control chars (kecuali tab/newline/cr)
 * - stripDangerousTags: hapus script/style/iframe/object/embed tags + content (server-side)
 * - normalizeWhitespace: collapse multiple spaces, trim each value
 *
 * NOTE: Untuk rich text content (blog, custom battery), pakai HTMLPurifier atau
 * set 'content' field skip sanitization via $exceptKeys.
 */
trait InputSanitizer
{
    protected array $sanitizeExceptKeys = ['content', 'description', 'message', 'admin_notes'];

    protected function sanitizeInput(array $input, ?array $exceptKeys = null): array
    {
        $except = $exceptKeys ?? $this->sanitizeExceptKeys;

        return $this->sanitizeArray($input, $except);
    }

    protected function sanitizeArray(array $data, array $except = []): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            // Skip excluded keys (typically rich text fields)
            if (in_array($key, $except, true)) {
                $result[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->sanitizeArray($value, $except);
            } elseif (is_string($value)) {
                $result[$key] = $this->sanitizeString($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    protected function sanitizeString(string $value): string
    {
        // Hapus null bytes
        $value = str_replace(chr(0), '', $value);

        // Hapus control characters (kecuali \t \n \r)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        // Hapus script/style/iframe/object/embed tags + content (defense in depth)
        // NOTE: Blade {{ }} auto-escape handles this on output. Extra layer di input.
        $value = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);
        $value = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $value);
        $value = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $value);
        $value = preg_replace('#<object(.*?)>(.*?)</object>#is', '', $value);
        $value = preg_replace('#<embed(.*?)>(.*?)</embed>#is', '', $value);
        $value = preg_replace('#<form(.*?)>(.*?)</form>#is', '', $value);

        // Hapus event handlers (onclick, onerror, onload, etc) — defense for inline scripts
        $value = preg_replace('#\s*on\w+\s*=\s*["\'][^"\']*["\']#i', '', $value);

        // Hapus javascript: protocol di href/src
        $value = preg_replace('#(href|src)\s*=\s*["\']?\s*javascript:#i', '$1=""', $value);
        $value = preg_replace('#(href|src)\s*=\s*["\']?\s*data:text/html#i', '$1=""', $value);
        $value = preg_replace('#(href|src)\s*=\s*["\']?\s*vbscript:#i', '$1=""', $value);

        // Normalize whitespace (collapse multiple spaces ke single, kecuali di pre/textarea)
        $value = preg_replace('/[ \t]+/', ' ', $value);

        return trim($value);
    }
}