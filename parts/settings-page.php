<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	flush_rewrite_rules();
?>

<div class="wrap ymfseo-seettings-page">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<!-- Navigation -->
	<nav class="ymfseo-seettings-page__nav">
		<?php foreach ( YMFSEO_Settings::$registered_sections as $section ) : ?>
			<div class="ymfseo-seettings-page__nav-item" data-target="<?php echo esc_attr( $section[ 'slug' ] ); ?>">
				<span class="dashicons <?php echo esc_attr( $section[ 'icon' ] ); ?>"></span>
				<span class="label"><?php echo esc_attr( $section[ 'title' ] ); ?></span>
			</div>
		<?php endforeach; ?>
	</nav>

	<!-- Form -->
	<form method="POST" action="options.php">
		<?php settings_fields( YMFSEO_Settings::$params[ 'page_slug' ] ); ?>

		<section>
		<?php do_settings_sections( YMFSEO_Settings::$params[ 'page_slug' ] ); ?>
		</section>
			
		<?php submit_button(); ?>
	</form>

	<!-- JS -->
	<script>
		window.addEventListener( 'DOMContentLoaded', e => {
			YMFSEO_Settings.initSettingsNav();
			YMFSEO_Settings.initSettingsSaveButtons();
			YMFSEO_Settings.initSettingsSections();
		});
	</script>
</div>