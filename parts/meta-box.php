<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	global $current_screen;

	$meta_fields = new YMFSEO_Meta_Fields( get_post( $post->ID ), true );
?>

<div class="ymfseo-box <?php echo $current_screen->is_block_editor() ? 'ymfseo-box_gutenberg' : ''; ?>">
	<div class="ymfseo-box__page">
		<!-- Title -->
		<div class="ymfseo-box__field-box" data-min="30" data-rec="40-60" data-max="70">
			<label for="ymfseo-title"><?php esc_html_e( 'Title', 'ym-fast-seo' ); ?></label>

			<?php printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s" placeholder="%4$s">',
				'ymfseo-title',
				'components-text-control__input',
				esc_attr( $meta_fields->title ),
				esc_attr__( 'Title', 'ym-fast-seo' ),
			); ?>

			<div class="ymfseo-box__field-box-range"></div>
		</div>

		<!-- Description -->
		<div class="ymfseo-box__field-box" data-min="50" data-rec="140-160" data-max="170">
			<label for="ymfseo-description"><?php esc_html_e( 'Description', 'ym-fast-seo' ); ?></label>
			
			<?php printf( '<textarea rows="4" name="%1$s" id="%1$s" class="%2$s" placeholder="%4$s">%3$s</textarea>',
				'ymfseo-description',
				'components-text-control__input',
				esc_attr( $meta_fields->description ),
				esc_attr__( 'Description', 'ym-fast-seo' ),
			); ?>

			<div class="ymfseo-box__field-box-range"></div>
		</div>

		<!-- Page Type -->
		<div class="ymfseo-box__field-box">
			<?php $page_type_options = [
				'WebPage'           => __( 'Common Page', 'ym-fast-seo' ),
				'CollectionPage'    => __( 'Collection Page', 'ym-fast-seo' ),
				'ItemPage'          => __( 'Item Page', 'ym-fast-seo' ),
				'AboutPage'         => __( 'About Page', 'ym-fast-seo' ),
				'FAQPage'           => __( 'FAQ Page', 'ym-fast-seo' ),
				'ContactPage'       => __( 'Contact Page', 'ym-fast-seo' ),
				'CheckoutPage'      => __( 'Checkout Page', 'ym-fast-seo' ),
				'SearchResultsPage' => __( 'Search results Page', 'ym-fast-seo' ),
			]; ?>

			<label for="ymfseo-page-type"><?php esc_html_e( 'Page Type', 'ym-fast-seo' ); ?></label>

			<select name="ymfseo-page-type" id="ymfseo-page-type">
				<?php foreach ( $page_type_options as $value => $label ) {
					printf( '<option value="%s"%s>%s</option>',
						esc_attr( $value ),
						selected( $meta_fields->page_type, $value, false ),
						esc_html( $label ),
					);
				} ?>
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

			<p><?php esc_html_e( 'Use these tags in the meta fields to insert dynamic data.', 'ym-fast-seo' ); ?></p>

			<div class="ymfseo-box__tags-table">
				<?php foreach ( YMFSEO_Meta_Fields::$replace_tags as $tag => $value ) : ?>
					<div><span><?php echo esc_html( $tag ); ?></span></div>
					<div><?php echo esc_html( $value ); ?></div>
				<?php endforeach; ?>
			</div>
		</details>
	</div>
</div>