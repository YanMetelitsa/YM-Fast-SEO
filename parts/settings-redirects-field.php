<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	$ymfseo_option_name  = $args[ 'label_for' ];
	$ymfseo_option_value = YMFSEO\Settings::get_option( $ymfseo_option_name, [] );
	
	if ( empty( $ymfseo_option_value ) ) {
		$ymfseo_option_value[] = [
			'type'     => '',
			'from'     => '',
			'is_regex' => false,
			'to'       => '',
		];
	}

	$ymfseo_redirect_types = [
		/* translators: Redirect type */
		'301' => '301 – ' . __( 'Permanent', 'ym-fast-seo' ),
		/* translators: Redirect type */
		'302' => '302 – ' . __( 'Temporary', 'ym-fast-seo' ),
	];
?>

<div class="ymfseo-settings-redirects-section">
	<div class="ymfseo-settings-redirects-section__grid">
		<?php foreach ( $ymfseo_option_value as $ymfseo_index => $ymfseo_redirect ) : ?>		
			<div class="ymfseo-settings-redirects-section__item" data-counter="<?php echo esc_attr( $ymfseo_index ); ?>">
				<?php printf( '<select name="%s" id="%s">',
					esc_attr( "{$ymfseo_option_name}[{$ymfseo_index}][type]" ),
					esc_attr( "{$ymfseo_option_name}_{$ymfseo_index}_type" ),
				); ?>
					<?php foreach ( $ymfseo_redirect_types as $ymfseo_value => $ymfseo_value ) {
						printf( '<option value="%s"%s>%s</option>',
							esc_attr( $ymfseo_value ),
							selected( $ymfseo_redirect[ 'type' ], $ymfseo_value, false ),
							esc_attr( $ymfseo_value ),
						);
					} ?>
				</select>
	
				<?php printf( '<input type="text" name="%s" class="code" id="%s" value="%s" spellcheck="false" placeholder="%s">',
					esc_attr( "{$ymfseo_option_name}[{$ymfseo_index}][from]" ),
					esc_attr( "{$ymfseo_option_name}_{$ymfseo_index}_from" ),
					esc_attr( $ymfseo_redirect[ 'from' ] ),
					esc_attr__( 'Source URL', 'ym-fast-seo' ),
				); ?>

				<div class="regex">
					<?php printf( '<input type="checkbox" name="%s" id="%s" value="1"%s>',
						esc_attr( "{$ymfseo_option_name}[{$ymfseo_index}][is_regex]" ),
						esc_attr( "{$ymfseo_option_name}_{$ymfseo_index}_is_regex" ),
						checked( $ymfseo_redirect[ 'is_regex' ], true, false ),
					); ?>

					<label for="<?php echo esc_attr( "{$ymfseo_option_name}_{$ymfseo_index}_is_regex" ) ?>">RegEx</label>
				</div>
				
				<?php printf( '<input type="text" name="%s" class="code" id="%s" value="%s" spellcheck="false" placeholder="%s">',
					esc_attr( "{$ymfseo_option_name}[{$ymfseo_index}][to]" ),
					esc_attr( "{$ymfseo_option_name}_{$ymfseo_index}_to" ),
					esc_attr( $ymfseo_redirect[ 'to' ] ),
					esc_attr__( 'Target URL', 'ym-fast-seo' ),
				); ?>
	
				<span class="dashicons dashicons-trash" onclick="YMFSEO_Settings.removeRedirectRow( this )"></span>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="button button-secondary" onclick="YMFSEO_Settings.addRedirectRow( this )">
		<span>
			<?php esc_html_e( 'Add Redirect', 'ym-fast-seo' ); ?>
		</span>
	</div>
</div>

<p class="description">
	<?php
		/* translators: %s: Option name – RegEx */
		printf( esc_html__( 'Use the %s option only if you know what you are doing. It allows you to use a regular expression in the Source URL field.', 'ym-fast-seo' ),
			wp_kses_post( '<code>RegEx</code>' ),
		);
	?>
</p>