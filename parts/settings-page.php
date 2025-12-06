<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	flush_rewrite_rules();
?>

<div class="wrap ymfseo-settings-page">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<nav class="nav-tab-wrapper">
		<?php foreach ( YMFSEO\Settings::$registered_sections as $ymfseo_section ) : ?>
			<div class="nav-tab" data-target="<?php echo esc_attr( $ymfseo_section[ 'slug' ] ); ?>">
				<span class="dashicons <?php echo esc_attr( $ymfseo_section[ 'icon' ] ); ?>"></span>
				<span><?php echo esc_html( $ymfseo_section[ 'title' ] ); ?></span>
			</div>
		<?php endforeach; ?>
	</nav>

	<nav class="ymfseo-settings-page__nav" style="display:none">
		<?php /*foreach ( YMFSEO\Settings::$registered_sections as $ymfseo_section ) : ?>
			<div class="ymfseo-settings-page__nav-item" data-target="<?php echo esc_attr( $ymfseo_section[ 'slug' ] ); ?>">
				<span class="dashicons <?php echo esc_attr( $ymfseo_section[ 'icon' ] ); ?>"></span>
				<span class="label"><?php echo esc_html( $ymfseo_section[ 'title' ] ); ?></span>
			</div>
		<?php endforeach;*/ ?>
	</nav>

	<form method="POST" action="options.php">
		<?php settings_fields( YMFSEO\Settings::$params[ 'page_slug' ] ); ?>

		<section>
			<?php do_settings_sections( YMFSEO\Settings::$params[ 'page_slug' ] ); ?>
		</section>
			
		<?php submit_button(); ?>
	</form>

	<script>
		window.addEventListener( 'DOMContentLoaded', e => {
			YMFSEO_Settings.initNav();
			YMFSEO_Settings.initSaveButtons();
			YMFSEO_Settings.initSections();
		});
	</script>
</div>