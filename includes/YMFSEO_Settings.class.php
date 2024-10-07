<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * YM Fast SEO plugin settings class.
 * 
 * @since 2.0.0
 */
class YMFSEO_Settings {
	/**
	 * Settings module parameters.
	 * 
	 * @var array
	 */
	public static array $params = [];
	
	/**
	 * Default YM Fast SEO settings options values.
	 * 
	 * @var array
	 */
	public static array $default_settings = [];

	/**
	 * Adds section to YM Fast SEO settings page.
	 * 
	 * @param string $slug  Section slug.
	 * @param string $title Section title.
	 * @param array  $args  {
	 * 		Section arguments.
	 * 
	 * 		@type string $description Section description below title. 
	 * }
	 */
	public static function add_section ( string $slug, string $title, array $args = [] ) {
		add_settings_section(
			"ymfseo_{$slug}_section",
			$title,
			fn ( $args ) => include YMFSEO_ROOT_DIR . 'parts/settings-section.php',
			YMFSEO_Settings::$params[ 'page_slug' ],
			$args
		);
	}

	/**
	 * Registers YM Fast SEO settings option.
	 * 
	 * @param string $slug       Option name.
	 * @param string $title      Option title.
	 * @param string $type       Option type.
	 * @param string $section    Option section slug without 'ymfseo_____section'.
	 * @param string $field_part Option field part file name without 'parts/settings-___-field.php'.
	 * @param array  $args       Option arguments.
	 */
	public static function register_option ( string $slug, string $title, string $type, string $section, string $field_part, array $args = [] ) : void {
		// Exits if default value no exist.
		if ( ! isset( self::$default_settings[ $slug ] ) ) {
			$break   = true;
			$default = '';

			$allowed = [
				'post_type_title_'       => '',
				'post_type_page_type_'   => 'ItemPage',
				'taxonomy_title_'        => '',
				'taxonomy_description_'  => '',
			];

			foreach ( $allowed as $allowed_item => $default_value ) {
				if ( str_contains( $slug, $allowed_item ) ) {
					$break   = false;
					$default = $default_value;
					
					break;
				}
			}

			if ( $break ) return;

			self::$default_settings[ $slug ] = $default;
		}

		// Registers setting and adds field.
		register_setting( YMFSEO_Settings::$params[ 'page_slug' ], "ymfseo_{$slug}", [
			'type'              => $type,
			'default'           => self::$default_settings[ $slug ],
			'sanitize_callback' => 'robots_txt' == $slug ? 'sanitize_textarea_field' : 'sanitize_text_field',
		]);
		add_settings_field(
			"ymfseo_{$slug}",
			$title,
			fn ( $args ) => include YMFSEO_ROOT_DIR . "parts/settings-{$field_part}-field.php",
			YMFSEO_Settings::$params[ 'page_slug' ],
			"ymfseo_{$section}_section",
			array_merge( [ 'label_for' => "ymfseo_{$slug}" ], $args )
		);
	}

	/**
	 * Retrieves an YMFSEO option value based on an option name.
	 * 
	 * @since 2.1.0 Has `$default` argument.
	 * 
	 * @param string $option  Option name. Allowed without 'ymfseo_'.
	 * @param mixed  $default Default option value.
	 * 
	 * @return mixed Option or default value.
	 */
	public static function get_option ( string $option, mixed $default = false ) : mixed {
		if ( 'ymfseo_' !== mb_substr( $option, 0, 7 ) ) {
			$option = "ymfseo_$option";
		}

		$default_value = self::$default_settings[ str_replace( 'ymfseo_', '', $option ) ] ?? $default;

		return get_option( $option, $default_value );
	}
}