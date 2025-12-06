<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	// Default values.
	$ymfseo_robots_txt_content     = '';
	$ymfseo_robots_txt_placeholder = '';

	// Sets robots.txt path.
	$ymfseo_robots_txt_path = home_url( 'robots.txt' );

	if ( YMFSEO\Checker::is_subdir_multisite() ) {
		$ymfseo_robots_txt_path = get_home_url( get_main_site_id(), 'robots.txt' );
	}

	// Gets robots.txt content.
	$ymfseo_robots_txt_response = wp_remote_get( $ymfseo_robots_txt_path );

	if ( is_wp_error ( $ymfseo_robots_txt_response ) || $ymfseo_robots_txt_response[ 'response' ][ 'code' ] != 200 ) {
		$ymfseo_robots_txt_placeholder = sprintf(
			/* translators: %s: robots.txt */
			__( 'Error loading the %s file', 'ym-fast-seo' ),
			esc_attr( 'robots.txt' ),
		);
	} else {
		$ymfseo_robots_txt_content = wp_remote_retrieve_body( $ymfseo_robots_txt_response );
	}

	printf( '<textarea name="%1$s" id="%1$s" class="code" rows="8" cols="50" placeholder="%2$s" %3$s>%4$s</textarea>',
		esc_attr( $args[ 'label_for' ] ),
		esc_attr( $ymfseo_robots_txt_placeholder ),
		esc_attr( ( YMFSEO\Checker::is_subdir_multisite() && ! is_main_site() ) ? 'readonly' : '' ),
		esc_textarea( $ymfseo_robots_txt_content ),
	);

	if ( is_main_site() ) {	
		printf( '<p class="description">%s</p>',
			esc_html__( 'To restore the default value, clear this field and save.', 'ym-fast-seo' ),
		);
	}

	if ( YMFSEO\Checker::is_subdir_multisite() ) {
		printf( '<p class="description">%s</p>',
			wp_kses_post(
				/* translators: %s: robots.txt */
				sprintf( __( 'A network of sites using the subdirectory structure shares a single %s file.', 'ym-fast-seo' ),
					'<code>robots.txt</code>',
				),
			),
		);
		
		if ( ! is_main_site() ) {
			printf( '<p class="description">%s</p>',
				wp_kses_post(
					/* translators: %s: robots.txt */
					sprintf( __( 'You can edit the %s file only on the main site.', 'ym-fast-seo' ),
						'<code>robots.txt</code>',
					),
				),
			);
		}
	}
?>

<?php if ( is_main_site() ) : ?>
	<script>
		jQuery( function ( $ ) {
			wp.codeEditor.initialize( '<?php echo esc_html( $args[ 'label_for' ] ); ?>', {} );
		});
	</script>
<?php endif; ?>