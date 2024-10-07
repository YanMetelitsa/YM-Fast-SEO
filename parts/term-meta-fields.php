<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	$meta_fields = new YMFSEO_Meta_Fields( $term, false );
?>

<table class="form-table">
	<h2><?php _e( 'SEO', 'ym-fast-seo' ); ?></h2>
	
	<tbody>
		<!-- Title -->
		<tr class="form-field">
			<th scope="row">
				<label for="ymfseo-title"><?php _e( 'Title', 'ym-fast-seo' ); ?></label>
			</th>
			<td>
				<?php printf( '<input type="text" name="%1$s" id="%1$s" value="%2$s">',
					'ymfseo-title',
					esc_attr( $meta_fields->title ),
				); ?>
			</td>
		</tr>

		<!-- Description -->
		<tr class="form-field">
			<th scope="row">
				<label for="ymfseo-description"><?php _e( 'Description', 'ym-fast-seo' ); ?></label>
			</th>
			<td>
				<?php printf( '<textarea name="%1$s" id="%1$s" rows="5" cols="50" class="large-text">%2$s</textarea>',
					'ymfseo-description',
					esc_attr( $meta_fields->description ),
				); ?>
			</td>
		</tr>
	</tbody>
</table>