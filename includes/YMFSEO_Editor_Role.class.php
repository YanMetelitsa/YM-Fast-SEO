<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Provides SEO Editor user role features.
 * 
 * @since 3.0.0
 */
class YMFSEO_Editor_Role {
	/**
	 * Inits SEO Editor role.
	 */
	public static function init () : void {
		// Creates SEO Editor role and adds caps.
		register_activation_hook( __FILE__, function () {
			/* translators: User role name */
			add_role( 'ymfseo_seo_editor', __( 'SEO Editor', 'ym-fast-seo' ), [
				'ymfseo_edit_metas'    => true,
				'ymfseo_edit_settings' => true,
				'read'                 => true,
				'upload_files'         => true,
				'manage_options'       => true,
				'edit_posts'           => true,
				'edit_others_posts'    => true,
				'edit_published_posts' => true,
				'publish_posts'        => true,
				'manage_categories'    => true,
				'edit_pages'           => true,
				'edit_others_pages'    => true,
				'edit_published_pages' => true,
				'publish_pages'        => true,
			]);

			$admin_role = get_role( 'administrator' );
			$admin_role->add_cap( 'ymfseo_edit_metas' );
			$admin_role->add_cap( 'ymfseo_edit_settings' );

			$editor_role = get_role( 'editor' );
			$editor_role->add_cap( 'ymfseo_edit_metas' );
			$editor_role->add_cap( 'ymfseo_edit_settings' );
		});

		// Removes SEO Editor role and caps.
		register_deactivation_hook( __FILE__, function () {
			remove_role( 'ymfseo_seo_editor' );

			$admin_role = get_role( 'administrator' );
			$admin_role->remove_cap( 'ymfseo_edit_metas' );
			$admin_role->remove_cap( 'ymfseo_edit_settings' );

			$editor_role = get_role( 'editor' );
			$editor_role->remove_cap( 'ymfseo_edit_metas' );
			$editor_role->remove_cap( 'ymfseo_edit_settings' );
		});
	}
}