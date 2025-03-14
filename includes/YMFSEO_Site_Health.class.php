<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Provides site SEO health validation functionality.
 * 
 * @since 3.0.0
 */
class YMFSEO_Site_Health {
	/**
	 * Registered tests.
	 * 
	 * @var array
	 */
	public static array $tests = [];

	/**
	 * Test status. Can be `yes`, `no`, `warning`.
	 * 
	 * @var string
	 */
	public string $is_passed;

	/**
	 * Test result title.
	 * 
	 * @var string
	 */
	public string $title;

	/**
	 * Test result description.
	 * 
	 * @var string[]
	 */
	public array $description;

	/**
	 * Test result content (table/links).
	 * 
	 * @var array
	 */
	public array $content;

	/**
	 * Site SEO Health test results.
	 * 
	 * @param string $is_passed   Test status. Can be `yes`, `no`, `warning`.
	 * @param string $title       Test result title.
	 * @param array  $description Test result description.
	 * @param array  $content     Test result content (table/links).
	 */
	function __construct ( string $is_passed, string $title, array $description, array $content = [] ) {
		$this->is_passed   = $is_passed;
		$this->title       = $title;
		$this->description = $description;
		$this->content     = $content;
	}

	/**
	 * Inits Site SEO Health features.
	 */
	public static function init () : void {
		// Adds site health SEO navigation tab.
		add_filter( 'site_health_navigation_tabs', function (array  $tabs ) : array {
			$tabs[ 'ymfseo-site-health-tab' ] = __( 'SEO', 'ym-fast-seo' );
		
			return $tabs;
		});

		// Includes site health SEO navigation tab content.
		add_action( 'site_health_tab_content', function ( string $tab ) {
			if ( 'ymfseo-site-health-tab' !== $tab ) {
				return;
			}
		
			include YMFSEO_ROOT_DIR . 'parts/site-health-seo-tab.php';
		});

		YMFSEO_Site_Health::register_test( 'is-indexing-available', function () : YMFSEO_Site_Health {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site is available for indexing', 'ym-fast-seo' );
			$description = [];
			$links = [
				__( 'Reading Settings', 'ym-fast-seo' ) => get_admin_url( null, 'options-reading.php#blog_public' ),
			];

			// Check.
			if ( 0 == get_option( 'blog_public' ) ) {
				$is_passed = 'no';
				$title     = __( 'Site is not available for indexing', 'ym-fast-seo' );
			}

			return new YMFSEO_Site_Health( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		YMFSEO_Site_Health::register_test( 'is-site-has-name', function () : YMFSEO_Site_Health {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site title is specified', 'ym-fast-seo' );
			$description = [];
			$links = [
				__( 'General Settings', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php#blogname' ),
			];

			// Check.
			if ( empty( get_option( 'blogname' ) ) ) {
				$is_passed = 'no';
				$title     = __( 'Site title is not specified', 'ym-fast-seo' );
			}

			return new YMFSEO_Site_Health( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		YMFSEO_Site_Health::register_test( 'is-site-has-tagline', function () : YMFSEO_Site_Health {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site tagline is specified', 'ym-fast-seo' );
			$description = [];
			$links = [
				__( 'General Settings', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php#blogdescription' ),
			];

			// Check.
			if ( empty( get_option( 'blogdescription' ) ) ) {
				$is_passed = 'no';
				$title     = __( 'Site tagline is not specified', 'ym-fast-seo' );
			}

			return new YMFSEO_Site_Health( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		YMFSEO_Site_Health::register_test( 'is-site-has-icon', function () : YMFSEO_Site_Health {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site icon is specified', 'ym-fast-seo' );
			$description = [];
			$links = [
				__( 'Manage Site Icon', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php' ),
			];

			// Check.
			if ( empty( get_option( 'site_icon' ) ) ) {
				$is_passed = 'no';
				$title     = __( 'Site icon is not specified', 'ym-fast-seo' );
			}

			return new YMFSEO_Site_Health( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		YMFSEO_Site_Health::register_test( 'is-site-has-preview-image', function () : YMFSEO_Site_Health {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site preview image is specified', 'ym-fast-seo' );
			$description = [];
			$links = [
				__( 'Manage Site Preview Image', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php?page=ymfseo-settings#preview' ),
			];

			// Get data.
			$preview_image_id = intval( YMFSEO_Settings::get_option( 'preview_image_id' ) );

			// Check.
			if ( 0 == $preview_image_id ) {
				$is_passed = 'no';
				$title     = __( 'Site preview image is not specified', 'ym-fast-seo' );
			}

			return new YMFSEO_Site_Health( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		YMFSEO_Site_Health::register_test( 'is-index-now-works', function () : YMFSEO_Site_Health {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'IndexNow is active', 'ym-fast-seo' );
			$description = [
				__( 'YM Fast SEO sends a notification request to search engines after creating, modifying, or deleting posts and terms to inform them of changes on your site.', 'ym-fast-seo' ),
				sprintf(
					/* translators: %1$s: 10, %2$s: 200, %3$s: 202 */
					__( 'The last %1$s requests are shown below. Status %2$s indicates that the search engines have successfully processed the request. Status %3$s is allowed for the first request and shows that the request was successfully sent, but search engines are still verifying the API key.', 'ym-fast-seo' ),
					'<strong>10</strong>',
					'<strong>200</strong>',
					'<strong>202</strong>',
				),
				sprintf(
					/* translators: %s: Number of minutes */
					__( 'The request will not be sent if the URL was already sent less than %s minutes ago.', 'ym-fast-seo' ),
					'<strong>' . YMFSEO_IndexNow::$delay . '</strong>',
				),
			];
			$content = [
				'links' => [
					__( 'IndexNow Response Formats', 'ym-fast-seo' ) => 'https://www.indexnow.org/documentation#response',
				],
				'table' => [
					'head' => [
						__( 'URL', 'ym-fast-seo' ), __( 'Status', 'ym-fast-seo' ), __( 'Date', 'ym-fast-seo' ),
					],
					'body' => [],
				],
			];

			// Get data.
			$logs = YMFSEO_Logs::read( 'IndexNow', 10 );

			// If has logs.
			if ( $logs ) {
				// Check statuses.
				foreach ( $logs as $i => $entry ) {
					switch ( intval( $entry[ 'status' ] ) ) {
						case 200:
							// Silence is golden.
							break;
						case 202:
							if ( 0 == $i ) {
								$is_passed = 'warning';
							}
							break;
						default:
							if ( 0 == $i ) {
								$is_passed = 'no';
							}
							break;
					}
				}

				// Set table.
				$content[ 'table' ][ 'body' ] = array_map( function ( $item ) {
					$datetime = YMFSEO_Logs::parse_datetime( $item[ 'date' ] );
					$format   = get_option( 'date_format' ) . ', H:i:s';

					$item[ 'date' ] = $datetime->format( $format );

					return $item;
				}, $logs );
			}

			// Check.
			if ( ! YMFSEO_Settings::get_option( 'indexnow_key' ) ) {
				$is_passed = 'no';
				$title     = __( 'IndexNow API key is missing', 'ym-fast-seo' );
			}

			if ( ! YMFSEO_Settings::get_option( 'indexnow_enabled' ) ) {
				$is_passed = 'no';
				$title     = __( 'IndexNow sending disabled', 'ym-fast-seo' );
			}

			return new YMFSEO_Site_Health( $is_passed, $title, $description, $content );
		});
	}

	/**
	 * Registers site SEO health test.
	 * 
	 * @param string   $id    Test ID.
	 * @param callable $check Test function. Must returns YMFSEO_Site_Health instance.
	 */
	private static function register_test ( string $id, callable $check ) : void {
		YMFSEO_Site_Health::$tests[ $id ] = $check;
	}
}