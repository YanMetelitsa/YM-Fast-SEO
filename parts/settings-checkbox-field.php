<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<fieldset>
	<label for="<?php echo esc_attr( $args[ 'label_for' ] ); ?>">
		<?php
			printf( '<input type="checkbox" name="%1$s" id="%1$s" value="1"%2$s>',
				esc_attr( $args[ 'label_for' ] ),
				checked( YMFSEO_Settings::get_option( $args[ 'label_for' ] ), true, false ),
			);

			echo esc_html( $args[ 'label' ] ?? '' );
		?>
	</label>

	<?php if ( isset( $args[ 'description' ] ) ) : ?>
		<p class="description"><?php echo esc_html( $args[ 'description' ] ); ?></p>
	<?php endif; ?>
</fieldset>