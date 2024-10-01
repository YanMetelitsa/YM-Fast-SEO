<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

printf( '<textarea name="%1$s" id="%1$s" rows="%3$s" cols="50">%2$s</textarea>',
	esc_attr( $args[ 'label_for' ] ),
	esc_textarea( YMFSEO_Settings::get_option( $args[ 'label_for' ] ) ),
	esc_attr( $args[ 'rows' ] ?? 4 ),
);

if ( isset( $args[ 'description' ] ) ) {
	printf( '<p class="description">%s</p>', esc_html( $args[ 'description' ] ) );
}
