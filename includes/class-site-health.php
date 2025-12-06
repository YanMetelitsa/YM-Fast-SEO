<?php

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

/**
 * Provides site SEO health validation functionality.
 * 
 * @since 3.0.0
 */
class SiteHealth {
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
	public function __construct ( string $is_passed, string $title, array $description, array $content = [] ) {
		$this->is_passed   = $is_passed;
		$this->title       = $title;
		$this->description = $description;
		$this->content     = $content;
	}

	/**
	 * Inits Site SEO Health features.
	 */
	public static function init () {
		// Adds site health SEO navigation tab.
		add_filter( 'site_health_navigation_tabs', function (array  $tabs ) : array {
			$tabs[ 'ymfseo' ] = __( 'SEO', 'ym-fast-seo' );
		
			return $tabs;
		});

		// Includes site health SEO navigation tab content.
		add_action( 'site_health_tab_content', function ( string $tab ) {
			if ( 'ymfseo' !== $tab ) {
				return;
			}
		
			include YMFSEO_ROOT_DIR . 'parts/site-health-seo-tab.php';
		});

		SiteHealth::register_test( 'is-indexing-available', function () : SiteHealth {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site is available for indexing', 'ym-fast-seo' );
			$description = [];
			$links       = [
				__( 'Reading Settings', 'ym-fast-seo' ) => get_admin_url( null, 'options-reading.php#blog_public' ),
			];

			// Check.
			if ( ! Checker::is_site_public() ) {
				$is_passed = 'no';
				$title     = __( 'Site is not available for indexing', 'ym-fast-seo' );
			}

			return new SiteHealth( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		SiteHealth::register_test( 'is-site-has-name', function () : SiteHealth {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site title is specified', 'ym-fast-seo' );
			$description = [];
			$links       = [
				__( 'General Settings', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php#blogname' ),
			];

			// Check.
			if ( empty( get_option( 'blogname' ) ) ) {
				$is_passed = 'no';
				$title     = __( 'Site title is not specified', 'ym-fast-seo' );
			}

			return new SiteHealth( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		SiteHealth::register_test( 'is-site-has-tagline', function () : SiteHealth {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site tagline is specified', 'ym-fast-seo' );
			$description = [];
			$links       = [
				__( 'General Settings', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php#blogdescription' ),
			];

			// Check.
			if ( empty( get_option( 'blogdescription' ) ) ) {
				$is_passed = 'no';
				$title     = __( 'Site tagline is not specified', 'ym-fast-seo' );
			}

			return new SiteHealth( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		SiteHealth::register_test( 'is-site-has-icon', function () : SiteHealth {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site icon is specified and configured', 'ym-fast-seo' );
			$description = [];
			$links       = [
				__( 'Manage Site Icon', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php#site-icon-preview' ),
			];

			// Check.
			if ( ! Checker::is_imagick_available() ) {
				$is_passed     = 'warning';
				$title         = __( 'Site icon could not be converted', 'ym-fast-seo' );
				/* translators: %1$s: Imagick, %2$s: File formats list */
				$description[] = sprintf( __( 'To install the site icon correctly, %1$s must be installed and support the following file formats: %2$s.', 'ym-fast-seo' ),
					'Imagick',	
					implode( ', ', [ 'SVG', 'PNG', 'ICO' ] )
				);
				$links = array_merge(
					[
						/* translators: %s: Imagick */
						sprintf( __( 'About %s', 'ym-fast-seo' ), 'Imagick' ) => 'https://www.php.net/manual/book.imagick.php',
					],
					$links,
				);
			}

			if ( ! Checker::is_svg_favicon() ) {
				$is_passed     = 'warning';
				$title         = __( 'Site icon is not in SVG format', 'ym-fast-seo' );
				$description[] = __( 'Modern web standards recommend using SVG icons. YM Fast SEO will automatically generate required PNG and ICO files.', 'ym-fast-seo' );
			}

			if ( empty( get_option( 'site_icon' ) ) ) {
				$is_passed   = 'no';
				$title       = __( 'Site icon is not specified', 'ym-fast-seo' );
				$description = [];
			}

			return new SiteHealth( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		SiteHealth::register_test( 'is-site-has-preview-image', function () : SiteHealth {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site preview image is specified', 'ym-fast-seo' );
			$description = [];
			$links       = [
				__( 'Manage Site Preview Image', 'ym-fast-seo' ) => get_admin_url( null, 'options-general.php?page=ymfseo#preview' ),
			];

			// Get data.
			$preview_image_id = intval( Settings::get_option( 'preview_image_id' ) );

			// Check.
			if ( 0 == $preview_image_id ) {
				$is_passed = 'no';
				$title     = __( 'Site preview image is not specified', 'ym-fast-seo' );
			}

			return new SiteHealth( $is_passed, $title, $description, [
				'links' => $links,
			]);
		});

		SiteHealth::register_test( 'is-indexnow-works', function () : SiteHealth {
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
					'<strong>' . IndexNow::$delay . '</strong>',
				),
			];
			$content     = [
				'links' => [
					__( 'IndexNow Response Formats', 'ym-fast-seo' ) => 'https://www.indexnow.org/documentation#response',
				],
				'table' => [
					'head' => [
						__( 'URL', 'ym-fast-seo' ),
						__( 'Status', 'ym-fast-seo' ),
						__( 'Date', 'ym-fast-seo' ),
					],
					'body' => [],
				],
			];

			// Get data.
			$logs = Logger::read( 'IndexNow', 10 );

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
					$datetime = Logger::parse_datetime( $item[ 'date' ] );

					$format = sprintf( '%s (%s)', 
						get_option( 'date_format' ),
						get_option( 'time_format' ),
					);

					$item[ 'date' ] = $datetime->format( $format );

					return $item;
				}, $logs );
			}

			// Check.
			if ( ! Settings::get_option( 'indexnow_key' ) ) {
				$is_passed = 'no';
				$title     = __( 'IndexNow API key is missing', 'ym-fast-seo' );
			}

			if ( ! Settings::get_option( 'indexnow_enabled' ) ) {
				$is_passed = 'no';
				$title     = __( 'IndexNow sending disabled', 'ym-fast-seo' );
			}

			if ( ! Checker::is_site_public() ) {
				$is_passed   = 'no';
				$title       = __( 'IndexNow sending disabled', 'ym-fast-seo' );
				$description = [];
				$content     = [
					'links' => [
						__( 'Reading Settings', 'ym-fast-seo' ) => get_admin_url( null, 'options-reading.php#blog_public' ),
					],
				];
			}

			return new SiteHealth( $is_passed, $title, $description, $content );
		});

		SiteHealth::register_test( 'is-llms-enabled', function () : SiteHealth {
			// Default.
			$is_passed   = 'yes';
			$title       = __( 'Site is optimized for AI crawlers', 'ym-fast-seo' );
			$description = [
				/* translators: %s: llms.txt */
				sprintf( __( 'The %s file is an automatically generated index designed to help large language models (LLMs) discover and access structured content from a website, such as recent posts and pages.', 'ym-fast-seo' ),
					wp_kses_post( '<code>llms.txt</code>' ),
				),
				/* translators: %s: llms.txt */
				sprintf( __( 'Here is a list of the most recent requests made to the %s file.', 'ym-fast-seo' ),
					wp_kses_post( '<code>llms.txt</code>' ),
				),
			];
			$content     = [
				'links' => [
					__( 'Find out More', 'ym-fast-seo' ) => 'https://llmstxt.org/',
				],
				'table' => [
					'head' => [
						__( 'User-Agent', 'ym-fast-seo' ),
						__( 'File', 'ym-fast-seo' ),
						__( 'Date', 'ym-fast-seo' ),
					],
					'body' => [],
				],
			];

			// Get data.
			$logs = Logger::read( 'llms-txt', 10 );

			// If has logs.
			if ( $logs ) {
				// Set table.
				$content[ 'table' ][ 'body' ] = array_map( function ( $item ) {
					$datetime = Logger::parse_datetime( $item[ 'date' ] );

					$format = sprintf( '%s (%s)', 
						get_option( 'date_format' ),
						get_option( 'time_format' ),
					);

					$item[ 'date' ] = $datetime->format( $format );

					return $item;
				}, $logs );
			}

			// Check.
			if ( ! Settings::get_option( 'enable_llms_txt' ) ) {
				$is_passed = 'warning';
				$title     = __( 'Site is not optimized for AI crawlers', 'ym-fast-seo' );
			}

			return new SiteHealth( $is_passed, $title, $description, $content );
		});
	}

	/**
	 * Registers site SEO health test.
	 * 
	 * @param string   $id    Test ID.
	 * @param callable $check Test function. Must returns YMFSEO_Site_Health instance.
	 */
	private static function register_test ( string $id, callable $check ) {
		SiteHealth::$tests[ $id ] = $check;
	}
}