<?php
	/** Exit if accessed directly */
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="POST" action="options.php">
		<p>
			<?php
				/* translators: %s: Link to general settings page */
				printf( wp_kses_post( __( 'To update the site name and description, navigate to the <a href="%s">general settings page</a>.', 'ym-fast-seo' ) ),
					esc_url( get_admin_url( null, 'options-general.php' ) ),
				);
			?>
		</p>

		<?php
			settings_fields( 'ymfseo_settings' );
			do_settings_sections( 'ymfseo_settings' );

			submit_button();
		?>
	</form>
</div>