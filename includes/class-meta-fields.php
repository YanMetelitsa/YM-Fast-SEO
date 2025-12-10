<?php

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

/**
 * YMFSEO Meta Fields class.
 * 
 * @since 2.0.0
 */
class MetaFields {
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
	 * @see https://schema.org/WebPage#subtypes
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
	 * @var array {
	 * 		@type string      $title       Meta title.
	 * 		@type string      $description Meta description.
	 * 		@type string      $image_uri   Preview image URL.
	 * 		@type string      $page_type   Page type.
	 * 		@type bool|string $noindex     Is noindex.
	 * }
	 */
	public static array $default_values = [
		'title'       => '',
		'description' => '',
		'image_uri'   => '',
		'page_type'   => 'default',
		'noindex'     => '',
	];

	/**
	 * Length of excerpt used for auto description.
	 * 
	 * @since  3.3.4
	 * 
	 * @var int[]
	 */
	public static array $excerpt_length_map = [
		'ru_RU'   => 15,
		'bel'     => 15,
		'default' => 20,
	];

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
	public static function init () {
		// Manages custom SEO column.
		add_action( 'init', function () {
			if ( ! Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Post types.
			foreach ( Core::get_public_post_types() as $post_type ) {
				add_filter( "manage_{$post_type}_posts_columns", function ( array $columns ) : array {
					$columns[ 'ymfseo' ] = __( 'SEO', 'ym-fast-seo' );
					
					return $columns;
				});
				add_action( "manage_{$post_type}_posts_custom_column" , function ( string $column, int $post_id ) {
					if ( 'ymfseo' === $column ) {
						MetaFields::print_custom_seo_column( get_post( $post_id ) );

						$meta_fields = new MetaFields( get_post( $post_id ), false );

						printf( '<input name="ymfseo-title-value"       value="%s" hidden disabled>', esc_attr( $meta_fields->title ) );
						printf( '<input name="ymfseo-description-value" value="%s" hidden disabled>', esc_attr( $meta_fields->description ) );
						printf( '<input name="ymfseo-page-type-value"   value="%s" hidden disabled>', esc_attr( $meta_fields->page_type ) );
						printf( '<input name="ymfseo-noindex-value"     value="%d" hidden disabled>', esc_attr( $meta_fields->noindex ? 1 : 0 ) );
					}
				}, 10, 2 );
			}

			// Taxonomies.
			foreach ( Core::get_public_taxonomies() as $taxonomy ) {
				if ( class_exists( 'WooCommerce' ) ) {
					if ( \in_array( $taxonomy, [ 'product_cat', 'product_brand' ] ) ) {
						break;
					}
				}

				add_filter( "manage_edit-{$taxonomy}_columns", function ( array $columns ) : array {
					$columns[ 'ymfseo' ] = __( 'SEO', 'ym-fast-seo' );
					
					return $columns;
				}, 20 );
				add_action( "manage_{$taxonomy}_custom_column" , function ( $string, string $column, int $term_id  ) {
					if ( 'ymfseo' === $column ) {
						MetaFields::print_custom_seo_column( get_term( $term_id ) );
					}
				}, 10, 3 );
			}
		}, 30 );

		// Adds posts quick edit fields.
		add_action( 'quick_edit_custom_box', function ( string $column_name, string $post_type ) {
			// Is user can edit metas.
			if ( ! Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Is public post type.
			if ( ! \in_array( $post_type, array_values( Core::get_public_post_types() ) ) ) {
				return;
			}

			if ( 'ymfseo' !== $column_name ) {
				return;
			}

			wp_nonce_field( YMFSEO_BASENAME, 'ymfseo_post_nonce' );

			include YMFSEO_ROOT_DIR . 'parts/post-quick-edit-meta-fields.php';
		}, 10, 2 );
		add_action( 'admin_footer', function () {
			?>
				<script>
					jQuery( document ).ready( function ( $ ) {
						// `Quick Edit` click.
						$( document ).on( 'click', '.editinline', function () {
							// Get post row.
							const tr = $( this ).closest( 'tr' );

							const title       = tr.find( 'input[ name="ymfseo-title-value" ]' ).val();
							const description = tr.find( 'input[ name="ymfseo-description-value" ]' ).val();
							const pageType    = tr.find( 'input[ name="ymfseo-page-type-value" ]' ).val();
							const isNoindex   = parseInt( tr.find( 'input[ name="ymfseo-noindex-value" ]' ).val() );

							// Get edit row.
							const editTr     = $( `tr#${tr.attr( 'id' ).replace( 'post', 'edit' )}` )
							const indicators = editTr.find( '.ymfseo-length-indicator' );

							indicators.each( ( index, indicator ) => {
								indicator.classList.remove( 'initialized' );
							});

							// Set values.
							$( 'input[ name="ymfseo-title" ]', '.inline-edit-row' ).attr( 'data-post-id', tr.attr( 'id' ).replace( 'post-', '' ) );

							$( 'input[ name="ymfseo-title" ]',          '.inline-edit-row' ).val( title );
							$( 'textarea[ name="ymfseo-description" ]', '.inline-edit-row' ).val( description );
							$( 'select[ name="ymfseo-page-type" ]',     '.inline-edit-row' ).val( pageType );
							$( 'input[ name="ymfseo-noindex" ]',        '.inline-edit-row' ).prop( 'checked', 1 == isNoindex );

							YMFSEO.initInputLengthIndicators();
						});
					});
				</script>
			<?php
		});

		// Adds SEO meta fields to public post types.
		add_action( 'add_meta_boxes', function () {
			if ( ! Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( \WP_Post $post ) {
				wp_nonce_field( YMFSEO_BASENAME, 'ymfseo_post_nonce' );
				
				include YMFSEO_ROOT_DIR . 'parts/post-meta-fields.php';
			}, Core::get_public_post_types(), 'side' );
		});

		// Adds SEO meta fields to public taxonomies.
		add_action( 'init', function () {
			if ( ! Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			foreach ( Core::get_public_taxonomies() as $taxonomy ) {
				add_action( "{$taxonomy}_edit_form_fields", function ( \WP_Term $term, string $taxonomy ) {
					wp_nonce_field( YMFSEO_BASENAME, "ymfseo_term_nonce" );

					include YMFSEO_ROOT_DIR . 'parts/term-meta-fields.php';
				}, 10, 2 );
			}
		}, 30 );


		// Saves post metas after saving post.
		add_action( 'save_post', function ( int $post_id ) {
			// Is not autosave.
			if ( wp_is_post_autosave( $post_id ) ) {
				return;
			}

			// Is not revision.
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			// Is user can edit metas.
			if ( ! Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Is post type public.
			if ( ! Checker::is_post_type_public( $post_id ) ) {
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

			// Updates metas.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			MetaFields::update_meta([
				'title'       => Core::sanitize_text_field(
					$_POST[ 'ymfseo-title' ]       ?? MetaFields::$default_values[ 'title' ]
				),
				'description' => Core::sanitize_text_field(
					$_POST[ 'ymfseo-description' ] ?? MetaFields::$default_values[ 'description' ]
				),
				'page_type'   => Core::sanitize_text_field(
					$_POST[ 'ymfseo-page-type' ]   ?? MetaFields::$default_values[ 'page_type' ]
				),
				'noindex'     => Core::sanitize_text_field(
					$_POST[ 'ymfseo-noindex' ]     ?? MetaFields::$default_values[ 'noindex' ]
				),
			], $post_id, 'post' );
			// phpcs:enable
		});

		// Saves term metas after saving term.
		add_action( 'saved_term', function ( int $term_id, int $tt_id, string $taxonomy ) {
			// Is user can edit metas.
			if ( ! Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Is taxonomy public.
			if ( ! Checker::is_taxonomy_public( $taxonomy ) ) {
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
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			MetaFields::update_meta([
				'title'       => Core::sanitize_text_field(
					$_POST[ 'ymfseo-title' ]       ?? MetaFields::$default_values[ 'title' ]
				),
				'description' => Core::sanitize_text_field(
					$_POST[ 'ymfseo-description' ] ?? MetaFields::$default_values[ 'description' ]
				),
			], $term_id, 'term' );
			// phpcs:enable
		}, 10, 3 );
	}

	/**
	 * Prints SEO check dot.
	 * 
	 * @param \WP_Post|\WP_Term $check_object Object for checking.
	 */
	public static function print_custom_seo_column ( \WP_Post|\WP_Term $check_object ) {
		$check = Checker::check_seo( $check_object );

		printf( '<span class="dashicons dashicons-%s %s" title="%s"></span> <ul><li>%s</li></ul>',
			esc_attr( match ( $check[ 'status' ] ) {
				'good'    => 'yes-alt',
				'bad'     => 'warning',
				'alert'   => 'warning',
				'archive' => 'archive',
				'noindex' => 'shield-alt',
				default   => 'info',
			}),
			esc_attr( $check[ 'status' ] ),
			esc_attr( implode( '&#013;', $check[ 'notes' ] ) ),
			wp_kses_post(
				implode( '</li><li>',
					array_map(
						fn ( $note ) => $note,
						$check[ 'notes' ],
					),
				)
			),
		);
	}

	/**
	 * Updates post/term meta fields after saving.
	 * 
	 * @since 2.1.0
	 * @since 3.1.0 Is YMFSEO_Meta_Fields method.
	 * 
	 * @param array  $meta_fields {
	 * 		Meta fields.
	 * 
	 * 		@type string $title       Meta title.
	 * 		@type string $description Meta description.
	 * 		@type string $image_uri   Preview image URL.
	 * 		@type string $page_type   Page type.
	 * 		@type bool   $noindex     Is noindex.
	 * }
	 * @param int    $id          Post/Term ID.
	 * @param string $type        Object type. Can be 'post' or 'term'.
	 */
	public static function update_meta ( array $meta_fields, int $id, string $type ) {
		$meta_value = [];

		$update_function_name = "update_{$type}_meta";
		$delete_function_name = "delete_{$type}_meta";

		foreach ( $meta_fields as $key => $value ) {
			if ( $value !== MetaFields::$default_values[ $key ] ) {
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
	 * Retrieves plugin excerpt length.
	 * 
	 * @since 3.3.4
	 * 
	 * @return int
	 */
	public static function get_excerpt_length () : int {
		$locale = get_locale();

		if ( isset( MetaFields::$excerpt_length_map[ $locale ] ) ) {
			return MetaFields::$excerpt_length_map[ $locale ];
		}

		return MetaFields::$excerpt_length_map[ 'default' ];
	}

	/**
	 * Class that contains meta fields values.
	 * 
	 * @param \WP_Post|\WP_Post_Type|\WP_Term|\WP_User|null  $queried_object Queried object.
	 * @param bool                                       $format         Set `false` to get raw meta data values.
	 */
	public function __construct ( \WP_Post|\WP_Post_Type|\WP_Term|\WP_User|null $queried_object = null, bool $format = true ) {
		// Sets default meta fields.
		$meta_fields = MetaFields::$default_values;

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

			if ( isset( MetaFields::$cache[ $cache_slug ] ) ) {
				$this->set_meta_fields( MetaFields::$cache[ $cache_slug ] );
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
						// Plugin excerpt data.
						$plugin_excerpt_length = MetaFields::get_excerpt_length();
						$plugin_excerpt_more   = '&hellip;';

						// Theme excerpt data.
						$theme_excerpt_length  = apply_filters( 'excerpt_length', 55 );         // phpcs:ignore
						$theme_excerpt_more    = apply_filters( 'excerpt_more', '[&hellip;]' ); // phpcs:ignore

						// Rewrite excerpt data..
						add_filter( 'excerpt_length', fn () : int    => $plugin_excerpt_length, PHP_INT_MAX );
						add_filter( 'excerpt_more',   fn () : string => $plugin_excerpt_more,   PHP_INT_MAX );

						// Get data.
						$post_title   = $queried_object->post_title;
						$post_excerpt = get_the_excerpt( $queried_object );
						$post_type    = $queried_object->post_type;

						// Revert excerpt data back.
						remove_all_filters( 'excerpt_length', PHP_INT_MAX );
						remove_all_filters( 'excerpt_more',   PHP_INT_MAX );
						
						add_filter( 'excerpt_length', fn () : int    => $theme_excerpt_length );
						add_filter( 'excerpt_more',   fn () : string => $theme_excerpt_more );

						// Format data.
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
							$meta_fields[ 'page_type' ] = Settings::get_option( "post_type_page_type_{$post_type}", 'ItemPage' );
						}
					}

					break;

				// Post Types.
				case 'WP_Post_Type':
					// Sets post type meta title and description.
					if ( $format ) {
						if ( empty( $meta_fields[ 'title' ] ) ) {
							$settings_title = settings::get_option( "archive_title_{$queried_object->name}" );

							$meta_fields[ 'title' ] = $settings_title ?? $queried_object->label;
						}

						if ( empty( $meta_fields[ 'description' ] ) ) {
							$settings_description = settings::get_option( "archive_description_{$queried_object->name}" );

							$meta_fields[ 'description' ] = $settings_description ?? $queried_object->description;
						}

						$meta_fields[ 'page_type' ] = 'CollectionPage';
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
						// Get data.
						$term_title       = $queried_object->name;
						$term_description = $queried_object->description;
						$taxonomy         = $queried_object->taxonomy;

						// Format data.
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

						if ( Checker::is_taxonomy_noindex( $taxonomy ) ) {
							$meta_fields[ 'noindex' ] = true;
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
			$this->replace_tags( $meta_fields, $queried_object );

			// Applies shortcodes.
			$meta_fields = array_map( fn ( $item ) => do_shortcode( $item ), $meta_fields );
		}

		// Adds meta fields to cache.
		if ( isset( $cache_slug ) ) {
			MetaFields::$cache[ $cache_slug ] = $meta_fields;
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
	private function format_fields ( array &$meta_fields, string $title, string $description, string $settings_mask, array $tags ) {
		// Sets title.
		if ( empty( $meta_fields[ 'title' ] ) ) {
			$settings_title = Settings::get_option( sprintf( $settings_mask, 'title' ) );

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
				$settings_description = Settings::get_option( sprintf( $settings_mask, 'description' ) );
	
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
	private function set_default_preview_image ( array &$meta_fields ) {
		if ( empty( $meta_fields[ 'image_uri' ] ) ) {
			$default_preview_image_id = Settings::get_option( 'preview_image_id' );

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
	 * @since 3.4.0 Has `$queried_object` argument.
	 * 
	 * @param array                                      $meta_fields    Meta fields.
	 * @param \WP_Post|\WP_Post_Type|\WP_Term|\WP_User|null  $queried_object Queried object.
	 */
	private function replace_tags ( array &$meta_fields, \WP_Post|\WP_Post_Type|\WP_Term|\WP_User|null $queried_object ) {
		foreach ( [ 'title', 'description' ] as $key ) {
			$tags = MetaFields::$replace_tags;

			if ( $queried_object ) {
				switch ( get_class( $queried_object ) ) {
					case 'WP_Post':
						$post_type = get_post_type( $queried_object );
						$post_id   = $queried_object->ID;

						$tags = array_merge( $tags, apply_filters( "ymfseo_{$post_type}_posts_tags", [], $post_id ) );
						break;
					case 'WP_Term':
						$taxonomy = $queried_object->taxonomy;
						$term_id  = $queried_object->term_id;
						
						$tags = array_merge( $tags, apply_filters( "ymfseo_{$taxonomy}_taxonomy_tags", [], $term_id ) );
						break;
				}
			}

			foreach ( $tags as $tag => $value ) {
				$meta_fields[ $key ] = str_replace( $tag, $value, $meta_fields[ $key ] );
			}
		}
	}

	/**
	 * Sets meta fields to current instance.
	 * 
	 * @param array $meta_fields Meta fields values.
	 */
	private function set_meta_fields ( array $meta_fields ) {
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
	 * @param \WP_Post|\WP_Post_Type|\WP_Term|\WP_User|null $queried_object Queried object
	 * 
	 * @return array Prepared Schema.org array for printing JSON-LD.
	 */
	public function get_schema_org ( $queried_object = null ) : array {
		global $wp;

		if ( is_null( $queried_object ) ) {
			$queried_object = get_queried_object();
		}

		// Sets object data template.
		$schema_org_blank = [
			'WebPage' => [
				'@type'      => $this->page_type,
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
		if ( $this->description ) {
			$schema_org_blank[ 'WebPage' ][ 'description' ] = $this->description;
		}
		// Adds WebPage image preview URI.
		if ( $this->image_uri ) {
			$schema_org_blank[ 'WebPage' ][ 'image' ] = $this->image_uri;
		}
		// Adds WebPage dates.
		if ( $queried_object && 'WP_Post' == get_class( $queried_object ) ) {
			$schema_org_blank[ 'WebPage' ][ 'datePublished' ] = get_the_date( 'c', $queried_object );
			$schema_org_blank[ 'WebPage' ][ 'dateModified' ]  = get_the_modified_date( 'c', $queried_object );
		}

		// Adds representative's details.
		$rep_data = [];

		$rep_type = Settings::get_option( 'rep_type' );

		$rep_name = Settings::get_option( "rep_{$rep_type}_name" );
		if ( $rep_name ) $rep_data[ 'name' ] = $rep_name;

		$rep_email = Settings::get_option( 'rep_email' );
		if ( $rep_email ) $rep_data[ 'email' ] = $rep_email;

		$rep_phone = Settings::get_option( 'rep_phone' );
		if ( $rep_phone ) $rep_data[ 'telephone' ] = $rep_phone;
		
		// Sets organization address.
		if ( 'org' === $rep_type ) {
			$address = [];

			$rep_city = Settings::get_option( 'rep_org_city' );
			if ( $rep_city ) {
				$address[ 'addressLocality' ] = $rep_city;
			}

			$rep_region = Settings::get_option( 'rep_org_region' );
			if ( $rep_region ) {
				$address[ 'addressRegion' ] = $rep_region;
			}

			$rep_address = Settings::get_option( 'rep_org_address' );
			if ( $rep_address ) {
				$address[ 'streetAddress' ] = $rep_address;
			}

			$rep_postal_code = Settings::get_option( 'rep_org_postal_code' );
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
		$rep_image_id = Settings::get_option( 'rep_image_id' );
		if ( $rep_image_id ) {
			$rep_data[ 'image' ] = wp_get_attachment_url( $rep_image_id );
		}

		// Pre-builds output object.
		if ( ! empty( $rep_data ) ) {
			$rep_data = array_merge([
				'@type' => match ( $rep_type ) {
					'org'    => Settings::get_option( 'rep_org_type' ),
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
			'@graph'   => array_values( $schema_org ),
		];

		return $schema_org;
	}
}