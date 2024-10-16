<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *  Main YM Fast SEO class.
 */
class YMFSEO {
	/**
	 * Available page types.
	 * 
	 * @since 2.1.0
	 * 
	 * @var string[]
	 */
	public static array $page_types = [];

	/**
	 * SEO check length values.
	 * 
	 * @since 2.2.0
	 * 
	 * @var array
	 */
	public static array $check_length_values = [
		'title' => [
			'min' => 30,
			'rec' => [ 40, 60 ],
			'max' => 70,
		],
		'description' => [
			'min' => 50,
			'rec' => [ 140, 160 ],
			'max' => 170,
		],
	];

	/**
	 * Inits YM Fast SEO Plugin.
	 */
	public static function init () : void {
		// Defines available page types.
		self::$page_types = [
			'WebPage'           => __( 'Regular Page', 'ym-fast-seo' ),
			'CollectionPage'    => __( 'Collection Page', 'ym-fast-seo' ),
			'ItemPage'          => __( 'Item Page', 'ym-fast-seo' ),
			'AboutPage'         => __( 'About Page', 'ym-fast-seo' ),
			'FAQPage'           => __( 'FAQ Page', 'ym-fast-seo' ),
			'ContactPage'       => __( 'Contact Page', 'ym-fast-seo' ),
			'CheckoutPage'      => __( 'Checkout Page', 'ym-fast-seo' ),
			'SearchResultsPage' => __( 'Search results Page', 'ym-fast-seo' ),
		];

		// Defines replace tags.
		YMFSEO_Meta_Fields::$default_values = [
			'title'       => '',
			'description' => '',
			'image_uri'   => '',
			'page_type'   => 'default',
			'noindex'     => '',
		];

		// Defines settings rguments.
		YMFSEO_Settings::$params = [
			'page_title'    => __( 'SEO Settings', 'ym-fast-seo' ),
			'menu_label'    => __( 'SEO', 'ym-fast-seo' ),
			'menu_position' => 3,
			'capability'    => 'manage_options',
			'page_slug'     => 'ymfseo-settings',
		];

		// Defines default settings.
		YMFSEO_Settings::$default_settings = [
			'hide_title_parts'          => true,
			'title_separator'           => '|',
			'post_type_page_type_page'  => 'WebPage',
			'preview_image_id'          => 0,
			'preview_size'              => 'summary_large_image',
			'rep_type'                  => 'org',
			'rep_org_type'              => 'Organization',
			'rep_org_name'              => '',
			'rep_person_name'           => '',
			'rep_email'                 => '',
			'rep_phone'                 => '',
			'rep_org_city'              => '',
			'rep_org_region'            => '',
			'rep_org_address'           => '',
			'rep_org_postal_code'       => '',
			'rep_image_id'              => 0,
			'google_search_console_key' => '',
			'yandex_webmaster_key'      => '',
			'bing_webmaster_tools_key'  => '',
			'robots_txt'                => '',
		];

		// Defines replace tags.
		YMFSEO_Meta_Fields::$replace_tags = [
			'%site_name%' => get_bloginfo( 'name' ),
			'%sep%'       => YMFSEO_Settings::get_option( 'title_separator' ),
		];
	}

	/**
	 * Retrieves array of public post types.
	 * 
	 * @since 2.0.0 Has argument $output.
	 * 
	 * @param string $output Output type. Default 'names'.
	 * 
	 * @return string[]|WP_Post[] Public post types.
	 */
	public static function get_public_post_types ( string $output = 'names' ) : array {
		$public_post_types = get_post_types( [ 'public' => true ], $output );

		unset( $public_post_types[ 'attachment' ] );

		return $public_post_types;
	}

	/**
	 * Retrieves array of public taxonomies.
	 * 
	 * @since 2.0.0
	 * 
	 * @param string $output Output type. Default 'names'.
	 * 
	 * @return string[]|WP_Term[] Public taxonomies.
	 */
	public static function get_public_taxonomies ( string $output = 'names' ) : array {
		$public_post_types = get_taxonomies( [ 'public' => true ], $output );

		unset( $public_post_types[ 'post_format' ] );

		return $public_post_types;
	}

	/**
	 * Adds SEO column.
	 * 
	 * @since 2.1.0
	 * 
	 * @param array $columns Input columns.
	 * 
	 * @return array Input with added SEO column.
	 */
	public static function manage_seo_columns ( $columns ) {
		$columns[ 'ymfseo' ] = __( 'SEO', 'ym-fast-seo' );
		
		return $columns;
	}

	/**
	 * Updates post/term meta fields after saving.
	 * 
	 * @since 2.1.0
	 * 
	 * @param array  $meta_fields Meta fields.
	 * @param int    $id          Post/term ID.
	 * @param string $type        'post' or 'term'.
	 */
	public static function update_meta ( array $meta_fields, int $id, string $type ) {
		$meta_value = [];

		$update_function_name = "update_{$type}_meta";
		$delete_function_name = "delete_{$type}_meta";

		foreach ( $meta_fields as $key => $value ) {
			if ( $value !== YMFSEO_Meta_Fields::$default_values[ $key ] ) {
				$meta_value[ $key ] = $value;
			}
		}

		if ( $meta_value ) {
			$update_function_name( $id, 'ymfseo_fields', $meta_value );
		} else {
			$delete_function_name( $id, 'ymfseo_fields' );
		}
	}

	/**
	 * Retrieves whether the site in a network with a subdirectory type.
	 * 
	 * @since 2.0.1
	 * 
	 * @return bool Is multisite with subdirectory structure.
	 */
	public static function is_subdir_multisite () : bool {
		return is_multisite() && defined( 'SUBDOMAIN_INSTALL' ) && ! SUBDOMAIN_INSTALL;
	}

	/**
	 * Checks post SEO status.
	 * 
	 * @param WP_Post|WP_Term $object Post or Term object.
	 * 
	 * @return array Check result data.
	 */
	public static function check_seo ( WP_Post|WP_Term $object ) : array {
		$status = 'good';
		$notes  = [];

		$meta_fields = new YMFSEO_Meta_Fields( $object );

		$title_length       = mb_strlen( $meta_fields->title );
		$description_length = mb_strlen( $meta_fields->description );

		// Too short title.
		if ( $title_length < self::$check_length_values[ 'title' ][ 'min' ] ) {
			$status = 'bad';
			/* translators: %d: Number of symbols */
			$notes[] = sprintf( __( 'The title is too short (%d).', 'ym-fast-seo' ),
				esc_html( $title_length ),
			);
		}
		// Too long title.
		if ( $title_length > self::$check_length_values[ 'title' ][ 'max' ] ) {
			$status = 'bad';
			/* translators: %d: Number of symbols */
			$notes[] = sprintf( __( 'The title is too long (%d).', 'ym-fast-seo' ),
				esc_html( $title_length ),
			);
		}

		// No description.
		if ( empty( $meta_fields->description ) ) {
			$status = 'bad';
			$notes[] = __( 'No description.', 'ym-fast-seo' );
		} else {
			// Too short description.
			if ( $description_length < self::$check_length_values[ 'description' ][ 'min' ] ) {
				$status = 'bad';
				/* translators: %d: Number of symbols */
				$notes[] = sprintf( __( 'The description is too short (%d).', 'ym-fast-seo' ),
					esc_html( $description_length ),
				);
			}

			// Too long description.
			if ( $description_length > self::$check_length_values[ 'description' ][ 'max' ] ) {
				$status = 'bad';
				/* translators: %d: Number of symbols */
				$notes[] = sprintf( __( 'The description is too long (%d).', 'ym-fast-seo' ),
					esc_html( $description_length ),
				);
			}
		}

		if ( $object instanceof WP_Post ) {
			// Not public.
			if ( 'publish' !== get_post_status( $object ) ) {
				$status = 'noindex';
				$notes[] = __( 'Post status is "not published".', 'ym-fast-seo' );
			}

			// Noindex.
			if ( $meta_fields->noindex ) {
				$status = 'noindex';
				$notes[] = __( 'Indexing has been disallowed.', 'ym-fast-seo' );
			}
		}

		// Good!
		if ( empty( $notes ) ) {
			$notes[] = __( 'Good!', 'ym-fast-seo' );
		}

		return [
			'status' => $status,
			'notes'  => $notes,
		];
	}
}