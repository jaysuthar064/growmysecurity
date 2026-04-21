<?php
/**
 * Theme header.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
	return;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="gms-site-shell">
	<header class="gms-site-header">
		<div class="gms-container gms-header-inner">
			<a class="gms-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo gms_get_logo_markup( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>

			<nav id="gms-primary-nav" class="gms-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'grow-my-security' ); ?>">
				<?php if ( function_exists( 'gms_render_theme_primary_nav' ) ) : ?>
					<?php gms_render_theme_primary_nav(); ?>
				<?php else : ?>
					<?php
					wp_nav_menu(
						[
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'gms-nav-list',
							'fallback_cb'    => 'gms_render_fallback_primary_menu',
						]
					);
					?>
				<?php endif; ?>
			</nav>

			<div class="gms-header-actions">
				<a class="gms-audit-button" href="<?php echo esc_url( home_url( '/website-audit/' ) ); ?>">
					<?php esc_html_e( 'Website Audit', 'grow-my-security' ); ?>
				</a>

				<button class="gms-nav-toggle" type="button" aria-expanded="false" aria-controls="gms-primary-nav" aria-label="<?php esc_attr_e( 'Toggle navigation', 'grow-my-security' ); ?>">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>
	<div class="gms-nav-backdrop" hidden></div>
	<main class="gms-site-main">
