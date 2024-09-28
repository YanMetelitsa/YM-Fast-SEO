<?php
	/** Exit if accessed directly */
	if ( ! defined( 'ABSPATH' ) ) exit;

	$preview_image_id = YMFSEO::get_option( 'preview_image_id' );

	if ( $preview_image_id ) {
		$preview_image_uri = wp_get_attachment_image_url( $preview_image_id, 'full' );
	}
?>

<div class="ymfseo-settings-preview-image-section site-icon-section">
	<?php printf( '<input type="hidden" name="%s" id="%s" value="%s">',
		'ymfseo_preview_image_id',
		'ymfseo-preview-image-id',
		esc_attr( $preview_image_id ),
	); ?>

	<?php printf( '<img src="%s" alt="%s" id="%s" style="%s" %s onclick="%s">',
		esc_url( $preview_image_uri ?? '' ),
		esc_html__( 'Preview Image', 'ym-fast-seo' ),
		'ymfseo-preview-image-img',
		'cursor:pointer;',
		$preview_image_uri ? '' : 'hidden',
		'document.querySelector( \'#ymfseo-preview-image-change-button\' ).click()',
	); ?>
	
	<div class="action-buttons">
		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			'ymfseo-preview-image-upload-button',
			'button-add-site-icon',
			$preview_image_uri ? 'hidden' : '',
			esc_html__( 'Choose an Image', 'ym-fast-seo' ),
		); ?>
		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			'ymfseo-preview-image-change-button',
			'button',
			$preview_image_uri ? '' : 'hidden',
			esc_html__( 'Change Image', 'ym-fast-seo' ),
		); ?>
		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			'ymfseo-preview-image-remove-button',
			'button reset',
			$preview_image_uri ? '' : 'hidden',
			esc_html__( 'Remove Image', 'ym-fast-seo' ),
		); ?>
	</div>
	
	<p class="description">
		<?php echo wp_kses_post( __( 'The image link will be added to the meta tags if no post/page thumbnail is set. The recommended size is <code>1200 × 630</code> pixels.', 'ym-fast-seo' ) ); ?>
	</p>

	<script>
		jQuery( document ).ready( function ( $ ) {
			let mediaUploader;

			const inputElement = $( '#ymfseo-preview-image-id' );
			const imageElement = $( '#ymfseo-preview-image-img' );

			const uploadButton = $( '#ymfseo-preview-image-upload-button' );
			const changeButton = $( '#ymfseo-preview-image-change-button' );
			const removeButton = $( '#ymfseo-preview-image-remove-button' );

			/** Upload */
			uploadButton.add( changeButton ).click( function ( e ) {
				e.preventDefault();

				if ( mediaUploader ) {
					mediaUploader.open();
					return;
				}

				mediaUploader = wp.media.frames.file_frame = wp.media({
					title: '<?php esc_html_e( 'Choose a Preview Image', 'ym-fast-seo' ); ?>',
					library: {
						type: [ 'image/jpeg', 'image/png', 'image/webp' ],
					},
					button: {
						text: '<?php esc_html_e( 'Set as Preview Image', 'ym-fast-seo' ); ?>',	
					},
					editing: true,
					multiple: false,
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

			/** Remove */
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