<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * YM Fast SEO site icon class.
 */
class YMFSEO_Favicon {
	/**
	 * Inits site icon.
	 * 
	 * @since 4.0.0
	 */
	public static function init () {
		if ( YMFSEO_Checker::is_imagick_available() ) {
			// Clears rewrite rules after updating site icon.
			add_action( 'update_option_site_icon', function () {
				flush_rewrite_rules();
			});

			if ( YMFSEO_Checker::is_svg_favicon() ) {
				// Adds favicons rewrite rules.
				add_action( 'init', function () {
					add_rewrite_rule( '^favicon\.svg$',          'index.php?ymfseo_favicon=svg', 'top' );
					add_rewrite_rule( '^favicon-ico\.ico$',      'index.php?ymfseo_favicon=ico', 'top' );
					add_rewrite_rule( '^favicon-32\.png$',       'index.php?ymfseo_favicon=32',  'top' );
					add_rewrite_rule( '^favicon-96\.png$',       'index.php?ymfseo_favicon=96',  'top' );
					add_rewrite_rule( '^favicon-192\.png$',      'index.php?ymfseo_favicon=192', 'top' );
					add_rewrite_rule( '^apple-touch-icon\.png$', 'index.php?ymfseo_favicon=180', 'top' );
				});
				add_filter( 'query_vars', function ( array $vars ) : array {
					$vars[] = 'ymfseo_favicon';
	
					return $vars;
				});
				add_action( 'template_redirect', function () {
					$favicon_type = get_query_var( 'ymfseo_favicon' );
	
					$site_icon_id   = get_option( 'site_icon' );
					$site_icon_path = get_attached_file( $site_icon_id );
	
					$imagick = new Imagick();
					$imagick->setBackgroundColor( new ImagickPixel( 'transparent' ) );
					$imagick->readImage( $site_icon_path );
					$imagick->setImageFormat( 'png' );
	
					// PNG formats.
					if ( is_numeric( $favicon_type ) ) {
						$size = (int) $favicon_type;
	
						$imagick->resizeImage( $size, $size, Imagick::FILTER_LANCZOS, 1 );
	
						header( 'Content-Type: image/png' );
						header( 'Cache-Control: max-age=31536000, public' );
						echo $imagick; // phpcs:ignore
	
						exit;
					}
	
					// Other formats.
					switch ( $favicon_type ) {
						case 'svg':
							header( 'Content-Type: image/svg+xml' );
							header( 'Cache-Control: max-age=31536000, public' );
							echo YMFSEO::get_filesystem()->get_contents( $site_icon_path ); // phpcs:ignore
	
							exit;
						case 'ico':
							$imagick->resizeImage( 48, 48, Imagick::FILTER_LANCZOS, 1 );
							$imagick->setImageFormat( 'ico' );
	
							header( 'Content-Type: image/x-icon' );
							header( 'Cache-Control: max-age=31536000, public' );
							echo $imagick; // phpcs:ignore
	
							exit;
					}
				});
	
				// Removes default favicon output.
				remove_action( 'wp_head', 'wp_site_icon', 99 );
	
				// Adds new favicon output.
				add_action( 'wp_head', function () {
					include YMFSEO_ROOT_DIR . 'parts/favicon.php';
				}, 99 );
			}
		}
	}
}