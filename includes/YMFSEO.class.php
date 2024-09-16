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
	 * Emapty meta fields values.
	 * 
	 * @var array
	 */
	public static array $empty_meta_fields = [
		'title'         => null,
		'description'   => null,
		'canonical_url' => null,
		'keywords'      => null,
	];

	/**
	 * Meta fields cached value.
	 * 
	 * @var array[]
	 */
	public static array $meta_fields_cache = [];

	/**
	 * Inits YMFSEO.
	 */
	public static function init () : void {
		self::$replace_tags = [
			'{name}'        => get_bloginfo( 'name' ),
			'{description}' => get_bloginfo( 'description' ),
			'{home_url}'    => home_url(),
			'{separator}'   => '–',
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
	public static function get_post_meta_fields ( int $post_id, bool $raw = false ) : array {
		// Return cached value
		if ( isset( self::$meta_fields_cache[ $post_id ] ) ) {
			return self::$meta_fields_cache[ $post_id ];
		}

		// Get values
		$meta_fields = get_post_meta( $post_id, 'ymfseo_fields', true );
		$meta_fields = !is_array( $meta_fields ) || empty( $meta_fields ) ? [] : $meta_fields;
		$meta_fields = wp_parse_args( $meta_fields, self::$empty_meta_fields );

		// Set some defaults
		if ( !$raw ) {
			if ( !$meta_fields[ 'description' ] ) $meta_fields[ 'description' ] = get_the_excerpt( $post_id );
			if ( has_post_thumbnail( $post_id ) ) $meta_fields[ 'image_url' ]   = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		// Apply user filters
		$meta_fields = apply_filters( 'ymfseo_meta_fields', $meta_fields, $post_id );

		// Replace tags
		if ( !$raw ) {
			foreach ( $meta_fields as $key => $meta_field ) {
				foreach ( YMFSEO::$replace_tags as $tag => $value ) {
					$meta_fields[ $key ] = str_replace( $tag, $value, $meta_fields[ $key ] );
				}
			}
		}

		// Add to cache
		self::$meta_fields_cache[ $post_id ] = $meta_fields;

		return $meta_fields;
	}
}