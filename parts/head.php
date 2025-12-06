<?php

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

// Gets meta data.
$ymfseo_queried_object = get_queried_object();
$ymfseo_meta_fields    = new MetaFields();

$ymfseo_document_title = wp_get_document_title();
$ymfseo_site_name      = get_bloginfo( 'name' );
$ymfseo_site_locale    = get_locale();
$ymfseo_canonical_url  = wp_get_canonical_url();

$ymfseo_is_front_page = is_front_page();

$ymfseo_google_search_console_key = Settings::get_option( 'google_search_console_key' );
$ymfseo_yandex_webmaster_key      = Settings::get_option( 'yandex_webmaster_key' );
$ymfseo_bing_webmaster_tools_key  = Settings::get_option( 'bing_webmaster_tools_key' );

echo '<!-- YM Fast SEO v' . esc_html( YMFSEO_PLUGIN_DATA[ 'Version' ] ) . ' -->';

if ( ! $ymfseo_queried_object  ) {
	echo '<!-- Queried object not found -->';
}

// Head scripts.
if ( Checker::should_print_head_scripts() ) {
	echo Settings::get_option( 'head_scripts' ); // phpcs:ignore
}

// Integrations.
if ( $ymfseo_google_search_console_key ) {
	\printf( '<meta name="google-site-verification" content="%s">', esc_attr( $ymfseo_google_search_console_key ) );
}
if ( $ymfseo_yandex_webmaster_key ) {
	\printf( '<meta name="yandex-verification" content="%s">', esc_attr( $ymfseo_yandex_webmaster_key ) );
}
if ( $ymfseo_bing_webmaster_tools_key ) {
	\printf( '<meta name="msvalidate.01" content="%s">', esc_attr( $ymfseo_bing_webmaster_tools_key ) );
}

// Titles.
\printf( '<meta name="title"         content="%s">', esc_attr( $ymfseo_document_title ) );
\printf( '<meta property="og:title"  content="%s">', esc_attr( $ymfseo_document_title ) );
\printf( '<meta name="twitter:title" content="%s">', esc_attr( $ymfseo_document_title ) );

// Descriptions.
if ( $ymfseo_meta_fields->description ) {
	\printf( '<meta name="description"         content="%s">', esc_attr( $ymfseo_meta_fields->description ) );
	\printf( '<meta property="og:description"  content="%s">', esc_attr( $ymfseo_meta_fields->description ) );
	\printf( '<meta name="twitter:description" content="%s">', esc_attr( $ymfseo_meta_fields->description ) );
}

// Common meta tags.
\printf( '<meta name="apple-mobile-web-app-title" content="%s">', esc_attr( $ymfseo_site_name ) );
\printf( '<meta property="og:site_name"           content="%s">', esc_attr( $ymfseo_site_name ) );
\printf( '<meta property="og:type"                content="%s">', esc_attr( $ymfseo_is_front_page ? 'website' : 'article' ) );
\printf( '<meta property="og:locale"              content="%s">', esc_attr( $ymfseo_site_locale ) );

\printf( '<meta name="twitter:card" content="%s">', esc_attr( Settings::get_option( 'preview_size' ) ) );

if ( $ymfseo_queried_object && 'WP_Post' == get_class( $ymfseo_queried_object ) ) {
	\printf( '<meta property="article:published_time" content="%s">', esc_attr( get_the_date( 'c', $ymfseo_queried_object ) ) );
	\printf( '<meta property="article:modified_time"  content="%s">', esc_attr( get_the_modified_date( 'c', $ymfseo_queried_object ) ) );
}

// Canonical URLs.
if ( $ymfseo_canonical_url && Checker::is_current_page_has_canonical() ) {
	\printf( '<meta property="og:url"  content="%s">', esc_url( $ymfseo_canonical_url ) );
	\printf( '<meta name="twitter:url" content="%s">', esc_url( $ymfseo_canonical_url ) );
}

// Pagination URLs.
$ymfseo_prev_page_url = get_previous_posts_page_link();
$ymfseo_next_page_url = get_next_posts_page_link( $GLOBALS[ 'wp_query' ]->max_num_pages ?: 1 );

if ( get_query_var( 'paged', 0 ) && $ymfseo_prev_page_url ) {
	\printf( '<link rel="prev" href="%s">', esc_url( $ymfseo_prev_page_url ) );
}

if ( $ymfseo_next_page_url ) {
	\printf( '<link rel="next" href="%s">', esc_url( $ymfseo_next_page_url ) );
}

// Preview image.
if ( $ymfseo_meta_fields->image_uri ) {
	$ymfseo_image_size = getimagesize( $ymfseo_meta_fields->image_uri );

	\printf( '<meta property="og:image" content="%s">', esc_url( $ymfseo_meta_fields->image_uri ) );
	if ( $ymfseo_image_size ) {
		\printf( '<meta property="og:image:type"   content="%s">', esc_attr( $ymfseo_image_size[ 'mime' ] ) );
		\printf( '<meta property="og:image:width"  content="%s">', esc_attr( $ymfseo_image_size[ 0 ] ) );
		\printf( '<meta property="og:image:height" content="%s">', esc_attr( $ymfseo_image_size[ 1 ] ) );
	}

	\printf( '<meta name="twitter:image" content="%s">', esc_url( $ymfseo_meta_fields->image_uri ) );
	if ( $ymfseo_image_size ) {
		\printf( '<meta name="twitter:image:type"   content="%s">', esc_attr( $ymfseo_image_size[ 'mime' ] ) );
		\printf( '<meta name="twitter:image:width"  content="%s">', esc_attr( $ymfseo_image_size[ 0 ] ) );
		\printf( '<meta name="twitter:image:height" content="%s">', esc_attr( $ymfseo_image_size[ 1 ] ) );
	}
}

// Schema.org JSON-LD.
$ymfseo_schema_org = $ymfseo_meta_fields->get_schema_org( $ymfseo_queried_object );
\printf( '<script type="application/ld+json">%s</script>',
	wp_json_encode( $ymfseo_schema_org, JSON_UNESCAPED_UNICODE ),
);

// Does user action.
do_action( 'ymfseo_after_print_metas' );

// Debugs queried object data.
if ( $ymfseo_queried_object ) {
	\printf( '<!-- / YM Fast SEO | %s – %s -->',
		// phpcs:ignore
		...match ( \get_class( $ymfseo_queried_object ) ) {
			'WP_Post'      => [ 'Post ID',   esc_html( $ymfseo_queried_object->ID ) ],
			'WP_Post_Type' => [ 'Post Type', esc_html( $ymfseo_queried_object->name ) ],
			'WP_Term'      => [ 'Term ID',   esc_html( $ymfseo_queried_object->term_id ) ],
			'WP_User'      => [ 'User ID',   esc_html( $ymfseo_queried_object->ID ) ],
		},
	);
}