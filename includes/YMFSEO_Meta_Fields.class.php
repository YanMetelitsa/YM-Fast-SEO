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
	 * Schema.org WebPage type.
	 * 
	 * @var string
	 */
	public string $page_type;

	/**
	 * Defines `index` and `follow` values of `robots` meta tag.
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
	 * Inits YMFSEO meta fields subclass.
	 */
	public static function init () : void {
		// Manages posts and terms custom SEO column.
		add_action( 'init', function () {
			if ( ! YMFSEO_Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Post types.
			foreach ( YMFSEO::get_public_post_types() as $post_type ) {
				add_filter( "manage_{$post_type}_posts_columns", 'YMFSEO_Meta_Fields::manage_seo_columns' );
				add_action( "manage_{$post_type}_posts_custom_column" , function ( $column, $post_id ) {
					if ( 'ymfseo' === $column ) {
						$check = YMFSEO_Checker::check_seo( get_post( $post_id ) );

						printf( '<div class="column-ymfseo__dot" title="%s"><span class="%s"></span><div>',
							esc_attr( implode( '&#013;', $check[ 'notes' ] ) ),
							esc_attr( $check[ 'status' ] ),
						);
					}
				}, 10, 2 );
			}

			// Taxonomies.
			foreach ( YMFSEO::get_public_taxonomies() as $taxonomy ) {
				add_filter( "manage_edit-{$taxonomy}_columns", 'YMFSEO_Meta_Fields::manage_seo_columns' );
				add_action( "manage_{$taxonomy}_custom_column" , function ( $string, $column, $term_id  ) {
					if ( 'ymfseo' === $column ) {
						$check = YMFSEO_Checker::check_seo( get_term( $term_id ) );

						printf( '<div class="column-ymfseo__dot" title="%s"><span class="%s"></span><div>',
							esc_attr( implode( '&#013;', $check[ 'notes' ] ) ),
							esc_attr( $check[ 'status' ] ),
						);
					}
				}, 10, 3 );
			}
		}, 30 );

		// Adds SEO meta box to public post types.
		add_action( 'add_meta_boxes', function () {
			if ( ! YMFSEO_Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( $post ) {
				wp_nonce_field( YMFSEO_BASENAME, 'ymfseo_post_nonce' );
				
				include YMFSEO_ROOT_DIR . 'parts/meta-box.php';
			}, YMFSEO::get_public_post_types(), 'side' );
		});

		// Adds SEO meta fields to public taxonomies.
		add_action( 'init', function () {
			if ( ! YMFSEO_Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			foreach ( YMFSEO::get_public_taxonomies() as $taxonomy ) {
				add_action( "{$taxonomy}_edit_form_fields", function ( $term ) {
					wp_nonce_field( YMFSEO_BASENAME, "ymfseo_term_nonce" );

					include YMFSEO_ROOT_DIR . 'parts/term-meta-fields.php';
				});
			}
		}, 30 );


		// Saves post metas after saving post.
		add_action( 'save_post', function ( $post_id ) {
			// Is user can edit metas.
			if ( ! YMFSEO_Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Is post type public.
			if ( ! YMFSEO_Checker::is_post_type_public( $post_id ) ) {
				return;
			}

			// Checks nonce.
			if ( ! isset( $_POST[ 'ymfseo_post_nonce' ] ) ) {
				return;
			}

			$nonce = sanitize_key( wp_unslash( $_POST[ 'ymfseo_post_nonce' ] ) );

			if ( ! wp_verify_nonce( $nonce, YMFSEO_BASENAME ) ) {
				return;
			}

			// Is not revision.
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			// Is not autosave.
			if ( wp_is_post_autosave( $post_id ) ) {
				return;
			}

			// Updates metas.
			YMFSEO_Meta_Fields::update_meta([
				'title'       =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-title' ]       ?? YMFSEO_Meta_Fields::$default_values[ 'title' ] ) ),
				'description' =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-description' ] ?? YMFSEO_Meta_Fields::$default_values[ 'description' ] ) ),
				'page_type'   =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-page-type' ]   ?? YMFSEO_Meta_Fields::$default_values[ 'page_type' ] ) ),
				'noindex'     =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-noindex' ]     ?? YMFSEO_Meta_Fields::$default_values[ 'noindex' ] ) ),
			], $post_id, 'post' );
		});


		// Saves term metas after saving term.
		add_action( 'saved_term', function ( $term_id, $tt_id, $taxonomy ) {
			// Is user can edit metas.
			if ( ! YMFSEO_Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Is taxonomy public.
			if ( ! YMFSEO_Checker::is_taxonomy_public( $taxonomy ) ) {
				return;
			}
			
			// Checks nonce.
			if ( ! isset( $_POST[ 'ymfseo_term_nonce' ] ) ) {
				return;
			}

			$nonce = sanitize_key( wp_unslash( $_POST[ 'ymfseo_term_nonce' ] ) );

			if ( ! wp_verify_nonce( $nonce, YMFSEO_BASENAME ) ) {
				return;
			}

			// Updates metas.
			YMFSEO_Meta_Fields::update_meta([
				'title'       =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-title' ]       ?? YMFSEO_Meta_Fields::$default_values[ 'title' ] ) ),
				'description' =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-description' ] ?? YMFSEO_Meta_Fields::$default_values[ 'description' ] ) ),
			], $term_id, 'term' );
		}, 10, 3 );
	}

	/**
	 * Adds SEO column.
	 * 
	 * @since 2.1.0
	 * @since 3.1.0 Is YMFSEO_Meta_Fields method.
	 * 
	 * @param array $columns Input columns.
	 * 
	 * @return array Input with added SEO column.
	 */
	public static function manage_seo_columns ( $columns ) : array {
		$columns[ 'ymfseo' ] = __( 'SEO', 'ym-fast-seo' );
		
		return $columns;
	}

	/**
	 * Updates post/term meta fields after saving.
	 * 
	 * @since 2.1.0
	 * @since 3.1.0 Is YMFSEO_Meta_Fields method.
	 * 
	 * @param array  $meta_fields Meta fields.
	 * @param int    $id          Post/term ID.
	 * @param string $type        Object type. Can be 'post' or 'term'.
	 */
	public static function update_meta ( array $meta_fields, int $id, string $type ) : void {
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
	 * Class that contains meta fields values.
	 * 
	 * @param WP_Post|WP_Post_Type|WP_Term|WP_User|null  $queried_object Queried object.
	 * @param bool                                       $format         Set `false` to get raw meta data values.
	 */
	public function __construct ( WP_Post|WP_Post_Type|WP_Term|WP_User|null $queried_object = null, bool $format = true ) {
		// Sets default meta fields.
		$meta_fields = YMFSEO_Meta_Fields::$default_values;

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
				'WP_User'      => $queried_object->ID,
			};

			// Checks for cache.
			$cache_slug = "{$queried_object_id}_{$queried_object_type}" . ( $format ? '' : '_raw' );

			if ( isset( YMFSEO_Meta_Fields::$cache[ $cache_slug ] ) ) {
				$this->set_meta_fields( YMFSEO_Meta_Fields::$cache[ $cache_slug ] );
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
						$post_excerpt = wp_trim_words( get_the_excerpt( $queried_object ), 22 );
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

						if ( 'default' === $meta_fields[ 'page_type' ] ) {
							$meta_fields[ 'page_type' ] = 'CollectionPage';
						}
					}

					break;

				// Users.
				case 'WP_User':
					$user_meta = get_user_meta( $queried_object->ID );

					$meta_fields[ 'title' ]       = $queried_object->data->display_name;
					$meta_fields[ 'description' ] = $user_meta[ 'description' ][ 0 ];
					$meta_fields[ 'image_uri' ]   = get_avatar_url( $queried_object->ID, [
						'size' => 512,
					]);

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
			YMFSEO_Meta_Fields::$cache[ $cache_slug ] = $meta_fields;
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
	 * Checks is 'image_uri' property empty. If true – tries to get default
	 * preview image from settings.
	 * 
	 * @since 2.1.0 Is private.
	 * 
	 * @param array $meta_fields Meta fields.
	 */
	private function set_default_preview_image ( array &$meta_fields ) : void {
		if ( empty( $meta_fields[ 'image_uri' ] ) ) {
			$default_preview_image_id = YMFSEO_Settings::get_option( 'preview_image_id' );

			if ( $default_preview_image_id ) {
				$meta_fields[ 'image_uri' ] = sprintf( '%s?v=%s',
					wp_get_attachment_image_url( $default_preview_image_id, 'full' ),
					wp_get_theme()->Version,
				);
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
			foreach ( YMFSEO_Meta_Fields::$replace_tags as $tag => $value ) {
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
	 * @global $wp
	 * 
	 * @param YMFSEO_Meta_Fields $meta_fields Meta fields instance.
	 * 
	 * @return array Prepared Shema.org array for printing JSON-LD.
	 */
	public static function build_schema_org ( YMFSEO_Meta_Fields $meta_fields ) : array {
		global $wp;

		$queried_object = get_queried_object();

		// Sets object data template.
		$schema_org_blank = [
			'WebPage' => [
				'@type'      => $meta_fields->page_type,
				'@id'        => '#webpage',
				'url'        => home_url( $wp->request ),
				'name'       => wp_get_document_title(),
				'inLanguage' => get_locale(),
				'isPartOf'   => [
					'@id' => '#website',
				],
				'potentialAction' => [
					[
						'@type'  => 'ReadAction',
						'target' => [ home_url( $wp->request ) ],
					],
				],
			],
			'WebSite' => [
				'@type'       => 'WebSite',
				'@id'         => '#website',
				'url'         => home_url(),
				'name'        => get_bloginfo( 'name' ),
				'description' => get_bloginfo( 'description' ),
				'inLanguage'  => get_locale(),
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

		// Adds representative's details.
		$rep_data = [];

		$rep_type = YMFSEO_Settings::get_option( 'rep_type' );

		$rep_name = YMFSEO_Settings::get_option( "rep_{$rep_type}_name" );
		if ( $rep_name ) $rep_data[ 'name' ] = $rep_name;

		$rep_email = YMFSEO_Settings::get_option( 'rep_email' );
		if ( $rep_email ) $rep_data[ 'email' ] = $rep_email;

		$rep_phone = YMFSEO_Settings::get_option( 'rep_phone' );
		if ( $rep_phone ) $rep_data[ 'telephone' ] = $rep_phone;
		
		// Sets organization address.
		if ( 'org' === $rep_type ) {
			$address = [];

			$rep_city = YMFSEO_Settings::get_option( 'rep_org_city' );
			if ( $rep_city ) {
				$address[ 'addressLocality' ] = $rep_city;
			}

			$rep_region = YMFSEO_Settings::get_option( 'rep_org_region' );
			if ( $rep_region ) {
				$address[ 'addressRegion' ] = $rep_region;
			}

			$rep_address = YMFSEO_Settings::get_option( 'rep_org_address' );
			if ( $rep_address ) {
				$address[ 'streetAddress' ] = $rep_address;
			}

			$rep_postal_code = YMFSEO_Settings::get_option( 'rep_org_postal_code' );
			if ( $rep_postal_code ) {
				$address[ 'postalCode' ] = $rep_postal_code;
			}

			if ( ! empty( $address ) ) {
				$rep_data[ 'address' ] = array_merge(
					[ '@type' => 'PostalAddress' ],
					$address,
				);
			}
		}

		// Sets representative's image.
		$rep_image_id = YMFSEO_Settings::get_option( 'rep_image_id' );
		if ( $rep_image_id ) {
			$rep_data[ 'image' ] = wp_get_attachment_url( $rep_image_id );
		}

		// Pre-builds output object.
		if ( ! empty( $rep_data ) ) {
			$rep_data = array_merge([
				'@type' => match ( $rep_type ) {
					'org'    => YMFSEO_Settings::get_option( 'rep_org_type' ),
					'person' => 'Person',
				},
				'@id'   => '#publisher',
				'url'   => home_url(),
			], $rep_data );

			$schema_org_blank[ 'Publisher' ] = $rep_data;

			$schema_org_blank[ 'WebSite' ][ 'publisher' ] = [
				'@id' => '#publisher',
			];
		}
		
		// Applies user filters.
		$schema_org = apply_filters( 'ymfseo_schema_org', $schema_org_blank, $queried_object );
		
		// Final build.
		$schema_org = [
			'@context' => 'https://schema.org',
			'@graph'   => array_values( $schema_org_blank ),
		];

		return $schema_org;
	}
}