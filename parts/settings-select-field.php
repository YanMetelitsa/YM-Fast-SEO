<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<select name="<?php echo esc_attr( $args[ 'label_for' ] ); ?>" id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>">
	<?php foreach ( $args[ 'options' ] ?? [] as $ymfseo_value => $ymfseo_label ) {
		printf( '<option value="%s"%s>%s</option>',
			esc_attr( $ymfseo_value ),
			selected( YMFSEO\Settings::get_option( $args[ 'label_for' ] ), $ymfseo_value, false ),
			esc_html( $ymfseo_label ),
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
	<p class="description">
		<?php echo esc_html( $args[ 'description' ] ); ?>
	</p>
<?php endif; ?>