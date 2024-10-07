<?php

/*
 * Plugin Name:       YM Fast SEO
 * Description:       Enhance your website with powerful, intuitive, and user-friendly SEO tools.
 * Version:           2.1.0
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Tested up to:      6.6.2
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

// Creates SEO Editor role and adds caps.
register_activation_hook( __FILE__, function () {
	add_role( 'ymfseo_seo_editor', __( 'SEO Editor', 'ym-fast-seo' ), [
		'ymfseo_edit_metas'    => true,
		'ymfseo_edit_settings' => true,
		'read'                 => true,
		'upload_files'         => true,
		'manage_options'       => true,
		'edit_posts'           => true,
		'edit_others_posts'    => true,
		'edit_published_posts' => true,
		'publish_posts'        => true,
		'manage_categories'    => true,
		'edit_pages'           => true,
		'edit_others_pages'    => true,
		'edit_published_pages' => true,
		'publish_pages'        => true,
	]);

	$admin_role = get_role( 'administrator' );
	$admin_role->add_cap( 'ymfseo_edit_metas' );
	$admin_role->add_cap( 'ymfseo_edit_settings' );

	$editor_role = get_role( 'editor' );
	$editor_role->add_cap( 'ymfseo_edit_metas' );
	$editor_role->add_cap( 'ymfseo_edit_settings' );
});

// Removes SEO Editor role and caps.
register_deactivation_hook( __FILE__, function () {
	remove_role( 'ymfseo_seo_editor' );

	$admin_role = get_role( 'administrator' );
	$admin_role->remove_cap( 'ymfseo_edit_metas' );
	$admin_role->remove_cap( 'ymfseo_edit_settings' );

	$editor_role = get_role( 'editor' );
	$editor_role->remove_cap( 'ymfseo_edit_metas' );
	$editor_role->remove_cap( 'ymfseo_edit_settings' );
});

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

// Manages custom SEO column.
add_action( 'init', function () {
	// Post types.
	foreach ( YMFSEO::get_public_post_types() as $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", 'YMFSEO::manage_seo_columns' );
		add_action( "manage_{$post_type}_posts_custom_column" , function ( $column, $post_id ) {
			if ( 'ymfseo' === $column ) {
				$check = YMFSEO::check_seo( get_post( $post_id ) );

				printf( '<div class="column-ymfseo__dot" title="%s"><span class="%s"></span><div>',
					esc_attr( implode( '&#013;', $check[ 'notes' ] ) ),
					esc_attr( $check[ 'status' ] ),
				);
			}
		}, 10, 2 );
	}

	// Taxonomies.
	foreach ( YMFSEO::get_public_taxonomies() as $taxonomy ) {
		add_filter( "manage_edit-{$taxonomy}_columns", 'YMFSEO::manage_seo_columns' );
		add_action( "manage_{$taxonomy}_custom_column" , function ( $string, $column, $term_id  ) {
			if ( 'ymfseo' === $column ) {
				$check = YMFSEO::check_seo( get_term( $term_id ) );

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
	if ( current_user_can( 'ymfseo_edit_metas' ) ) {
		add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( $post ) {
			wp_nonce_field( plugin_basename( __FILE__ ), 'ymfseo_edit_post_nonce' );
			
			include plugin_dir_path( __FILE__ ) . 'parts/meta-box.php';
		}, YMFSEO::get_public_post_types(), 'side' );
	}
});

// Adds SEO meta fields to public taxonomies.
add_action( 'init', function () {
	foreach ( YMFSEO::get_public_taxonomies() as $taxonomy ) {
		add_action( "{$taxonomy}_edit_form_fields", function ( $term ) {
			include plugin_dir_path( __FILE__ ) . 'parts/term-meta-fields.php';
		});
	}
}, 30 );

// Saves post metas after saving post.
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
	if( ! current_user_can( 'ymfseo_edit_metas' ) ) {
		return;
	}

	YMFSEO::update_meta( [
		'title'       =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-title' ]       ?? YMFSEO_Meta_Fields::$default_values[ 'title' ] ) ),
		'description' =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-description' ] ?? YMFSEO_Meta_Fields::$default_values[ 'description' ] ) ),
		'page_type'   =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-page-type' ]   ?? YMFSEO_Meta_Fields::$default_values[ 'page_type' ] ) ),
		'noindex'     =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-noindex' ]     ?? YMFSEO_Meta_Fields::$default_values[ 'noindex' ] ) ),
	], $post_id, 'post' );
});

// Saves terms metas after saving term.
add_action( 'saved_term', function ( $term_id, $tt_id, $taxonomy ) {
	// Checks is taxonomy public.
	if ( ! in_array( $taxonomy, YMFSEO::get_public_taxonomies() ) ) {
		return;
	}

	// Checks user capability.
	if( ! current_user_can( 'ymfseo_edit_metas' ) ) {
		return;
	}

	YMFSEO::update_meta( [
		'title'       =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-title' ]       ?? YMFSEO_Meta_Fields::$default_values[ 'title' ] ) ),
		'description' =>  sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-description' ] ?? YMFSEO_Meta_Fields::$default_values[ 'description' ] ) ),
	], $term_id, 'term' );
}, 10, 3 );

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

// Prints head metas.
add_action( 'wp_head', function () {
	include plugin_dir_path( __FILE__ ) . 'parts/head.php';
}, 1 );

// Modifies robots.txt file.
add_filter( 'robots_txt', function ( $output ) {
	// Checks settings robots.txt.
	$settings_robots_txt = YMFSEO_Settings::get_option( 'robots_txt' );

	if ( ! empty( $settings_robots_txt ) ) {
		return $settings_robots_txt;
	}

	// Checks multisite sitemaps.
	if ( YMFSEO::is_subdir_multisite() ) {
		foreach ( get_sites() as $site ) {
			if ( get_main_site_id() != intval( $site->blog_id ) ) {
				$output .= sprintf( "Sitemap: %s\n", esc_url( get_home_url( $site->blog_id, 'wp-sitemap.xml' ) ) );
			}
		}
	}

	return $output;
}, 999 );

// Registers YM Fast SEO settings page.
add_action( 'admin_menu', function () {
	if ( ! current_user_can( 'ymfseo_edit_settings' ) ) {
		return;
	}

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
	if ( ! current_user_can( 'ymfseo_edit_settings' ) ) {
		return;
	}
	
	// General section.
	YMFSEO_Settings::add_section( 'general', __( 'General', 'ym-fast-seo' ) );
	YMFSEO_Settings::register_option(
		'hide_title_parts',
		_x( 'Clear Titles', 'Verb', 'ym-fast-seo' ),
		'boolean',
		'general',
		'checkbox',
		[
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
			/* translators: %s: Separator tag name */
			'description' => sprintf( __( 'Specify the separator used in titles and %s tags.', 'ym-fast-seo' ), '<code>%sep%</code>' ),
		],
	);

	// Post Types section.
	YMFSEO_Settings::add_section( 'post-types', __( 'Post Types', 'ym-fast-seo' ), [
		'description' => implode( "</p><p>",[
			__( 'The default title and page type values for single post pages.', 'ym-fast-seo' ),
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
			[
				'placeholder' => '%post_title%',
			],
		);
		YMFSEO_Settings::register_option(
			"post_type_page_type_{$post_type->name}",
			__( 'Page Type', 'ym-fasst-seo' ),
			'string',
			'post-types',
			'select',
			[
				'class'   => 'sub-field',
				'options' => YMFSEO::$page_types,
			],
		);
	}

	// Taxonomies section.
	YMFSEO_Settings::add_section( 'taxonomies', __( 'Taxonomies', 'ym-fast-seo' ), [
		'description' => implode( "</p><p>",[
			__( 'The default title and description values for taxonomy term pages.', 'ym-fast-seo' ),
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
	}

	// Site Preview section.
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

	// Representative section.
	YMFSEO_Settings::add_section( 'representative', __( 'Representative', 'ym-fast-seo' ), [
		'description' => __( 'If this website represents a company or person, you can include their details. This information will not be visible to visitors but will be available to search engines.', 'ym-fast-seo' ),
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
			'class' => 'rep-org',
			'options' => [
				'Organization'          => __( 'Regular Organization', 'ym-fast-seo' ),
				'LocalBusiness'         => __( 'Local Business', 'ym-fast-seo' ),
				'OnlineBusiness'        => __( 'Online Business', 'ym-fast-seo' ),
				'NGO'                   => __( 'Non-Governmental Organization', 'ym-fast-seo' ),
				'NewsMediaOrganization' => __( 'News/Media', 'ym-fast-seo' ),
				'Project'               => __( 'Project', 'ym-fast-seo' ),
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

	// Integrations section.
	YMFSEO_Settings::add_section( 'integrations', __( 'Integrations', 'ym-fast-seo' ), [
		'description' => __( 'Enter the verification codes for the required services. They are usually found in the <code>content</code> attribute of the verification meta tag.', 'ym-fast-seo' ),
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
	YMFSEO_Settings::register_option(
		'bing_webmaster_tools_key',
		__( 'Bing Webmaster Tools', 'ym-fast-seo' ),
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