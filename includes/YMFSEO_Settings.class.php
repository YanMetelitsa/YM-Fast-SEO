<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * YM Fast SEO plugin settings class.
 * 
 * @since 2.0.0
 */
class YMFSEO_Settings {
	/**
	 * Settings module parameters.
	 * 
	 * @var array
	 */
	public static array $params = [];
	
	/**
	 * Default YM Fast SEO settings options values.
	 * 
	 * @var array
	 */
	public static array $default_settings = [];
	
	/**
	 * Contains info adbout registered sections.
	 * 
	 * @var array
	 */
	public static array $registered_sections = [];

	/**
	 * Inits YMFSEO Settings.
	 * 
	 * @since 3.0.0
	 */
	public static function init () : void {
		// Registers YM Fast SEO settings page.
		add_action( 'admin_menu', function () {
			add_options_page(
				YMFSEO_Settings::$params[ 'page_title' ],
				YMFSEO_Settings::$params[ 'menu_label' ],
				YMFSEO_Settings::$params[ 'capability' ],
				YMFSEO_Settings::$params[ 'page_slug' ],
				fn () => include YMFSEO_ROOT_DIR . 'parts/settings-page.php',
				YMFSEO_Settings::$params[ 'menu_position' ]
			);
		});

		// Adds YM Fast SEO settings sections and options.
		add_action( 'admin_init', function () {
			/**
			 * General section.
			 */

			/* translators: Settings section name */
			YMFSEO_Settings::add_section( 'general', __( 'General', 'ym-fast-seo' ), 'dashicons-admin-settings', [
				/* translators: %s: Link to general settings page */
				'description' => sprintf( wp_kses_post( __( 'To update the site name and description, navigate to the <a href="%s">general settings page</a>.', 'ym-fast-seo' ) ),
					esc_url( get_admin_url( null, 'options-general.php' ) ),
				),
			]);
			YMFSEO_Settings::register_option(
				'hide_title_parts',
				/* translators: Verb */
				__( 'Clear Titles', 'ym-fast-seo' ),
				'boolean',
				'general',
				'checkbox',
				[
					/* translators: Option description */
					'label'       => __( 'Simplify title tags by removing unnecessary parts', 'ym-fast-seo' ),
					'description' => __( 'The site description on the front page, and the site name on all other pages.', 'ym-fast-seo' ),
				],
			);
			YMFSEO_Settings::register_option(
				'title_separator',
				__( 'Title Separator', 'ym-fast-seo' ),
				'string',
				'general',
				'separator',
				[
					'options'     => [ '|', '-', '–', '—', ':', '/', '·', '•', '⋆', '~', '«', '»', '<', '>' ],
					
					'description' => sprintf(
						/* translators: %s: Separator tag name */
						__( 'Specify the separator used in titles and %s tag.', 'ym-fast-seo' ),
						'<code>%sep%</code>',
					),
				],
			);
			YMFSEO_Settings::register_option(
				'clear_excerpts',
				/* translators: Verb */
				__( 'Clear Excerpts', 'ym-fast-seo' ),
				'boolean',
				'general',
				'checkbox',
				[
					'label'       => __( 'Enhance excerpts by removing unnecessary parts', 'ym-fast-seo' ),
					'description' => __( 'Removes headings from excerpts.', 'ym-fast-seo' ),
				],
			);
			YMFSEO_Settings::register_option(
				'hide_users_sitemap',
				/* translators: Verb */
				__( 'Hide Users Sitemap', 'ym-fast-seo' ),
				'boolean',
				'general',
				'checkbox',
				[
					'label' => __( 'Exclude the users page from the sitemap', 'ym-fast-seo' ),
				],
			);

			/**
			 * Post Types section.
			 */

			/* translators: Settings section name */
			YMFSEO_Settings::add_section( 'post-types', __( 'Post Types', 'ym-fast-seo' ), 'dashicons-admin-post', [
				'description' => implode( "</p><p>",[
					__( 'The default title and page type values for single post pages.', 'ym-fast-seo' ),
					sprintf(
						/* translators: %s: List of available tags */
						__( 'Available tags: %s.', 'ym-fast-seo' ),
						implode( ', ',[
							'<code>%post_title%</code>',
							...array_map( function ( $tag ) {
								return "<code>$tag</code>";
							}, array_keys( YMFSEO_Meta_Fields::$replace_tags ) ),
						]),
					),
				]),
			]);
			foreach ( YMFSEO::get_public_post_types( 'objects' ) as $post_type ) {
				YMFSEO_Settings::register_option(
					"post_type_title_{$post_type->name}",
					$post_type->label,
					'string',
					'post-types',
					'text',
					[
						'placeholder' => '%post_title%',
						'menu_icon'   => $post_type->menu_icon,
					],
				);
				YMFSEO_Settings::register_option(
					"post_type_page_type_{$post_type->name}",
					__( 'Page Type', 'ym-fast-seo' ),
					'string',
					'post-types',
					'select',
					[
						'class'   => 'sub-field',
						'options' => YMFSEO::$page_types,
					],
				);
			}

			/**
			 * Taxonomies section.
			 */

			/* translators: Settings section name */
			YMFSEO_Settings::add_section( 'taxonomies', __( 'Taxonomies', 'ym-fast-seo' ), 'dashicons-tag', [
				'description' => implode( "</p><p>",[
					__( 'The default title and description values for taxonomy term pages.', 'ym-fast-seo' ),
					sprintf(
						/* translators: %s: List of available tags */
						__( 'Available tags: %s.', 'ym-fast-seo' ),
						implode( ', ',[
							'<code>%term_title%</code>',
							...array_map( function ( $tag ) {
								return "<code>$tag</code>";
							}, array_keys( YMFSEO_Meta_Fields::$replace_tags ) ),
						]),
					),
				]),
			]);
			foreach ( YMFSEO::get_public_taxonomies( 'objects' ) as $taxonomy ) {
				YMFSEO_Settings::register_option(
					"taxonomy_title_{$taxonomy->name}",
					$taxonomy->label,
					'string',
					'taxonomies',
					'text',
					[
						'placeholder' => '%term_title%',
					],
				);
				YMFSEO_Settings::register_option(
					"taxonomy_description_{$taxonomy->name}",
					__( 'Description', 'ym-fast-seo' ),
					'string',
					'taxonomies',
					'textarea',
					[
						'class' => 'sub-field',
					],
				);
				YMFSEO_Settings::register_option(
					"taxonomy_noindex_{$taxonomy->name}",
					__( 'Indexing', 'ym-fast-seo' ),
					'boolean',
					'taxonomies',
					'checkbox',
					[
						'class' => 'sub-field',
						'label' => __( 'Disallow indexing', 'ym-fast-seo' ),
					],
				);
			}

			/**
			 * Site Preview section.
			 */

			/* translators: Settings section name */
			YMFSEO_Settings::add_section( 'preview', __( 'Site Preview', 'ym-fast-seo' ), 'dashicons-format-image' );
			YMFSEO_Settings::register_option(
				'preview_image_id',
				/* translators: Noun */
				__( 'Preview Image', 'ym-fast-seo' ),
				'integer',
				'preview',
				'image',
				[
					'description' => sprintf(
						/* translators: %s: Size in pixels */
						__( 'The image link will be added to the meta tags if no post/page thumbnail is set. The recommended size is %s pixels.', 'ym-fast-seo' ),
						'<code>1200 × 630</code>',
					),
				],
			);
			YMFSEO_Settings::register_option(
				'preview_size',
				/* translators: Noun */
				__( 'Preview Size', 'ym-fast-seo' ),
				'string',
				'preview',
				'select',
				[
					'options' => [
						/* translators: Twitter Cards property */
						'summary'             => __( 'Summary', 'ym-fast-seo' ),
						/* translators: Twitter Cards property */
						'summary_large_image' => __( 'Large Image', 'ym-fast-seo' ),
					],
				],
			);

			/**
			 * Representative section.
			 */

			/* translators: Settings section name */
			YMFSEO_Settings::add_section( 'representative', __( 'Representative', 'ym-fast-seo' ), 'dashicons-businessperson', [
				'description' => __( 'If this website represents a company or person, you can include some details. This information will not be visible to visitors but will be available to search engines.', 'ym-fast-seo' ),
			]);
			YMFSEO_Settings::register_option(
				'rep_type',
				__( 'Represented by', 'ym-fast-seo' ),
				'string',
				'representative',
				'select',
				[
					'class'   => 'rep-type',
					'options' => [
						'org'    => __( 'Organization', 'ym-fast-seo' ),
						'person' => __( 'Person', 'ym-fast-seo' ),
					],
				],
			);
			YMFSEO_Settings::register_option(
				'rep_org_type',
				__( 'Organization Type', 'ym-fast-seo' ),
				'string',
				'representative',
				'select',
				[
					'class'   => 'rep-org',
					'options' => [
						/* translators: Organization type */
						'Organization'            => __( 'Regular', 'ym-fast-seo' ),
						/* translators: Organization type */
						'LocalBusiness'           => __( 'Local Business', 'ym-fast-seo' ),
						/* translators: Organization type */
						'OnlineBusiness'          => __( 'Online Business', 'ym-fast-seo' ),
						/* translators: Organization type */
						'OnlineStore'             => __( 'Online Store', 'ym-fast-seo' ),
						/* translators: Organization type */
						'NewsMediaOrganization'   => __( 'News/Media', 'ym-fast-seo' ),
						/* translators: Organization type */
						'MedicalOrganization'     => __( 'Medical', 'ym-fast-seo' ),
						/* translators: Organization type */
						'EducationalOrganization' => __( 'Educational', 'ym-fast-seo' ),
						/* translators: Organization type */
						'SportsOrganization'      => __( 'Sports', 'ym-fast-seo' ),
						/* translators: Organization type */
						'MusicGroup'              => __( 'Music Group', 'ym-fast-seo' ),
						/* translators: Organization type */
						'NGO'                     => __( 'Non-Governmental', 'ym-fast-seo' ),
						/* translators: Organization type */
						'Project'                 => __( 'Project', 'ym-fast-seo' ),
					],
				],
			);
			YMFSEO_Settings::register_option(
				'rep_org_name',
				__( 'Organization Name', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'class'        => 'rep-org',
					'autocomplete' => 'organization',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_person_name',
				__( 'Person Name', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'class'        => 'rep-person',
					'autocomplete' => 'name',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_email',
				__( 'Email', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'type'         => 'email',
					'autocomplete' => 'email',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_phone',
				__( 'Phone', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'type'         => 'tel',
					'autocomplete' => 'tel',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_org_city',
				__( 'City', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'class' => 'rep-org',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_org_region',
				__( 'Region', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'class' => 'rep-org',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_org_address',
				__( 'Address', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'class' => 'rep-org',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_org_postal_code',
				__( 'Postal Code', 'ym-fast-seo' ),
				'string',
				'representative',
				'text',
				[
					'class'       => 'rep-org',
					'input-class' => 'code',
				],
			);
			YMFSEO_Settings::register_option(
				'rep_image_id',
				__( 'Image', 'ym-fast-seo' ),
				'integer',
				'representative',
				'image',
				[
					'description' => __( 'The representative\'s image will be available to search engines.', 'ym-fast-seo' ),
				],
			);

			/**
			 * Integrations section.
			 */

			/* translators: Settings section name */
			YMFSEO_Settings::add_section( 'integrations', __( 'Integrations', 'ym-fast-seo' ), 'dashicons-rest-api', [
				'description' => sprintf(
					/* translators: %s: <meta> tag `content` attribute name */
					__( 'Enter the verification codes for the required services. They are usually found in the %s attribute of the verification meta tag.', 'ym-fast-seo' ),
					'<code>content</code>',
				),
			]);
			YMFSEO_Settings::register_option(
				'google_search_console_key',
				sprintf( '<a href="https://search.google.com/search-console" target="_blank">%s</a>',
					/* translators: Service name (probably doesn't translate) */
					__( 'Google Search Console', 'ym-fast-seo' ),
				),
				'string',
				'integrations',
				'text',
				[
					'input-class' => 'code',
				],
			);
			YMFSEO_Settings::register_option(
				'bing_webmaster_tools_key',
				sprintf( '<a href="https://www.bing.com/webmasters" target="_blank">%s</a>',
					/* translators: Service name (probably doesn't translate) */
					__( 'Bing Webmaster Tools', 'ym-fast-seo' ),
				),
				'string',
				'integrations',
				'text',
				[
					'input-class' => 'code',
				],
			);
			YMFSEO_Settings::register_option(
				'yandex_webmaster_key',
				sprintf( '<a href="https://webmaster.yandex.ru/" target="_blank">%s</a>',
					/* translators: Service name (probably doesn't translate) */
					__( 'Yandex Webmaster', 'ym-fast-seo' ),
				),
				'string',
				'integrations',
				'text',
				[
					'input-class' => 'code',
				],
			);
			YMFSEO_Settings::register_option(
				'indexnow_key',
				/* translators: IndexNow – Protocol name (probably doesn't translate) */
				__( 'IndexNow Key', 'ym-fast-seo' ),
				'string',
				'integrations',
				'text',
				[
					'input-class' => 'code',
					'readonly'    => true,
					'description' => __( 'IndexNow API key is generated automatically.', 'ym-fast-seo' ),
				],
			);
			YMFSEO_Settings::register_option(
				'indexnow_enabled',
				/* translators: Option name */
				__( 'IndexNow Sending', 'ym-fast-seo' ),
				'boolean',
				'integrations',
				'checkbox',
				[
					'label'       => __( 'Enable IndexNow sending', 'ym-fast-seo' ),
					'description' => implode([
						get_option( 'blog_public' ) ? '' : '<strong>' . __( 'Search engines are discouraged by the site\'s settings.', 'ym-fast-seo' ) . '</strong><br>',
						sprintf(
							/* translators: %s - link to settings page */
							__( 'If the site is configured to <a href="%s">discourage indexing</a>, IndexNow will not function, regardless of this option.', 'ym-fast-seo' ),
							get_admin_url( null, 'options-reading.php#blog_public' ),
						),
					]),
				],
			);

			/**
			 * Redirects section.
			 */

			/* translators: Redirects section name */
			// YMFSEO_Settings::add_section( 'redirects', __( 'Redirects', 'ym-fast-seo' ), 'dashicons-randomize' );
			// YMFSEO_Settings::register_option(
			// 	'redirects',
			// 	__( 'Redirects', 'ym-fast-seo' ),
			// 	'array',
			// 	'redirects',
			// 	'redirects',
			// );

			/**
			 * Additional section.
			 */

			/* translators: Settings section name */
			YMFSEO_Settings::add_section( 'additional', __( 'Additional', 'ym-fast-seo' ), 'dashicons-admin-generic' );
			YMFSEO_Settings::register_option(
				'head_scripts',
				__( 'Head Scripts', 'ym-fast-seo' ),
				'string',
				'additional',
				'textarea',
				[
					'rows'        => 8,
					'codemirror'  => true,
					'description' => sprintf(
						/* translators: %s: <head> tag name */
						__( 'Here you can insert analytics counters code and other scripts. The code will be printed inside the %s tag.', 'ym-fast-seo' ),
						'<code>&lt;head&gt;</code>',
					),
				],
			);
			YMFSEO_Settings::register_option(
				'head_scripts_only_visitors',
				__( 'Only for Visitors', 'ym-fast-seo' ),
				'boolean',
				'additional',
				'checkbox',
				[
					'label' => __( 'Do not insert head scripts for logged-in users', 'ym-fast-seo' ),
				],
			);
			YMFSEO_Settings::register_option(
				'robots_txt',
				sprintf(
					/* translators: %s: robots.txt */
					__( 'Edit %s', 'ym-fast-seo' ),
					'robots.txt',
				),
				'string',
				'additional',
				'robots-txt',
			);
		});
	}

	/**
	 * Adds section to YM Fast SEO settings page.
	 * 
	 * @since 3.2.0 Has `$icon` argument.
	 * 
	 * @param string $slug  Section slug.
	 * @param string $title Section title.
	 * @param string $icon  Full dashicon name e.g. `dashicons-admin-generic`.
	 * @param array  $args {
	 * 		Section arguments.
	 * 
	 * 		@type string $description Section description below title. 
	 * }
	 */
	public static function add_section ( string $slug, string $title, string $icon, array $args = [] ) : void {
		YMFSEO_Settings::$registered_sections[] = [
			'slug'  => $slug,
			'title' => $title,
			'icon'  => $icon,
		];

		add_settings_section( "ymfseo_{$slug}_section",
			"<span class=\"dashicons $icon\"></span> $title",
			fn ( $args ) => include YMFSEO_ROOT_DIR . 'parts/settings-section.php',
			YMFSEO_Settings::$params[ 'page_slug' ],
			[
				'after_section' => implode( ' ', [
					sprintf( '<div class="ymfseo-submit"><button class="%s">%s</button></div>',
						esc_attr( 'button button-primary' ),
						esc_attr__( 'Save Changes', 'ym-fast-seo' ),
					),
					sprintf( '</section><section>' ),
				]),
				...$args,
			]
		);
	}

	/**
	 * Registers YM Fast SEO settings option.
	 * 
	 * @param string $slug       Option name.
	 * @param string $title      Option title.
	 * @param string $type       Option type.
	 * @param string $section    Option section slug without 'ymfseo_$_section'.
	 * @param string $field_part Option field part file name without 'parts/settings-$-field.php'.
	 * @param array  $args {
	 * 		Option arguments.
	 * 
	 * 		@type string $menu_icon Menu dashicon name.
	 * }
	 */
	public static function register_option ( string $slug, string $title, string $type, string $section, string $field_part, array $args = [] ) : void {
		// Checks is default value exist.
		if ( ! isset( YMFSEO_Settings::$default_settings[ $slug ] ) ) {
			$break   = true;
			$default = '';

			// Allowed settings by mask. Slug mask => default value.
			$allowed = [
				'post_type_title_'       => '',
				'post_type_page_type_'   => 'ItemPage',
				'taxonomy_title_'        => '',
				'taxonomy_description_'  => '',
				'taxonomy_noindex_'      => false,
			];

			// Checks is setting allowed by mask.
			foreach ( $allowed as $allowed_item => $default_value ) {
				if ( str_contains( $slug, $allowed_item ) ) {
					$break   = false;
					$default = $default_value;
					
					break;
				}
			}

			// Breaks if not allowed.
			if ( $break ) return;

			// Begins setting init.
			YMFSEO_Settings::$default_settings[ $slug ] = $default;
		}

		// Defines sanitize callback.
		$sanitize_callback = 'sanitize_text_field';

		if ( in_array( $slug, [ 'head_scripts', 'robots_txt' ] ) ) {
			$sanitize_callback = function ( $value ) {
				return wp_unslash( $value );
			};
		}

		if ( 'redirects' == $slug ) {
			$sanitize_callback = function ( $value ) : array {
				foreach ( $value as $i => $item ) {
					foreach ( $item as $param ) {
						if ( empty( $param ) && isset( $value[ $i ] ) ) {
							unset( $value[ $i ] );
						}
					}
				}

				if ( ! is_array( $value ) ) {
					return [];
				}

				return $value;
			};
		}

		$tagged_options = [
			'post_type_title_', 'post_type_description_',
			'taxonomy_title_', 'taxonomy_description_'
		];
		
		foreach ( $tagged_options as $option_slug ) {
			if ( str_contains( $slug, $option_slug ) ) {
				$sanitize_callback = [ 'YMFSEO_Sanitizer', 'sanitize_text_field' ];
			}
		}

		// Check menu icon.
		$menu_icon = false;

		if ( isset( $args[ 'menu_icon' ] ) ) {
			$menu_icon = $args[ 'menu_icon' ];

			if ( ! str_contains( $menu_icon, 'dashicons-' ) ) {
				$menu_icon = false;
			}
		}

		// Registers setting.
		// phpcs:ignore
		register_setting( YMFSEO_Settings::$params[ 'page_slug' ], "ymfseo_$slug", [
			'type'              => $type,
			'default'           => YMFSEO_Settings::$default_settings[ $slug ],
			'sanitize_callback' => $sanitize_callback,
		]);

		// Adds field.
		add_settings_field( "ymfseo_$slug",
			sprintf( '%s %s',
				$menu_icon ? "<span class=\"dashicons {$menu_icon}\"></span>" : '',
				$title,
			),
			fn ( $args ) => include YMFSEO_ROOT_DIR . "parts/settings-$field_part-field.php",
			YMFSEO_Settings::$params[ 'page_slug' ],
			"ymfseo_{$section}_section",
			array_merge( [ 'label_for' => "ymfseo_$slug" ], $args )
		);
	}

	/**
	 * Prepares option name before usage.
	 * 
	 * @since 3.0.0
	 * 
	 * @param string $option Option name.
	 */
	public static function prepare_option_name ( string &$option ) : void {
		if ( 'ymfseo_' !== mb_substr( $option, 0, 7 ) ) {
			$option = "ymfseo_$option";
		}
	}

	/**
	 * Updates option value in database or create new.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option Option name. Allowed without 'ymfseo_'.
	 * @param mixed  $value  Option value.

	 * @return bool `true` if option updated, `false` if no changes or error.
	 */
	public static function update_option ( string $option, mixed $value ) : bool {
		YMFSEO_Settings::prepare_option_name( $option );

		return update_option( $option, $value );
	}

	/**
	 * Retrieves an YMFSEO option value based on an option name.
	 * 
	 * @since 2.1.0 Has `$default` argument.
	 * 
	 * @param string $option  Option name. Allowed without 'ymfseo_'.
	 * @param mixed  $default Default option value.
	 * 
	 * @return mixed Option or default value.
	 */
	public static function get_option ( string $option, mixed $default = false ) : mixed {
		YMFSEO_Settings::prepare_option_name( $option );

		$default_value = YMFSEO_Settings::$default_settings[ str_replace( 'ymfseo_', '', $option ) ] ?? $default;

		return get_option( $option, $default_value );
	}
}