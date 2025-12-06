<?php

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

/**
 * Initializes the plugin and provides common methods.
 */
class Core {
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
	public static function init () {
		// Defines classes parameters.
		add_action( 'init', function () {
			// Defines available page types.
			Core::$page_types = [
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

			// Defines settings arguments.
			Settings::$params = [
				'page_title'    => __( 'SEO Settings', 'ym-fast-seo' ),
				'menu_label'    => __( 'SEO', 'ym-fast-seo' ),
				'menu_position' => 3,
				'capability'    => 'manage_options',
				'page_slug'     => 'ymfseo',
			];

			// Defines replace tags.
			MetaFields::$replace_tags = apply_filters( 'ymfseo_tags', [
				'%site_name%' => get_bloginfo( 'name' ),
				'%tagline%'   => get_bloginfo( 'description' ),
				'%sep%'       => Core::get_separator(),
			]);
		});


		// Creates SEO Editor role and adds caps.
		register_activation_hook( YMFSEO_BASENAME, function () {
			/* translators: User role name */
			add_role( 'ymfseo_seo_editor', __( 'SEO Editor', 'ym-fast-seo' ), [
				'read'                    => true,

				'publish_pages'           => true,
				'edit_pages'              => true,
				'edit_published_pages'    => true,
				'edit_others_pages'       => true,

				'publish_posts'           => true,
				'edit_posts'              => true,
				'edit_published_posts'    => true,
				'edit_others_posts'       => true,

				'manage_categories'       => true,

				'upload_files'            => true,

				'ymfseo_edit_metas'       => true,
				'view_site_health_checks' => true,
				'manage_options'          => true,
			]);

			$admin_role = get_role( 'administrator' );

			if ( $admin_role ) {
				$admin_role->add_cap( 'ymfseo_edit_metas' );
			}

			$editor_role = get_role( 'editor' );

			if ( $editor_role ) {
				$editor_role->add_cap( 'ymfseo_edit_metas' );
			}
		});

		// Removes SEO Editor role and caps.
		register_deactivation_hook( YMFSEO_BASENAME, function () {
			remove_role( 'ymfseo_seo_editor' );

			$admin_role = get_role( 'administrator' );

			if ( $admin_role ) {
				$admin_role->remove_cap( 'ymfseo_edit_metas' );
			}

			$editor_role = get_role( 'editor' );
			
			if ( $editor_role ) {
				$editor_role->remove_cap( 'ymfseo_edit_metas' );
			}
		});


		// Adds links to plugin's card on 'Plugins' page.
		add_filter( 'plugin_action_links_' . YMFSEO_BASENAME, function ( array $links ) : array {
			if ( Checker::is_current_user_can_view_site_health() ) {
				array_unshift( $links, sprintf( '<a href="%s">%s</a>',
					admin_url( 'site-health.php?tab=ymfseo' ),
					__( 'SEO Health', 'ym-fast-seo' ),
				));
			}

			if ( Checker::is_current_user_can_manage_options() ) {
				array_unshift( $links, sprintf( '<a href="%s">%s</a>',
					menu_page_url( 'ymfseo', false ),
					__( 'Settings', 'ym-fast-seo' ),
				));
			}

			return $links;
		});

		// Adds WordPress theme supports.
		add_action( 'after_setup_theme', function () {
			add_theme_support( 'html5', [
				'script', 'style', 'search-form', 'gallery', 'caption',
			]);
			add_theme_support( 'title-tag' );
			add_theme_support( 'post-thumbnails' );
		});

		// Connects admin styles and scripts.
		add_action( 'admin_enqueue_scripts', function ( string $hook_suffix ) {
			global $_wp_admin_css_colors;

			$color_scheme    = get_user_option( 'admin_color', get_current_user_id() );
			$colors          = $_wp_admin_css_colors[ $color_scheme ]->colors;
			$primary_color   = $colors[ 1 ];
			$secondary_color = $colors[ 2 ];

			// CSS
			wp_enqueue_style( 'ymfseo-styles', YMFSEO_ROOT_URI . 'assets/css/ymfseo.css', [], YMFSEO_PLUGIN_DATA[ 'Version' ] );
			wp_add_inline_style( 'ymfseo-styles', ":root{--ymfseo-primary:{$primary_color};--ymfseo-secondary:{$secondary_color};}" );

			// JS
			wp_enqueue_script( 'ymfseo-script', YMFSEO_ROOT_URI . 'assets/js/ymfseo.js', [], YMFSEO_PLUGIN_DATA[ 'Version' ], true );
			wp_add_inline_script( 'ymfseo-script', 'const YMFSEO_WP = ' . wp_json_encode([
				'isTitlePartsHidden' => (bool) Settings::get_option( 'hide_title_parts' ),
				'siteName'           => get_bloginfo( 'sitename' ),
				'titleSeparator'     => Core::get_separator(),
				'siteDescription'    => get_bloginfo( 'description' ),
				'frontPageID'        => (int) get_option( 'page_on_front' ),
				'replaceTags'        => MetaFields::$replace_tags,
			]), 'before' );

			// WordPress
			wp_enqueue_style( 'dashicons' );

			if ( 'settings_page_' . Settings::$params[ 'page_slug' ] == $hook_suffix ) {
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
		add_action( 'admin_bar_menu', function ( \WP_Admin_Bar $wp_admin_bar ) {
			if ( ! Checker::is_current_user_can_manage_options() ) {
				return;
			}

			$wp_admin_bar->add_menu([
				'id'     => 'ymfseo',
				'title'  => sprintf(
					'<span class="ab-icon dashicons-welcome-view-site" style="top: 2px;"></span><span class="ab-label">%s</span>',
					__( 'SEO', 'ym-fast-seo' )
				),
				'href'   => admin_url( 'options-general.php?page=ymfseo' ),
			]);

			$wp_admin_bar->add_menu([
				'id'     => 'ymfseo-settings',
				'title'  => __( 'Settings', 'ym-fast-seo' ),
				'href'   => admin_url( 'options-general.php?page=ymfseo' ),
				'parent' => 'ymfseo',
			]);
			$wp_admin_bar->add_menu([
				'id'     => 'ymfseo-health',
				'title'  => __( 'Health', 'ym-fast-seo' ),
				'href'   => admin_url( 'site-health.php?tab=ymfseo' ),
				'parent' => 'ymfseo',
			]);
		}, 70 );

		// Adds media states.
		add_filter( 'display_media_states', function ( array $media_states, \WP_Post $post ) : array {
			if ( $post->ID == Settings::get_option( 'preview_image_id' ) ) {
				$media_states[] =  __( 'Preview Image', 'ym-fast-seo' );
			}

			if ( $post->ID == Settings::get_option( 'rep_image_id' ) ) {
				$media_states[] =  __( 'Representative\'s Image', 'ym-fast-seo' );
			}
		
			return $media_states;
		}, 10, 2 );


		// Rewrites favicon logic.
		if ( Checker::is_imagick_available() ) {
			// Clears rewrite rules after updating site icon.
			add_action( 'update_option_site_icon', function () {
				flush_rewrite_rules();
			});

			if ( Checker::is_svg_favicon() ) {
				// Adds favicons rewrite rules.
				add_action( 'init', function () {
					add_rewrite_rule( '^favicon\.svg$',                'index.php?ymfseo_favicon=svg',         'top' );
					add_rewrite_rule( '^favicon-ico\.ico$',            'index.php?ymfseo_favicon=ico',         'top' );
					add_rewrite_rule( '^favicon-([0-9]+)\.png$',       'index.php?ymfseo_favicon=$matches[1]', 'top' );
					add_rewrite_rule( '^android-chrome-192x192\.png$', 'index.php?ymfseo_favicon=192',         'top' );
					add_rewrite_rule( '^android-chrome-512x512\.png$', 'index.php?ymfseo_favicon=512',         'top' );
					add_rewrite_rule( '^apple-touch-icon\.png$',       'index.php?ymfseo_favicon=180',         'top' );
				});

				// Adds favicon query vars.
				add_filter( 'query_vars', function ( array $vars ) : array {
					$vars[] = 'ymfseo_favicon';
	
					return $vars;
				});

				// Outputs favicons.
				add_action( 'template_redirect', function () {
					$favicon_type = get_query_var( 'ymfseo_favicon', false );

					if ( false === $favicon_type ) return;

					$site_icon_id   = get_option( 'site_icon' );
					$site_icon_path = get_attached_file( $site_icon_id );

					header( 'Cache-Control: max-age=31536000, public' );
	
					// PNG formats.
					if ( is_numeric( $favicon_type ) ) {
						$size = (int) $favicon_type;

						if ( $size > 512 ) {
							$size = 512;
						}

						$imagick = new \Imagick();

						$imagick->setBackgroundColor( new \ImagickPixel( 'transparent' ) );
						$imagick->setResolution( 512, 512 );
						$imagick->readImage( $site_icon_path );
						$imagick->setImageFormat( 'png' );
						$imagick->resizeImage( $size, $size, \Imagick::FILTER_LANCZOS, 1 );
	
						header( 'Content-Type: image/png' );
						echo $imagick; // phpcs:ignore
	
						exit;
					}
	
					// Other formats.
					switch ( $favicon_type ) {
						case 'svg':
							header( 'Content-Type: image/svg+xml' );
							echo Core::get_filesystem()->get_contents( $site_icon_path ); // phpcs:ignore
	
							exit;
						case 'ico':
							$sizes = [ 48, 32, 16 ];

							$ico  = new \Imagick();
							$base = new \Imagick();

							$base->setBackgroundColor( new \ImagickPixel( 'transparent' ) );
							$base->setResolution( 512, 512 );
							$base->readImage( $site_icon_path );
							$base->setImageFormat( 'png32' );

							foreach ( $sizes as $size ) {
								$layer = clone $base;

								$layer->resizeImage( $size, $size, \Imagick::FILTER_LANCZOS, 1, true );
								$layer->setImageFormat( 'png32' );
								$layer->setImagePage( 0, 0, 0, 0 );

								$ico->addImage( $layer );
							}

							$ico->setFormat( 'ico' );

							header( 'Content-Type: image/x-icon' );
							header( 'Content-Disposition: inline; filename="favicon.ico"' );
							echo $ico->getImagesBlob(); // phpcs:ignore

							exit;
					}
				});
	
				// Removes default favicon output.
				remove_action( 'wp_head', 'wp_site_icon', 99 );
	
				// Adds new favicon output.
				add_action( 'wp_head', function () {
					include YMFSEO_ROOT_DIR . 'parts/favicon.php';
				}, 99 );
			}
		}

		// Modifies title tag parts.
		add_filter( 'document_title_parts', function ( array $title ) : array {
			$meta_fields = new MetaFields();

			// Hides title parts if option enabled.
			if ( Settings::get_option( 'hide_title_parts' ) ) {
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
		add_filter( 'document_title_separator', function () : string {
			return Core::get_separator();
		});

		// Modifies `robots` meta tag.
		add_filter( 'wp_robots', function ( array $robots ) : array {
			$meta_fields = new MetaFields();
			
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

		// Prints head section.
		add_action( 'wp_head', function () {
			include YMFSEO_ROOT_DIR . 'parts/head.php';
		}, 1 );


		// Removes blocks from post excerpts.
		add_filter( 'excerpt_allowed_blocks', function ( array $allowed_blocks ) : array {
			if ( ! Settings::get_option( 'clear_excerpts' ) ) {
				return $allowed_blocks;
			}

			return array_filter( $allowed_blocks, function ( ?string $block ) : bool {
				return ! in_array( $block, [
					'core/heading',
				]);
			});
		});


		// Adds redirects.
		add_filter( 'mod_rewrite_rules', function ( string $rules ) : string {
			// $redirects_option = Settings::get_option( 'redirects', [] );

			// if ( empty( $redirects_option ) ) {
			// 	return $rules;
			// }

			// $redirects = [
			// 	"\n# BEGIN YMFSEO Redirects",
			// ];

			// foreach ( $redirects_option as $item ) {
			// 	$redirects[] = sprintf( '%s %d %s %s',
			// 		esc_html( $item[ 'is_regex' ] ? 'RedirectMatch' : 'Redirect' ),
			// 		esc_html( $item[ 'type' ] ),
			// 		esc_html( $item[ 'from' ] ),
			// 		esc_html( $item[ 'to' ] ),
			// 	);
			// }

			// $redirects[] = "# END YMFSEO Redirects\n\n";
		
			// return implode( "\n", $redirects ) . $rules;
			return $rules;
		});

		// Modifies `robots.txt` file.
		add_filter( 'robots_txt', function ( string $output ) : string {
			// Checks settings robots.txt.
			$settings_robots_txt = Settings::get_option( 'robots_txt' );

			if ( ! empty( $settings_robots_txt ) ) {
				return $settings_robots_txt;
			}

			// Checks multisite sitemaps.
			if ( Checker::is_subdir_multisite() ) {
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

		// Provides `llms.txt` file.
		if ( Checker::is_llms_txt_enabled() ) {
			// Adds `llms.txt` rewrite rules.
			add_action( 'init', function () {
				add_rewrite_rule( '^llms\.txt$',      'index.php?llms_txt=base', 'top' );
				add_rewrite_rule( '^llms-full\.txt$', 'index.php?llms_txt=full', 'top' );
			});
	
			// Adds `llms.txt` query vars.
			add_filter( 'query_vars', function ( $vars ) {
				$vars[] = 'llms_txt';
	
				return $vars;
			});
	
			// Outputs `llms.txt`.
			add_action( 'template_redirect', function () {
				$llms_txt_type = get_query_var( 'llms_txt', false );
	
				if ( false === $llms_txt_type ) {
					return;
				}
	
				header( 'Content-Type: text/plain; charset=utf-8' );
				include YMFSEO_ROOT_DIR . 'parts/llms-txt.php';
	
				exit;
			});
		}


		// Removes users from the sitemap.
		add_filter( 'wp_sitemaps_add_provider', function ( \WP_Sitemaps_Provider $provider, string $name ) : bool|\WP_Sitemaps_Provider {
			if ( ! Settings::get_option( 'hide_users_sitemap' ) ) {
				return $provider;
			}

			if ( 'users' === $name ) {
				return false;
			}
		
			return $provider;
		}, 10, 2 );

		// Removes `noindex` pages from the sitemap.
		add_filter( 'wp_sitemaps_posts_query_args', function ( array $args ) : array {
			// phpcs:ignore
			$args[ 'post__not_in' ] = $args[ 'post__not_in' ] ?? [];

			$page_query = new \WP_Query([
				'post_type'      => Core::get_public_post_types(),
				'meta_key'       => 'ymfseo_fields',	// phpcs:ignore
				'meta_value'     => 'noindex";s:1',		// phpcs:ignore
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
		});

		// Removes `noindex` taxonomies from the sitemap.
		add_filter( 'wp_sitemaps_taxonomies', function ( array $taxonomies ) : array {
			foreach ( $taxonomies as $slug => $data ) {
				if ( Checker::is_taxonomy_noindex( $slug ) ) {
					unset( $taxonomies[ $slug ] );
				}
			}
		
			return $taxonomies;
		});
	}

	/**
	 * Prepares and retrieve global `$wp_filesystem`.
	 * 
	 * @global $wp_filesystem
	 * 
	 * @since 4.0.0 Is Core method instead of Logger.
	 * 
	 * @return \WP_Filesystem_Base Filesystem object.
	 */
	public static function get_filesystem () : \WP_Filesystem_Base {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Retrieves array of public post types.
	 * 
	 * @since 2.0.0 Has argument $output.
	 * 
	 * @param string $output Output type. Default 'names'.
	 * 
	 * @return string[]|\WP_Post[] Public post types.
	 */
	public static function get_public_post_types ( string $output = 'names' ) : array {
		$public_post_types = get_post_types( [ 'publicly_queryable' => true ], $output );

		$page_post_type = get_post_types( [ 'name' => 'page' ], $output );

		array_splice( $public_post_types, 1, 0, $page_post_type );

		unset( $public_post_types[ 'attachment' ] );

		return $public_post_types;
	}

	public static function get_post_types_with_archives () : array {
		$post_types = get_post_types( [], 'objects' );

		$post_types = array_filter( $post_types, function ( \WP_Post_Type $post_type ) {
			return 'post' !== $post_type->name && ! empty( get_post_type_archive_link( $post_type->name ) );
		});

		return $post_types;
	}

	/**
	 * Retrieves array of public taxonomies.
	 * 
	 * @since 2.0.0
	 * @since 4.1.0 Has `$filter_noindex` parameter.
	 * 
	 * @param string $output         Output type, may be `names` and `objects`. Default 'names'.
	 * @param bool   $filter_noindex Whether to exclude `noindex` taxonomies. Default `false`.
	 * 
	 * @return string[]|\WP_Term[] Public taxonomies.
	 */
	public static function get_public_taxonomies ( string $output = 'names', bool $filter_noindex = false ) : array {
		$public_taxonomies = get_taxonomies( [ 'publicly_queryable' => true ], $output );

		unset( $public_taxonomies[ 'post_format' ] );

		if ( $filter_noindex ) {
			$public_taxonomies = array_filter( $public_taxonomies, function ( $value, $key ) {
				return ! Checker::is_taxonomy_noindex( $key );
			}, ARRAY_FILTER_USE_BOTH );
		}

		return $public_taxonomies;
	}

	/**
	 * Retrieves filtered document title separator.
	 * 
	 * @since 3.1.3
	 * 
	 * @return string
	 */
	public static function get_separator () : string {
		$sep = Settings::get_option( 'title_separator' );
		$sep = apply_filters( 'ymfseo_title_separator', $sep );

		return $sep;
	}

	/**
	 * Sanitizes text field.
	 * 
	 * @param string $value Text value.
	 * 
	 * @return string Sanitized text.
	 */
	public static function sanitize_text_field ( string $value ) : string {
		$value = wp_unslash( $value );
		$value = wp_kses_post( $value );
		$value = normalize_whitespace( $value );
		$value = trim( $value );

		return $value;
	}
}