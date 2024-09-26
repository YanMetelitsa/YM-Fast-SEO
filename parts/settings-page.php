<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="POST" action="options.php">
		<?php
			settings_fields( 'ymfseo_settings' );
			do_settings_sections( 'ymfseo_settings' );

			submit_button();
		?>
	</form>
</div>