<?php

/** Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

printf( '<input type="text" name="%1$s" id="%1$s" class="regular-text" value="%2$s" placeholder="%3$s">',
	esc_attr( $args[ 'label_for' ] ),
	esc_attr( YMFSEO::get_option( str_replace( 'ymfseo_', '', $args[ 'label_for' ] ) ) ),
	esc_attr( $args[ 'placeholder' ] ?? null ),
);