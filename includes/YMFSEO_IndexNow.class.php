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
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
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