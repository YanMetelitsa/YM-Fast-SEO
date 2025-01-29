<?php

/*
 * Plugin Name:       YM Fast SEO
 * Description:       Enhance your website with powerful, intuitive, and user-friendly SEO tools.
 * Version:           3.2.3
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Tested up to:      6.7.1
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
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Meta_Fields.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Schema.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Checker.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Settings.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_IndexNow.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Editor_Role.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Site_Health.class.php';
require_once YMFSEO_ROOT_DIR . 'includes/YMFSEO_Logs.class.php';

YMFSEO::init();
YMFSEO_Editor_Role::init();
YMFSEO_Settings::init();
YMFSEO_Meta_fields::init();
YMFSEO_IndexNow::init();
YMFSEO_Site_Health::init();