# UltraNet Security SEO audit — 22 September 2026

Scope: public PHP templates, catalogue discovery, indexing, metadata, sitemap/robots, public error responses, image delivery and deployment. This is a technical SEO audit, not a claim of Google rankings or a penetration test.

| Confirmed issue | Correction |
| --- | --- |
| HTTP homepage served independently of HTTPS | Canonical HTTPS/domain redirects; POST redirects preserve the request method; proxy HTTPS avoids loops |
| Page 2+ canonical pointed at page 1 | Each valid page has its own canonical and page-specific title; invalid pages return 404 |
| Branded series unintentionally noindexed | Real series are indexable; search, arbitrary filters and empty listings remain noindex |
| Sitemap and category URLs disagreed | Shared hierarchy URL builder, legacy child-category redirects and canonical breadcrumb links |
| Unknown series silently returned 200 | Missing or mismatched category/series/brand returns 404 |
| Products assigned directly to a parent could become unreachable | Explicit direct-product links alongside the category/brand/series flow |
| Search fetched every matching product | Search alias redirects to the shared 24-item paginated catalogue |
| Imported price 0 appeared free or 100% discounted | Price on request; omit misleading Offer and sale badges until a positive price is entered |
| Sitemap claimed every static page changed daily | Omit unsupported dates; retain valid product modification dates; exclude inactive products and empty categories |
| Homepage's H1 did not name the service or city | CCTV Installation in Karachi |
| Generic fallback product descriptions could repeat | Include the product name; preserve explicit admin metadata |
| Large homepage service/about photos | Build bounded WebP copies from live originals; preserve original files and fallback URLs |
| Content hidden if JavaScript/storage/observer failed | Visible by default; optional animations; defensive theme/gallery scripts |
| Product gallery eagerly loaded every full image | First image prioritized, other images and video lazy loaded, image dimensions and video title added |
| Database outage returned a normal page and exposed error details | 503, Retry-After and a generic production response |
| Private estimate responses could be cached | Private/no-store headers and HTTP noindex, including missing estimates; mobile viewport |
| Placeholder footer social links pointed to # | Remove nonfunctional links; retain WhatsApp |
| LocalBusiness coordinates were generic city-centre coordinates | Remove unverified coordinates and an unsupported fixed price range |
| index.php and product.php aliases duplicated public URLs | Permanent redirects to homepage and pretty product URLs |
| Legacy indexed /packages/ returned 404 | Deployment adds a narrowly scoped redirect to calculator.php, preserving hosting rules and restoring them if validation fails |
| Deployment used an unsupported sync-ignored setting | Use the FTP action's supported exclude input; preserve uploads/images/.env and exclude tests/tools |

Validation: PHP syntax across the repository, existing budget-package regressions, real MySQL catalogue fixtures, HTTP assertions covering pagination/indexability/schema/404/private estimates, and all 52 fixture sitemap targets. Dedicated canonical-origin checks cover HTTPS, www, proxy headers and POST redirects. Live deployment publishes a read-only crawl report as a GitHub Actions artifact, including every sitemap URL, duplicate metadata and first-party image checks.

The initial live check returned 27 URLs from both sitemap.xml and sitemap.php. This is the current publicly advertised URL count, not the admin product total; inactive products are intentionally excluded. No production product data was changed by this audit. Original-photo compression measured locally: 10,305,319 bytes to 570,014 bytes; production figures are logged during deployment.

Remaining verification outside the code audit:

- Search Console indexing, impressions, query rankings and field Core Web Vitals require access to the corresponding Google property. No such results are claimed here.
- Owner should verify existing marketing claims (authorized/certified status, years, client counts, warranty, opening hours and package prices). These business facts cannot be established from source code alone.
- Product records still need genuine images and positive prices where missing. The site does not invent them or publish fake offers/reviews.
- Hosting rewrite configuration remains server-managed; the deployment only adds the marked legacy-package rule and preserves the rest.

Reference guidance: [Google pagination](https://developers.google.com/search/docs/specialty/ecommerce/pagination-and-incremental-page-loading), [Product structured data](https://developers.google.com/search/docs/appearance/structured-data/product-snippet), [Sitemap guidance](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap), [noindex](https://developers.google.com/search/docs/crawling-indexing/block-indexing).
