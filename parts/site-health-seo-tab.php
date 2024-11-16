<?php
	// Exits if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="health-check-body ymfseo-site-health hide-if-no-js">
	<h2><?php esc_html_e( 'Site SEO Health', 'ym-fast-seo' ); ?></h2>

	<p><?php esc_html_e( 'This page shows the current state of your site\'s SEO health, allowing you to take prompt action when errors occur.', 'ym-fast-seo' ); ?></p>

	<div class="health-check-accordion">
		<?php foreach ( YMFSEO_Site_Health::$tests as $test_id => $check_function ) : ?>
			<?php
				$test_result = $check_function();

				$test_icon = match ( $test_result->is_passed ) {
					'yes'     => 'dashicons-yes-alt',
					'no'      => 'dashicons-dismiss',
					'warning' => 'dashicons-warning',
					default   => false,
				 };

				$test_table = $test_result->content[ 'table' ] ?? null;
				$test_links = $test_result->content[ 'links' ] ?? null;
			?>

			<h4 class="health-check-accordion-heading">
				<button class="health-check-accordion-trigger" aria-controls="<?php echo esc_html( $test_id ); ?>" type="button">
					<?php if ( $test_icon ) : ?>
						<span class="dashicons <?php echo esc_html( $test_icon ); ?>"></span>
					<?php endif; ?>

					<span class="title"><?php echo esc_html( $test_result->title ); ?></span>
	
					<span class="icon"></span>
				</button>
			</h4>

			<div id="<?php echo esc_html( $test_id ); ?>" class="health-check-accordion-panel" hidden="hidden">
				<?php foreach ( $test_result->description as $p ) : ?>
					<p><?php echo wp_kses_post( $p ); ?></p>
				<?php endforeach; ?>
			
				<?php if ( $test_links ) : ?>
					<div class="actions">
						<?php foreach ( $test_links as $caption => $url ) : ?>
							<p>
								<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $caption ); ?></a>
							</p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $test_table ) : ?>
					<table class="striped widefat">
						<?php if ( isset( $test_table[ 'head' ] ) ) : ?>
							<thead>
								<tr>
									<?php foreach ( $test_table[ 'head' ] as $th ) : ?>
										<th><?php echo esc_html( $th ); ?></th>
									<?php endforeach; ?>
								</tr>
							</thead>
						<?php endif; ?>

						<tbody>
							<?php if ( ! empty ( $test_table[ 'body' ] ) ) : ?>
								<?php foreach ( $test_table[ 'body' ] as $tr ) : ?>
									<tr>
										<?php foreach ( $tr as $td ) : ?>
											<td><?php echo esc_html( $td ); ?></td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
								<?php if ( isset( $test_table[ 'head' ] ) ) : ?>
									<tr>
										<td colspan="<?php echo esc_attr( count( $test_table[ 'head' ] ) ); ?>">
											<?php /* translators: Empty table message */ ?>
											<?php esc_html_e( 'No data.', 'ym-fast-seo' ); ?>
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
