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
	public static function init () : void {
		// Adds posts quick edit fields.
		add_action( 'quick_edit_custom_box', function ( string $column_name, string $post_type ) {
			if ( ! in_array( $post_type, array_values( YMFSEO::get_public_post_types() ) ) ) {
				return;
			}

			if ( 'ymfseo' !== $column_name ) {
				return;
			}

			wp_nonce_field( YMFSEO_BASENAME, 'ymfseo_post_nonce' );

			?>
				<fieldset class="inline-edit-col-right">
					<legend class="inline-edit-legend"><?php esc_html_e( 'SEO', 'ym-fast-seo' ); ?></legend>

					<div class="inline-edit-col">
						<label>
							<span class="title"><?php esc_html_e( 'Title', 'ym-fast-seo' ); ?></span>
							<span class="input-text-wrap">
								<input type="text" name="ymfseo-title">
							</span>
						</label>

						<label>
							<span class="title"><?php esc_html_e( 'Description', 'ym-fast-seo' ); ?></span>
							<textarea cols="22" rows="1" name="ymfseo-description"></textarea>
						</label>

						<label>
							<span class="title"><?php esc_html_e( 'Page Type', 'ym-fast-seo' ); ?></span>
							<select name="ymfseo-page-type">
								<?php
									$default_page_type       = YMFSEO_Settings::get_option( "post_type_page_type_$post_type" );
									$default_page_type_label = __( YMFSEO::$page_types[ $default_page_type ], 'ym-fast-seo' ); // phpcs:ignore
				
									printf( '<option value="default">%s (%s)</option>',
										esc_html__( 'Default', 'ym-fast-seo' ),
										esc_html( $default_page_type_label ),
									);
									
									foreach ( YMFSEO::$page_types as $value => $label ) {
										printf( '<option value="%s">%s</option>',
											esc_attr( $value ),
											esc_html( $label ),
										);
									}
								?>
							</select>
						</label>

						<label class="alignleft">
							<?php printf( '<input type="checkbox" name="%1$s" value="1">',
								'ymfseo-noindex',
							); ?>
							<span class="checkbox-title"><?php esc_html_e( 'Disallow indexing', 'ym-fast-seo' ); ?></span>
						</label>
					</div>
				</fieldset>
			<?php
		}, 10, 2 );
		add_action( 'admin_footer', function () {
			?>
				<script>
					jQuery( document ).ready( function ( $ ) {
						$( document ).on( 'click', '.editinline', function () {
							const tr = $( this ).closest( 'tr' );

							const title       = tr.find( 'input[ name="ymfseo-title-value" ]' ).val();
							const description = tr.find( 'input[ name="ymfseo-description-value" ]' ).val();
							const pageType    = tr.find( 'input[ name="ymfseo-page-type-value" ]' ).val();
							const isNoindex   = parseInt( tr.find( 'input[ name="ymfseo-noindex-value" ]' ).val() );

							$( 'input[ name="ymfseo-title" ]',          '.inline-edit-row' ).val( title );
							$( 'textarea[ name="ymfseo-description" ]', '.inline-edit-row' ).val( description );
							$( 'select[ name="ymfseo-page-type" ]',     '.inline-edit-row' ).val( pageType );
							$( 'input[ name="ymfseo-noindex" ]',        '.inline-edit-row' ).prop( 'checked', 1 == isNoindex );
						});
					});
				</script>
			<?php
		});


		// Manages posts and terms custom SEO column.
		add_action( 'init', function () {
			if ( ! YMFSEO_Checker::is_current_user_can_edit_metas() ) {
				return;
			}

			// Post types.
			foreach ( YMFSEO::get_public_post_types() as $post_type ) {
				add_filter( "manage_{$post_type}_posts_columns", 'YMFSEO_Meta_Fields::manage_seo_columns' );
				add_action( "manage_{$post_type}_posts_custom_column" , function ( string $column, int $post_id ) : void {
					if ( 'ymfseo' === $column ) {
						$check = YMFSEO_Checker::check_seo( get_post( $post_id ) );

						printf( '<div class="column-ymfseo__dot" title="%s"><span class="%s"></span><div>',
							esc_attr( implode( '&#013;', $check[ 'notes' ] ) ),
							esc_attr( $check[ 'status' ] ),
						);

						$meta_fields = new YMFSEO_Meta_Fields( get_post( $post_id ), false );

						printf( '<input name="ymfseo-title-value"       value="%s" hidden>', esc_attr( $meta_fields->title ) );
						printf( '<input name="ymfseo-description-value" value="%s" hidden>', esc_attr( $meta_fields->description ) );
						printf( '<input name="ymfseo-page-type-value"   value="%s" hidden>', esc_attr( $meta_fields->page_type ) );
						printf( '<input name="ymfseo-noindex-value"     value="%d" hidden>', esc_attr( $meta_fields->noindex ? 1 : 0 ) );
					}
				}, 10, 2 );
			}

			// Taxonomies.
			foreach ( YMFSEO::get_public_taxonomies() as $taxonomy ) {
				if ( class_exists( 'WooCommerce' ) ) {
					if ( in_array( $taxonomy, [ 'product_cat', 'product_brand' ] ) ) {
						break;
					}
				}

				add_filter( "manage_edit-{$taxonomy}_columns", 'YMFSEO_Meta_Fields::manage_seo_columns', 20 );
				add_action( "manage_{$taxonomy}_custom_column" , function ( $string, string $column, int $term_id  ) : void {
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

			add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( WP_Post $post ) {
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
				add_action( "{$taxonomy}_edit_form_fields", function ( WP_Term $term, string $taxonomy ) {
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

			// Updates metas.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			YMFSEO_Meta_Fields::update_meta([
				'title'       => YMFSEO_Sanitizer::sanitize_text_field(
					$_POST[ 'ymfseo-title' ] ?? YMFSEO_Meta_Fields::$default_values[ 'title' ]
				),
				'description' => YMFSEO_Sanitizer::sanitize_text_field(
					$_POST[ 'ymfseo-description' ] ?? YMFSEO_Meta_Fields::$default_values[ 'description' ]
				),
				'page_type'   => YMFSEO_Sanitizer::sanitize_text_field(
					$_POST[ 'ymfseo-page-type' ] ?? YMFSEO_Meta_Fields::$default_values[ 'page_type' ]
				),
				'noindex'     => YMFSEO_Sanitizer::sanitize_text_field(
					$_POST[ 'ymfseo-noindex' ] ?? YMFSEO_Meta_Fields::$default_values[ 'noindex' ]
				),
			], $post_id, 'post' );
			// phpcs:enable
		});


		// Saves term metas after saving term.
		add_action( 'saved_term', function ( int $term_id, int $tt_id, string $taxonomy ) {
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
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			YMFSEO_Meta_Fields::update_meta([
				'title'       => YMFSEO_Sanitizer::sanitize_text_field(
					$_POST[ 'ymfseo-title' ] ?? YMFSEO_Meta_Fields::$default_values[ 'title' ]
				),
				'description' => YMFSEO_Sanitizer::sanitize_text_field(
					$_POST[ 'ymfseo-description' ] ?? YMFSEO_Meta_Fields::$default_values[ 'description' ]
				),
			], $term_id, 'term' );
			// phpcs:enable
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
	public static function manage_seo_columns ( array $columns ) : array {
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
	 * Retrieves plugin excerpt length.
	 * 
	 * @since 3.3.4
	 * 
	 * @return int
	 */
	public static function get_excerpt_length () : int {
		$locale = get_locale();

		if ( isset( YMFSEO_Meta_Fields::$excerpt_length_map[ $locale ] ) ) {
			return YMFSEO_Meta_Fields::$excerpt_length_map[ $locale ];
		}

		return YMFSEO_Meta_Fields::$excerpt_length_map[ 'default' ];
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
						// Plugin excerpt data.
						$plugin_excerpt_length = YMFSEO_Meta_Fields::get_excerpt_length();
						$plugin_excerpt_more   = '&hellip;';

						// Theme excerpt data.
						$theme_excerpt_length  = apply_filters( 'excerpt_length', 55 );
						$theme_excerpt_more    = apply_filters( 'excerpt_more', '[&hellip;]' );

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

						if ( YMFSEO_Settings::get_option( "taxonomy_noindex_{$taxonomy}" ) ) {
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
	 * @since 3.4.0 Has `$queried_object` argument.
	 * 
	 * @param array                                      $meta_fields    Meta fields.
	 * @param WP_Post|WP_Post_Type|WP_Term|WP_User|null  $queried_object Queried object.
	 */
	private function replace_tags ( array &$meta_fields, WP_Post|WP_Post_Type|WP_Term|WP_User|null $queried_object ) : void {
		foreach ( [ 'title', 'description' ] as $key ) {
			$tags = YMFSEO_Meta_Fields::$replace_tags;

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
	private function set_meta_fields ( array $meta_fields ) : void {
		$this->title       = $meta_fields[ 'title' ];
		$this->description = $meta_fields[ 'description' ];
		$this->image_uri   = $meta_fields[ 'image_uri' ];
		$this->page_type   = $meta_fields[ 'page_type' ];
		$this->noindex     = $meta_fields[ 'noindex' ];
	}
}