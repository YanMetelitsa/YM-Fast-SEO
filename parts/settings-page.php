<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap ymfseo-seettings-page">
	<header class="ymfseo-seettings-page__header">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p>
			<?php
				/* translators: %s: Link to general settings page */
				printf( wp_kses_post( __( 'To update the site name and description, navigate to the <a href="%s">general settings page</a>.', 'ym-fast-seo' ) ),
					esc_url( get_admin_url( null, 'options-general.php' ) ),
				);
			?>
		</p>
	</header>

	<form method="POST" action="options.php">
		<?php
			submit_button();

			settings_fields( YMFSEO_Settings::$params[ 'page_slug' ] );
			do_settings_sections( YMFSEO_Settings::$params[ 'page_slug' ] );

			submit_button();
		?>
	</form>
</div>