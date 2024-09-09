<?php

/** Exit if accessed directly */
if ( !defined( 'ABSPATH' ) ) exit;

$queried_object_id = get_queried_object_id();

if ( $queried_object_id ) {
	// Get fields
	$meta_fields = ymfseo_get_post_meta_fields( $queried_object_id );
	
	if ( !$meta_fields[ 'title' ] )       $meta_fields[ 'title' ]       = get_the_title( $queried_object_id );
	if ( !$meta_fields[ 'description' ] ) $meta_fields[ 'description' ] = get_the_excerpt( $queried_object_id );
	
	if ( has_post_thumbnail() ) $meta_fields[ 'image_url' ] = get_the_post_thumbnail_url( $queried_object_id, 'full' );

	$meta_fields = apply_filters( 'ymfseo_meta_fields', $meta_fields );

	// Print

	echo '<!-- YM Fast SEO -->';

	// Common
	if ( $meta_fields[ 'title' ] ) {
		printf( '<meta name="title" content="%s">', esc_attr( $meta_fields[ 'title' ] ) );
	}
	if ( $meta_fields[ 'description' ] ) {
		printf( '<meta name="description" content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
	}
	if ( $meta_fields[ 'keywords' ] ) {
		printf( '<meta name="keywords" content="%s">', esc_attr( $meta_fields[ 'keywords' ] ) );
	}
	if ( $meta_fields[ 'canonical_url' ] ) {
		printf( '<link rel="canonical" href="%s">', esc_attr( $meta_fields[ 'canonical_url' ] ) );
	}

	// Open Graph
	printf( '<meta property="og:site_name" content="%s">', esc_attr( get_bloginfo( 'sitename' ) ) );
	printf( '<meta property="og:locale" content="%s">', esc_attr( get_locale() ) );
	if ( $meta_fields[ 'title' ] ) {
		printf( '<meta property="og:title" content="%s">', esc_attr( $meta_fields[ 'title' ] ) );
	}
	if ( $meta_fields[ 'description' ] ) {
		printf( '<meta property="og:description" content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
	}
	if ( $meta_fields[ 'canonical_url' ] ) {
		printf( '<meta property="og:url" content="%s">', esc_attr( $meta_fields[ 'canonical_url' ] ) );
	}
	if ( $meta_fields[ 'image_url' ] ) {
		printf( '<meta property="og:image" content="%s">', esc_attr( $meta_fields[ 'image_url' ] ) );
	}

	// Twitter
	if ( $meta_fields[ 'title' ] ) {
		printf( '<meta name="twitter:title" content="%s">', esc_attr( $meta_fields[ 'title' ] ) );
	}
	if ( $meta_fields[ 'description' ] ) {
		printf( '<meta name="twitter:description" content="%s">', esc_attr( $meta_fields[ 'description' ] ) );
	}
	if ( $meta_fields[ 'image_url' ] ) {
		printf( '<meta name="twitter:image" content="%s">', esc_attr( $meta_fields[ 'image_url' ] ) );
	}

	do_action( 'ymfseo_after_print_metas' );

	echo '<!-- YM Fast SEO -->';
}