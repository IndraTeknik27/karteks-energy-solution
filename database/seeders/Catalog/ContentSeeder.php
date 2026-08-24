<?php

namespace Database\Seeders\Catalog;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTestimonials();
        $this->seedBlogCategories();
        $this->seedBlogs();
    }

    protected function seedTestimonials(): void
    {
        $testimonials = [
            [
                'customer_name' => 'Ahmad Rizki',
                'position' => 'Pemilik',
                'company' => 'Toko Surya Gowa',
                'content' => 'KARTEKS sangat profesional dalam membantu instalasi panel solar di toko kami. Hemat listrik dan tim-nya sangat responsif.',
                'rating' => 5,
                'is_active' => true,
                'is_featured' => true,
                'sort' => 1,
            ],
            [
                'customer_name' => 'Siti Aminah',
                'position' => 'Manager Operasional',
                'company' => 'CV Mitra Transport',
                'content' => 'Konversi motor operasional kami ke listrik berjalan mulus. After-sales juga luar biasa, recommended!',
                'rating' => 5,
                'is_active' => true,
                'is_featured' => true,
                'sort' => 2,
            ],
            [
                'customer_name' => 'Budi Santoso',
                'position' => 'Owner',
                'company' => 'Bengkel Sumber Rezeki',
                'content' => 'Custom battery dari KARTEKS memang sesuai spek. Saya order untuk 5 unit EV bike, semua berjalan lancar.',
                'rating' => 5,
                'is_active' => true,
                'is_featured' => true,
                'sort' => 3,
            ],
            [
                'customer_name' => 'Dewi Lestari',
                'position' => 'Ibu Rumah Tangga',
                'company' => null,
                'content' => 'Pelayanan ramah dan produk berkualitas. Battery storage untuk rumah kami bekerja sempurna, tagihan listrik turun drastis.',
                'rating' => 4,
                'is_active' => true,
                'is_featured' => false,
                'sort' => 4,
            ],
            [
                'customer_name' => 'Pak Hasanuddin',
                'position' => 'Petani',
                'company' => 'Tani Maju Bersama',
                'content' => 'Pompa air solar dari KARTEKS sangat membantu di sawah kami. Operasional jadi lebih hemat.',
                'rating' => 5,
                'is_active' => true,
                'is_featured' => false,
                'sort' => 5,
            ],
            [
                'customer_name' => 'Maya Sari',
                'position' => 'Mahasiswa',
                'company' => 'Universitas Hasanuddin',
                'content' => 'Konsultasi EV gratis dari KARTEKS sangat membantu penelitian saya tentang adopsi kendaraan listrik di Sulawesi Selatan.',
                'rating' => 5,
                'is_active' => true,
                'is_featured' => true,
                'sort' => 6,
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::updateOrCreate(
                ['customer_name' => $data['customer_name']],
                $data,
            );
        }
    }

    protected function seedBlogCategories(): void
    {
        $categories = [
            ['name' => 'Tips & Panduan', 'slug' => 'tips-panduan', 'description' => 'Tips praktis seputar energi terbarukan'],
            ['name' => 'Berita Industri', 'slug' => 'berita-industri', 'description' => 'Update terbaru industri EV dan renewable energy'],
            ['name' => 'Studi Kasus', 'slug' => 'studi-kasus', 'description' => 'Studi kasus instalasi & project KARTEKS'],
            ['name' => 'Teknologi', 'slug' => 'teknologi', 'description' => 'Perkembangan teknologi baterai dan solar'],
        ];

        foreach ($categories as $data) {
            BlogCategory::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }
    }

    protected function seedBlogs(): void
    {
        $author = User::query()->whereHas('roles', function ($q) {
            $q->whereIn('name', ['super-admin', 'admin'])->where('guard_name', 'web');
        })->first();

        $catTips = BlogCategory::where('slug', 'tips-panduan')->first();
        $catNews = BlogCategory::where('slug', 'berita-industri')->first();
        $catTech = BlogCategory::where('slug', 'teknologi')->first();

        $blogs = [
            [
                'title' => '5 Cara Memilih Panel Solar yang Tepat untuk Rumah Anda',
                'slug' => '5-cara-memilih-panel-solar-rumah',
                'excerpt' => 'Panduan lengkap memilih panel solar berdasarkan kebutuhan, budget, dan lokasi geografis Anda.',
                'content' => '<p>Panel solar adalah investasi jangka panjang yang membutuhkan pertimbangan matang...</p>',
                'reading_time' => 5,
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'blog_category_id' => $catTips?->id,
                'author_id' => $author?->id,
            ],
            [
                'title' => 'Tren Kendaraan Listrik di Indonesia 2026: Peluang & Tantangan',
                'slug' => 'tren-kendaraan-listrik-indonesia-2026',
                'excerpt' => 'Industri EV Indonesia tumbuh pesat. Berikut analisis tren dan peluang untuk tahun 2026.',
                'content' => '<p>Industri kendaraan listrik Indonesia mengalami pertumbuhan signifikan...</p>',
                'reading_time' => 7,
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(5),
                'blog_category_id' => $catNews?->id,
                'author_id' => $author?->id,
            ],
            [
                'title' => 'Studi Kasus: Custom Battery 100kWh untuk Transportasi Tambang',
                'slug' => 'studi-kasus-custom-battery-100kwh-tambang',
                'excerpt' => 'Bagaimana KARTEKS mendesain battery pack 100kWh untuk truk operasional tambang di Sulawesi.',
                'content' => '<p>Project ini membutuhkan desain yang tahan terhadap getaran tinggi dan suhu ekstrem...</p>',
                'reading_time' => 8,
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(10),
                'blog_category_id' => BlogCategory::where('slug', 'studi-kasus')->first()?->id,
                'author_id' => $author?->id,
            ],
            [
                'title' => 'LiFePO4 vs Lithium-Ion: Mana yang Lebih Baik untuk Solar?',
                'slug' => 'lifepo4-vs-lithium-ion-solar',
                'excerpt' => 'Perbandingan mendalam kedua teknologi baterai untuk aplikasi solar energy storage.',
                'content' => '<p>Pemilihan teknologi baterai yang tepat sangat krusial untuk sistem solar...</p>',
                'reading_time' => 6,
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(14),
                'blog_category_id' => $catTech?->id,
                'author_id' => $author?->id,
            ],
        ];

        foreach ($blogs as $data) {
            Blog::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }
    }
}