<?php

/** Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *  Main YM Fast SEO class.
 */
class YMFSEO {
	/**
	 * Emapty meta fields values.
	 * 
	 * @var array
	 */
	public static array $empty_meta_fields = [
		'title'       => null,
		'description' => null,
		'image_url'   => null,
		'page_type'   => 'WebPage',
		'noindex'     => false,
	];

	/**
	 * Tags for replace in meta fields.
	 * 
	 * @var string[]
	 */
	public static array $replace_tags = [];

	/**
	 * Meta fields cached value.
	 * 
	 * @var array[]
	 */
	public static array $meta_fields_cache = [];

	/**
	 * Default YMSEO settings.
	 * 
	 * @var string[]
	 */
	public static array $default_settings = [
		'hide_title_parts'     => true,
		'title_separator'      => '|',
		'preview_image_id'     => 0,
		'preview_size'         => 'summary_large_image',
		'search_console_key'   => '',
		'yandex_webmaster_key' => '',
		'robots_txt'           => '',
	];

	/**
	 * Inits YMFSEO.
	 */
	public static function init () : void {
		// Set replace tags
		self::$replace_tags = [
			'%name%' => get_bloginfo( 'name' ),
			'%desc%' => get_bloginfo( 'description' ),
			'%home%' => home_url(),
			'%sep%'  => self::get_option( 'title_separator' ),
		];
	}

	/**
	 * Retrieves an YMFSEO option value based on an option name.
	 * 
	 * @param string $option Option name.
	 * 
	 * @return mixed Option or default value.
	 */
	public static function get_option ( string $option ) : mixed {
		return get_option( "ymfseo_$option", self::$default_settings[ $option ] ?? false );
	}

	/**
	 * Returns array of public post types.
	 * 
	 * @return string[] Public post types.
	 */
	public static function get_public_post_types () : array {
		$public_post_types = get_post_types( [ 'public' => true, ], 'names' );

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
	public static function get_post_meta_fields ( int $post_id, bool $is_raw = false ) : array {
		// Return cached value
		$cache_slug = strval( $post_id ) . ( $is_raw ? '_raw' : '' );

		if ( isset( self::$meta_fields_cache[ $cache_slug ] ) ) {
			return self::$meta_fields_cache[ $cache_slug ];
		}

		// Get raw values
		$meta_fields = get_post_meta( $post_id, 'ymfseo_fields', true );
		$meta_fields = ! is_array( $meta_fields ) || empty( $meta_fields ) ? [] : $meta_fields;
		$meta_fields = wp_parse_args( $meta_fields, self::$empty_meta_fields );

		// Preview image
		$meta_fields[ 'image_url' ] = get_the_post_thumbnail_url( $post_id, 'full' );

		// Taxonomies descriptions
		if ( is_category() ) {
			$term = get_the_category()[ 0 ];

			$meta_fields[ 'description' ] = $term->description;
		}
		if ( is_tag() ) {
			$term = get_the_tags()[ 0 ];

			$meta_fields[ 'description' ] = $term->description;
		}

		// Set some defaults
		if ( ! $is_raw ) {
			// Description
			if ( ! $meta_fields[ 'description' ] ) {
				$meta_fields[ 'description' ] = get_the_excerpt( $post_id );
			}

			// Apply user filters
			$meta_fields = apply_filters( 'ymfseo_meta_fields', $meta_fields, $post_id );

			// Set default preview image
			if ( ! $meta_fields[ 'image_url' ] ) {
				$default_preview_image_id = self::get_option( 'preview_image_id' );

				if ( $default_preview_image_id ) {
					$meta_fields[ 'image_url' ] = wp_get_attachment_image_url( $default_preview_image_id, 'full' );
				}
			}

			// Replace tags
			foreach ( $meta_fields as $key => $meta_field ) {
				foreach ( YMFSEO::$replace_tags as $tag => $value ) {
					$meta_fields[ $key ] = str_replace( $tag, $value, $meta_fields[ $key ] );
				}
			}
		}

		// Add to cache
		self::$meta_fields_cache[ $cache_slug ] = $meta_fields;

		return $meta_fields;
	}

	/**
	 * Checks post SEO status.
	 * 
	 * @param int $post_id Post/page ID.
	 * 
	 * @return array Check result data.
	 */
	public static function check_seo ( int $post_id ) : array {
		$status = 'good';
		$notes  = [];

		$meta_fields = YMFSEO::get_post_meta_fields( $post_id );

		// Too short title
		if ( strlen( $meta_fields[ 'title' ] ) < 30 ) {
			$status = 'bad';
			$notes[] = __( 'The title is too short.', 'ym-fast-seo' );
		}
		// Too long title
		if ( strlen( $meta_fields[ 'title' ] ) > 70 ) {
			$status = 'bad';
			$notes[] = __( 'The title is too long.', 'ym-fast-seo' );
		}

		// No description
		if ( ! $meta_fields[ 'description' ] ) {
			$status = 'bad';
			$notes[] = __( 'No description.', 'ym-fast-seo' );
		} else {
			// Too short description
			if ( strlen( $meta_fields[ 'description' ] ) < 50 ) {
				$status = 'bad';
				$notes[] = __( 'The description is too short.', 'ym-fast-seo' );
			}

			// Too long description
			if ( strlen( $meta_fields[ 'description' ] ) > 170 ) {
				$status = 'bad';
				$notes[] = __( 'The description is too long.', 'ym-fast-seo' );
			}
		}

		// Not public
		if ( get_post_status( $post_id ) !== 'publish' ) {
			$status = 'noindex';
			$notes[] = __( 'Post status is "not published".', 'ym-fast-seo' );
		}

		// Noindex
		if ( $meta_fields[ 'noindex' ] ) {
			$status = 'noindex';
			$notes[] = __( 'Indexing has been disallowed.', 'ym-fast-seo' );
		}

		if ( empty( $notes ) ) {
			$notes[] = __( 'Good!', 'ym-fast-seo' );
		}

		return [
			'status' => $status,
			'notes'  => $notes,
		];
	}
}