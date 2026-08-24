<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt dynamically.
     */
    public function index(): Response
    {
        $content = $this->build();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    protected function build(): string
    {
        $siteUrl = url('/');
        $sitemapUrl = route('sitemap.xml');
        $env = app()->environment();

        // Cek apakah ada setting untuk full disallow (maintenance/private)
        $disallowAll = (bool) SiteSetting::get('robots_disallow_all', false);
        $extraDisallow = (string) SiteSetting::get('robots_extra_disallow', '');

        $lines = [];

        if ($disallowAll) {
            // Maintenance mode atau private: block all bots
            $lines[] = 'User-agent: *';
            $lines[] = 'Disallow: /';
            $lines[] = '';
            $lines[] = '# Sitemap reference kept for post-launch re-crawl:';
            $lines[] = '# Sitemap: '.$sitemapUrl;
            return implode("\n", $lines);
        }

        // Default: allow all, block admin + API + private endpoints
        $lines[] = '# KARTEKS ENERGY SOLUTION robots.txt';
        $lines[] = '# Generated: '.now()->toAtomString();
        $lines[] = '# Environment: '.$env;
        $lines[] = '';
        $lines[] = 'User-agent: *';
        $lines[] = 'Allow: /';
        $lines[] = '';
        $lines[] = '# Disallow admin, API, dashboard, payment';
        $lines[] = 'Disallow: /admin';
        $lines[] = 'Disallow: /admin/';
        $lines[] = 'Disallow: /api/';
        $lines[] = 'Disallow: /dashboard';
        $lines[] = 'Disallow: /dashboard/';
        $lines[] = 'Disallow: /payment/';
        $lines[] = 'Disallow: /livewire/';
        $lines[] = 'Disallow: /storage/';
        $lines[] = 'Disallow: /sanctum/';
        $lines[] = 'Disallow: /up';
        $lines[] = 'Disallow: /forgot-password';
        $lines[] = 'Disallow: /reset-password';
        $lines[] = 'Disallow: /login';
        $lines[] = 'Disallow: /register';

        // Extra disallow paths dari SiteSetting
        if ($extraDisallow) {
            $paths = array_filter(array_map('trim', explode("\n", $extraDisallow)));
            foreach ($paths as $path) {
                $lines[] = 'Disallow: '.$path;
            }
        }

        $lines[] = '';
        $lines[] = '# Crawl-delay (1 detik untuk mencegah overload)';
        $lines[] = 'Crawl-delay: 1';
        $lines[] = '';

        // Sitemap
        $lines[] = '# Sitemap';
        $lines[] = 'Sitemap: '.$sitemapUrl;
        $lines[] = '';

        // Specific bots
        $lines[] = '# Googlebot';
        $lines[] = 'User-agent: Googlebot';
        $lines[] = 'Allow: /';
        $lines[] = '';
        $lines[] = '# Bingbot';
        $lines[] = 'User-agent: Bingbot';
        $lines[] = 'Allow: /';
        $lines[] = '';

        // AI bots blocked (opsional — default block)
        $lines[] = '# AI bots (blocked by default)';
        $lines[] = 'User-agent: GPTBot';
        $lines[] = 'Disallow: /';
        $lines[] = '';
        $lines[] = 'User-agent: ChatGPT-User';
        $lines[] = 'Disallow: /';
        $lines[] = '';
        $lines[] = 'User-agent: ClaudeBot';
        $lines[] = 'Disallow: /';
        $lines[] = '';
        $lines[] = 'User-agent: anthropic-ai';
        $lines[] = 'Disallow: /';
        $lines[] = '';
        $lines[] = 'User-agent: CCBot';
        $lines[] = 'Disallow: /';
        $lines[] = '';

        return implode("\n", $lines);
    }
}