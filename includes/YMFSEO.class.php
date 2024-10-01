<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *  Main YM Fast SEO class.
 */
class YMFSEO {
	/**
	 * Inits YM Fast SEO Plugin.
	 */
	public static function init () : void {
		// Defines replace tags.
		YMFSEO_Meta_Fields::$default_values = [
			'title'       => '',
			'description' => '',
			'image_uri'   => '',
			'page_type'   => 'WebPage',
			'noindex'     => false,
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
			'preview_image_id'          => 0,
			'preview_size'              => 'summary_large_image',
			'rep_type'                  => 'org',
			'rep_org_type'              => 'Organization',
			'rep_org_name'              => '',
			'rep_person_name'           => '',
			'rep_email'                 => '',
			'google_search_console_key' => '',
			'yandex_webmaster_key'      => '',
			'robots_txt'                => '',
		];

		// Defines replace tags.
		YMFSEO_Meta_Fields::$replace_tags = [
			'%site_name%' => get_bloginfo( 'name' ),
			'%site_desc%' => get_bloginfo( 'description' ),
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
	 * @param int $post_id Public Post/Page ID.
	 * 
	 * @return array Check result data.
	 */
	public static function check_post_seo ( int $post_id ) : array {
		$status = 'good';
		$notes  = [];

		$meta_fields = new YMFSEO_Meta_Fields( get_post( $post_id ) );

		// Too short title.
		if ( strlen( $meta_fields->title ) < 30 ) {
			$status = 'bad';
			$notes[] = __( 'The title is too short.', 'ym-fast-seo' );
		}
		// Too long title.
		if ( strlen( $meta_fields->title ) > 70 ) {
			$status = 'bad';
			$notes[] = __( 'The title is too long.', 'ym-fast-seo' );
		}

		// No description.
		if ( empty( $meta_fields->description ) ) {
			$status = 'bad';
			$notes[] = __( 'No description.', 'ym-fast-seo' );
		} else {
			// Too short description.
			if ( strlen( $meta_fields->description ) < 50 ) {
				$status = 'bad';
				$notes[] = __( 'The description is too short.', 'ym-fast-seo' );
			}

			// Too long description.
			if ( strlen( $meta_fields->description ) > 170 ) {
				$status = 'bad';
				$notes[] = __( 'The description is too long.', 'ym-fast-seo' );
			}
		}

		// Not public.
		if ( 'publish' !== get_post_status( $post_id ) ) {
			$status = 'noindex';
			$notes[] = __( 'Post status is "not published".', 'ym-fast-seo' );
		}

		// Noindex.
		if ( $meta_fields->noindex ) {
			$status = 'noindex';
			$notes[] = __( 'Indexing has been disallowed.', 'ym-fast-seo' );
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