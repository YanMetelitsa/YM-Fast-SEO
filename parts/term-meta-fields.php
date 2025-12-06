<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	$ymfseo_meta_fields = new YMFSEO\MetaFields( $term, false );
?>

<table class="form-table">
	<h2><?php esc_html_e( 'SEO', 'ym-fast-seo' ); ?></h2>
	
	<tbody>
		<!-- Title -->
		<tr class="form-field">
			<th scope="row">
				<label for="ymfseo-title">
					<?php esc_html_e( 'Title', 'ym-fast-seo' ); ?>
				</label>
			</th>

			<td>
				<?php printf( '<input type="text" name="%1$s" id="%1$s" value="%2$s" data-min="%3$s" data-rec="%4$s" data-max="%5$s" placeholder="%6$s">',
					esc_attr( 'ymfseo-title' ),
					esc_attr( $ymfseo_meta_fields->title ),
					esc_attr( YMFSEO\Checker::$meta_lengths[ 'title' ][ 'min' ] ),
					esc_attr( implode( '-', YMFSEO\Checker::$meta_lengths[ 'title' ][ 'rec' ] ) ),
					esc_attr( YMFSEO\Checker::$meta_lengths[ 'title' ][ 'max' ] ),
					esc_attr( YMFSEO\Settings::get_option( "taxonomy_title_{$taxonomy}" ) ),
				); ?>

				<div class="ymfseo-length-indicator ymfseo-length-indicator_term" data-for="ymfseo-title"></div>
			</td>
		</tr>

		<!-- Description -->
		<tr class="form-field">
			<th scope="row">
				<label for="ymfseo-description">
					<?php esc_html_e( 'Description', 'ym-fast-seo' ); ?>
				</label>
			</th>

			<td>
				<?php printf( '<textarea name="%1$s" id="%1$s" rows="5" cols="50" class="large-text" style="%2$s" data-min="%3$s" data-rec="%4$s" data-max="%5$s" placeholder="%6$s">%7$s</textarea>',
					esc_attr( 'ymfseo-description' ),
					esc_attr( 'vertical-align: middle;' ),
					esc_attr( YMFSEO\Checker::$meta_lengths[ 'description' ][ 'min' ] ),
					esc_attr( implode( '-', YMFSEO\Checker::$meta_lengths[ 'description' ][ 'rec' ] ) ),
					esc_attr( YMFSEO\Checker::$meta_lengths[ 'description' ][ 'max' ] ),
					esc_attr( YMFSEO\Settings::get_option( "taxonomy_description_{$taxonomy}" ) ),
					esc_attr( $ymfseo_meta_fields->description ),
				); ?>
				
				<div class="ymfseo-length-indicator ymfseo-length-indicator_term" data-for="ymfseo-description"></div>
			</td>
		</tr>
	</tbody>
</table>