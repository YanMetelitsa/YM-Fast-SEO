<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

printf( '<input type="%1$s" name="%2$s" id="%2$s" class="regular-text %3$s" value="%4$s" placeholder="%5$s">',
	esc_attr( $args[ 'type' ] ?? 'text' ),
	esc_attr( $args[ 'label_for' ] ),
	esc_attr( $args[ 'input-class' ] ?? '' ),
	esc_attr( YMFSEO_Settings::get_option( $args[ 'label_for' ] ) ),
	esc_attr( $args[ 'placeholder' ] ?? null ),
);