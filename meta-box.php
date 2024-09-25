<?php
	/** Exit if accessed directly */
	if ( ! defined( 'ABSPATH' ) ) exit;

	$fields = YMFSEO::get_post_meta_fields( $post->ID, true );

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

		<!-- Tags -->
		<details>
			<summary><?php esc_html_e( 'Tags', 'ym-fast-seo' ); ?></summary>

			<p><?php esc_html_e( 'Use these tags in the meta fields to insert dynamic data.', 'ym-fast-seo' ); ?></p>

			<table class="ymfseo-tags-table">
				<tbody>
					<?php foreach ( YMFSEO::$replace_tags as $tag => $value ) : ?>
						<tr>
							<td><span><?php echo esc_html( $tag ); ?></span></td>
							<td><?php echo esc_html( $value ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</details>
	</div>
</div>