<?php

/*
 * Plugin Name:       YM Fast SEO
 * Plugin URI:        https://yanmet.com/blog/ym-fast-seo-wordpress-plugin-documentation
 * Description:       Enhance your website with powerful, intuitive, and user-friendly SEO tools.
 * Version:           4.1.1
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Author:            Yan Metelitsa
 * Author URI:        https://yanmet.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ym-fast-seo
 */

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

// Gets plugin data.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Defines plugin constants.
define( 'YMFSEO_PLUGIN_DATA', get_plugin_data( __FILE__, true, false ) );
define( 'YMFSEO_ROOT_DIR',    plugin_dir_path( __FILE__ ) );
define( 'YMFSEO_ROOT_URI',    plugin_dir_url( __FILE__ ) );
define( 'YMFSEO_BASENAME',    plugin_basename( __FILE__ ) );

// Includes plugin components.
require_once YMFSEO_ROOT_DIR . 'includes/class-core.php';
require_once YMFSEO_ROOT_DIR . 'includes/class-settings.php';

require_once YMFSEO_ROOT_DIR . 'includes/class-meta-fields.php';
require_once YMFSEO_ROOT_DIR . 'includes/class-indexnow.php';

require_once YMFSEO_ROOT_DIR . 'includes/class-checker.php';
require_once YMFSEO_ROOT_DIR . 'includes/class-site-health.php';
require_once YMFSEO_ROOT_DIR . 'includes/class-logger.php';

require_once YMFSEO_ROOT_DIR . 'includes/deprecated.php';

// Inits plugin components.
Core::init();
Settings::init();

MetaFields::init();
IndexNow::init();

SiteHealth::init();