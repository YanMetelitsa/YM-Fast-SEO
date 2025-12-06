<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="ymfseo-settings-separator-grid">
	<?php foreach ( $args[ 'options' ] as $ymfseo_value ) : ?>
		<div class="ymfseo-settings-separator-grid__item">
			<?php printf( '<input type="radio" name="%s" value="%s"%s>',
				esc_attr( $args[ 'label_for' ] ),
				esc_attr( $ymfseo_value ),
				checked( YMFSEO\Settings::get_option( $args[ 'label_for' ] ), $ymfseo_value, false ),
			); ?>
			<span><?php echo esc_html( $ymfseo_value ); ?></span>
		</div>
	<?php endforeach; ?>
</div>

<?php if ( isset( $args[ 'description' ] ) ) : ?>
	<p class="description">
		<?php echo wp_kses_post( $args[ 'description' ] ); ?>
	</p>
<?php endif; ?>