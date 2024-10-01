<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<select name="<?php echo esc_attr( $args[ 'label_for' ] ); ?>" id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>">
	<?php foreach ( $args[ 'options' ] ?? [] as $value => $label ) {
		printf( '<option value="%s"%s>%s</option>',
			esc_attr( $value ),
			selected( YMFSEO_Settings::get_option( $args[ 'label_for' ] ), $value, false ),
			esc_html( $label ),
		);
	} ?>
</select>

<script>
	document.querySelector( 'select[ name=<?php echo esc_attr( $args[ 'label_for' ] ); ?> ]' ).addEventListener( 'change', e => {
		e.target.querySelectorAll( 'option' ).forEach( option => {
			option.removeAttribute( 'selected' );

			if ( e.target.value == option.getAttribute( 'value' ) ) {
				option.setAttribute( 'selected', 'selected' );
			}
		});
	});
</script>

<?php if ( isset( $args[ 'description' ] ) ) : ?>
	<p class="description"><?php echo esc_html( $args[ 'description' ] ); ?></p>
<?php endif; ?>