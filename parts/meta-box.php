<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	global $current_screen;

	$meta_fields = new YMFSEO_Meta_Fields( get_post( $post->ID ), false );
?>

<div class="ymfseo-box <?php echo $current_screen->is_block_editor() ? 'ymfseo-box_gutenberg' : ''; ?>">
	<div class="ymfseo-box__container">
		<!-- Title -->
		<div class="ymfseo-box__field-box">
			<label for="ymfseo-title"><?php esc_html_e( 'Title', 'ym-fast-seo' ); ?></label>

			<?php printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s" data-min="%4$s" data-rec="%5$s" data-max="%6$s">',
				'ymfseo-title',
				'components-text-control__input',
				esc_attr( $meta_fields->title ),
				esc_attr( YMFSEO_Checker::$meta_lengths[ 'title' ][ 'min' ] ),
				esc_attr( implode( '-', YMFSEO_Checker::$meta_lengths[ 'title' ][ 'rec' ] ) ),
				esc_attr( YMFSEO_Checker::$meta_lengths[ 'title' ][ 'max' ] ),
			); ?>

			<div class="ymfseo-length-checker" data-for="ymfseo-title"></div>
		</div>

		<!-- Description -->
		<div class="ymfseo-box__field-box">
			<label for="ymfseo-description"><?php esc_html_e( 'Description', 'ym-fast-seo' ); ?></label>
			
			<?php printf( '<textarea rows="4" name="%1$s" id="%1$s" class="%2$s" data-min="%3$s" data-rec="%4$s" data-max="%5$s">%6$s</textarea>',
				'ymfseo-description',
				'components-text-control__input',
				esc_attr( YMFSEO_Checker::$meta_lengths[ 'description' ][ 'min' ] ),
				esc_attr( implode( '-', YMFSEO_Checker::$meta_lengths[ 'description' ][ 'rec' ] ) ),
				esc_attr( YMFSEO_Checker::$meta_lengths[ 'description' ][ 'max' ] ),
				esc_attr( $meta_fields->description ),
			); ?>

			<div class="ymfseo-length-checker" data-for="ymfseo-description"></div>
		</div>

		<!-- Page Type -->
		<div class="ymfseo-box__field-box">
			<label for="ymfseo-page-type"><?php esc_html_e( 'Page Type', 'ym-fast-seo' ); ?></label>

			<select name="ymfseo-page-type" id="ymfseo-page-type">
				<?php
					$default_page_type       = YMFSEO_Settings::get_option( "post_type_page_type_$post->post_type" );
					$default_page_type_label = __( YMFSEO::$page_types[ $default_page_type ], 'ym-fast-seo' );

					printf( '<option value="default">%s (%s)</option>',
						esc_html__( 'Default', 'ym-fast-seo' ),
						esc_html( $default_page_type_label ),
					);
					
					foreach ( YMFSEO::$page_types as $value => $label ) {
						printf( '<option value="%s"%s>%s</option>',
							esc_attr( $value ),
							selected( $meta_fields->page_type, $value, false ),
							esc_html( $label ),
						);
					}
				?>
			</select>
		</div>

		<!-- Noindex -->
		<div class="ymfseo-box__field-box">
			<label for="ymfseo-noindex"><?php esc_html_e( 'Indexing', 'ym-fast-seo' ); ?></label>

			<div class="ymfseo-box__checkbox">
				<span class="components-form-toggle <?php echo $meta_fields->noindex ? 'is-checked' : ''; ?>">
					<?php printf( '<input type="checkbox" name="%1$s" id="%1$s" class="%2$s" value="1"%3$s>',
						'ymfseo-noindex',
						'components-form-toggle__input',
						checked( $meta_fields->noindex, true, false ),
					); ?>
					<span class="components-form-toggle__track"></span>
					<span class="components-form-toggle__thumb"></span>
				</span>
				
				<label for="ymfseo-noindex"><?php esc_html_e( 'Disallow indexing', 'ym-fast-seo' ); ?></label>
			</div>
		</div>

		<!-- Tags -->
		<details>
			<summary><?php esc_html_e( 'Tags', 'ym-fast-seo' ); ?></summary>

			<p><?php esc_html_e( 'Use these tags to insert dynamic data.', 'ym-fast-seo' ); ?></p>

			<div class="ymfseo-box__tags-table">
				<?php foreach ( YMFSEO_Meta_Fields::$replace_tags as $tag => $value ) : ?>
					<div><span><?php echo esc_html( $tag ); ?></span></div>
					<div><?php echo esc_html( $value ); ?></div>
				<?php endforeach; ?>
			</div>
		</details>
	</div>
</div>