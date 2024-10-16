<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	$meta_fields = new YMFSEO_Meta_Fields( $term, false );
?>

<table class="form-table">
	<h2><?php esc_html_e( 'SEO', 'ym-fast-seo' ); ?></h2>
	
	<tbody>
		<!-- Title -->
		<tr class="form-field">
			<th scope="row">
				<label for="ymfseo-title"><?php esc_html_e( 'Title', 'ym-fast-seo' ); ?></label>
			</th>
			<td>
				<?php printf( '<input type="text" name="%1$s" id="%1$s" value="%2$s" data-min="%3$s" data-rec="%4$s" data-max="%5$s">',
					'ymfseo-title',
					esc_attr( $meta_fields->title ),
					esc_attr( YMFSEO::$check_length_values[ 'title' ][ 'min' ] ),
					esc_attr( implode( '-', YMFSEO::$check_length_values[ 'title' ][ 'rec' ] ) ),
					esc_attr( YMFSEO::$check_length_values[ 'title' ][ 'max' ] ),
				); ?>
				<div class="ymfseo-length-checker ymfseo-length-checker_top-margin" data-for="ymfseo-title"></div>
			</td>
		</tr>

		<!-- Description -->
		<tr class="form-field">
			<th scope="row">
				<label for="ymfseo-description"><?php esc_html_e( 'Description', 'ym-fast-seo' ); ?></label>
			</th>
			<td>
				<?php printf( '<textarea name="%1$s" id="%1$s" rows="5" cols="50" class="large-text" style="%2$s" data-min="%3$s" data-rec="%4$s" data-max="%5$s">%6$s</textarea>',
					'ymfseo-description',
					esc_attr( 'vertical-align:middle' ),
					esc_attr( YMFSEO::$check_length_values[ 'description' ][ 'min' ] ),
					esc_attr( implode( '-', YMFSEO::$check_length_values[ 'description' ][ 'rec' ] ) ),
					esc_attr( YMFSEO::$check_length_values[ 'description' ][ 'max' ] ),
					esc_attr( $meta_fields->description ),
				); ?>
				<div class="ymfseo-length-checker ymfseo-length-checker_top-margin" data-for="ymfseo-description"></div>
			</td>
		</tr>
	</tbody>
</table>