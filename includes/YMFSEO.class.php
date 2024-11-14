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
			'SearchResultsPage' => __( 'Search results Page', 'ym-fast-seo' ),
		];

		// Defines default meta values.
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
			'capability'    => 'ymfseo_edit_settings',
			'page_slug'     => 'ymfseo-settings',
		];

		// Defines default settings.
		YMFSEO_Settings::$default_settings = [
			'hide_title_parts'          => true,
			'title_separator'           => '|',
			'clear_excerpts'            => true,
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
			'bing_webmaster_tools_key'  => '',
			'yandex_webmaster_key'      => '',
			'indexnow_key'              => '',
			'robots_txt'                => '',
		];

		// Defines replace tags.
		YMFSEO_Meta_Fields::$replace_tags = [
			'%site_name%' => get_bloginfo( 'name' ),
			'%sep%'       => YMFSEO_Settings::get_option( 'title_separator' ),
		];


		// Adds WordPress theme supports.
		add_action( 'after_setup_theme', function () {
			add_theme_support( 'title-tag' );
			add_theme_support( 'post-thumbnails' );
		});

		// Connects styles and scripts.
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_style( 'ymfseo-styles', YMFSEO_ROOT_URI . 'assets/css/ymfseo-style.css', [], YMFSEO_PLUGIN_DATA[ 'Version' ] );
			
			wp_enqueue_media();
			wp_enqueue_script( 'ymfseo-script', YMFSEO_ROOT_URI . 'assets/js/ymfseo-scripts.js', [], YMFSEO_PLUGIN_DATA[ 'Version' ], true );
			wp_add_inline_script( 'ymfseo-script', 'const YMFSEO_WP = ' . wp_json_encode([
				'replaceTags' => YMFSEO_Meta_Fields::$replace_tags,
			]), 'before' );
		});


		// Modifies title tag parts.
		add_filter( 'document_title_parts', function ( $title ) {
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
		add_filter( 'document_title_separator', function ( $sep ) {
			return YMFSEO_Settings::get_option( 'title_separator' );
		});

		// Modifies robots meta tag.
		add_filter( 'wp_robots', function ( $robots ) {
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
		
		// Removes headings from post excerpts.
		add_filter( 'excerpt_allowed_blocks', function ( $allowed_blocks ) {
			if ( ! YMFSEO_Settings::get_option( 'clear_excerpts' ) ) {
				return $allowed_blocks;
			}

			return array_filter( $allowed_blocks, function ( $block ) {
				return ! in_array( $block, [
					'core/heading',
				]);
			});
		});

		// Modifies robots.txt file.
		add_filter( 'robots_txt', function ( $output ) {
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
	public static function manage_seo_columns ( $columns ) : array {
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
	 * @param string $type        Object type. Can be 'post' or 'term'.
	 */
	public static function update_metas ( array $meta_fields, int $id, string $type ) : void {
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
}