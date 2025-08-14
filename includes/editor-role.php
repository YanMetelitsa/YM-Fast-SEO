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
	public static function init () {
		// Creates SEO Editor role and adds caps.
		register_activation_hook( YMFSEO_BASENAME, function () {
			/* translators: User role name */
			add_role( 'ymfseo_seo_editor', __( 'SEO Editor', 'ym-fast-seo' ), [
				'read'                    => true,

				'publish_pages'           => true,
				'edit_pages'              => true,
				'edit_published_pages'    => true,
				'edit_others_pages'       => true,

				'publish_posts'           => true,
				'edit_posts'              => true,
				'edit_published_posts'    => true,
				'edit_others_posts'       => true,

				'manage_categories'       => true,

				'upload_files'            => true,

				'ymfseo_edit_metas'       => true,
				'view_site_health_checks' => true,
				'manage_options'          => true,
			]);

			$admin_role = get_role( 'administrator' );

			if ( $admin_role ) {
				$admin_role->add_cap( 'ymfseo_edit_metas' );
			}

			$editor_role = get_role( 'editor' );

			if ( $editor_role ) {
				$editor_role->add_cap( 'ymfseo_edit_metas' );
			}
		});

		// Removes SEO Editor role and caps.
		register_deactivation_hook( YMFSEO_BASENAME, function () {
			remove_role( 'ymfseo_seo_editor' );

			$admin_role = get_role( 'administrator' );

			if ( $admin_role ) {
				$admin_role->remove_cap( 'ymfseo_edit_metas' );
			}

			$editor_role = get_role( 'editor' );
			
			if ( $editor_role ) {
				$editor_role->remove_cap( 'ymfseo_edit_metas' );
			}
		});
	}
}