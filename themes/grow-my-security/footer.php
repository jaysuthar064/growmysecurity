<?php
/**
 * Theme footer.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
	return;
}

if ( function_exists( 'gms_is_homepage_elementor_context' ) && gms_is_homepage_elementor_context() ) {
	?>
		</main>
	</div>
	<?php wp_footer(); ?>
	</body>
	</html>
	<?php
	return;
}

$config         = gms_get_demo_config();
$footer_groups  = function_exists( 'gms_get_footer_groups' ) ? gms_get_footer_groups() : [];
$footer_socials = function_exists( 'gms_get_footer_social_links' ) ? gms_get_footer_social_links() : [];
?>
	</main>
	<footer class="gms-site-footer">
		<div class="gms-container gms-footer-grid">
			<div class="gms-footer-brand">
				<a class="gms-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo gms_get_logo_markup( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<p><?php esc_html_e( 'Grow My Security Company is a full-service digital marketing agency helping security businesses grow with SEO, PPC, website development, and performance marketing.', 'grow-my-security' ); ?></p>
				
				<div class="gms-footer-contact">
					<a href="mailto:<?php echo esc_attr( $config['branding']['email'] ); ?>"><?php echo esc_html( $config['branding']['email'] ); ?></a>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $config['branding']['phone'] ) ); ?>"><?php echo esc_html( $config['branding']['phone'] ); ?></a>
				</div>
				<div class="gms-badge"><?php esc_html_e( 'Veteran-led', 'grow-my-security' ); ?></div>
				<div class="gms-footer-socials" aria-label="<?php esc_attr_e( 'Social media links', 'grow-my-security' ); ?>">
					<?php foreach ( $footer_socials as $social ) : ?>
						<a class="gms-footer-social gms-footer-social--<?php echo esc_attr( $social['slug'] ); ?>" href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noreferrer noopener" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
							<?php echo $social['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<?php foreach ( $footer_groups as $group_key => $group ) : ?>
				<?php $is_default_open = 'services' === $group_key; ?>
				<div class="gms-footer-column gms-footer-column--<?php echo esc_attr( $group_key ); ?><?php echo $is_default_open ? ' is-open' : ''; ?>">
					<button class="gms-footer-column__toggle" type="button" aria-expanded="<?php echo $is_default_open ? 'true' : 'false'; ?>">
						<span><?php echo esc_html( $group['title'] ); ?></span>
						<span class="gms-footer-column__chevron" aria-hidden="true"></span>
					</button>
					<div class="gms-footer-column__panel">
						<ul class="gms-footer-links">
							<?php foreach ( $group['links'] as $link ) : ?>
								<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
						<?php if ( ! empty( $group['subtitle'] ) && ! empty( $group['sub_links'] ) ) : ?>
							<div class="gms-footer-column__subgroup">
								<h5 class="gms-footer-subtitle"><?php echo esc_html( $group['subtitle'] ); ?></h5>
								<ul class="gms-footer-links gms-footer-links--sub">
									<?php foreach ( $group['sub_links'] as $link ) : ?>
										<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="gms-container gms-footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $config['branding']['company'] ); ?>. <?php esc_html_e( 'All rights reserved.', 'grow-my-security' ); ?></p>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
