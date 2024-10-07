<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Default values.
$robots_txt_content     = '';
$robots_txt_placeholder = '';

// Sets robots.txt path.
$robots_txt_path = home_url( 'robots.txt' );

if ( YMFSEO::is_subdir_multisite() ) {
	$robots_txt_path = get_home_url( get_main_site_id(), 'robots.txt' );
}

// Gets robots.txt content.
$response = wp_remote_get( $robots_txt_path );

if ( is_wp_error ( $response ) || $response[ 'response' ][ 'code' ] != 200 ) {
	$robots_txt_placeholder = __( 'Error loading robots.txt file', 'ym-fast-seo' );
} else {
	$robots_txt_content = wp_remote_retrieve_body( $response );
}

printf( '<textarea name="%1$s" id="%1$s" class="code" rows="8" cols="50" placeholder="%2$s">%3$s</textarea>',
	esc_attr( $args[ 'label_for' ] ),
	esc_attr( $robots_txt_placeholder ),
	esc_textarea( $robots_txt_content ),
);

printf( '<p class="description">%s</p>', esc_html__( 'To restore the default value, clear this field and save.', 'ym-fast-seo' ) );
if ( YMFSEO::is_subdir_multisite() ) {
	printf( '<p class="description">%s</p>', esc_html__( 'A network of sites using the subdirectory structure shares a single robots.txt file.', 'ym-fast-seo' ) );
}