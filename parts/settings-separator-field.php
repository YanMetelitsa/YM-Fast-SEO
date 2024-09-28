<?php
	/** Exit if accessed directly */
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="ymfseo-settings-separator-grid">
	<?php foreach ( $args[ 'options' ] as $value => $label ) : ?>
		<div class="ymfseo-settings-separator-grid__item">
			<?php printf( '<input type="radio" name="%s" value="%s"%s>',
				esc_attr( $args[ 'label_for' ] ),
				esc_attr( $value ),
				checked( YMFSEO::get_option( str_replace( 'ymfseo_', '', $args[ 'label_for' ] ) ), $value, false ),
			); ?>
			<span><?php echo esc_html( $label ); ?></span>
		</div>
	<?php endforeach; ?>
</div>

<?php if ( isset( $args[ 'description' ] ) ) : ?>
	<p class="description"><?php echo wp_kses_post( $args[ 'description' ] ); ?></p>
<?php endif; ?>