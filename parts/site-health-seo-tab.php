<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="health-check-body ymfseo-seo-health hide-if-no-js">
	<h2><?php esc_html_e( 'Site SEO Health', 'ym-fast-seo' ); ?></h2>

	<p><?php esc_html_e( 'This page shows the current state of your site\'s SEO health, allowing you to take prompt action when errors occur.', 'ym-fast-seo' ); ?></p>

	<div class="health-check-accordion">
		<?php foreach ( YMFSEO\SiteHealth::$tests as $ymfseo_test_id => $ymfseo_check_function ) : ?>
			<?php
				$ymfseo_test_result = $ymfseo_check_function();

				$ymfseo_test_icon = match ( $ymfseo_test_result->is_passed ) {
					'yes'     => 'dashicons-yes-alt',
					'no'      => 'dashicons-dismiss',
					'warning' => 'dashicons-warning',
					default   => false,
				};

				$ymfseo_test_table = $ymfseo_test_result->content[ 'table' ] ?? null;
				$ymfseo_test_links = $ymfseo_test_result->content[ 'links' ] ?? null;
			?>

			<h4 class="health-check-accordion-heading">
				<button class="health-check-accordion-trigger" aria-controls="<?php echo esc_html( $ymfseo_test_id ); ?>" type="button">
					<?php if ( $ymfseo_test_icon ) : ?>
						<span class="dashicons <?php echo esc_html( $ymfseo_test_icon ); ?>"></span>
					<?php endif; ?>

					<span class="title">
						<?php echo esc_html( $ymfseo_test_result->title ); ?>
					</span>
	
					<span class="icon"></span>
				</button>
			</h4>

			<div id="<?php echo esc_html( $ymfseo_test_id ); ?>" class="health-check-accordion-panel" hidden="hidden">
				<?php foreach ( $ymfseo_test_result->description as $ymfseo_index => $ymfseo_paragraph ) : ?>
					<p style="<?php echo esc_attr( 0 == $ymfseo_index ? 'margin-top: 0;' : '' ); ?>">
						<?php echo wp_kses_post( $ymfseo_paragraph ); ?>
					</p>
				<?php endforeach; ?>
			
				<?php if ( $ymfseo_test_links ) : ?>
					<div class="actions">
						<?php foreach ( $ymfseo_test_links as $ymfseo_caption => $ymfseo_url ) : ?>
							<p>
								<a href="<?php echo esc_url( $ymfseo_url ); ?>">
									<?php echo esc_html( $ymfseo_caption ); ?>
								</a>
							</p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $ymfseo_test_table ) : ?>
					<table class="striped widefat">
						<?php if ( isset( $ymfseo_test_table[ 'head' ] ) ) : ?>
							<thead>
								<tr>
									<?php foreach ( $ymfseo_test_table[ 'head' ] as $ymfseo_th ) : ?>
										<th><?php echo esc_html( $ymfseo_th ); ?></th>
									<?php endforeach; ?>
								</tr>
							</thead>
						<?php endif; ?>

						<tbody>
							<?php if ( ! empty ( $ymfseo_test_table[ 'body' ] ) ) : ?>
								<?php foreach ( $ymfseo_test_table[ 'body' ] as $ymfseo_tr ) : ?>
									<tr>
										<?php foreach ( $ymfseo_tr as $ymfseo_td ) : ?>
											<td><?php echo esc_html( $ymfseo_td ); ?></td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
								<?php if ( isset( $ymfseo_test_table[ 'head' ] ) ) : ?>
									<tr>
										<td colspan="<?php echo esc_attr( count( $ymfseo_test_table[ 'head' ] ) ); ?>">
											<?php
												/* translators: Empty table message */
												esc_html_e( 'No data.', 'ym-fast-seo' );
											?>
										</td>
									</tr>
								<?php endif; ?>
							<?php endif; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
