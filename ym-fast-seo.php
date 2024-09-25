<?php

/*
 * Plugin Name:       YM Fast SEO
 * Description:       Enhance your website with powerful, intuitive, and user-friendly SEO tools.
 * Version:           1.0.0
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

/** Adds WordPress theme supports */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
});

/** Connects styles and scripts */
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'ymfseo-styles', YMFSEO_ROOT_URI . 'assets/css/ymfseo-style.css', [], YMFSEO_PLUGIN_DATA[ 'Version' ] );
});

/** Adds posts custom columns */
add_action( 'init', function () {
	foreach ( YMFSEO::get_public_post_types() as $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", function ( $columns ) {
			$columns[ 'ymfseo' ] = 'SEO';
		
			return $columns;
		});
		add_action( "manage_{$post_type}_posts_custom_column" , function ( $column, $post_id ) {
			switch ( $column ) {
				case 'ymfseo':
					$status = 'good';
					$notes  = [];
	
					$meta_fields = YMFSEO::get_post_meta_fields( $post_id );
	
					if ( ! $meta_fields[ 'description' ] ) {
						$status = 'bad';
						$notes[] = __( 'No description.',' ym-fast-seo' );
					}

					if ( empty( $notes ) ) {
						$notes[] = __( 'Good!',' ym-fast-seo' );
					}
	
					?>
						<div class="column-ymfseo__dot" title="<?php echo esc_attr( implode( '&#013;', $notes ) ); ?>">
							<span class="<?php echo esc_attr( $status ); ?>"></span>
						<div>
					<?php
	
					break;
			}
		}, 10, 2 );
	}
}, 20 );

/** Adds meta boxes to public post types */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'ymfseo_edit_post_nonce' );
		
		include plugin_dir_path( __FILE__ ) . 'meta-box.php';
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
	
	// Set meta data object
	$meta_fields = [
		'title'       => sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-title' ] ?? null ) ),
		'description' => sanitize_text_field( wp_unslash( $_POST[ 'ymfseo-description' ] ?? null ) ),
	];

	// Update post meta
	update_post_meta( $post_id, 'ymfseo_fields', $meta_fields );
});

/** Modifies title tag content */
add_filter( 'document_title_parts', function ( $title ) {
	$queried_object_id = get_queried_object_id();

	if ( $queried_object_id ) {
		$meta_fields = YMFSEO::get_post_meta_fields( $queried_object_id );

		if ( $meta_fields[ 'title' ] ) {
			$title[ 'title' ] = $meta_fields[ 'title' ];
		}
	}

	return $title;
});

/** Sets robots index, follow as default */
add_filter( 'wp_robots', function ( $robots ) {
	if ( ! $robots[ 'noindex' ] ) {
		$robots[ 'index' ] = true;
	}
	if ( ! $robots[ 'nofollow' ] ) {
		$robots[ 'follow' ] = true;
	}

    return $robots;
});

/** Adds metas to head */
add_action( 'wp_head', function () {
	include plugin_dir_path( __FILE__ ) . 'head.php';
});