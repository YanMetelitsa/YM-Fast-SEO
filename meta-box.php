<?php
	/** Exit if accessed directly */
	if ( !defined( 'ABSPATH' ) ) exit;

	$fields = YMFSEO::get_post_meta_fields( $post->ID );

	global $current_screen;
	$is_gutenberg = $current_screen->is_block_editor();
?>

<div class="ymfseo-box <?php echo $is_gutenberg ? 'ymfseo-box_gutenberg' : ''; ?>">
	<div class="ymfseo-box__page">
		<!-- Title -->
		<div class="ymfseo-box__field-box">
			<label for="ymfseo-title"><?php esc_html_e( 'Title', 'ym-fast-seo' ); ?></label>
			<?php printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s" placeholder="%4$s">',
				'ymfseo-title',
				'components-text-control__input',
				esc_attr( $fields[ 'title' ] ),
				esc_attr__( 'Title', 'ym-fast-seo' ),
			); ?>
		</div>
		<div class="ymfseo-box__check-box" style="margin-top:-6px">
			<span class="components-form-toggle <?php echo YMFSEO::parse_checkbox_value( $fields[ 'use_in_title_tag' ] ) ? 'is-checked' : ''; ?>">
				<?php printf( '<input name="%1$s" id="%1$s" class="%2$s" type="checkbox" %3$s>',
					'ymfseo-use-in-title-tag',
					'components-form-toggle__input',
					checked( YMFSEO::parse_checkbox_value( $fields[ 'use_in_title_tag' ] ), true, false ),
				); ?>

				<span class="components-form-toggle__track"></span>
				<span class="components-form-toggle__thumb"></span>
			</span>

			<label for="ymfseo-use-in-title-tag"><?php _e( 'Use in title tag', 'ym-fast-seo' ); ?></label>
		</div>
		<div class="ymfseo-box__check-box" style="margin-top:-6px">
			<span class="components-form-toggle <?php echo YMFSEO::parse_checkbox_value( $fields[ 'remove_sitename' ] ) ? 'is-checked' : ''; ?>">
				<?php printf( '<input name="%1$s" id="%1$s" class="%2$s" type="checkbox" %3$s>',
					'ymfseo-remove-sitename',
					'components-form-toggle__input',
					checked( YMFSEO::parse_checkbox_value( $fields[ 'remove_sitename' ] ), true, false ),
				); ?>

				<span class="components-form-toggle__track"></span>
				<span class="components-form-toggle__thumb"></span>
			</span>

			<label for="ymfseo-remove-sitename"><?php _e( 'Remove site name', 'ym-fast-seo' ); ?></label>
		</div>

		<!-- Description -->
		<div class="ymfseo-box__field-box">
			<label for="ymfseo-description"><?php esc_html_e( 'Description', 'ym-fast-seo' ); ?></label>
			<?php printf( '<textarea rows="4" name="%1$s" id="%1$s" class="%2$s" placeholder="%4$s">%3$s</textarea>',
				'ymfseo-description',
				'components-text-control__input',
				esc_attr( $fields[ 'description' ] ),
				esc_textarea( __( 'Description', 'ym-fast-seo' ) ),
			); ?>
		</div>

		<!-- Additional -->
		<details>
			<summary><?php esc_html_e( 'Additional', 'ym-fast-seo' ); ?></summary>

			<!-- Canonical -->
			<div class="ymfseo-box__field-box">
				<label for="ymfseo-canonical-url"><?php esc_html_e( 'Canonical URL', 'ym-fast-seo' ); ?></label>
				<?php printf( '<input type="url" name="%1$s" id="%1$s" class="%2$s" value="%3$s" placeholder="%4$s">',
					'ymfseo-canonical-url',
					'components-text-control__input',
					esc_attr( $fields[ 'canonical_url' ] ),
					'https://',
				); ?>
			</div>
		</details>

		<!-- Deprecated -->
		<details>
			<summary><?php esc_html_e( 'Deprecated', 'ym-fast-seo' ); ?></summary>

			<!-- Keywords -->
			<div class="ymfseo-box__field-box">
				<label for="ymfseo-keywords"><?php esc_html_e( 'Keywords', 'ym-fast-seo' ); ?></label>
				<?php printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s" placeholder="%4$s">',
					'ymfseo-keywords',
					'components-text-control__input',
					esc_attr( $fields[ 'keywords' ] ),
					'key, key',
				); ?>
			</div>
		</details>

		<!-- Tags -->
		<details>
			<summary><?php esc_html_e( 'Tags', 'ym-fast-seo' ); ?></summary>

			<p><?php _e( 'Use these tags in meta fields to insert dynamic data.', 'ym-fast-seo' ); ?></p>

			<table class="ymfseo-tags-table">
				<tbody>
					<?php foreach ( YMFSEO::$replace_tags as $tag => $value ) : ?>
						<tr>
							<td><span><?php echo $tag; ?></span></td>
							<td><?php echo $value; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</details>
	</div>
</div>