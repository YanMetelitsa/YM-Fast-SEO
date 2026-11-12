=== YM Fast SEO ===
Contributors: yanmetelitsa
Tags: seo, search, optimization
Stable tag: 4.2.1
Requires PHP: 7.4
Requires at least: 5.5.1
Tested up to: 7.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Enhance your website with powerful, intuitive, and user-friendly SEO tools.

== Description ==

YM Fast SEO enhances your website with powerful, intuitive, and user-friendly SEO tools. In today’s digital world, SEO is essential for driving organic traffic and improving your website’s visibility on search engines.

With YM Fast SEO, you can easily manage important aspects of search optimization without unnecessary complexity. Unlike bulky alternatives, this lightweight plugin is efficient, flexible, and easy to extend, allowing you to keep full control over your website’s performance.

=== Features ===

* Lightweight & Extendable
* Meta Tag Fields for Posts and Taxonomies
* Automated Open Graph, Twitter Card, and Schema.org Markups
* AI Crawlers Support
* Automatic IndexNow Sending
* Quick SEO Analysis and Site SEO Health Page
* Services Integration (Analytics + Webmasters)
* SVG Favicon Support
* `Robots.txt` Editor
* Other Useful Tools & Improvements

=== Documentation ===

See [the official documentation](https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation) for a description of actions and filters.

== Screenshots ==

1. Gutenberg meta fields box.
2. Classic editor meta fields box.
3. Taxonomy meta fields box.
4. Settings screen.
5. Site SEO Health screen.

== Changelog ==

= 4.2.1 =

*Fixed*

* OpenGraph preview image output when `allow_url_fopen` is disallowed
* Sending noindex posts to IndexNow
* Small improvements and minor fixes

= 4.2.0 =

*Added*

* Archive URLs to corresponding Post Type sitemaps

*Fixed*

* Missing meta tags on archive pages
* Small improvements and minor fixes

= 4.1.2 =

*Added*

* "Hide Users REST API" settings option

*Fixed*

* Small improvements and minor fixes

= 4.1.0 =

*Added*

* Meta tags for archive pages
* Length indicators in quick edit
* [`ymfseo_print_head_scripts`](https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation#ymfseo_print_head_scripts) filter
* Checking website optimization for working with AI crawlers
* `llms.txt` and `llms-full.txt` files generation function
* [`ymfseo_{$post_type}_posts_llms_txt_custom_fields`](https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation#ymfseo_post_type_llms_txt_custom_fields) filter

*Fixed*

* SVG favicons low resolution and ICO sizes

= 4.0.0 =

*Added*

* Wide support for SVG site icons. If you use an SVG site icon (requires a [third-party plugin](https://wordpress.org/plugins/search/SVG/)), it will now be rendered according to modern web standards

*Fixed*

* SEO check when unnecessary title parts are enabled

= 3.4.1 =

*Added*

* [`ymfseo_{$taxonomy}_taxonomy_tags`](https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation#ymfseo_taxonomy_tags) filter that allows you to create custom tags for taxonomy

= 3.4.0 =

*Added*

* [`ymfseo_{$post_type}_posts_tags`](https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation#ymfseo_post_type_tags) filter that allows you to create custom tags for post types

*Fixed*

* WooCommerce SEO column

= 3.3.3 =

*Fixed*

* Open Graph/Twitter Cards canonical URL
* IndexNow sending for `noindex` taxonomies
* Posts auto descriptions length

= 3.3.2 =

*Fixed*

* Text fields sanitization now preserves custom tags

= 3.3.1 =

*Added*

* Post meta fields placeholders

*Fixed*

* Fatal error `Uncaught TypeError: YMFSEO_Meta_Fields` on taxonomy pages

= 3.3.0 =

*Added*

* Splitting the settings page into tabs
* Ability to use shortcodes in meta fields
* `%tagline%` tag and [`ymfseo_tags`](https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation#ymfseo_tags) filter

*Fixed*

* Settings page UI

= 3.2.8 =

*Added*

* `Representative's Image` media state

*Fixed*

* Now `noindex` taxonomies are not displayed in sitemaps

= 3.2.7 =

*Added*

* Taxonomy default "Indexing" settings option
* Default terms Title & Description values in placeholders

*Fixed*

* Quick edit SEO fields

= 3.2.6 =

*Added*

* "Preview Image" media state

*Fixed*

* Gutenberg meta box style

= 3.2.4 =

*Added*

* Quick edit SEO fields

*Fixed*

* Editor Role capabilities

= 3.2.3 =

*Fixed*

* Now `noindex` pages are not displayed in sitemaps

= 3.2.0 =

*Added*

* Settings page UI
* Admin Bar menu
* "IndexNow Sending" option

*Fixed*

* WordPress 6.7.0 `get_plugin_data()`
* IndexNow sending logic

= 3.1.3 =

*Added*

* [`ymfseo_title_separator`](https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation#ymfseo_title_separator) filter

*Fixed*

* Terms pages now have the "CollectionPage" type as default
* Limit resending URL to IndexNow within 10 minutes

= 3.1.2 =

*Fixed*

* Fatal error on plugin activation due to `add_cap()` function

= 3.1.1 =

*Fixed*

* Activation/Deactivation hook
* Roles capabilities

= 3.1.0 =

*Added*

* "Head Scripts" editor
* "Hide Users Sitemap" option
* `<link rel="next">` tag for pagination pages

*Fixed*

* User page meta tags
* Multisite `robots.txt` edit
* Representative image
* IndexNow
* Empty IndexNow table on the Site SEO Health page
* Settings page UI

= 3.0.0 =

*Added*

* IndexNow system
* Site SEO Health page
* Logs system
* Representative organization types

= 2.2.1 =

*Added*

* "Clear Excerpts" option
* Additional"`Save" settings page button
* Version of the current theme now appends to the preview image URL

= 2.2.0 =

*Added*

* Length check for taxonomy meta fields
* "Online Store" organization type
* Representative "Phone", "City", "Address", "Region", "Postal Code", "Image" options

*Fixed*

* Taxonomy meta fields security

= 2.1.0 =

*Added*

* Taxonomy meta fields
* SEO Editor role and capabilities
* Bing Webmaster Tools integration

*Fixed*

* JSON-LD output escaping

= 2.0.1 =

*Fixed*

* Multisite sitemaps in `robots.txt`
* Multisite `robots.txt` edit
* UTF-8 symbols counting in Basic SEO checks

= 2.0.0 =

*Added*

* Settings page UI
* "Post Types" and "Taxonomies" settings blocks
* "Representative" settings block

*Fixed*

* Performance optimization
* Database space optimization

= 1.2.1 =

*Fixed*

* Schema.org encoding
* Undefined `$preview_image_uri`
* Translations

= 1.2.0 =

*Added*

* "Page Type" meta field
* "Clear Titles", "Title Separator", "Preview Image" settings options
* Ability to disallow indexing for individual pages/posts
* Webmaster services integrations

*Fixed*

* Meta fields caching
* `robots.txt` editor

= 1.1.0 =

*Added*

* `robots.txt` editor
* Basic SEO checks
* Meta fields length indicator

*Fixed*

* `robots` meta tag

= 1.0.0 =

* Initial release