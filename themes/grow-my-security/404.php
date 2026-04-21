<?php
/**
 * 404 template.
 *
 * @package GrowMySecurity
 */

get_header();

$quick_links = [
	[ 'label' => __( 'Services', 'grow-my-security' ), 'text' => __( 'See the full trust-led growth stack.', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
	[ 'label' => __( 'Industries', 'grow-my-security' ), 'text' => __( 'Explore the security segments we support.', 'grow-my-security' ), 'url' => home_url( '/industries/' ) ],
	[ 'label' => __( 'Resources', 'grow-my-security' ), 'text' => __( 'Read the latest insights and commentary.', 'grow-my-security' ), 'url' => home_url( '/resources-insights/' ) ],
	[ 'label' => __( 'Contact Us', 'grow-my-security' ), 'text' => __( 'Talk with the team about your next move.', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
];

ob_start();
?>
<div class="gms-approved-search-panel">
	<p class="gms-approved-toolbar-note"><?php esc_html_e( 'Use the search below or jump into the most useful site sections from the approved public experience.', 'grow-my-security' ); ?></p>
	<?php echo get_search_form( false ); ?>
</div>
<?php
$support_html = ob_get_clean();
?>
<div class="gms-page-shell gms-approved-page gms-approved-page--404">
	<div class="gms-container gms-approved-stack">
		<?php if ( function_exists( 'gms_render_internal_intro' ) ) : ?>
			<?php
			gms_render_internal_intro(
				[
					'eyebrow'      => __( 'Page Not Found', 'grow-my-security' ),
					'title'        => __( 'The page you requested is off the grid.', 'grow-my-security' ),
					'lede'         => __( 'The route may have changed, the content may have been moved, or the link may be outdated.', 'grow-my-security' ),
					'modifier'     => 'not-found',
					'support_html' => $support_html,
				]
			);
			?>
		<?php else : ?>
			<?php $hero_args = function_exists( 'gms_get_404_hero_data' ) ? gms_get_404_hero_data() : []; ?>
			<?php $hero_args['extra'] = get_search_form( false ); ?>
			<?php gms_render_page_hero( $hero_args ); ?>
		<?php endif; ?>

		<section class="gms-approved-quick-links">
			<?php foreach ( $quick_links as $quick_link ) : ?>
				<a class="gms-approved-quick-link" href="<?php echo esc_url( $quick_link['url'] ); ?>">
					<strong><?php echo esc_html( $quick_link['label'] ); ?></strong>
					<span><?php echo esc_html( $quick_link['text'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</section>

		<?php if ( function_exists( 'gms_render_money_cta' ) ) : ?>
			<?php
			gms_render_money_cta(
				[
					'title'  => __( 'Need help finding the right page?', 'grow-my-security' ),
					'copy'   => __( 'Tell us what you were trying to reach and we will point you to the right part of the Grow My Security site.', 'grow-my-security' ),
					'button' => __( 'Contact Us', 'grow-my-security' ),
					'url'    => home_url( '/contact-us/' ),
				]
			);
			?>
		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
