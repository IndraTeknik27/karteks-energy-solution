<?php
    /**
     * SEO meta tags component.
     * Usage:
     *   <x-meta :model="$product" />
     *   <x-meta :meta="['title' => '...', 'description' => '...', 'image' => '...']" />
     *
     * Slot opsional: <x-slot:head> untuk additional head elements (JSON-LD, dst.)
     */
    $meta = $meta ?? app(\App\Services\V1\SeoService::class)->generateMeta($model ?? null);
    $title = $meta['title'] ?? config('karteks.company.name');
    $description = $meta['description'] ?? '';
    $keywords = $meta['keywords'] ?? null;
    $canonical = $meta['canonical'] ?? url()->current();
    $image = $meta['image'] ?? null;
    $ogType = $meta['og_type'] ?? 'website';
    $twitterCard = $meta['twitter_card'] ?? 'summary_large_image';
    $siteName = $meta['site_name'] ?? config('karteks.company.name');
    $locale = $meta['locale'] ?? 'id_ID';
    $twitterHandle = $meta['twitter_handle'] ?? config('karteks.seo.twitter_handle');
?>


<title><?php echo e($title); ?></title>
<meta name="title" content="<?php echo e($title); ?>">
<meta name="description" content="<?php echo e($description); ?>">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($keywords): ?>
    <meta name="keywords" content="<?php echo e($keywords); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<meta name="author" content="<?php echo e($siteName); ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?php echo e($canonical); ?>">


<meta property="og:type" content="<?php echo e($ogType); ?>">
<meta property="og:url" content="<?php echo e($canonical); ?>">
<meta property="og:title" content="<?php echo e($title); ?>">
<meta property="og:description" content="<?php echo e($description); ?>">
<meta property="og:site_name" content="<?php echo e($siteName); ?>">
<meta property="og:locale" content="<?php echo e($locale); ?>">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image): ?>
    <meta property="og:image" content="<?php echo e($image); ?>">
    <meta property="og:image:alt" content="<?php echo e($title); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($ogType ?? null) === 'product'): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($meta['product:price:amount'])): ?>
        <meta property="product:price:amount" content="<?php echo e($meta['product:price:amount']); ?>">
        <meta property="product:price:currency" content="<?php echo e($meta['product:price:currency']); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($meta['product:availability'])): ?>
        <meta property="product:availability" content="<?php echo e($meta['product:availability']); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($ogType ?? null) === 'article'): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($meta['article:published_time'])): ?>
        <meta property="article:published_time" content="<?php echo e($meta['article:published_time']); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($meta['article:author'])): ?>
        <meta property="article:author" content="<?php echo e($meta['article:author']); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($meta['article:section'])): ?>
        <meta property="article:section" content="<?php echo e($meta['article:section']); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<meta name="twitter:card" content="<?php echo e($twitterCard); ?>">
<meta name="twitter:url" content="<?php echo e($canonical); ?>">
<meta name="twitter:title" content="<?php echo e($title); ?>">
<meta name="twitter:description" content="<?php echo e($description); ?>">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image): ?>
    <meta name="twitter:image" content="<?php echo e($image); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($twitterHandle): ?>
    <meta name="twitter:site" content="<?php echo e($twitterHandle); ?>">
    <meta name="twitter:creator" content="<?php echo e($twitterHandle); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<meta name="theme-color" content="#10b981">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/components/meta.blade.php ENDPATH**/ ?>