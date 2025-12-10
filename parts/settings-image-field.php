<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	$ymfseo_image_id  = YMFSEO\Settings::get_option( $args[ 'label_for' ] );
	$ymfseo_image_uri = false;

	if ( $ymfseo_image_id ) {
		$ymfseo_image_uri = wp_get_attachment_image_url( $ymfseo_image_id, 'full' );
	}
?>

<div class="ymfseo-settings-image-section site-icon-section">
	<?php printf( '<input type="hidden" name="%1$s" id="%1$s" value="%2$s">',
		esc_attr( $args[ 'label_for' ] ),
		esc_attr( $ymfseo_image_id ),
	); ?>

	<?php
		// phpcs:ignore
		printf( '<img src="%s" id="%s" alt="%s" style="%s" %s onclick="%s">',
			esc_url( $ymfseo_image_uri ?? '' ),
			esc_attr( "{$args[ 'label_for' ]}-img" ),
			esc_attr__( 'Image', 'ym-fast-seo' ),
			esc_attr( 'cursor: pointer;' ),
			esc_attr( $ymfseo_image_uri ? '' : 'hidden' ),
			esc_attr( "document.querySelector( '#{$args[ 'label_for' ]}-change-button' ).click()" ),
		);
	?>

	<div class="action-buttons site-icon-action-buttons">
		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			esc_attr( "{$args[ 'label_for' ]}-upload-button" ),
			esc_attr( 'upload-button button-hero button' ),
			esc_attr( $ymfseo_image_uri ? 'hidden' : '' ),
			esc_html__( 'Choose an Image', 'ym-fast-seo' ),
		); ?>

		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			esc_attr( "{$args[ 'label_for' ]}-change-button" ),
			esc_attr( 'button' ),
			esc_attr( $ymfseo_image_uri ? '' : 'hidden' ),
			esc_html__( 'Change Image', 'ym-fast-seo' ),
		); ?>

		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			esc_attr( "{$args[ 'label_for' ]}-remove-button" ),
			esc_attr( 'button button-secondary reset remove-site-icon' ),
			esc_attr( $ymfseo_image_uri ? '' : 'hidden' ),
			esc_html__( 'Remove Image', 'ym-fast-seo' ),
		); ?>
	</div>

	<?php if ( isset( $args[ 'description' ] ) ) : ?>
		<p class="description">
			<?php echo wp_kses_post( $args[ 'description' ] ); ?>
		</p>
	<?php endif; ?>

	<script>
		jQuery( document ).ready( function ( $ ) {
			let mediaUploader;

			const inputElement = $( '[ name=<?php echo esc_attr( $args[ 'label_for' ] ); ?> ]' );
			const imageElement = $( '#<?php echo esc_attr( $args[ 'label_for' ] ); ?>-img' );

			const uploadButton = $( '#<?php echo esc_attr( $args[ 'label_for' ] ); ?>-upload-button' );
			const changeButton = $( '#<?php echo esc_attr( $args[ 'label_for' ] ); ?>-change-button' );
			const removeButton = $( '#<?php echo esc_attr( $args[ 'label_for' ] ); ?>-remove-button' );

			// Upload.
			uploadButton.add( changeButton ).click( function ( e ) {
				e.preventDefault();

				if ( mediaUploader ) {
					mediaUploader.open();

					return;
				}

				mediaUploader = wp.media.frames.file_frame = wp.media({
					library: {
						type: [ 'image' ],
					},
				});

				mediaUploader.on( 'open', function () {
					if ( $.isNumeric( inputElement.val() ) ) {
						const selection  = mediaUploader.state().get( 'selection' );
						const attachment = wp.media.attachment( inputElement.val() );
	
						attachment.fetch();

						selection.add( attachment ? [ attachment ] : [] );
					}
				});

				mediaUploader.on( 'select', function () {
					const attachment = mediaUploader.state().get( 'selection' ).first().toJSON();

					inputElement.val( attachment.id );

					imageElement.attr( 'src', attachment.url );
					imageElement.removeAttr( 'hidden' );

					uploadButton.attr( 'hidden', '' );
					changeButton.removeAttr( 'hidden' );
					removeButton.removeAttr( 'hidden' );
				});

				mediaUploader.open();
			});

			// Remove.
			removeButton.click( function ( e ) {
				e.preventDefault();

				inputElement.val( '' );
				imageElement.attr( 'hidden', '' );
				imageElement.attr( 'src', '' );

				uploadButton.removeAttr( 'hidden' );
				changeButton.attr( 'hidden', '' );
				removeButton.attr( 'hidden', '' );
			});
		});
	</script>
</div>