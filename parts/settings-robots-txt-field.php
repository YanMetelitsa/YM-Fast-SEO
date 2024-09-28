<?php

/** Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

$robots_txt_content     = '';
$robots_txt_placeholder = '';

$response = wp_remote_get( home_url( 'robots.txt' ) );

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