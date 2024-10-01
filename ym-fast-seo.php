<?php

/*
 * Plugin Name:       YM Fast SEO
 * Description:       Enhance your website with powerful, intuitive, and user-friendly SEO tools.
 * Version:           2.0.1
 * Tested up to:      6.6.2
 * Requires at least: 6.4
 * Author:            Yan Metelitsa
 * Author URI:        https://yanmet.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ym-fast-seo
 */

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Gets plugin data.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Defines plugin constants.
define( 'YMFSEO_PLUGIN_DATA', get_plugin_data( __FILE__ ) );
define( 'YMFSEO_ROOT_DIR',    plugin_dir_path( __FILE__ ) );
define( 'YMFSEO_ROOT_URI',    plugin_dir_url( __FILE__ ) );

// Includes plugin components.
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Meta_Fields.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Settings.class.php';

YMFSEO::init();

// Adds settings link to plugin's card on Plugins page.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, sprintf( '<a href="%s">%s</a>',
		menu_page_url( 'ymfseo-settings', false ),
		__( 'SEO Settings', 'ym-fast-seo' ),
	));

	return $links;
});

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

// Manages public posts custom SEO column.
add_action( 'init', function () {
	foreach ( YMFSEO::get_public_post_types() as $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", function ( $columns ) {
			$columns[ 'ymfseo' ] = __( 'SEO', 'ym-fast-seo' );
		
			return $columns;
		});
		add_action( "manage_{$post_type}_posts_custom_column" , function ( $column, $post_id ) {
			switch ( $column ) {
				case 'ymfseo':
					$check = YMFSEO::check_post_seo( $post_id );

					printf( '<div class="column-ymfseo__dot" title="%s"><span class="%s"></span><div>',
						esc_attr( implode( '&#013;', $check[ 'notes' ] ) ),
						esc_attr( $check[ 'status' ] ),
					);
	
					break;
			}
		}, 10, 2 );
	}
}, 20 );

// Adds SEO meta box to public post types.
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'ymfseo_edit_post_nonce' );
		
		include plugin_dir_path( __FILE__ ) . 'parts/meta-box.php';
	}, YMFSEO::get_public_post_types(), 'side' );
});

// Adds update post meta action after saving public post.
add_action( 'save_post', function ( $post_id ) {
	// Checks is auto-save.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Checks is a revision.
    $parent_id = wp_is_post_revision( $post_id );

    if ( false !== $parent_id ) {
        $post_id = $parent_id;
    }

	// Checks nonce.
	if ( ! isset( $_POST[ 'ymfseo_edit_post_nonce' ] ) ) {
		return;
	}

	$nonce = sanitize_key( wp_unslash( $_POST[ 'ymfseo_edit_post_nonce' ] ) );

	if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
		return;
	}

	// Checks is post type public.
	if ( ! in_array( get_post_type( $post_id ), YMFSEO::get_public_post_types() ) ) {
		return;
	}

	// Checks user capability.
	if( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Gets POST meta data.
	$post_meta = [];
	$post_data = [
		'title'       => wp_unslash( $_POST[ 'ymfseo-title' ]       ?? YMFSEO_Meta_Fields::$default_values[ 'title' ] ),
		'description' => wp_unslash( $_POST[ 'ymfseo-description' ] ?? YMFSEO_Meta_Fields::$default_values[ 'description' ] ),
		'page_type'   => wp_unslash( $_POST[ 'ymfseo-page-type' ]   ?? YMFSEO_Meta_Fields::$default_values[ 'page_type' ] ),
		'noindex'     => wp_unslash( $_POST[ 'ymfseo-noindex' ]     ?? YMFSEO_Meta_Fields::$default_values[ 'noindex' ] ),
	];

	// Adds to save output if not equal to default value.
	foreach ( $post_data as $key => $value ) {
		if ( $value !== YMFSEO_Meta_Fields::$default_values[ $key ] ) {
			$post_meta[ $key ] = sanitize_text_field( $value );
		}
	}

	if ( $post_meta ) {
		// Updates post meta.
		update_post_meta( $post_id, 'ymfseo_fields', $post_meta );
	} else {
		// Deletes post meta.
		delete_post_meta( $post_id, 'ymfseo_fields' );
	}
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

	// Set default index and follow if noindex disabled.
	if ( ! isset( $robots[ 'nofollow' ] ) || ! $robots[ 'nofollow' ] ) {
		$robots = array_merge( [ 'follow' => true ], $robots );
	}
	if ( ! isset( $robots[ 'noindex' ] ) || ! $robots[ 'noindex' ] ) {
		$robots = array_merge(  [ 'index' => true ], $robots );
	}

	return $robots;
});

// Prints metas in head.
add_action( 'wp_head', function () {
	include plugin_dir_path( __FILE__ ) . 'parts/head.php';
}, 1 );

// Modifies robots.txt file.
add_filter( 'robots_txt', function ( $output ) {
	$settings_robots_txt = YMFSEO_Settings::get_option( 'robots_txt' );

	if ( ! empty( $settings_robots_txt ) ) {
		$output = $settings_robots_txt;
	} else {
		if ( YMFSEO::is_subdir_multisite() ) {
			foreach ( get_sites() as $site ) {
				if ( get_main_site_id() != intval( $site->blog_id ) ) {
					$output .= sprintf( "Sitemap: %s\n", esc_url( get_home_url( $site->blog_id, 'wp-sitemap.xml' ) ) );
				}
			}
		}
	}

	return $output;
});

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
	// Title section.
	YMFSEO_Settings::add_section( 'titles', __( 'Titles', 'ym-fast-seo' ) );
	YMFSEO_Settings::register_option(
		'hide_title_parts',
		_x( 'Clear Titles', 'Verb', 'ym-fast-seo' ),
		'boolean',
		'titles',
		'checkbox',
		[
			'label'       => __( 'Simplify the title tags by removing unnecessary parts', 'ym-fast-seo' ),
			'description' => __( 'The site description on the front page and the site name on all other pages.', 'ym-fast-seo' ),
		],
	);
	YMFSEO_Settings::register_option(
		'title_separator',
		__( 'Title Separator', 'ym-fast-seo' ),
		'string',
		'titles',
		'separator',
		[
			'options'     => [ '|', '-', '–', '—', ':', '/', '·', '•', '⋆', '~', '«', '»', '<', '>' ],
			/* translators: %s: Separator tag name */
			'description' => sprintf( __( 'Specify the separator used in the titles and %s tags.', 'ym-fast-seo' ), '<code>%sep%</code>' ),
		],
	);

	// Post Types section.
	YMFSEO_Settings::add_section( 'post-types', __( 'Post Types', 'ym-fast-seo' ), [
		'description' => implode( "</p><p>",[
			__( 'These values are used on single post pages. Titles are applied if no meta titles are specified.', 'ym-fast-seo' ),
			/* translators: %s: List of available tags */
			sprintf( __( 'Available tags: %s', 'ym-fast-seo' ),
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
		);
	}

	// Taxonomies section.
	YMFSEO_Settings::add_section( 'taxonomies', __( 'Taxonomies', 'ym-fast-seo' ), [
		'description' => implode( "</p><p>",[
			__( 'These values are used on taxonomy pages. Titles always apply, and descriptions are used if the term has no description.', 'ym-fast-seo' ),
			/* translators: %s: List of available tags */
			sprintf( __( 'Available tags: %s', 'ym-fast-seo' ),
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
		);
		YMFSEO_Settings::register_option(
			"taxonomy_description_{$taxonomy->name}",
			__( 'Description', 'ym-fast-seo' ),
			'string',
			'taxonomies',
			'textarea',
		);
	}

	// Preview section.
	YMFSEO_Settings::add_section( 'preview', __( 'Site Preview', 'ym-fast-seo' ) );
	YMFSEO_Settings::register_option(
		'preview_image_id',
		__( 'Preview Image', 'ym-fast-seo' ),
		'integer',
		'preview',
		'preview-image',
	);
	YMFSEO_Settings::register_option(
		'preview_size',
		__( 'Preview Size', 'ym-fast-seo' ),
		'string',
		'preview',
		'select',
		[
			'options' => [
				'summary'             => __( 'Summary', 'ym-fast-seo' ),
				'summary_large_image' => __( 'Large Image', 'ym-fast-seo' ),
			],
		],
	);

	// Representatives section.
	YMFSEO_Settings::add_section( 'representatives', __( 'Representative', 'ym-fast-seo' ), [
		'description' => __( 'If this website represents a company or person, you can include their details. This information will not be visible to visitors but will be available to search engines.', 'ym-fast-seo' ),
	]);
	YMFSEO_Settings::register_option(
		'rep_type',
		__( 'Represented by', 'ym-fast-seo' ),
		'string',
		'representatives',
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
		'representatives',
		'select',
		[
			'class' => 'rep-org',
			'options' => [
				'Organization'          => __( 'No Type', 'ym-fast-seo' ),
				'OnlineBusiness'        => __( 'Online Business', 'ym-fast-seo' ),
				'LocalBusiness'         => __( 'Local Business', 'ym-fast-seo' ),
				'NewsMediaOrganization' => __( 'News/Media', 'ym-fast-seo' ),
				'Project'               => __( 'Project', 'ym-fast-seo' ),
				'NGO'                   => __( 'NGO', 'ym-fast-seo' ),
			],
		],
	);
	YMFSEO_Settings::register_option(
		'rep_org_name',
		__( 'Organization Name', 'ym-fast-seo' ),
		'string',
		'representatives',
		'text',
		[
			'class' => 'rep-org',
		],
	);
	YMFSEO_Settings::register_option(
		'rep_person_name',
		__( 'Person Name', 'ym-fast-seo' ),
		'string',
		'representatives',
		'text',
		[
			'class' => 'rep-person',
		],
	);
	YMFSEO_Settings::register_option(
		'rep_email',
		__( 'Email', 'ym-fast-seo' ),
		'string',
		'representatives',
		'text',
		[
			'type' => 'email',
		],
	);

	// Integrations section.
	YMFSEO_Settings::add_section( 'integrations', __( 'Integrations', 'ym-fast-seo' ), [
		'description' => __( 'Enter the values of the <code>content</code> attribute for the required services to verify the site.', 'ym-fast-seo' ),
	]);
	YMFSEO_Settings::register_option(
		'google_search_console_key',
		__( 'Google Search Console', 'ym-fast-seo' ),
		'string',
		'integrations',
		'text',
		[
			'input-class' => 'code',
		],
	);
	YMFSEO_Settings::register_option(
		'yandex_webmaster_key',
		__( 'Yandex Webmaster', 'ym-fast-seo' ),
		'string',
		'integrations',
		'text',
		[
			'input-class' => 'code',
		],
	);

	// Additional section.
	YMFSEO_Settings::add_section( 'additional', _x( 'Additional', 'Additional settings', 'ym-fast-seo' ) );
	YMFSEO_Settings::register_option(
		'robots_txt',
		__( 'Edit robots.txt', 'ym-fast-seo' ),
		'string',
		'additional',
		'robots-txt',
	);
});