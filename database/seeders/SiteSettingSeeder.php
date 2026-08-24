<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_tagline', 'value' => 'Solusi Energi Terbarukan & Kendaraan Listrik', 'type' => 'string', 'group' => 'general', 'label' => 'Tagline', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'KARTEKS ENERGY SOLUTION menyediakan solusi energi terbarukan, kendaraan listrik, custom battery, solar panel, dan konsultasi profesional di Gowa, Sulawesi Selatan.', 'type' => 'text', 'group' => 'general', 'label' => 'Deskripsi Situs', 'is_public' => true],
            ['key' => 'contact_email', 'value' => 'karteksenergy27@gmail.com', 'type' => 'string', 'group' => 'contact', 'label' => 'Email', 'is_public' => true],
            ['key' => 'contact_phone', 'value' => '+6281545326426', 'type' => 'string', 'group' => 'contact', 'label' => 'Telepon', 'is_public' => true],
            ['key' => 'contact_whatsapp', 'value' => '6281545326426', 'type' => 'string', 'group' => 'contact', 'label' => 'WhatsApp', 'is_public' => true],
            ['key' => 'contact_address', 'value' => 'Jln. Bonto Marannu, Perumahan Tanjung Kencana Residence No. 8, Kabupaten Gowa, Sulawesi Selatan', 'type' => 'text', 'group' => 'contact', 'label' => 'Alamat', 'is_public' => true],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/karteksenergy', 'type' => 'string', 'group' => 'social', 'label' => 'Facebook URL', 'is_public' => true],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/karteksenergy', 'type' => 'string', 'group' => 'social', 'label' => 'Instagram URL', 'is_public' => true],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/@karteksenergy', 'type' => 'string', 'group' => 'social', 'label' => 'YouTube URL', 'is_public' => true],
            ['key' => 'social_tiktok', 'value' => 'https://tiktok.com/@karteksenergy', 'type' => 'string', 'group' => 'social', 'label' => 'TikTok URL', 'is_public' => true],
            ['key' => 'order_expiry_hours', 'value' => '24', 'type' => 'integer', 'group' => 'ecommerce', 'label' => 'Order Expiry (hours)', 'is_public' => false],
            ['key' => 'min_order_amount', 'value' => '0', 'type' => 'integer', 'group' => 'ecommerce', 'label' => 'Minimum Order Amount', 'is_public' => true],
            ['key' => 'default_tax_rate', 'value' => '11', 'type' => 'integer', 'group' => 'ecommerce', 'label' => 'Default Tax Rate (%)', 'is_public' => true],
            ['key' => 'enable_registration', 'value' => '1', 'type' => 'boolean', 'group' => 'auth', 'label' => 'Enable Customer Registration', 'is_public' => true],
            ['key' => 'enable_guest_checkout', 'value' => '1', 'type' => 'boolean', 'group' => 'ecommerce', 'label' => 'Enable Guest Checkout', 'is_public' => true],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'label' => 'Maintenance Mode', 'is_public' => false],

            // SEO Settings (FASE 4.2)
            ['key' => 'seo_og_image', 'value' => null, 'type' => 'image', 'group' => 'seo', 'label' => 'Default OG Image (1200x630)', 'is_public' => true],
            ['key' => 'seo_google_verification', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label' => 'Google Search Console Verification', 'is_public' => false],
            ['key' => 'seo_bing_verification', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label' => 'Bing Webmaster Verification', 'is_public' => false],
            ['key' => 'seo_facebook_pixel', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label' => 'Facebook Pixel ID', 'is_public' => false],
            ['key' => 'seo_google_analytics', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label' => 'Google Analytics ID (GA4)', 'is_public' => false],
            ['key' => 'seo_google_tag_manager', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label' => 'Google Tag Manager ID', 'is_public' => false],
            ['key' => 'seo_schema_type', 'value' => 'LocalBusiness', 'type' => 'string', 'group' => 'seo', 'label' => 'Schema.org Type (LocalBusiness, Organization)', 'is_public' => true],
            ['key' => 'seo_twitter_handle', 'value' => '@karteksenergy', 'type' => 'string', 'group' => 'seo', 'label' => 'Twitter Handle', 'is_public' => true],
            ['key' => 'seo_facebook_url', 'value' => 'https://facebook.com/karteksenergy', 'type' => 'string', 'group' => 'seo', 'label' => 'Facebook Page URL (sameAs)', 'is_public' => true],
            ['key' => 'seo_instagram_url', 'value' => 'https://instagram.com/karteksenergy', 'type' => 'string', 'group' => 'seo', 'label' => 'Instagram URL (sameAs)', 'is_public' => true],
            ['key' => 'seo_linkedin_url', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label' => 'LinkedIn URL (sameAs)', 'is_public' => true],
            ['key' => 'seo_youtube_url', 'value' => 'https://youtube.com/@karteksenergy', 'type' => 'string', 'group' => 'seo', 'label' => 'YouTube URL (sameAs)', 'is_public' => true],
            ['key' => 'robots_disallow_all', 'value' => '0', 'type' => 'boolean', 'group' => 'seo', 'label' => 'Robots: Disallow All (maintenance mode)', 'is_public' => false],
            ['key' => 'robots_extra_disallow', 'value' => '', 'type' => 'text', 'group' => 'seo', 'label' => 'Robots: Extra Disallow Paths (one per line)', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('Site settings seeded: '.count($settings));
    }
}