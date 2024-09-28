<?php

/*
 * Plugin Name:       YM Fast SEO
 * Description:       Enhance your website with powerful, intuitive, and user-friendly SEO tools.
 * Version:           1.2.1
 * Tested up to:      6.6.1
 * Requires at least: 6.4
 * Author:            Yan Metelitsa
 * Author URI:        https://yanmet.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ym-fast-seo
 */

/** Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Get plugin data */
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/** Define constants */
define( 'YMFSEO_PLUGIN_DATA', get_plugin_data( __FILE__ ) );
define( 'YMFSEO_ROOT_DIR',    plugin_dir_path( __FILE__ ) );
define( 'YMFSEO_ROOT_URI',    plugin_dir_url( __FILE__ ) );

/** Include components */
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO.class.php';

YMFSEO::init();

/** Adds settings link */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, sprintf( '<a href="%s">%s</a>',
		menu_page_url( 'ymfseo-settings', false ),
		__( 'SEO Settings', 'ym-fast-seo' ),
	));

	return $links;
});

/** Adds WordPress theme supports */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
});

/** Connects styles and scripts */
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'ymfseo-styles', YMFSEO_ROOT_URI . 'assets/css/ymfseo-style.css', [], YMFSEO_PLUGIN_DATA[ 'Version' ] );
	
	wp_enqueue_media();
	wp_enqueue_script( 'ymfseo-script', YMFSEO_ROOT_URI . 'assets/js/ymfseo-scripts.js', [], YMFSEO_PLUGIN_DATA[ 'Version' ], true );
	wp_add_inline_script( 'ymfseo-script', 'const YMFSEO_WP = ' . wp_json_encode([
		'replaceTags' => YMFSEO::$replace_tags,
	]), 'before' );
});

/** Adds posts custom columns */
add_action( 'init', function () {
	foreach ( YMFSEO::get_public_post_types() as $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", function ( $columns ) {
			$columns[ 'ymfseo' ] = __( 'SEO', 'ym-fast-seo' );
		
			return $columns;
		});
		add_action( "manage_{$post_type}_posts_custom_column" , function ( $column, $post_id ) {
			switch ( $column ) {
				case 'ymfseo':
					$check = YMFSEO::check_seo( $post_id );

					printf( '<div class="column-ymfseo__dot" title="%s"><span class="%s"></span><div>',
				esc_attr( implode( '&#013;', $check[ 'notes' ] ) ),
						esc_attr( $check[ 'status' ] ),
					);
	
					break;
			}
		}, 10, 2 );
	}
}, 20 );

/** Adds meta boxes to public post types */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'ymfseo_edit_post_nonce' );
		
		include plugin_dir_path( __FILE__ ) . 'parts/meta-box.php';
	}, YMFSEO::get_public_post_types(), 'side' );
});

/** Adds action after saving post */
add_action( 'save_post', function ( $post_id ) {
	// Is public post type
	if ( ! in_array( get_post_type( $post_id ), YMFSEO::get_public_post_types() ) ) {
		return;
	}

	// Check nonce
	if ( ! isset( $_POST[ 'ymfseo_edit_post_nonce' ] ) ) {
		return;
	}

	$nonce = sanitize_key( wp_unslash( $_POST[ 'ymfseo_edit_post_nonce' ] ) );

	if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
		return;
	}

	// Is auto-save
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check user capability
	if( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	// Set meta fields object
	$meta_fields = [
		'title' => sanitize_text_field(
			wp_unslash( $_POST[ 'ymfseo-title' ] ?? YMFSEO::$empty_meta_fields[ 'title' ] )
		),
		'description' => sanitize_text_field(
			wp_unslash( $_POST[ 'ymfseo-description' ] ?? YMFSEO::$empty_meta_fields[ 'description' ] )
		),
		'page_type' => sanitize_text_field(
			wp_unslash( $_POST[ 'ymfseo-page-type' ] ?? YMFSEO::$empty_meta_fields[ 'page_type' ] )
		),
		'noindex' => sanitize_text_field(
			wp_unslash( $_POST[ 'ymfseo-noindex' ] ?? YMFSEO::$empty_meta_fields[ 'noindex' ] )
		),
	];

	// Update post meta
	update_post_meta( $post_id, 'ymfseo_fields', $meta_fields );
});

/** Modifies title tag content */
add_filter( 'document_title_parts', function ( $title ) {
	// Hide title parts
	if ( YMFSEO::get_option( 'hide_title_parts' ) ) {
		if ( isset( $title[ 'site' ] ) )    unset( $title[ 'site' ] );
		if ( isset( $title[ 'tagline' ] ) ) unset( $title[ 'tagline' ] );
	}

	// Set title tag the same as meta title
	$queried_object_id = get_queried_object_id();

	if ( $queried_object_id ) {
		$meta_fields = YMFSEO::get_post_meta_fields( $queried_object_id );

		if ( $meta_fields[ 'title' ] ) {
			$title[ 'title' ] = $meta_fields[ 'title' ];
		}
	}

	return $title;
});

/** Modifies title tag separator */
add_filter( 'document_title_separator', function ( $sep ) {
    return YMFSEO::get_option( 'title_separator' );
});

/** Modifies robots meta */
add_filter( 'wp_robots', function ( $robots ) {
	// Set noindex
	$queried_object_id = get_queried_object_id();

	if ( $queried_object_id ) {
		$meta_fields = YMFSEO::get_post_meta_fields( $queried_object_id );
		
		if ( $meta_fields[ 'noindex' ] ) {
			$robots = array_merge(
				[
					'noindex'  => true,
					'nofollow' => true,
				],
				$robots
			);
		}
	}

	// Set default index / follow
	if ( ! isset( $robots[ 'nofollow' ] ) || ! $robots[ 'nofollow' ] ) {
		$robots = array_merge( [ 'follow' => true ], $robots );
	}
	if ( ! isset( $robots[ 'noindex' ] ) || ! $robots[ 'noindex' ] ) {
		$robots = array_merge(  [ 'index' => true ], $robots );
	}

	return $robots;
});

/** Adds metas to head */
add_action( 'wp_head', function () {
	include plugin_dir_path( __FILE__ ) . 'parts/head.php';
}, 1 );

/** Modifies robots.txt via settings */
add_filter( 'robots_txt', function ( $output ) {
	$settings_robots_txt = YMFSEO::get_option( 'robots_txt' );

	if ( $settings_robots_txt ) {
		$output = $settings_robots_txt;
	}

	return $output;
});

/** Registers YMFSEO settings */
add_action( 'admin_menu', function () {
	add_options_page(
		__( 'SEO Settings', 'ym-fast-seo' ),
		__( 'SEO', 'ym-fast-seo' ),
		'manage_options',
		'ymfseo-settings',
		function () {
			include YMFSEO_ROOT_DIR . 'parts/settings-page.php';
		},
		3
	);
});
add_action( 'admin_init', function () {
	/**
	 * Title section
	 */
	add_settings_section(
		'ymfseo_titles_section',
		__( 'Titles', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-section.php';
		},
		'ymfseo_settings'
	);

	// Clear Titles
	register_setting( 'ymfseo_settings', 'ymfseo_hide_title_parts', [
		'type'              => 'boolean',
		'default'           => YMFSEO::$default_settings[ 'hide_title_parts' ],
		'sanitize_callback' => 'sanitize_text_field',
	]);
	add_settings_field(
		'ymfseo_hide_title_parts',
		__( 'Clear Titles', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-checkbox-field.php';
		},
		'ymfseo_settings',
		'ymfseo_titles_section',
		[
			'label_for'   => 'ymfseo_hide_title_parts',
			'label'       => __( 'Simplify the title tag by removing unnecessary parts', 'ym-fast-seo' ),
			'description' => __( 'The site description on the front page and the site name on all other pages.', 'ym-fast-seo' ),
		]
	);

	// Title Separator
	register_setting( 'ymfseo_settings', 'ymfseo_title_separator', [
		'type'              => 'string',
		'default'           => YMFSEO::$default_settings[ 'title_separator' ],
		'sanitize_callback' => 'sanitize_text_field',
	]);
	add_settings_field(
		'ymfseo_title_separator',
		__( 'Title Separator', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-separator-field.php';
		},
		'ymfseo_settings',
		'ymfseo_titles_section',
		[
			'label_for' => 'ymfseo_title_separator',
			'options'   => [
				'|' => '|',
				'-' => '-',
				'–' => '–',
				'—' => '—',
				':' => ':',
				'/' => '/',
				'·' => '·',
				'•' => '•',
				'⋆' => '⋆',
				'~' => '~',
				'«' => '«',
				'»' => '»',
				'<' => '<',
				'>' => '>',
			],
			/* translators: %s: Separator tag name */
			'description' => sprintf( __( 'Specify the separator used in the title tag and %s tag.', 'ym-fast-seo' ), '<code>%sep%</code>' ),
		]
	);

	/**
	 * Preview section
	 */
	add_settings_section(
		'ymfseo_preview_section',
		__( 'Site Preview', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-section.php';
		},
		'ymfseo_settings'
	);

	// Preview Image
	register_setting( 'ymfseo_settings', 'ymfseo_preview_image_id', [
		'type'              => 'integer',
		'default'           => YMFSEO::$default_settings[ 'preview_image_id' ],
		'sanitize_callback' => 'sanitize_text_field',
	]);
	add_settings_field(
		'ymfseo_preview_image_id',
		__( 'Preview Image', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-preview-image-field.php';
		},
		'ymfseo_settings',
		'ymfseo_preview_section'
	);

	// Preview Size
	register_setting( 'ymfseo_settings', 'ymfseo_preview_size', [
		'type'              => 'string',
		'default'           => YMFSEO::$default_settings[ 'preview_size' ],
		'sanitize_callback' => 'sanitize_text_field',
	]);
	add_settings_field(
		'ymfseo_preview_size',
		__( 'Preview Size', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-select-field.php';
		},
		'ymfseo_settings',
		'ymfseo_preview_section',
		[
			'label_for' => 'ymfseo_preview_size',
			'options'   => [
				'summary'             => __( 'Summary', 'ym-fast-seo' ),
				'summary_large_image' => __( 'Large Image', 'ym-fast-seo' ),
			],
		]
	);

	/**
	 * Integrations section
	 */
	add_settings_section(
		'ymfseo_integrations_section',
		__( 'Integrations', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-section.php';
		},
		'ymfseo_settings',
		[
			'description' => __( 'Enter the values of the <code>content</code> attribute for the required services to verify the site.', 'ym-fast-seo' ),
		]
	);

	// Google Search Console
	register_setting( 'ymfseo_settings', 'ymfseo_google_search_console_key', [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	]);
	add_settings_field(
		'ymfseo_google_search_console_key',
		__( 'Google Search Console', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-text-field.php';
		},
		'ymfseo_settings',
		'ymfseo_integrations_section',
		[
			'label_for' => 'ymfseo_google_search_console_key',
		]
	);

	// Yandex Webmaster
	register_setting( 'ymfseo_settings', 'ymfseo_yandex_webmaster_key', [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	]);
	add_settings_field(
		'ymfseo_yandex_webmaster_key',
		__( 'Yandex Webmaster', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-text-field.php';
		},
		'ymfseo_settings',
		'ymfseo_integrations_section',
		[
			'label_for' => 'ymfseo_yandex_webmaster_key',
		]
	);

	/**
	 * Additional section
	 */
	add_settings_section(
		'ymfseo_additional_section',
		__( 'Additional', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-section.php';
		},
		'ymfseo_settings'
	);

	// Edit robots.txt
	register_setting( 'ymfseo_settings', 'ymfseo_robots_txt', [
		'type'              => 'string',
		'default'           => YMFSEO::$default_settings[ 'robots_txt' ],
		'sanitize_callback' => 'sanitize_textarea_field',
	]);
	add_settings_field(
		'ymfseo_robots_txt',
		__( 'Edit robots.txt', 'ym-fast-seo' ),
		function ( $args ) {
			include YMFSEO_ROOT_DIR . 'parts/settings-robots-txt-field.php';
		},
		'ymfseo_settings',
		'ymfseo_additional_section',
		[
			'label_for' => 'ymfseo_robots_txt',
		]
	);
});