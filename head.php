<?php

$queried_object_id = get_queried_object_id();

if ( $queried_object_id ) {
	// Get fields
	$seo_fields = ymfseo_get_post_meta_fields( $queried_object_id );

	$title         = $seo_fields[ 'title' ];
	$description   = $seo_fields[ 'description' ];
	$keywords      = $seo_fields[ 'keywords' ];
	$canonical_url = $seo_fields[ 'canonical_url' ];
	
	if ( !$title )       $title       = get_the_title( $queried_object_id );
	if ( !$description ) $description = get_the_excerpt( $queried_object_id );

	// Print

	// Common
	if ( $title ) {
		printf( '<meta name="title" content="%s">', esc_attr( $title ) );
	}
	if ( $description ) {
		printf( '<meta name="description" content="%s">', esc_attr( $description ) );
	}
	if ( $keywords ) {
		printf( '<meta name="keywords" content="%s">', esc_attr( $keywords ) );
	}
	if ( $canonical_url ) {
		printf( '<link rel="canonical" href="%s">', esc_attr( $canonical_url ) );
	}

	// Open Graph
	printf( '<meta property="og:site_name" content="%s">', esc_attr( get_bloginfo( 'sitename' ) ) );
	printf( '<meta property="og:locale" content="%s">', esc_attr( get_locale() ) );
	if ( $title ) {
		printf( '<meta property="og:title" content="%s">', esc_attr( $title ) );
	}
	if ( $description ) {
		printf( '<meta property="og:description" content="%s">', esc_attr( $description ) );
	}
	if ( $canonical_url ) {
		printf( '<meta property="og:url" content="%s">', esc_attr( $canonical_url ) );
	}

	do_action( 'ymfseo_after_print_metas' );
}