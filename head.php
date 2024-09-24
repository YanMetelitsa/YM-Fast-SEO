<?php

/** Exit if accessed directly */
if ( !defined( 'ABSPATH' ) ) exit;

echo '<!-- YM Fast SEO v' . YMFSEO_PLUGIN_DATA[ 'Version' ] . ' -->';

// Common
printf( '<meta property="og:site_name" content="%s">', esc_attr( get_bloginfo( 'sitename' ) ) );
printf( '<meta property="og:locale"    content="%s">', esc_attr( get_locale() ) );
printf( '<meta property="og:url"       content="%s">', esc_attr( wp_get_canonical_url() ) );
printf( '<meta name="twitter:card"     content="%s">', 'summary_large_image' );
printf( '<meta name="twitter:url"      content="%s">', esc_attr( wp_get_canonical_url() ) );

// Title
$document_title = wp_get_document_title();

printf( '<meta name="title"         content="%s">', esc_attr( $document_title ) );
printf( '<meta property="og:title"  content="%s">', esc_attr( $document_title ) );
printf( '<meta name="twitter:title" content="%s">', esc_attr( $document_title ) );

// Get queried object ID
$queried_object_id = get_queried_object_id();

if ( $queried_object_id ) {
	// Get fields
	$meta_fields = YMFSEO::get_post_meta_fields( $queried_object_id );	

	// Description
	if ( $meta_fields[ 'description' ] ) {
		printf( '<meta name="description"         content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
		printf( '<meta property="og:description"  content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
		printf( '<meta name="twitter:description" content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
	}

	// Preview image
	if ( $meta_fields[ 'image_url' ] ) {
		printf( '<meta property="og:image"  content="%s">', esc_attr( $meta_fields[ 'image_url' ] ) );
		printf( '<meta name="twitter:image" content="%s">', esc_attr( $meta_fields[ 'image_url' ] ) );

		$image_size = getimagesize( $meta_fields[ 'image_url' ] );

		if ( $image_size ) {
			printf( '<meta property="og:image:width"  content="%s">', esc_attr( $image_size[ 0 ] ) );
			printf( '<meta property="og:image:height" content="%s">', esc_attr( $image_size[ 1 ] ) );

			printf( '<meta name="twitter:image:width"  content="%s">', esc_attr( $image_size[ 0 ] ) );
			printf( '<meta name="twitter:image:height" content="%s">', esc_attr( $image_size[ 1 ] ) );
		}
	}

	// Schema.org
	if ( $meta_fields[ 'description' ] ) {
		$schema_org = [
			'@context'    => 'https://schema.org',
			'@type'       => 'WebPage',
			'headline'    => $document_title,
			'description' => $meta_fields[ 'description' ],
		];

		if ( $meta_fields[ 'image_url' ] ) {
			$schema_org[ 'image' ] = $meta_fields[ 'image_url' ];
		}

		echo '<script type="application/ld+json">';
		echo wp_json_encode( $schema_org, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		echo '</script>';
	}

	// Do user action
	do_action( 'ymfseo_after_print_metas' );
} else {
	echo '<!-- Queried object not found -->';
}

echo '<!-- / YM Fast SEO -->';