<?php

/*
 * Plugin Name:       YM Fast SEO
 * Description:       Enhance your website with powerful, intuitive, and user-friendly SEO tools.
 * Version:           4.0.0
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Tested up to:      6.8
 * Author:            Yan Metelitsa
 * Author URI:        https://yanmet.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ym-fast-seo
 */

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

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
require_once YMFSEO_ROOT_DIR . 'includes/plugin.php';
require_once YMFSEO_ROOT_DIR . 'includes/favicon.php';
require_once YMFSEO_ROOT_DIR . 'includes/sanitizer.php';
require_once YMFSEO_ROOT_DIR . 'includes/meta-fields.php';
require_once YMFSEO_ROOT_DIR . 'includes/schema.php';
require_once YMFSEO_ROOT_DIR . 'includes/checker.php';
require_once YMFSEO_ROOT_DIR . 'includes/settings.php';
require_once YMFSEO_ROOT_DIR . 'includes/indexnow.php';
require_once YMFSEO_ROOT_DIR . 'includes/editor-role.php';
require_once YMFSEO_ROOT_DIR . 'includes/site-health.php';
require_once YMFSEO_ROOT_DIR . 'includes/logger.php';

// Inits plugin components.
YMFSEO::init();
YMFSEO_Favicon::init();
YMFSEO_Editor_Role::init();
YMFSEO_Settings::init();
YMFSEO_Meta_fields::init();
YMFSEO_IndexNow::init();
YMFSEO_Site_Health::init();