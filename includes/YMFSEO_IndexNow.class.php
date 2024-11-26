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


		// Sends IndexNow after any post save.
		add_action( 'save_post', function ( $post_id, $post ) {
			// Is post type public.
			if ( ! YMFSEO_Checker::is_post_type_public( $post_id ) ) {
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

			// Is new post status 'publish'.
			if ( 'publish' !== $post->post_status ) {
				return;
			}

			// Sends IndexNow.
			YMFSEO_IndexNow::send( get_permalink( $post_id ) );
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
	 * @since 3.1.3 Checks last request time for the permalink.
	 * 
	 * @param string $permalink Permalink to send.
	 * 
	 * @return int Response status code.
	 */
	public static function send ( string $permalink ) : int {
		// Checks is IndexNow was sent recently
		$indexNow_logs = YMFSEO_Logs::read( 'IndexNow' );
		$current_time  = YMFSEO_Logs::parse_datetime( YMFSEO_Logs::get_current_datetime() );

		foreach ( $indexNow_logs as $entry ) {
			// Checks is data exists.
			if ( ! isset( $entry[ 'URL' ] ) || ! isset( $entry[ 'date' ] ) ) {
				break;
			}

			$send_time = YMFSEO_Logs::parse_datetime( $entry[ 'date' ] );

			// Looks for the same URL.
			if ( $permalink == $entry[ 'URL' ] ) {
				$difference = $current_time->getTimestamp() - $send_time->getTimestamp();

				// Exits if difference less than 10 minutes.
				if ( $difference < 10 * 60 ) {
					return 0;
				}
			}
		}

		// Gets API key.
		$api_key = YMFSEO_IndexNow::get_api_key();

		// Send request.
		$response = wp_remote_get( 'https://api.indexnow.org/indexnow?' . build_query([
			'url' => $permalink,
			'key' => $api_key,
		]));

		// Parses response status code.
		$response_code = intval( $response[ 'response' ][ 'code' ] );

		// Writes logs entry.
		YMFSEO_Logs::write( 'IndexNow', [
			'URL'    => $permalink,
			'status' => $response_code,
		]);

		return $response_code;
	}
}