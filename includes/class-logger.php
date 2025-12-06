<?php

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

/**
 * Provides logs features.
 * 
 * @since 3.0.0
 */
class Logger {
	/**
	 * Allowed file names for logging.
	 * 
	 * @var string[]
	 */
	private static array $allowed_files = [
		'debug', 'IndexNow', 'llms-txt',
	];

	/**
	 * Retrieves current UTC time string.
	 * 
	 * @since 3.1.3
	 * 
	 * @return string
	 */
	public static function get_current_datetime () : string {
		return gmdate( 'c' );
	}

	/**
	 * Retrieves parsed DateTime object from string.
	 * 
	 * @since 3.1.3
	 * 
	 * @param string $date Time string in ISO 8601 format.
	 * 
	 * @return \DateTime
	 */
	public static function parse_datetime ( string $date ) : \DateTime {
		$datetime = \DateTime::createFromFormat( 'Y-m-d\TH:i:sP', $date );
		$datetime->setTimezone( wp_timezone() );

		return $datetime;
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
		if ( ! in_array( $file, Logger::$allowed_files ) ) {
			return false;
		}
		
		$filesystem =  Core::get_filesystem();

		// Get file path.
		$file_path = YMFSEO_ROOT_DIR . "logs/{$file}.json";

		// Write empty array if file not exists or empty.
		if ( ! $filesystem->exists( $file_path ) || ! $filesystem->get_contents( $file_path ) ) {
			$filesystem->put_contents( $file_path, wp_json_encode([]) );
		}

		// Get current logs.
		$logs = json_decode( $filesystem->get_contents( $file_path ) );

		// Write entry.
		array_unshift( $logs, [
			...$entry,
			'date' => Logger::get_current_datetime(),
		]);

		// Get only first 20 items.
		$logs = array_slice( $logs, 0, 20 );

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
	 * @param int    $slice How much entries returns.
	 * 
	 * @return bool|array Data array or `false` on error.
	 */
	public static function read ( string $file, int $slice = 20 ) : bool|array {
		// Is file name allowed.
		if ( ! in_array( $file, Logger::$allowed_files ) ) {
			return false;
		}

		$filesystem =  Core::get_filesystem();

		// Get file path.
		$file_path = YMFSEO_ROOT_DIR . "logs/{$file}.json";

		if ( ! $filesystem->exists( $file_path ) ) {
			return [];
		}

		$logs = json_decode( $filesystem->get_contents( $file_path ), true );

		return array_slice( $logs, 0, $slice );
	}
}