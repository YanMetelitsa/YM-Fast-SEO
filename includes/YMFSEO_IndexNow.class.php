<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main YM Fast SEO class.
 * 
 * @since 3.0.0
 */
class YMFSEO_IndexNow {
	/**
	 * Inits IndexNow features.
	 */
	public static function init () : void {
		// Registers virtual .txt file API key address.
		add_action( 'init', function () {
			$api_key = YMFSEO_IndexNow::get_api_key();

			add_rewrite_rule(
				"^$api_key.txt/?$",
				"index.php?ymfseo_indexnow_key=$api_key",
				'top'
			);
			add_rewrite_tag( '%ymfseo_indexnow_key%', '([^&]+)' );
		});

		// Prints API key when virtual .txt file accessed.
		add_action( 'template_redirect', function () {
			$api_key = YMFSEO_IndexNow::get_api_key();

			if ( $api_key == get_query_var( 'ymfseo_indexnow_key' ) ) {
				echo esc_html( $api_key );
				exit;
			}
		});

		// Updates rewrite rules after plugin deactivationn.
		register_deactivation_hook( YMFSEO_BASENAME, 'flush_rewrite_rules' );


		// Sends IndexNow after any post change.
		add_action( 'save_post', function ( $post_id, $post ) {
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

			// Is not revision.
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			// Is not autosave.
			if ( wp_is_post_autosave( $post_id ) ) {
				return;
			}

			// Is post status 'publish'.
			if ( 'publish' !== $post->post_status ) {
				return;
			}
			
			// Get permalink.
			$permalink = get_permalink( $post_id );

			$permalink = preg_replace( '/__trashed$/', '', $permalink );

			// Sends IndexNow.
			YMFSEO_IndexNow::send( $permalink );
		}, 20, 2 );


		// Sends IndexNow after creating term.
		add_action( 'create_term', function ( $term_id, $tt_id, $taxonomy ) {
			// Is taxonomy public.
			if ( ! YMFSEO_Checker::is_taxonomy_public( $taxonomy ) ) {
				return;
			}

			// Sends IndexNow.
			YMFSEO_IndexNow::send( get_term_link( $term_id ) );
		}, 10, 3 );

		// Sends IndexNow after saving term.
		add_action( 'saved_term', function ( $term_id, $tt_id, $taxonomy ) {
			// Is taxonomy public.
			if ( ! YMFSEO_Checker::is_taxonomy_public( $taxonomy ) ) {
				return;
			}
			
			// Sends IndexNow.
			YMFSEO_IndexNow::send( get_term_link( $term_id ) );
		}, 10, 3 );

		// Sends IndexNow before deleting term.
		add_action( 'pre_delete_term', function ( $term_id, $taxonomy ) {
			// Checks is taxonomy public.
			if ( ! YMFSEO_Checker::is_taxonomy_public( $taxonomy ) ) {
				return;
			}

			// Sends IndexNow.
			YMFSEO_IndexNow::send( get_term_link( $term_id ) );
		}, 10, 2 );
	}

	/**
	 * Generates IndexNow API key.
	 * 
	 * @return string IndexNow API key string.
	 */
	public static function generate_api_key () : string {
		$chars     = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$chars_len = strlen( $chars );
		
		$api_key = '';
		
		while ( strlen( $api_key ) < 40 ) {
			$api_key .= $chars[ random_int( 0, $chars_len - 1 ) ];
		}

		return $api_key;
	}

	/**
	 * Retrives IndexNow API key.
	 * 
	 * @return string IndexNow API key.
	 */
	private static function get_api_key () : string {
		$api_key = YMFSEO_Settings::get_option( 'indexnow_key' );

		if ( empty( $api_key ) ) {
			YMFSEO_Settings::update_option( 'indexnow_key', YMFSEO_IndexNow::generate_api_key() );

			flush_rewrite_rules();

			$api_key = YMFSEO_Settings::get_option( 'indexnow_key' );
		}

		return $api_key;
	}

	/**
	 * Sends IndexNow request.
	 * 
	 * @param string $permalink Permalink to send.
	 * 
	 * @return int Response status code.
	 */
	public static function send ( string $permalink ) : int {
		// Get API key.
		$api_key = YMFSEO_IndexNow::get_api_key();

		// Send request.
		$response = wp_remote_get( 'https://api.indexnow.org/indexnow?' . build_query([
			'url' => $permalink,
			'key' => $api_key,
		]));

		// Parse response status code.
		$response_code = intval( $response[ 'response' ][ 'code' ] );

		// Write log.
		YMFSEO_Logs::write( 'IndexNow', [
			'URL'    => $permalink,
			'status' => $response_code,
		]);

		return $response_code;
	}
}