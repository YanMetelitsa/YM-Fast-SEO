<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * YM Fast SEO class providing sanitation methods.
 */
class YMFSEO_Sanitizer {
	/**
	 * Sanitizes text field.
	 * 
	 * @param string $value Text value.
	 * 
	 * @return string Sanitized string.
	 */
	public static function sanitize_text_field ( string $value ) : string {
		$value = wp_unslash( $value );

		$value = wp_kses_post( $value );

		$value = normalize_whitespace( $value );

		$value = trim( $value );

		return $value;
	}
}