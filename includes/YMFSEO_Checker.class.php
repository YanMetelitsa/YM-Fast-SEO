<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Validates posts/terms when creating, saving (updating), deleting and other.
 * 
 * @since 3.0.0
 */
class YMFSEO_Checker {
	/**
	 * Meta check length values.
	 * 
	 * @since 2.2.0
	 * @since 3.0.0 Is YMFSEO_Checker property. 
	 * 
	 * @var array
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
		return in_array( get_post_type( $post_id ), YMFSEO::get_public_post_types() );
	}

	/**
	 * Retrieves `true` if taxonomy public.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * 
	 * @return bool
	 */
	public static function is_taxonomy_public ( string $taxonomy ) : bool {
		return in_array( $taxonomy, YMFSEO::get_public_taxonomies() );
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
		return YMFSEO_Settings::get_option( "ymfseo_taxonomy_noindex_{$taxonomy}" );
	}

	/**
	 * Checks post SEO status.
	 * 
	 * @since 3.0.0 Is YMFSEO_Checker method.
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
		if ( $title_length < YMFSEO_Checker::$meta_lengths[ 'title' ][ 'min' ] ) {
			$status = 'bad';
			/* translators: %d: Number of symbols */
			$notes[] = sprintf( __( 'The title is too short (%d).', 'ym-fast-seo' ),
				esc_html( $title_length ),
			);
		}
		// Too long title.
		if ( $title_length > YMFSEO_Checker::$meta_lengths[ 'title' ][ 'max' ] ) {
			$status = 'alert';
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
			if ( $description_length < YMFSEO_Checker::$meta_lengths[ 'description' ][ 'min' ] ) {
				$status = 'bad';
				/* translators: %d: Number of symbols */
				$notes[] = sprintf( __( 'The description is too short (%d).', 'ym-fast-seo' ),
					esc_html( $description_length ),
				);
			}

			// Too long description.
			if ( $description_length > YMFSEO_Checker::$meta_lengths[ 'description' ][ 'max' ] ) {
				$status = 'alert';
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
		}

		if ( $object instanceof WP_Post || $object instanceof WP_Term ) {
			// Noindex.
			if ( $meta_fields->noindex ) {
				$status = 'noindex';
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