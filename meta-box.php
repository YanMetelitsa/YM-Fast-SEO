<?php
	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;

	$fields = ymfseo_get_post_meta_fields( $post->ID );

	global $current_screen;
	$is_gutenberg = $current_screen->is_block_editor();
?>

<div class="ymfseo-box <?php echo $is_gutenberg ? 'ymfseo-box_gutenberg' : ''; ?>">
	<div class="ymfseo-box__page">
		<div class="ymfseo-box__field-box">
			<label for="ymfseo-title"><?php esc_html_e( 'Title', 'ym-fast-seo' ); ?></label>
			<?php printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s">',
				'ymfseo-title',
				'components-text-control__input',
				esc_attr( $fields[ 'title' ] ),
			); ?>
		</div>

		<div class="ymfseo-box__field-box">
			<label for="ymfseo-description"><?php esc_html_e( 'Description', 'ym-fast-seo' ); ?></label>
			<?php printf( '<textarea rows="4" name="%1$s" id="%1$s" class="%2$s">%3$s</textarea>',
				'ymfseo-description',
				'components-text-control__input',
				esc_attr( $fields[ 'description' ] ),
			); ?>
		</div>

		<div class="ymfseo-box__field-box">
			<label for="ymfseo-keywords"><?php esc_html_e( 'Keywords', 'ym-fast-seo' ); ?></label>
			<?php printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s">',
				'ymfseo-keywords',
				'components-text-control__input',
				esc_attr( $fields[ 'keywords' ] ),
			); ?>
		</div>

		<div class="ymfseo-box__field-box">
			<label for="ymfseo-canonical-url"><?php esc_html_e( 'Canonical URL', 'ym-fast-seo' ); ?></label>
			<?php printf( '<input type="url" name="%1$s" id="%1$s" class="%2$s" value="%3$s">',
				'ymfseo-canonical-url',
				'components-text-control__input',
				esc_attr( $fields[ 'canonical_url' ] ),
			); ?>
		</div>
	</div>
</div>