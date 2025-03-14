<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	$option_name  = $args[ 'label_for' ];
	$option_value = YMFSEO_Settings::get_option( $option_name, [] );
	
	if ( empty( $option_value ) ) {
		$option_value[] = [
			'type'     => '',
			'from'     => '',
			'is_regex' => false,
			'to'       => '',
		];
	}

	$redirect_types = [
		/* translators: Redirect type */
		'301' => '301 – ' . __( 'Permanent', 'ym-fast-seo' ),
		/* translators: Redirect type */
		'302' => '302 – ' . __( 'Temporary', 'ym-fast-seo' ),
	];
?>

<div class="ymfseo-settings-redirects-section">
	<div class="ymfseo-settings-redirects-section__grid">
		<!-- Items -->
		<?php foreach ( $option_value as $i => $redirect ) : ?>		
			<div class="ymfseo-settings-redirects-section__item" data-counter="<?php echo esc_attr( $i ); ?>">
				<?php printf( '<select name="%s" id="%s">',
					esc_attr( "{$option_name}[{$i}][type]" ),
					esc_attr( "{$option_name}_{$i}_type" ),
				); ?>
					<?php foreach ( $redirect_types as $value => $label ) {
						printf( '<option value="%s"%s>%s</option>',
							esc_attr( $value ),
							selected( $redirect[ 'type' ], $value, false ),
							esc_attr( $label ),
						);
					} ?>
				</select>
	
				<?php printf( '<input type="text" name="%s" class="code" id="%s" value="%s" spellcheck="false" placeholder="%s">',
					esc_attr( "{$option_name}[{$i}][from]" ),
					esc_attr( "{$option_name}_{$i}_from" ),
					esc_attr( $redirect[ 'from' ] ),
					esc_attr__( 'Source URL', 'ym-fast-seo' ),
				); ?>

				<div class="regex">
					<?php printf( '<input type="checkbox" name="%s" id="%s" value="1"%s>',
						esc_attr( "{$option_name}[{$i}][is_regex]" ),
						esc_attr( "{$option_name}_{$i}_is_regex" ),
						checked( $redirect[ 'is_regex' ], true, false ),
					); ?>

					<label for="<?php echo esc_attr( "{$option_name}_{$i}_is_regex" ) ?>">RegEx</label>
				</div>
				
				<?php printf( '<input type="text" name="%s" class="code" id="%s" value="%s" spellcheck="false" placeholder="%s">',
					esc_attr( "{$option_name}[{$i}][to]" ),
					esc_attr( "{$option_name}_{$i}_to" ),
					esc_attr( $redirect[ 'to' ] ),
					esc_attr__( 'Target URL', 'ym-fast-seo' ),
				); ?>
	
				<span class="dashicons dashicons-trash" onclick="YMFSEO_Settings.removeRedirectRow( this )"></span>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Add Button -->
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