<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	printf( '<textarea name="%1$s" id="%1$s" class="%2$s" rows="%3$s" cols="50">%4$s</textarea>',
		esc_attr( $args[ 'label_for' ] ),
		esc_attr( $args[ 'input_class' ] ?? '' ),
		esc_attr( $args[ 'rows' ] ?? 4 ),
		esc_textarea( YMFSEO_Settings::get_option( $args[ 'label_for' ] ) ),
	);

	if ( isset( $args[ 'description' ] ) ) {
		printf( '<p class="description">%s</p>', wp_kses_post( $args[ 'description' ] ) );
	}
?>

<?php if ( $args[ 'codemirror' ] ?? false ) : ?>
	<script>
		jQuery( function( $ ) {
			wp.codeEditor.initialize( '<?php echo esc_html( $args[ 'label_for' ] ); ?>', {} );
		});
	</script>
<?php endif; ?>