<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

printf( '<input type="%1$s" name="%2$s" id="%2$s" autocomplete="%3$s" class="regular-text %4$s" placeholder="%5$s" value="%6$s" %7$s>',
	esc_attr( $args[ 'type' ] ?? 'text' ),
	esc_attr( $args[ 'label_for' ] ),
	esc_attr( $args[ 'autocomplete' ] ?? '' ),
	esc_attr( $args[ 'input-class' ] ?? '' ),
	esc_attr( $args[ 'placeholder' ] ?? null ),
	esc_attr( YMFSEO_Settings::get_option( $args[ 'label_for' ] ) ),
	$args[ 'readonly' ] ?? false ? 'readonly' : '',
);

if ( isset( $args[ 'description' ] ) ) {
	printf( '<p class="description">%s</p>', wp_kses_post( $args[ 'description' ] ) );
}