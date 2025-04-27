<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($categories as $category)
        <url>
            <loc>{{ route('categories.story.show', $category->slug) }}</loc>
            <lastmod>{{ $category->updated_at ? $category->updated_at->toAtomString() : \Carbon\Carbon::now()->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
</urlset>