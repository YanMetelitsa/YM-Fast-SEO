<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Provides logs features.
 * 
 * @since 3.0.0
 */
class YMFSEO_Logs {
	/**
	 * Allowed file names for logging.
	 * 
	 * @var string[]
	 */
	private static array $allowed_files = [
		'debug',
		'IndexNow',
	];

	/**
	 * Prepares and retrive global $wp_filesystem.
	 * 
	 * @global $wp_filesystem
	 */
	private static function get_filesystem () {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Writes entry into log file.
	 * 
	 * @param string $file  File name.
	 * @param array  $entry Data to write.
	 * 
	 * @return bool `true` if file has been written.
	 */
	public static function write ( string $file, array $entry ) : bool {
		// Is file name allowed.
		if ( ! in_array( $file, YMFSEO_Logs::$allowed_files ) ) {
			return false;
		}
		
		$filesystem = YMFSEO_Logs::get_filesystem();

		// Get file path.
		$file_path = YMFSEO_ROOT_DIR . "logs/$file.json";

		// Write empty array if file not exists or empty.
		if ( ! $filesystem->exists( $file_path ) || ! $filesystem->get_contents( $file_path ) ) {
			$filesystem->put_contents( $file_path, wp_json_encode([]) );
		}

		// Get current logs.
		$logs = json_decode( $filesystem->get_contents( $file_path ) );

		// Write entry.
		array_unshift( $logs, [
			...$entry,
			'date' => gmdate( 'c' ),
		]);

		// Slice only first 100 items.
		$logs = array_slice( $logs, 0, 100 );

		// Write to file.
		return boolval(
			$filesystem->put_contents(
				$file_path,
				wp_json_encode( $logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
			)
		);
	}

	/**
	 * Reads log file data.
	 * 
	 * @param string $file  File name.
	 * @param array  $slice How much entries returns.
	 * 
	 * @return bool Dara array or `false` on error.
	 */
	public static function read ( string $file, int $slice = 100 ) : bool|array {
		// Is file name allowed.
		if ( ! in_array( $file, YMFSEO_Logs::$allowed_files ) ) {
			return false;
		}

		$filesystem = YMFSEO_Logs::get_filesystem();

		// Get file path.
		$file_path = YMFSEO_ROOT_DIR . "logs/$file.json";

		if ( ! $filesystem->exists( $file_path ) ) {
			return [];
		}

		$logs = json_decode( $filesystem->get_contents( $file_path ), true );

		return array_slice( $logs, 0, $slice );
	}
}