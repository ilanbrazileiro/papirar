<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>{{ route('site.sitemaps.pages') }}</loc>
    </sitemap>
    <sitemap>
        <loc>{{ route('site.sitemaps.subjects') }}</loc>
    </sitemap>
    <sitemap>
        <loc>{{ route('site.sitemaps.topics') }}</loc>
    </sitemap>
@for($page = 1; $page <= $questionPages; $page++)
    <sitemap>
        <loc>{{ route('site.sitemaps.questions', ['page' => $page]) }}</loc>
    </sitemap>
@endfor
    <sitemap>
        <loc>{{ route('site.sitemaps.courses') }}</loc>
    </sitemap>  
</sitemapindex>
