<?php
	namespace YMFSEO;

	// Exits if accessed directly.
	if ( ! \defined( 'ABSPATH' ) ) exit;
?>

<fieldset class="inline-edit-col-right">
	<legend class="inline-edit-legend">
		<?php esc_html_e( 'SEO', 'ym-fast-seo' ); ?>
	</legend>

	<div class="inline-edit-col">
		<!-- Title -->
		<label>
			<span class="title">
				<?php esc_html_e( 'Title', 'ym-fast-seo' ); ?>
			</span>
			
			<span class="input-text-wrap" style="display: flex; flex-direction: column;">
				<?php \printf( '<input type="text" name="ymfseo-title" data-min="%1$s" data-rec="%2$s" data-max="%3$s" placeholder="%4$s" style="%5$s">', 
					esc_attr( Checker::$meta_lengths[ 'title' ][ 'min' ] ),
					esc_attr( implode( '-', Checker::$meta_lengths[ 'title' ][ 'rec' ] ) ),
					esc_attr( Checker::$meta_lengths[ 'title' ][ 'max' ] ),
					esc_attr( Settings::get_option( "post_type_title_{$post_type}", '' ) ),
					esc_attr( 'margin: 0;' ),
				); ?>
				
				<div class="ymfseo-length-indicator ymfseo-length-indicator_quick-edit" data-for="ymfseo-title"></div>
			</span>
		</label>

		<!-- Description -->
		<label>
			<span class="title">
				<?php esc_html_e( 'Description', 'ym-fast-seo' ); ?>
			</span>

			<?php \printf( '<textarea cols="22" rows="1" name="ymfseo-description" data-min="%1$s" data-rec="%2$s" data-max="%3$s"></textarea>',
				esc_attr( Checker::$meta_lengths[ 'description' ][ 'min' ] ),
				esc_attr( implode( '-', Checker::$meta_lengths[ 'description' ][ 'rec' ] ) ),
				esc_attr( Checker::$meta_lengths[ 'description' ][ 'max' ] ),
			); ?>

			<div class="ymfseo-length-indicator ymfseo-length-indicator_quick-edit" data-for="ymfseo-description"></div>
		</label>

		<!-- Page Type -->
		<label>
			<span class="title">
				<?php esc_html_e( 'Page Type', 'ym-fast-seo' ); ?>
			</span>

			<select name="ymfseo-page-type">
				<?php
					$ymfseo_default_page_type       = Settings::get_option( "post_type_page_type_{$post_type}" );
					$ymfseo_default_page_type_label = __( Core::$page_types[ $ymfseo_default_page_type ], 'ym-fast-seo' ); // phpcs:ignore

					\printf( '<option value="default">%s (%s)</option>',
						esc_html__( 'Default', 'ym-fast-seo' ),
						esc_html( $ymfseo_default_page_type_label ),
					);
					
					foreach ( Core::$page_types as $ymfseo_value => $ymfseo_label ) {
						\printf( '<option value="%s">%s</option>',
							esc_attr( $ymfseo_value ),
							esc_html( $ymfseo_label ),
						);
					}
				?>
			</select>
		</label>

		<!-- Noindex -->
		<label class="alignleft">
			<?php \printf( '<input type="checkbox" name="%1$s" value="1">',
				esc_attr( 'ymfseo-noindex' ),
			); ?>

			<span class="checkbox-title">
				<?php esc_html_e( 'Disallow indexing', 'ym-fast-seo' ); ?>
			</span>
		</label>
	</div>
</fieldset>