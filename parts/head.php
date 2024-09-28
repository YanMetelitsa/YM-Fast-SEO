<?php

/** Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

echo '<!-- YM Fast SEO v' . esc_html( YMFSEO_PLUGIN_DATA[ 'Version' ] ) . ' -->';

global $wp;

// Get meta fields
$meta_fields       = [];
$queried_object_id = get_queried_object_id();

if ( $queried_object_id ) {
	$meta_fields = YMFSEO::get_post_meta_fields( $queried_object_id );
} else {
	echo '<!-- Queried object not found -->';
}

$document_title = wp_get_document_title();

// Has checks
$has_meta_description = $meta_fields && $meta_fields[ 'description' ];
$has_meta_image_url   = $meta_fields && $meta_fields[ 'image_url' ];

// Integrations
$google_search_console_key = YMFSEO::get_option( 'google_search_console_key' );
$yandex_webmaster_key      = YMFSEO::get_option( 'yandex_webmaster_key' );

if ( $google_search_console_key ) {
	printf( '<meta name="google-site-verification" content="%s">', esc_attr( $google_search_console_key ) );
}
if ( $yandex_webmaster_key ) {
	printf( '<meta name="yandex-verification" content="%s">', esc_attr( $yandex_webmaster_key ) );
}

// Title
printf( '<meta name="title"         content="%s">', esc_attr( $document_title ) );
printf( '<meta property="og:title"  content="%s">', esc_attr( $document_title ) );
printf( '<meta name="twitter:title" content="%s">', esc_attr( $document_title ) );

// Description
if ( $has_meta_description ) {
	printf( '<meta name="description"         content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
	printf( '<meta property="og:description"  content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
	printf( '<meta name="twitter:description" content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
}

// Common
printf( '<meta property="og:site_name" content="%s">', esc_attr( get_bloginfo( 'name' ) ) );
printf( '<meta property="og:type"      content="%s">', esc_attr( is_front_page() ? 'website' : 'article' ) );
printf( '<meta property="og:locale"    content="%s">', esc_attr( get_locale() ) );

printf( '<meta name="twitter:card" content="%s">', esc_attr( YMFSEO::get_option( 'preview_size' ) ) );

// Canonical URL and pagination
if ( is_singular() ) {
	printf( '<meta property="og:url"  content="%s">', esc_url( wp_get_canonical_url() ) );
	printf( '<meta name="twitter:url" content="%s">', esc_url( wp_get_canonical_url() ) );
}
if ( get_query_var( 'paged', 0 ) ) {
	printf( '<link rel="prev" href="%s">', esc_url( get_previous_posts_page_link() ) );
}

// Preview image
if ( $has_meta_image_url ) {
	$image_size = getimagesize( $meta_fields[ 'image_url' ] );

	printf( '<meta property="og:image" content="%s">', esc_url( $meta_fields[ 'image_url' ] ) );
	if ( $image_size ) {
		printf( '<meta property="og:image:type"   content="%s">', esc_attr( $image_size[ 'mime' ] ) );
		printf( '<meta property="og:image:width"  content="%s">', esc_attr( $image_size[ 0 ] ) );
		printf( '<meta property="og:image:height" content="%s">', esc_attr( $image_size[ 1 ] ) );
	}

	printf( '<meta name="twitter:image" content="%s">', esc_url( $meta_fields[ 'image_url' ] ) );
	if ( $image_size ) {
		printf( '<meta name="twitter:image:type"   content="%s">', esc_attr( $image_size[ 'mime' ] ) );
		printf( '<meta name="twitter:image:width"  content="%s">', esc_attr( $image_size[ 0 ] ) );
		printf( '<meta name="twitter:image:height" content="%s">', esc_attr( $image_size[ 1 ] ) );
	}
}

// Schema.org
$schema_org = [
	'@context'   => 'https://schema.org',
	'@type'      => YMFSEO::$empty_meta_fields[ 'page_type' ],
	'url'        => home_url( $wp->request ),
	'name'       => $document_title,
	'inLanguage' => get_locale(),
	'isPartOf'   => [
		'@type'       => 'WebSite',
		'url'         => home_url(),
		'name'        => get_bloginfo( 'name' ),
		'description' => get_bloginfo( 'description' ),
		'inLanguage'  => get_locale(),
	],
];

if ( $queried_object_id ) {
	$schema_org[ 'datePublished' ] = get_the_date( 'c', $queried_object_id );
	$schema_org[ 'dateModified' ]  = get_the_modified_date( 'c', $queried_object_id );
}

if ( $meta_fields ) {
	if ( $schema_org[ '@type' ] !== $meta_fields[ 'page_type' ] ) {
		$schema_org[ '@type' ] = [
			$schema_org[ '@type' ],
			esc_html( $meta_fields[ 'page_type' ] ),
		];
	}
	if ( $has_meta_description ) {
		$schema_org[ 'description' ] = $meta_fields[ 'description' ];
	}
	if ( $has_meta_image_url ) {
		$schema_org[ 'image' ] = $meta_fields[ 'image_url' ];
	}
}

printf( '<script type="application/ld+json">%s</script>', wp_json_encode( $schema_org, JSON_UNESCAPED_UNICODE ) );

// Do user action
do_action( 'ymfseo_after_print_metas' );

echo '<!-- / YM Fast SEO -->';