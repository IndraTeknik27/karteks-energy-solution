<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'email', 'ip_address', 'successful',
        'user_agent', 'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'attempted_at' => 'datetime',
        ];
    }

    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    public function scopeRecent($query, int $minutes = 15)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    public function scopeForIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Check if email has too many failed attempts recently.
     */
    public static function isEmailThrottled(string $email, int $maxAttempts = 5, int $minutes = 15): bool
    {
        $count = self::failed()->forEmail($email)->recent($minutes)->count();
        return $count >= $maxAttempts;
    }

    /**
     * Check if IP has too many failed attempts recently.
     */
    public static function isIpThrottled(string $ip, int $maxAttempts = 20, int $minutes = 15): bool
    {
        $count = self::failed()->forIp($ip)->recent($minutes)->count();
        return $count >= $maxAttempts;
    }

    /**
     * Record a login attempt.
     */
    public static function record(string $email, string $ip, bool $successful, ?string $userAgent = null): self
    {
        return self::create([
            'email' => strtolower($email),
            'ip_address' => $ip,
            'successful' => $successful,
            'user_agent' => substr($userAgent ?? '', 0, 500),
            'attempted_at' => now(),
        ]);
    }
}