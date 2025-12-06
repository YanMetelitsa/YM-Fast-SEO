<?php
	namespace YMFSEO;

	// Exits if accessed directly.
	if ( ! \defined( 'ABSPATH' ) ) exit;
	
	global $current_screen;

	$ymfseo_meta_fields = new MetaFields( get_post( $post->ID ), false );
?>

<div class="ymfseo-post-meta-box <?php echo esc_attr( $current_screen->is_block_editor() ? 'ymfseo-post-meta-box_gutenberg' : '' ); ?>">
	<div class="ymfseo-post-meta-box__container">
		<!-- Title -->
		<div class="ymfseo-post-meta-box__field">
			<label for="ymfseo-title">
				<?php esc_html_e( 'Title', 'ym-fast-seo' ); ?>
			</label>

			<?php \printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s" data-min="%4$s" data-rec="%5$s" data-max="%6$s" data-post-id="%7$s" placeholder="%8$s">',
				esc_attr( 'ymfseo-title' ),
				esc_attr( 'components-text-control__input' ),
				esc_attr( $ymfseo_meta_fields->title ),
				esc_attr( Checker::$meta_lengths[ 'title' ][ 'min' ] ),
				esc_attr( implode( '-', Checker::$meta_lengths[ 'title' ][ 'rec' ] ) ),
				esc_attr( Checker::$meta_lengths[ 'title' ][ 'max' ] ),
				esc_attr( $post->ID ),
				esc_attr( Settings::get_option( "post_type_title_{$post->post_type}", '' ) ),
			); ?>

			<div class="ymfseo-length-indicator" data-for="ymfseo-title"></div>
		</div>

		<!-- Description -->
		<div class="ymfseo-post-meta-box__field">
			<label for="ymfseo-description">
				<?php esc_html_e( 'Description', 'ym-fast-seo' ); ?>
			</label>
			
			<?php \printf( '<textarea rows="4" name="%1$s" id="%1$s" class="%2$s" data-min="%3$s" data-rec="%4$s" data-max="%5$s">%6$s</textarea>',
				esc_attr( 'ymfseo-description' ),
				esc_attr( 'components-text-control__input' ),
				esc_attr( Checker::$meta_lengths[ 'description' ][ 'min' ] ),
				esc_attr( implode( '-', Checker::$meta_lengths[ 'description' ][ 'rec' ] ) ),
				esc_attr( Checker::$meta_lengths[ 'description' ][ 'max' ] ),
				esc_attr( $ymfseo_meta_fields->description ),
			); ?>

			<div class="ymfseo-length-indicator" data-for="ymfseo-description"></div>
		</div>

		<!-- Page Type -->
		<div class="ymfseo-post-meta-box__field">
			<label for="ymfseo-page-type">
				<?php esc_html_e( 'Page Type', 'ym-fast-seo' ); ?>
			</label>

			<select name="ymfseo-page-type" id="ymfseo-page-type">
				<?php
					$ymfseo_default_page_type       = Settings::get_option( "post_type_page_type_{$post->post_type}" );
					$ymfseo_default_page_type_label = __( Core::$page_types[ $ymfseo_default_page_type ], 'ym-fast-seo' ); // phpcs:ignore

					\printf( '<option value="default">%s (%s)</option>',
						esc_html__( 'Default', 'ym-fast-seo' ),
						esc_html( $ymfseo_default_page_type_label ),
					);
					
					foreach ( Core::$page_types as $ymfseo_value => $ymfseo_label ) {
						\printf( '<option value="%s"%s>%s</option>',
							esc_attr( $ymfseo_value ),
							selected( $ymfseo_meta_fields->page_type, $ymfseo_value, false ),
							esc_html( $ymfseo_label ),
						);
					}
				?>
			</select>
		</div>

		<!-- Noindex -->
		<div class="ymfseo-post-meta-box__field">
			<label for="ymfseo-noindex">
				<?php esc_html_e( 'Indexing', 'ym-fast-seo' ); ?>
			</label>

			<div class="ymfseo-post-meta-box__checkbox">
				<span class="components-form-toggle <?php echo $ymfseo_meta_fields->noindex ? 'is-checked' : ''; ?>">
					<?php \printf( '<input type="checkbox" name="%1$s" id="%1$s" class="%2$s" value="1"%3$s>',
						esc_attr( 'ymfseo-noindex' ),
						esc_attr( 'components-form-toggle__input' ),
						checked( $ymfseo_meta_fields->noindex, true, false ),
					); ?>
					<span class="components-form-toggle__track"></span>
					<span class="components-form-toggle__thumb"></span>
				</span>
				
				<label for="ymfseo-noindex">
					<?php esc_html_e( 'Disallow indexing', 'ym-fast-seo' ); ?>
				</label>
			</div>
		</div>

		<!-- Tags -->
		<details>
			<summary>
				<?php esc_html_e( 'Tags', 'ym-fast-seo' ); ?>
			</summary>

			<p>
				<?php esc_html_e( 'Use these tags to insert dynamic data.', 'ym-fast-seo' ); ?>
			</p>

			<div class="ymfseo-post-meta-box__tags">
				<?php foreach ( MetaFields::$replace_tags as $tag => $ymfseo_value ) : ?>
					<div>
						<span><?php echo esc_html( $tag ); ?></span>
					</div>
					
					<div>
						<?php echo esc_html( $ymfseo_value ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</details>
	</div>
</div>