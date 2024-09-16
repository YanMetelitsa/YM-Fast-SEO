<?php

/** Exit if accessed directly */
if ( !defined( 'ABSPATH' ) ) exit;

/**
 *  Main YM Fast SEO class.
 */
class YMFSEO {
	/**
	 * Tags for replace in meta fields.
	 * 
	 * @var string[]
	 */
	public static array $replace_tags = [];

	/**
	 * Default meta fields values.
	 * 
	 * @var array
	 */
	public static array $default_meta_fields = [
		'title'            => null,
		'description'      => null,
		'keywords'         => null,
		'canonical_url'    => null,
	];

	/**
	 * Inits YMFSEO.
	 */
	public static function init () : void {
		self::$replace_tags = [
			'{site_name}' => get_bloginfo( 'name' ),
			'{base_url}'  => home_url(),
			'{separator}' => '–',
		];
	}

	/**
	 * Returns array of public post types.
	 * 
	 * @return string[] Public post types.
	 */
	public static function get_public_post_types () : array {
		$public_post_types = get_post_types([
			'public' => true,
		], 'names' );

		unset( $public_post_types[ 'attachment' ] );

		return $public_post_types;
	}

	/**
	 * Returns post meta fields.
	 * 
	 * @param int  $post_id Post ID.
	 * @param bool $raw     Set false to get ready to print meta fields values.
	 * 
	 * @return array Meta fields values.
	 */
	public static function get_post_meta_fields ( int $post_id, bool $raw = true ) : array {
		$meta_fields = get_post_meta( $post_id, 'ymfseo_fields', true );
		$meta_fields = !is_array( $meta_fields ) || empty( $meta_fields ) ? [] : $meta_fields;
		$meta_fields = wp_parse_args( $meta_fields, self::$default_meta_fields );

		if ( !$raw ) {
			// Set some defaults
			if ( !$meta_fields[ 'title' ] )       $meta_fields[ 'title' ]       = get_the_title( $post_id );
			if ( !$meta_fields[ 'description' ] ) $meta_fields[ 'description' ] = get_the_excerpt( $post_id );
			if ( has_post_thumbnail( $post_id ) ) $meta_fields[ 'image_url' ]   = get_the_post_thumbnail_url( $post_id, 'full' );
		
			// Apply user filter
			$meta_fields = apply_filters( 'ymfseo_meta_fields', $meta_fields );

			// Replace tags
			foreach ( $meta_fields as $key => $meta_field ) {
				foreach ( YMFSEO::$replace_tags as $tag => $value ) {
					$meta_fields[ $key ] = str_replace( $tag, $value, $meta_fields[ $key ] );
				}
			}
		}

		return $meta_fields;
	}

	/**
	 * Sanitizes the value of the text field before saving.
	 * 
	 * @param string $str String to sanitize.
	 * 
	 * @return string Sanitized string.
	 */
	public static function sanitize_text_field ( string $str ) : string {
		$str = wp_unslash( $str );
		$str = sanitize_text_field( $str );

		return $str;
	}
}