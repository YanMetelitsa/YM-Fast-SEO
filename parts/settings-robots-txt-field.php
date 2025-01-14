<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	// Default values.
	$robots_txt_content     = '';
	$robots_txt_placeholder = '';

	// Sets robots.txt path.
	$robots_txt_path = home_url( 'robots.txt' );

	if ( YMFSEO_Checker::is_subdir_multisite() ) {
		$robots_txt_path = get_home_url( get_main_site_id(), 'robots.txt' );
	}

	// Gets robots.txt content.
	$response = wp_remote_get( $robots_txt_path );

	if ( is_wp_error ( $response ) || $response[ 'response' ][ 'code' ] != 200 ) {
		$robots_txt_placeholder = sprintf(
			/* translators: %s: robots.txt */
			__( 'Error loading the %s file', 'ym-fast-seo' ),
			'robots.txt',
		);
	} else {
		$robots_txt_content = wp_remote_retrieve_body( $response );
	}

	printf( '<textarea name="%1$s" id="%1$s" class="code" rows="8" cols="50" placeholder="%2$s" %3$s>%4$s</textarea>',
		esc_attr( $args[ 'label_for' ] ),
		esc_attr( $robots_txt_placeholder ),
		( YMFSEO_Checker::is_subdir_multisite() && ! is_main_site() ) ? 'readonly' : '',
		esc_textarea( $robots_txt_content ),
	);

	if ( is_main_site() ) {	
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'To restore the default value, clear this field and save.', 'ym-fast-seo' ),
		);
	}

	if ( YMFSEO_Checker::is_subdir_multisite() ) {
		printf(
			'<p class="description">%s</p>',
			wp_kses_post(
				sprintf(
					/* translators: %s: robots.txt */
					__( 'A network of sites using the subdirectory structure shares a single %s file.', 'ym-fast-seo' ),
					'<code>robots.txt</code>',
				)
			),
		);
		
		if ( ! is_main_site() ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post(
					sprintf(
						/* translators: %s: robots.txt */
						__( 'You can edit the %s file only on the main site.', 'ym-fast-seo' ),
						'<code>robots.txt</code>',
					)
				),
			);
		}
	}
?>

<?php if ( is_main_site() ) : ?>
	<script>
		jQuery( function( $ ) {
			wp.codeEditor.initialize( '<?php echo esc_html( $args[ 'label_for' ] ); ?>', {} );
		});
	</script>
<?php endif; ?>