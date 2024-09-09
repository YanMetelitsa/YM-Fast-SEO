<?php

/*
 * Plugin Name:       YM Fast SEO
 * Description:       Flexible toolkit for basic SEO optimization of your pages and posts.
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
if ( !defined( 'ABSPATH' ) ) exit;

/** Get plugin data */
if ( !function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$YMFSEO_plugin_data = get_plugin_data( __FILE__ );

/** Define constants */
define( 'YMFSEO_PLUGIN_DATA', $YMFSEO_plugin_data );
define( 'YMFSEO_ROOT_DIR',    plugin_dir_path( __FILE__ ) );
define( 'YMFSEO_ROOT_URI',    plugin_dir_url( __FILE__ ) );

/** Connects styles and scripts */
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'ymfseo-styles', YMFSEO_ROOT_URI . 'assets/css/ymfseo-style.css', [], YMFSEO_PLUGIN_DATA[ 'Version' ] );
	wp_enqueue_script( 'ymfseo-scripts', YMFSEO_ROOT_URI . 'assets/js/ymfseo-script.js', [], YMFSEO_PLUGIN_DATA[ 'Version' ] );
});

/** Adds meta boxes to public post types */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ymfseo_fields', __( 'SEO', 'ym-fast-seo' ), function ( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'ymfseo_edit_post_nonce' );
		
		include plugin_dir_path( __FILE__ ) . 'meta-box.php';
	}, ymfseo_get_public_post_types(), 'side' );
});

/** Adds action after saving post */
add_action( 'save_post', function ( $post_id ) {
	// Is public post type
	if ( !in_array( get_post_type( $post_id ), ymfseo_get_public_post_types() ) ) {
		return;
	}

	// Check nonce
	if ( !isset( $_POST[ 'ymfseo_edit_post_nonce' ] ) ) {
		return;
	}

	$nonce = sanitize_key( wp_unslash( $_POST[ 'ymfseo_edit_post_nonce' ] ) );

	if ( !wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
		return;
	}

	// Is auto-save
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check user capability
	if( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	// Set meta data object
	$ymfseo_fields_data = [
		'title'            => ymfseo_sanitize_text_field( $_POST[ 'ymfseo-title' ]            ?? null ),
		'use_in_title_tag' => ymfseo_sanitize_text_field( $_POST[ 'ymfseo-use-in-title-tag' ] ?? true ),
		'remove_sitename'  => ymfseo_sanitize_text_field( $_POST[ 'ymfseo-remove-sitename' ]  ?? true ),
		'description'      => ymfseo_sanitize_text_field( $_POST[ 'ymfseo-description' ]      ?? null ),
		'keywords'         => ymfseo_sanitize_text_field( $_POST[ 'ymfseo-keywords' ]         ?? null ),
		'canonical_url'    => ymfseo_sanitize_text_field( $_POST[ 'ymfseo-canonical-url' ]    ?? null ),
	];

	// Update post meta
	update_post_meta( $post_id, 'ymfseo_fields', $ymfseo_fields_data );
});

/** Adds metas to head */
add_action( 'wp_head', function () {
	include plugin_dir_path( __FILE__ ) . 'head.php';
});

/** Modifies title tag content */
add_filter( 'document_title_parts', function ( $title ) {
	$queried_object_id = get_queried_object_id();

	if ( $queried_object_id ) {
		$meta_fields = ymfseo_get_post_meta_fields( $queried_object_id );

		if ( $meta_fields[ 'title' ] ) {
			if ( ymfseo_parse_checkbox_value( $meta_fields[ 'use_in_title_tag' ] ) ) {
				$title[ 'title' ] = $meta_fields[ 'title' ];
			}
		}

		if ( ymfseo_parse_checkbox_value( $meta_fields[ 'remove_sitename' ] ) ) {
			if ( isset( $title[ 'site' ] ) )    unset( $title[ 'site' ] );
			if ( isset( $title[ 'tagline' ] ) ) unset( $title[ 'tagline' ] );
		}
	}

	return $title;
});

/**
 * Returns array of public post types.
 * 
 * @return string[] Public post types.
 */
function ymfseo_get_public_post_types () : array {
	$public_post_types = get_post_types([
		'public' => true,
	]);

	return array_filter( $public_post_types, function ( $value ) {
		return $value !== 'attachment';
	});
}

/**
 * Returns the post's YMFSEO meta fields.
 * 
 * @param int $post_id Post ID.
 * 
 * @return array Meta fields values.
 */
function ymfseo_get_post_meta_fields ( int $post_id ) : array {
	// Get meta fields
	$meta_fields = get_post_meta( $post_id, 'ymfseo_fields', true );
	$meta_fields = empty( $meta_fields ) ? [] : $meta_fields;
	$meta_fields = wp_parse_args( $meta_fields, [
		'title'            => null,
		'use_in_title_tag' => true,
		'remove_sitename'  => true,
		'description'      => null,
		'keywords'         => null,
		'canonical_url'    => null,
	]);

	return $meta_fields;
}

/**
 * Coverts default type checkbox value to bool type.
 * 
 * @param mixed $value Checkbox default type value.
 * 
 * @return bool Checkbox bool value.
 */
function ymfseo_parse_checkbox_value ( mixed $value ) : bool {
	if ( is_bool( $value ) ) {
		return $value;
	}

	if ( is_string( $value ) && strtolower( $value ) == 'on' ) {
		return true;
	}

	return false;
}

/**
 * Sanitizes the value of the text field before saving.
 * 
 * @param string $str String to sanitize.
 * 
 * @return string Sanitized string.
 */
function ymfseo_sanitize_text_field ( string $str ) : string {
	$str = wp_unslash( $str );
	$str = sanitize_text_field( $str );

	return $str;
}