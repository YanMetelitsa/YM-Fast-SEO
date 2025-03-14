<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	$image_id  = YMFSEO_Settings::get_option( $args[ 'label_for' ] );
	$image_uri = false;

	if ( $image_id ) {
		$image_uri = wp_get_attachment_image_url( $image_id, 'full' );
	}
?>

<div class="ymfseo-settings-image-section site-icon-section">
	<?php printf( '<input type="hidden" name="%1$s" id="%1$s" value="%2$s">',
		esc_attr( $args[ 'label_for' ] ),
		esc_attr( $image_id ),
	); ?>

	<?php
		// phpcs:ignore
		printf( '<img src="%s" id="%s" alt="%s" style="%s" %s onclick="%s">',
			esc_url( $image_uri ?? '' ),
			esc_attr( "{$args[ 'label_for' ]}-img" ),
			esc_attr__( 'Image', 'ym-fast-seo' ),
			'cursor:pointer;',
			esc_attr( $image_uri ? '' : 'hidden' ),
			"document.querySelector( '#" . esc_attr( $args[ 'label_for' ] ) . "-change-button' ).click()",
		);
	?>

	<div class="action-buttons site-icon-action-buttons">
		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			esc_attr( "{$args[ 'label_for' ]}-upload-button" ),
			'button-add-site-icon',
			esc_attr( $image_uri ? 'hidden' : '' ),
			esc_html__( 'Choose an Image', 'ym-fast-seo' ),
		); ?>

		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			esc_attr( "{$args[ 'label_for' ]}-change-button" ),
			'button',
			esc_attr( $image_uri ? '' : 'hidden' ),
			esc_html__( 'Change Image', 'ym-fast-seo' ),
		); ?>

		<?php printf( '<button type="button" id="%s" class="%s" %s>%s</button>',
			esc_attr( "{$args[ 'label_for' ]}-remove-button" ),
			'button button-secondary reset remove-site-icon',
			esc_attr( $image_uri ? '' : 'hidden' ),
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

			// Uploads.
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

			// Removes.
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