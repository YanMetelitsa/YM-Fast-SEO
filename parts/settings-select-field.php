<?php
	/** Exit if accessed directly */
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<select name="<?php echo esc_attr( $args[ 'label_for' ] ); ?>" id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>">
	<?php foreach ( $args[ 'options' ] ?? [] as $value => $label ) {
		printf( '<option value="%s"%s>%s</option>',
		esc_attr( $value ),
		selected( YMFSEO::get_option( str_replace( 'ymfseo_', '', $args[ 'label_for' ] ) ), $value, false ),
		esc_html( $label ),
		);
	} ?>
</select>
<?php if ( isset( $args[ 'description' ] ) ) : ?>
	<p class="description"><?php echo esc_html( $args[ 'description' ] ); ?></p>
<?php endif; ?>