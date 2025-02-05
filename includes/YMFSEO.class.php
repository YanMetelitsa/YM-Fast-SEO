<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main YM Fast SEO class.
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
	 * Inits YM Fast SEO Plugin.
	 */
	public static function init () : void {
		add_action( 'init', function () {
			// Defines available page types.
			YMFSEO::$page_types = [
				/* translators: Web page type */
				'WebPage'           => __( 'Regular Page', 'ym-fast-seo' ),
				/* translators: Web page type */
				'CollectionPage'    => __( 'Collection Page', 'ym-fast-seo' ),
				/* translators: Web page type */
				'ItemPage'          => __( 'Item Page', 'ym-fast-seo' ),
				/* translators: Web page type */
				'AboutPage'         => __( 'About Page', 'ym-fast-seo' ),
				/* translators: Web page type */
				'FAQPage'           => __( 'FAQ Page', 'ym-fast-seo' ),
				/* translators: Web page type */
				'ContactPage'       => __( 'Contact Page', 'ym-fast-seo' ),
				/* translators: Web page type */
				'CheckoutPage'      => __( 'Checkout Page', 'ym-fast-seo' ),
				/* translators: Web page type */
				'SearchResultsPage' => __( 'Search Results Page', 'ym-fast-seo' ),
			];

			// Defines default meta values.
			YMFSEO_Meta_Fields::$default_values = [
				'title'       => '',
				'description' => '',
				'image_uri'   => '',
				'page_type'   => 'default',
				'noindex'     => '',
			];

			// Defines settings arguments.
			YMFSEO_Settings::$params = [
				'page_title'    => __( 'SEO Settings', 'ym-fast-seo' ),
				'menu_label'    => __( 'SEO', 'ym-fast-seo' ),
				'menu_position' => 3,
				'capability'    => 'manage_options',
				'page_slug'     => 'ymfseo-settings',
			];

			// Defines default settings.
			YMFSEO_Settings::$default_settings = [
				'hide_title_parts'           => true,
				'title_separator'            => '|',
				'clear_excerpts'             => true,
				'hide_users_sitemap'         => true,
				'post_type_page_type_page'   => 'WebPage',
				'preview_image_id'           => 0,
				'preview_size'               => 'summary_large_image',
				'rep_type'                   => 'org',
				'rep_org_type'               => 'Organization',
				'rep_org_name'               => '',
				'rep_person_name'            => '',
				'rep_email'                  => '',
				'rep_phone'                  => '',
				'rep_org_city'               => '',
				'rep_org_region'             => '',
				'rep_org_address'            => '',
				'rep_org_postal_code'        => '',
				'rep_image_id'               => 0,
				'google_search_console_key'  => '',
				'bing_webmaster_tools_key'   => '',
				'yandex_webmaster_key'       => '',
				'indexnow_key'               => '',
				'indexnow_enabled'           => true,
				'redirects'                  => [],
				'head_scripts'               => '',
				'head_scripts_only_visitors' => true,
				'robots_txt'                 => '',
			];

			// Defines replace tags.
			YMFSEO_Meta_Fields::$replace_tags = [
				'%site_name%' => get_bloginfo( 'name' ),
				'%sep%'       => YMFSEO::get_separator(),
			];
		});


		// Adds links to plugin's card on Plugins page.
		add_filter( 'plugin_action_links_' . YMFSEO_BASENAME, function ( array $links ) : array {
			if ( YMFSEO_Checker::is_current_user_can_view_site_health() ) {
				array_unshift( $links, sprintf( '<a href="%s">%s</a>',
					admin_url( 'site-health.php?tab=ymfseo-site-health-tab' ),
					__( 'SEO Health', 'ym-fast-seo' ),
				));
			}

			if ( YMFSEO_Checker::is_current_user_can_manage_options() ) {
				array_unshift( $links, sprintf( '<a href="%s">%s</a>',
					menu_page_url( 'ymfseo-settings', false ),
					__( 'Settings', 'ym-fast-seo' ),
				));
			}

			return $links;
		});


		// Adds WordPress theme supports.
		add_action( 'after_setup_theme', function () {
			add_theme_support( 'title-tag' );
			add_theme_support( 'post-thumbnails' );
		});

		// Connects styles and scripts.
		add_action( 'admin_enqueue_scripts', function ( string $hook_suffix ) {
			global $_wp_admin_css_colors;

			$color_scheme    = get_user_option( 'admin_color', get_current_user_id() );
			$colors          = $_wp_admin_css_colors[ $color_scheme ]->colors;
			$primary_color   = $colors[ 1 ];
			$secondary_color = $colors[ 2 ];

			// CSS
			wp_enqueue_style( 'ymfseo-styles', YMFSEO_ROOT_URI . 'assets/css/ymfseo-style.css', [], YMFSEO_PLUGIN_DATA[ 'Version' ] );
			wp_add_inline_style( 'ymfseo-styles', ":root{--ymfseo-primary:{$primary_color};--ymfseo-secondary:{$secondary_color};}" );

			// JS
			wp_enqueue_script( 'ymfseo-script', YMFSEO_ROOT_URI . 'assets/js/ymfseo-scripts.js', [], YMFSEO_PLUGIN_DATA[ 'Version' ], true );
			wp_add_inline_script( 'ymfseo-script', 'const YMFSEO_WP = ' . wp_json_encode([
				'replaceTags' => YMFSEO_Meta_Fields::$replace_tags,
			]), 'before' );

			// WordPress
			if ( 'settings_page_' . YMFSEO_Settings::$params[ 'page_slug' ] == $hook_suffix ) {
				wp_enqueue_media();

				wp_enqueue_code_editor([
					'type'       => 'text/html',
					'codemirror' => [
						'indentUnit'     => 4,
						'indentWithTabs' => true,
						'lineWrapping'   => false,
					],
				]);
				wp_enqueue_script( 'wp-codemirror' );
			}
		});

		// Adds admin bar menu.
		add_action( 'admin_bar_menu', function ( WP_Admin_Bar $wp_admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$wp_admin_bar->add_menu([
				'id'     => 'ymfseo-admin-bar-menu',
				'title'  => sprintf(
					'<span class="ab-icon dashicons-welcome-view-site" style="top:2px"></span><span class="ab-label">%s</span>',
					__( 'SEO', 'ym-fast-seo' )
				),
				'href'   => admin_url( 'options-general.php?page=ymfseo-settings' ),
			]);

			$wp_admin_bar->add_menu([
				'id'     => 'ymfseo-admin-bar-menu-settings',
				'title'  => __( 'Settings', 'ym-fast-seo' ),
				'href'   => admin_url( 'options-general.php?page=ymfseo-settings' ),
				'parent' => 'ymfseo-admin-bar-menu',
			]);
			$wp_admin_bar->add_menu([
				'id'     => 'ymfseo-admin-bar-menu-health',
				'title'  => __( 'Health', 'ym-fast-seo' ),
				'href'   => admin_url( 'site-health.php?tab=ymfseo-site-health-tab' ),
				'parent' => 'ymfseo-admin-bar-menu',
			]);
		}, 70 );


		// Modifies title tag parts.
		add_filter( 'document_title_parts', function ( array $title ) : array {
			$meta_fields = new YMFSEO_Meta_Fields();

			// Hides title parts if option enabled.
			if ( YMFSEO_Settings::get_option( 'hide_title_parts' ) ) {
				if ( isset( $title[ 'site' ] ) )    unset( $title[ 'site' ] );
				if ( isset( $title[ 'tagline' ] ) ) unset( $title[ 'tagline' ] );
			}

			// Sets title tag the same as meta title if exists.
			if ( $meta_fields->title ) {
				$title[ 'title' ] = $meta_fields->title;
			}

			return $title;
		});

		// Modifies title tag separator.
		add_filter( 'document_title_separator', function ( string $sep ) : string {
			return YMFSEO::get_separator();
		});

		// Modifies robots meta tag.
		add_filter( 'wp_robots', function ( array $robots ) : array {
			$meta_fields = new YMFSEO_Meta_Fields();
			
			// Sets noindex if needed.
			if ( $meta_fields->noindex ) {
				$robots = array_merge( [ 'noindex' => true, 'nofollow' => true, ], $robots, );
			}

			// Sets default index and follow if noindex disabled.
			if ( ! isset( $robots[ 'nofollow' ] ) || ! $robots[ 'nofollow' ] ) {
				$robots = array_merge( [ 'follow' => true ], $robots );
			}
			if ( ! isset( $robots[ 'noindex' ] ) || ! $robots[ 'noindex' ] ) {
				$robots = array_merge(  [ 'index' => true ], $robots );
			}

			// Additional parameters.
			$robots[ 'max-snippet' ]       = '-1';
			$robots[ 'max-image-preview' ] = 'large';
			$robots[ 'max-video-preview' ] = '-1';

			return $robots;
		});

		// Prints head metas.
		add_action( 'wp_head', function () {
			include YMFSEO_ROOT_DIR . 'parts/head.php';
		}, 1 );


		// Adds redirects.
		// add_filter( 'mod_rewrite_rules', function ( string $rules ) : string {
		// 	$redirects_option = YMFSEO_Settings::get_option( 'redirects', [] );

		// 	if ( empty( $redirects_option ) ) {
		// 		return $rules;
		// 	}

		// 	$redirects = [
		// 		"\n# BEGIN YMFSEO Redirects",
		// 	];

		// 	foreach ( $redirects_option as $item ) {
		// 		$redirects[] = sprintf( '%s %d %s %s',
		// 			esc_html( $item[ 'is_regex' ] ? 'RedirectMatch' : 'Redirect' ),
		// 			esc_html( $item[ 'type' ] ),
		// 			esc_html( $item[ 'from' ] ),
		// 			esc_html( $item[ 'to' ] ),
		// 		);
		// 	}

		// 	$redirects[] = "# END YMFSEO Redirects\n\n";
		
		// 	return implode( "\n", $redirects ) . $rules;
		// });

		// Modifies robots.txt file.
		add_filter( 'robots_txt', function ( string $output ) : string {
			// Checks settings robots.txt.
			$settings_robots_txt = YMFSEO_Settings::get_option( 'robots_txt' );

			if ( ! empty( $settings_robots_txt ) ) {
				return $settings_robots_txt;
			}

			// Checks multisite sitemaps.
			if ( YMFSEO_Checker::is_subdir_multisite() ) {
				foreach ( get_sites() as $site ) {
					if ( get_main_site_id() != intval( $site->blog_id ) ) {
						$output .= sprintf(
							"Sitemap: %s\n",
							esc_url( get_home_url( $site->blog_id, 'wp-sitemap.xml' ) )
						);
					}
				}
			}

			return $output;
		}, 999 );
		
		// Removes headings from post excerpts.
		add_filter( 'excerpt_allowed_blocks', function ( array $allowed_blocks ) : array {
			if ( ! YMFSEO_Settings::get_option( 'clear_excerpts' ) ) {
				return $allowed_blocks;
			}

			return array_filter( $allowed_blocks, function ( $block ) {
				return ! in_array( $block, [
					'core/heading',
				]);
			});
		});

		// Removes users from the sitemap.
		add_filter( 'wp_sitemaps_add_provider', function ( WP_Sitemaps_Provider $provider, string $name ) : bool|WP_Sitemaps_Provider {
			if ( ! YMFSEO_Settings::get_option( 'hide_users_sitemap' ) ) {
				return $provider;
			}

			if ( 'users' === $name ) {
				return false;
			}
		
			return $provider;
		}, 10, 2 );

		// Removes `noindex` pages from the sitemap.
		add_filter( 'wp_sitemaps_posts_query_args', function ( array $args, string $post_type ) : array {
			$args[ 'post__not_in' ] = $args[ 'post__not_in' ] ?? [];

			$page_query = new WP_Query([
				'post_type'      => YMFSEO::get_public_post_types(),
				'meta_key'       => 'ymfseo_fields',
				'meta_value'     => 'noindex";s:1',
				'meta_compare'   => 'LIKE',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]);
			while ( $page_query->have_posts() ) {
				$page_query->the_post();
				
				$args[ 'post__not_in' ][] = get_the_ID();
			}
			wp_reset_postdata();
		
			return $args;
		}, 10, 2 );

		// Adds preview image media state.
		add_filter( 'display_media_states', function ( array $media_states, WP_Post $post ) : array {
			if ( YMFSEO_Settings::get_option( 'preview_image_id' ) == $post->ID ) {
				$media_states[] =  __( 'Preview Image', 'ym-fast-seo' );
			}
		
			return $media_states;
		}, 10, 2 );
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
	 * Retrives filtered document title separator.
	 * 
	 * @since 3.1.3
	 * 
	 * @return string
	 */
	public static function get_separator () : string {
		$sep = YMFSEO_Settings::get_option( 'title_separator' );
		$sep = apply_filters( 'ymfseo_title_separator', $sep );

		return $sep;
	}
}