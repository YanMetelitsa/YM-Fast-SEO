<?php

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

/**
 * YMFSEO Checker class.
 * 
 * @since 3.0.0
 */
class Checker {
	/**
	 * Meta check length values.
	 * 
	 * @since 2.2.0
	 * @since 3.0.0 Is YMFSEO_Checker property. 
	 * 
	 * @var array {
	 * 		@type array $title       Title lengths.
	 * 		@type array $description Description lengths.
	 * }
	 */
	public static array $meta_lengths = [
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
	 * Compares two strings for similarity.
	 * 
	 * @since 4.1.0
	 * 
	 * @param string $firsts First string.
	 * @param string $second Second string.
	 * @param int    $length How many first characters will be compared.
	 * 
	 * @return bool `true` if strings similar.
	 */
	public static function are_strings_similar ( string $firsts, string $second, int $length = 30 ) {
		$firsts = mb_strtolower( $firsts, 'UTF-8' );
		$second = mb_strtolower( $second, 'UTF-8' );

		$firsts_substr = mb_substr( $firsts, 0, $length, 'UTF-8' );
		$second_substr = mb_substr( $second, 0, $length, 'UTF-8' );

		return $firsts_substr === $second_substr;
	}


	/**
	 * Retrieves `true` if current site is not `noindex`.
	 * 
	 * @return bool
	 */
	public static function is_site_public () : bool {
		return get_option( 'blog_public', true );
	}

	/**
	 * Retrieves whether the site in a network with a subdirectory type.
	 * 
	 * @since 2.0.1
	 * @since 3.0.0 Is YMFSEO_Checker method.
	 * 
	 * @return bool Is multisite with subdirectory structure.
	 */
	public static function is_subdir_multisite () : bool {
		return is_multisite() && ! is_subdomain_install();
	}


	/**
	 * Retrieves `true` if current page has canonical output;
	 * 
	 * @since 3.3.3
	 * @since 4.1.0 Method of `YMFSEO_Checker` class instead of `YMFSEO`.
	 * 
	 * @return bool
	 */
	public static function is_current_page_has_canonical () : bool {
		$has_canonical = false;

		ob_start();
			
		rel_canonical();
		
		if ( ob_get_contents() ) {
			$has_canonical = true;
		}
		
		ob_end_clean();

		return $has_canonical;
	}


	/**
	 * Retrieves `true` if user can edit metas.
	 * 
	 * @return bool
	 */
	public static function is_current_user_can_edit_metas () : bool {
		return current_user_can( 'ymfseo_edit_metas' );
	}

	/**
	 * Retrieves `true` if user can view site health.
	 * 
	 * @since 3.1.1
	 * 
	 * @return bool
	 */
	public static function is_current_user_can_view_site_health () : bool {
		return current_user_can( 'view_site_health_checks' );
	}

	/**
	 * Retrieves `true` if user can manage options
	 * 
	 * @since 3.1.1
	 * 
	 * @return bool
	 */
	public static function is_current_user_can_manage_options () : bool {
		return current_user_can( 'manage_options' );
	}


	/**
	 * Retrieves `true` if post type is public.
	 *
	 * @param int $post_id Post ID.
	 * 
	 * @return bool
	 */
	public static function is_post_type_public ( int $post_id ) : bool {
		return \in_array( get_post_type( $post_id ), Core::get_public_post_types() );
	}

	/**
	 * Retrieves `true` if page is an Archive.
	 * 
	 * @since 4.1.1
	 * 
	 * @param \WP_Post $page Page object.
	 * 
	 * @return bool
	 */
	public static function is_page_an_archive ( \WP_Post $page ) : bool {
		if ( \in_array( get_the_permalink( $page ), Core::$archive_urls ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves `true` if taxonomy public.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * 
	 * @return bool
	 */
	public static function is_taxonomy_public ( string $taxonomy ) : bool {
		return \in_array( $taxonomy, Core::get_public_taxonomies() );
	}

	/**
	 * Retrieves `true` if taxonomy noindex.
	 * 
	 * @since 3.3.3
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * 
	 * @return bool
	 */
	public static function is_taxonomy_noindex ( string $taxonomy ) : bool {
		return Settings::get_option( "ymfseo_taxonomy_noindex_{$taxonomy}" );
	}


	/**
	 * Retrieves `true` if Imagick enabled.
	 * 
	 * @since 4.0.0
	 * 
	 * @return bool
	 */
	public static function is_imagick_available () : bool {
		if ( ! class_exists( 'Imagick' ) ) {
			return false;
		}

		return empty(
			array_diff(
				[ 'svg', 'ico', 'png' ],
				array_map( fn ( $format ) => strtolower( $format ), \Imagick::queryFormats() )
			)
		);
	}

	/**
	 * Retrieves `true` if site icon is SVG format.
	 * 
	 * @since 4.0.0
	 * 
	 * @return bool
	 */
	public static function is_svg_favicon () : bool {
		return '.svg' == substr( get_site_icon_url(), -4 );
	}

	/**
	 * Retrieves `true` if head scripts should be printed.
	 * 
	 * @since 4.1.0
	 * 
	 * @return bool
	 */
	public static function should_print_head_scripts () : bool {
		$should_print  = true;
		$only_visitors = Settings::get_option( 'head_scripts_only_visitors' );

		if ( $only_visitors && is_user_logged_in() ) {
			$should_print = false;
		}

		return apply_filters( 'ymfseo_print_head_scripts', $should_print, $only_visitors );
	}

	/**
	 * Retrieves `true` if `llms.txt` enabled.
	 * 
	 * @since 4.1.0
	 * 
	 * @return bool
	 */
	public static function is_llms_txt_enabled () : bool {
		return Settings::get_option( 'enable_llms_txt' );
	}


	/**
	 * Checks post SEO status.
	 * 
	 * @since 3.0.0 Is YMFSEO_Checker method.
	 * @since 4.0.0 Adds title parts if needed.
	 * 
	 * @param \WP_Post|\WP_Term $object Post or Term object.
	 * 
	 * @return array {
	 * 		Check result data.
	 * 
	 * 		@type string   $status Check status. May be `good`, `bad`, `alert`, `noindex`.
	 * 		@type string[] $notes  Check notes.
	 * }
	 */
	public static function check_seo ( \WP_Post|\WP_Term $object ) : array {
		$status = 'good';
		$notes  = [];

		if ( $object instanceof \WP_Post && Checker::is_page_an_archive( $object ) ) {
			return [
				'status' => 'archive',
				'notes'  => [
					__( 'This is an Archive page.', 'ym-fast-seo' ),
				],
			];
		}

		$meta_fields = new MetaFields( $object );

		// Adds title parts if not hidden via settings.
		if ( ! Settings::get_option( 'hide_title_parts' ) ) {
			$meta_fields->title = implode( ' ', [
				$meta_fields->title,
				Core::get_separator(),
				( $object instanceof \WP_Post && $object->ID == get_option( 'page_on_front' ) ) ? get_bloginfo( 'description' ) : get_bloginfo( 'name' ),
			]);
		}

		$title_length       = mb_strlen( $meta_fields->title );
		$description_length = mb_strlen( $meta_fields->description );

		// Too short title.
		if ( $title_length < Checker::$meta_lengths[ 'title' ][ 'min' ] ) {
			$status = 'bad';
			/* translators: %d: Number of symbols */
			$notes[] = sprintf( __( 'The title is too short (%d).', 'ym-fast-seo' ),
				esc_html( $title_length ),
			);
		}

		// Too long title.
		if ( $title_length > Checker::$meta_lengths[ 'title' ][ 'max' ] ) {
			$status = 'alert';
			/* translators: %d: Number of symbols */
			$notes[] = sprintf( __( 'The title is too long (%d).', 'ym-fast-seo' ),
				esc_html( $title_length ),
			);
		}

		// No description.
		if ( empty( $meta_fields->description ) ) {
			$status  = 'bad';
			$notes[] = __( 'No description.', 'ym-fast-seo' );
		} else {
			// Too short description.
			if ( $description_length < Checker::$meta_lengths[ 'description' ][ 'min' ] ) {
				$status = 'bad';
				/* translators: %d: Number of symbols */
				$notes[] = sprintf( __( 'The description is too short (%d).', 'ym-fast-seo' ),
					esc_html( $description_length ),
				);
			}

			// Too long description.
			if ( $description_length > Checker::$meta_lengths[ 'description' ][ 'max' ] ) {
				$status = 'alert';
				/* translators: %d: Number of symbols */
				$notes[] = sprintf( __( 'The description is too long (%d).', 'ym-fast-seo' ),
					esc_html( $description_length ),
				);
			}
		}

		if ( $object instanceof \WP_Post ) {
			// Not public.
			if ( 'publish' !== get_post_status( $object ) ) {
				$status  = 'noindex';
				$notes[] = __( 'Post status is "not published".', 'ym-fast-seo' );
			}
		}

		if ( $object instanceof \WP_Post || $object instanceof \WP_Term ) {
			// Noindex.
			if ( $meta_fields->noindex ) {
				$status  = 'noindex';
				$notes[] = __( 'Indexing has been disallowed.', 'ym-fast-seo' );
			}
		}

		// Good!
		if ( empty( $notes ) ) {
			/* translators: SEO check state */
			$notes[] = __( 'Good!', 'ym-fast-seo' );
		}

		return [
			'status' => $status,
			'notes'  => $notes,
		];
	}
}