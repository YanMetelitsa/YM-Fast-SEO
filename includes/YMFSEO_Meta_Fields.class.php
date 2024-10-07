<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * YM Fast SEO meta fields values class.
 * 
 * @since 2.0.0
 */
class YMFSEO_Meta_Fields {
	/**
	 * Meta title.
	 * 
	 * @var string
	 */
	public string $title;

	/**
	 * Meta description.
	 * 
	 * @var string
	 */
	public string $description;

	/**
	 * Preview image URI.
	 * 
	 * @var string
	 */
	public string $image_uri;

	/**
	 * Schema.org page type.
	 * 
	 * @var string
	 */
	public string $page_type;

	/**
	 * Defines the values of the robots meta tag "index" and "follow".
	 * 
	 * @var bool
	 */
	public bool $noindex;

	/**
	 * Default meta fields values.
	 * 
	 * @var array
	 */
	public static array $default_values = [];

	/**
	 * Tags for replacing in meta fields.
	 * 
	 * @var array
	 */
	public static array $replace_tags = [];

	/**
	 * Cached meta fields values.
	 * 
	 * @var array
	 */
	public static array $cache = [];

	/**
	 * Class that contains meta fields values.
	 * 
	 * @param WP_Post|WP_Post_Type|WP_Term|WP_User|null  $queried_object Queried object.
	 * @param bool                                       $format         Set `false` to get raw meta data values.
	 */
	public function __construct ( WP_Post|WP_Post_Type|WP_Term|WP_User|null $queried_object = null, bool $format = true ) {
		// Sets default meta fields.
		$meta_fields = self::$default_values;

		// Gets queried object.
		if ( is_null( $queried_object ) ) {
			$queried_object = get_queried_object();
		}

		// Does if object queried.
		if ( ! is_null( $queried_object ) ) {
			// Gets queried object data.
			$queried_object_type = get_class( $queried_object );
			$queried_object_id   = match ( $queried_object_type ) {
				'WP_Post'      => $queried_object->ID,
				'WP_Post_Type' => 0,
				'WP_Term'      => $queried_object->term_id,
			};

			// Checks for cache.
			$cache_slug = "{$queried_object_id}_{$queried_object_type}" . ( $format ? '' : '_raw' );

			if ( isset( self::$cache[ $cache_slug ] ) ) {
				$this->set_meta_fields( self::$cache[ $cache_slug ] );
				return;
			}

			// Does if no cache found.
			switch ( $queried_object_type ) {
				// Posts/Pages.
				case 'WP_Post':
					// Gets post meta data.
					$post_meta = get_post_meta( $queried_object_id, 'ymfseo_fields', true );
					if ( ! empty( $post_meta ) ) {
						$meta_fields = wp_parse_args( $post_meta, $meta_fields );
					}
					$meta_fields[ 'image_uri' ] = get_the_post_thumbnail_url( $queried_object_id, 'full' );

					// Sets post/page meta title and description.
					if ( $format ) {
						$post_title   = $queried_object->post_title;
						$post_excerpt = wp_trim_words( get_the_content( $queried_object ), 20 );
						$post_type    = $queried_object->post_type;

						$this->format_fields(
							$meta_fields,
							$post_title,
							$post_excerpt,
							"post_type_%s_{$post_type}",
							[
								'%post_title%' => $post_title,
								'%post_desc%'  => $post_excerpt,
							],
						);

						if ( 'default' === $meta_fields[ 'page_type' ] ) {
							$meta_fields[ 'page_type' ] = YMFSEO_Settings::get_option( "post_type_page_type_$post_type", 'ItemPage' );
						}
					}

					break;

				// Post Types.
				case 'WP_Post_Type':
					// Sets post type meta title and description.
					if ( $format ) {
						if ( empty( $meta_fields[ 'title' ] ) ) {
							$meta_fields[ 'title' ] = $queried_object->label;
						}
						if ( empty( $meta_fields[ 'description' ] ) ) {
							$meta_fields[ 'description' ] = $queried_object->description;
						}
					}

					break;

				// Taxonomies/Terms.
				case 'WP_Term':
					// Gets term meta data.
					$term_meta = get_term_meta( $queried_object_id, 'ymfseo_fields', true );
					if ( ! empty( $term_meta ) ) {
						$meta_fields = wp_parse_args( $term_meta, $meta_fields );
					}

					// Sets term meta title and description.
					if ( $format ) {
						$term_title       = $queried_object->name;
						$term_description = $queried_object->description;
						$taxonomy         = $queried_object->taxonomy;

						$this->format_fields(
							$meta_fields,
							$term_title,
							$term_description,
							"taxonomy_%s_{$taxonomy}",
							[
								'%term_title%' => $term_title,
								'%term_desc%'  => $term_description,
							],
						);
					}

					break;

				// Users.
				case 'WP_User':
					// Silence is golden.

					break;
			}
		}

		// Prepares not raw data.
		if ( $format ) {
			// Formats default page type.
			if ( 'default' === $meta_fields[ 'page_type' ] ) {
				$meta_fields[ 'page_type' ] = 'WebPage';
			}

			// Applies user filters.
			$meta_fields = apply_filters( 'ymfseo_meta_fields', $meta_fields, $queried_object );

			// Sets default preview image URI.
			$this->set_default_preview_image( $meta_fields );

			// Replaces tags.
			$this->replace_tags( $meta_fields );
		}

		// Adds meta fields to cache.
		if ( isset( $cache_slug ) ) {
			self::$cache[ $cache_slug ] = $meta_fields;
		}

		// Sets instance values.
		$this->set_meta_fields( $meta_fields );
	}

	/**
	 * Formats meta fields.
	 * 
	 * @since 2.1.0 Is private.
	 * 
	 * @param array  $meta_fields   Meta fields array.
	 * @param string $title         Queried object title.
	 * @param string $description   Queried object description.
	 * @param string $settings_mask Settings mask.
	 * @param array  $tags          Tags list in tag - value format.
	 */
	private function format_fields ( array &$meta_fields, string $title, string $description, string $settings_mask, array $tags ) : void {
		// Sets title.
		if ( empty( $meta_fields[ 'title' ] ) ) {
			$settings_title = YMFSEO_Settings::get_option( sprintf( $settings_mask, 'title' ) );

			if ( ! empty( $settings_title ) ) {
				foreach ( $tags as $tag => $value ) {
					$settings_title = str_replace( $tag, $value, $settings_title );
				}

				$title = $settings_title;
			}

			$meta_fields[ 'title' ] = $title;
		}

		// Sets description.
		if ( empty( $meta_fields[ 'description' ] ) ) {
			if ( empty( $description ) ) {
				$settings_description = YMFSEO_Settings::get_option( sprintf( $settings_mask, 'description' ) );
	
				if ( ! empty( $settings_description ) ) {
					foreach ( $tags as $tag => $value ) {
						$settings_description = str_replace( $tag, $value, $settings_description );
					}

					$description = $settings_description;
				}
			}

			$meta_fields[ 'description' ] = $description;
		}
	}

	/**
	 * Sets default preview image URI into 'image_uri' meta array property.
	 * 
	 * @since 2.1.0 Is private.
	 * 
	 * Checks is 'image_uri' property empty. If true – tries to get default
	 * preview image from settings.
	 * 
	 * @param array $meta_fields Meta fields.
	 */
	private function set_default_preview_image ( array &$meta_fields ) : void {
		if ( empty( $meta_fields[ 'image_uri' ] ) ) {
			$default_preview_image_id = YMFSEO_Settings::get_option( 'preview_image_id' );

			if ( $default_preview_image_id ) {
				$meta_fields[ 'image_uri' ] = wp_get_attachment_image_url( $default_preview_image_id, 'full' );
			}
		}
	}

	/**
	 * Looks for and replaces tags in some meta fields.
	 * 
	 * @since 2.1.0 Is private.
	 * 
	 * @param array $meta_fields Meta fields.
	 */
	private function replace_tags ( array &$meta_fields ) : void {
		foreach ( [ 'title', 'description' ] as $key ) {
			foreach ( self::$replace_tags as $tag => $value ) {
				$meta_fields[ $key ] = str_replace( $tag, $value, $meta_fields[ $key ] );
			}
		}
	}

	/**
	 * Sets meta fields to current instance.
	 * 
	 * @param array $meta_fields Meta fields values.
	 */
	private function set_meta_fields ( array $meta_fields ) : void {
		$this->title       = $meta_fields[ 'title' ];
		$this->description = $meta_fields[ 'description' ];
		$this->image_uri   = $meta_fields[ 'image_uri' ];
		$this->page_type   = $meta_fields[ 'page_type' ];
		$this->noindex     = $meta_fields[ 'noindex' ];
	}
	
	/**
	 * Prepares data for Schema.org JSON-LD printing.
	 * 
	 * @param YMFSEO_Meta_Fields $meta_fields    Meta fields instance.
	 * 
	 * @return array Prepared Shema.org array for printing JSON-LD.
	 */
	public static function build_schema_org ( YMFSEO_Meta_Fields $meta_fields ) : array {
		global $wp;

		$queried_object = get_queried_object();
		
		$document_title   = wp_get_document_title();
		$site_name        = get_bloginfo( 'name' );
		$site_description = get_bloginfo( 'description' );
		$site_locale      = get_locale();
		$home_url         = home_url();
		$current_url      = home_url( $wp->request );

		$schema_org_blank = [
			'WebPage' => [
				'@type'      => $meta_fields->page_type,
				'url'        => $current_url,
				'name'       => $document_title,
				'inLanguage' => $site_locale,
				'isPartOf'   => [
					'@id' => "$home_url#website",
				],
				'potentialAction' => [
					[
						'@type'  => 'ReadAction',
						'target' => [ $current_url ],
					],
				],
			],
			'WebSite' => [
				'@type'       => 'WebSite',
				'@id'         => "$home_url#website",
				'url'         => $home_url,
				'name'        => $site_name,
				'description' => $site_description,
				'inLanguage'  => $site_locale,
			],
		];
		
		// Adds WebPage description.
		if ( $meta_fields->description ) {
			$schema_org_blank[ 'WebPage' ][ 'description' ] = $meta_fields->description;
		}
		// Adds WebPage image preview URI.
		if ( $meta_fields->image_uri ) {
			$schema_org_blank[ 'WebPage' ][ 'image' ] = $meta_fields->image_uri;
		}
		// Adds WebPage dates.
		if ( $queried_object && 'WP_Post' == get_class( $queried_object ) ) {
			$schema_org_blank[ 'WebPage' ][ 'datePublished' ] = get_the_date( 'c', $queried_object );
			$schema_org_blank[ 'WebPage' ][ 'dateModified' ]  = get_the_modified_date( 'c', $queried_object );
		}

		// Adds representative.
		$rep_data = [];

		$rep_type = YMFSEO_Settings::get_option( 'rep_type' );

		$rep_name = YMFSEO_Settings::get_option( "rep_{$rep_type}_name" );
		if ( $rep_name ) $rep_data[ 'name' ] = $rep_name;

		$rep_email = YMFSEO_Settings::get_option( 'rep_email' );
		if ( $rep_email ) $rep_data[ 'email' ] = $rep_email;

		if ( ! empty( $rep_data ) ) {
			$rep_data = array_merge([
				'@type' => match ( $rep_type ) {
					'org'    => YMFSEO_Settings::get_option( 'rep_org_type' ),
					'person' => 'Person',
				},
				'@id'   => "$home_url#publisher",
				'url'   => $home_url,
			], $rep_data );

			$schema_org_blank[ 'Publisher' ] = $rep_data;
			$schema_org_blank[ 'WebSite' ][ 'publisher' ] = [
				'@id' => "$home_url#publisher",
			];
		}
		
		// Applies user filters.
		$schema_org = apply_filters( 'ymfseo_schema_org', $schema_org_blank, $queried_object );
		
		$schema_org = [
			'@context' => 'https://schema.org',
			'@graph'   => array_values( $schema_org_blank ),
		];

		return $schema_org;
	}
}