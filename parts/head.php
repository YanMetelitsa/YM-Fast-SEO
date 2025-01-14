<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Gets meta data.
$queried_object = get_queried_object();

$meta_fields    = new YMFSEO_Meta_Fields();
$document_title = wp_get_document_title();
$site_name      = get_bloginfo( 'name' );
$site_locale    = get_locale();
$canonical_url  = wp_get_canonical_url();
$is_front_page  = is_front_page();

$google_search_console_key = YMFSEO_Settings::get_option( 'google_search_console_key' );
$yandex_webmaster_key      = YMFSEO_Settings::get_option( 'yandex_webmaster_key' );
$bing_webmaster_tools_key  = YMFSEO_Settings::get_option( 'bing_webmaster_tools_key' );

echo '<!-- YM Fast SEO v' . esc_html( YMFSEO_PLUGIN_DATA[ 'Version' ] ) . ' -->';

if ( ! $queried_object  ) {
	echo '<!-- Queried object not found -->';
}

// Head scripts.
$head_scripts = YMFSEO_Settings::get_option( 'head_scripts' );

if ( $head_scripts ) {
	$only_visitors_head_scripts = YMFSEO_Settings::get_option( 'head_scripts_only_visitors' );

	if ( ! $only_visitors_head_scripts || ! is_user_logged_in() ) {
		echo $head_scripts;
	}
}

// Integrations.
if ( $google_search_console_key ) {
	printf( '<meta name="google-site-verification" content="%s">', esc_attr( $google_search_console_key ) );
}
if ( $yandex_webmaster_key ) {
	printf( '<meta name="yandex-verification" content="%s">', esc_attr( $yandex_webmaster_key ) );
}
if ( $bing_webmaster_tools_key ) {
	printf( '<meta name="msvalidate.01" content="%s">', esc_attr( $bing_webmaster_tools_key ) );
}

// Titles.
printf( '<meta name="title"         content="%s">', esc_attr( $document_title ) );
printf( '<meta property="og:title"  content="%s">', esc_attr( $document_title ) );
printf( '<meta name="twitter:title" content="%s">', esc_attr( $document_title ) );

// Descriptions.
if ( $meta_fields->description ) {
	printf( '<meta name="description"         content="%s">', esc_attr( $meta_fields->description ) );
	printf( '<meta property="og:description"  content="%s">', esc_attr( $meta_fields->description ) );
	printf( '<meta name="twitter:description" content="%s">', esc_attr( $meta_fields->description ) );
}

// Common meta tags.
printf( '<meta property="og:site_name" content="%s">', esc_attr( $site_name ) );
printf( '<meta property="og:type"      content="%s">', esc_attr( $is_front_page ? 'website' : 'article' ) );
printf( '<meta property="og:locale"    content="%s">', esc_attr( $site_locale ) );

printf( '<meta name="twitter:card" content="%s">', esc_attr( YMFSEO_Settings::get_option( 'preview_size' ) ) );

if ( $queried_object && 'WP_Post' == get_class( $queried_object ) ) {
	printf( '<meta property="article:published_time" content="%s">', esc_attr( get_the_date( 'c', $queried_object ) ) );
	printf( '<meta property="article:modified_time"  content="%s">', esc_attr( get_the_modified_date( 'c', $queried_object ) ) );
}

// Canonical URL and pagination.
if ( is_singular() ) {
	printf( '<meta property="og:url"  content="%s">', esc_url( $canonical_url ) );
	printf( '<meta name="twitter:url" content="%s">', esc_url( $canonical_url ) );
}

$prev_page_url = get_previous_posts_page_link();
$next_page_url = get_next_posts_page_link( $GLOBALS[ 'wp_query' ]->max_num_pages ?: 1 );

if ( get_query_var( 'paged', 0 ) && $prev_page_url ) {
	printf( '<link rel="prev" href="%s">', esc_url( $prev_page_url ) );
}

if ( $next_page_url ) {
	printf( '<link rel="next" href="%s">', esc_url( $next_page_url ) );
}

// Preview image.
if ( $meta_fields->image_uri ) {
	$image_size = getimagesize( $meta_fields->image_uri );

	printf( '<meta property="og:image" content="%s">', esc_url( $meta_fields->image_uri ) );
	if ( $image_size ) {
		printf( '<meta property="og:image:type"   content="%s">', esc_attr( $image_size[ 'mime' ] ) );
		printf( '<meta property="og:image:width"  content="%s">', esc_attr( $image_size[ 0 ] ) );
		printf( '<meta property="og:image:height" content="%s">', esc_attr( $image_size[ 1 ] ) );
	}

	printf( '<meta name="twitter:image" content="%s">', esc_url( $meta_fields->image_uri ) );
	if ( $image_size ) {
		printf( '<meta name="twitter:image:type"   content="%s">', esc_attr( $image_size[ 'mime' ] ) );
		printf( '<meta name="twitter:image:width"  content="%s">', esc_attr( $image_size[ 0 ] ) );
		printf( '<meta name="twitter:image:height" content="%s">', esc_attr( $image_size[ 1 ] ) );
	}
}

// Schema.org JSON-LD.
$schema_org = YMFSEO_Schema::build( $meta_fields, $queried_object );
printf( '<script type="application/ld+json">%s</script>',
	wp_json_encode( $schema_org, JSON_UNESCAPED_UNICODE ),
);

// Does user action.
do_action( 'ymfseo_after_print_metas' );

// Debugs queried object data.
if ( $queried_object ) {
	printf( '<!-- %s-%s -->',
		...match ( get_class( $queried_object ) ) {
			'WP_Post'      => [ 'P',  esc_html( $queried_object->ID ) ],
			'WP_Post_Type' => [ 'PT', esc_html( $queried_object->name ) ],
			'WP_Term'      => [ 'T',  esc_html( $queried_object->term_id ) ],
			'WP_User'      => [ 'U',  esc_html( $queried_object->ID ) ],
		},
	);
}

echo '<!-- / YM Fast SEO -->';