<?php
/**
 * Template Name: Shared Detail Page
 *
 * Shared template for service and industry detail pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( function_exists( 'gms_render_elementor_content_fallback' ) && gms_render_elementor_content_fallback() ) {
	get_footer();
	return;
}

global $post;

$detail_parent = $post instanceof WP_Post && $post->post_parent ? get_post( $post->post_parent ) : null;
$detail_parent_slug = $detail_parent instanceof WP_Post ? $detail_parent->post_name : '';

if (
	$post instanceof WP_Post
	&& function_exists( 'gms_post_has_elementor_content' )
	&& gms_post_has_elementor_content( (int) $post->ID )
	&& function_exists( 'gms_output_elementor_builder_markup' )
) {
	?>
	<div class="gms-page-shell gms-approved-page gms-approved-page--industry-detail gms-approved-page--elementor-live">
		<div class="gms-page-content gms-page-content--elementor gms-page-content--theme-builder">
			<?php if ( ! gms_output_elementor_builder_markup( $post ) ) : ?>
				<?php the_content(); ?>
			<?php endif; ?>
		</div>
	</div>
	<?php
	get_footer();
	return;
}

if ( 'services' === $detail_parent_slug && function_exists( 'gms_get_service_config_by_slug' ) && function_exists( 'gms_render_service_detail_content' ) ) {
	$service = gms_get_service_config_by_slug( (string) $post->post_name );

	if ( is_array( $service ) ) {
		gms_render_service_detail_content( $service );
		get_footer();
		return;
	}
}

$current_slug = $post->post_name;
$data         = gms_get_industry_data( $current_slug );

$hero_img_name   = $data['images']['hero'] ?? 'Industry.png';
$visual_img_name = $data['images']['visual1'] ?? 'Services-1.png';
$detail_img_name = $data['images']['visual2'] ?? 'Resources.png';

$hero_img_url   = get_theme_file_uri( 'assets/images/' . $hero_img_name );
$visual_img_url = get_theme_file_uri( 'assets/images/' . $visual_img_name );
$detail_img_url = get_theme_file_uri( 'assets/images/' . $detail_img_name );

$results = $data['results'] ?? [];
$features = $data['features'] ?? [];
$process = $data['process'] ?? [];
$problem_points = $data['problem']['points'] ?? [];

$benefit_icons = [ 'target', 'layers', 'star' ];
$benefit_copy  = [
	'Stronger positioning helps your team turn visibility into better-fit enquiries.',
	'A clearer buyer journey reduces wasted effort and improves conversion quality.',
	'Consistent authority signals build trust earlier and shorten buying cycles.',
];

$benefit_cards = [];
foreach ( $results as $index => $result ) {
	$benefit_cards[] = [
		'icon'        => $benefit_icons[ $index ] ?? 'shield',
		'stat'        => $result['stat'] ?? '',
		'title'       => $result['label'] ?? __( 'Measurable growth', 'grow-my-security' ),
		'description' => $benefit_copy[ $index ] ?? __( 'A cleaner digital experience supports stronger pipeline conversations.', 'grow-my-security' ),
	];
}

$render_icon = static function ( string $icon ): string {
	switch ( $icon ) {
		case 'search':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2a8 8 0 1 0 5.293 14.002L21 21.707 22.707 20l-5.705-5.707A8 8 0 0 0 10 2Zm0 2a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm1 3H9v4l3.5 2.1 1-1.64L11 10.4V7Z" fill="currentColor"/></svg>';
		case 'shield':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Z" fill="currentColor"/></svg>';
		case 'target':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10.012 10.012 0 0 0 12 2Zm0 2a8 8 0 1 1-8 8 8.009 8.009 0 0 1 8-8Zm0 3a5 5 0 1 0 5 5 5.006 5.006 0 0 0-5-5Zm0 2a3 3 0 1 1-3 3 3 3 0 0 1 3-3Zm0-8h2v4h-2V1Zm0 18h2v4h-2v-4Zm7.78-14.36 1.42 1.42-2.83 2.83-1.42-1.42 2.83-2.83ZM4.22 19.36l-1.42-1.42 2.83-2.83 1.42 1.42-2.83 2.83ZM19 11h4v2h-4v-2ZM1 11h4v2H1v-2Z" fill="currentColor"/></svg>';
		case 'cpu':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h10v10H7V7Zm2 2v6h6V9H9Zm1-8h2v3h-2V1Zm4 0h2v3h-2V1ZM1 10h3v2H1v-2Zm0 4h3v2H1v-2Zm19-4h3v2h-3v-2Zm0 4h3v2h-3v-2ZM8 20h2v3H8v-3Zm6 0h2v3h-2v-3ZM5 5h14v14H5V5Z" fill="currentColor"/></svg>';
		case 'users':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-2.83-6.83A4 4 0 0 0 16 11Zm-8 0A4 4 0 1 0 8 3a4 4 0 0 0 0 8Zm0 2c-2.67 0-8 1.34-8 4v2h8v-2a4.96 4.96 0 0 1 1.58-3.64A10.54 10.54 0 0 0 8 13Zm8 0c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" fill="currentColor"/></svg>';
		case 'zap':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" fill="currentColor"/></svg>';
		case 'trending-down':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 7 6 6 4-4 8 8v-4h2v8h-8v-2h4L13 11l-4 4L1 9l2-2Z" fill="currentColor"/></svg>';
		case 'star':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.9 6 6.6.96-4.75 4.63 1.12 6.56L12 16.9l-5.87 3.25 1.12-6.56L2.5 8.96 9.1 8 12 2Z" fill="currentColor"/></svg>';
		case 'mouse-pointer':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3 10.07 20l2.51-7.39L20 10.1 3 3Zm10 10 4.95-1.68-6.08-2.14 2.13 6.08Z" fill="currentColor"/></svg>';
		case 'layers':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 10 5-10 5L2 7l10-5Zm-8.2 9L12 15.1 20.2 11 22 11.9l-10 5-10-5 1.8-.9Zm0 4L12 19.1 20.2 15 22 15.9l-10 5-10-5 1.8-.9Z" fill="currentColor"/></svg>';
		case 'pie-chart':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 2v10H1A10 10 0 0 1 11 2Zm2 0a10 10 0 1 1-9.95 11H13V2Z" fill="currentColor"/></svg>';
		case 'check':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.55 18.36-5.66-5.65 1.42-1.42 4.24 4.24 8.48-8.49 1.42 1.42-9.9 9.9Z" fill="currentColor"/></svg>';
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Z" fill="currentColor"/></svg>';
	}
};
?>

<div class="gms-service-page gms-industry-detail-page">
	<section class="gms-industry-hero">
		<div class="gms-shell-narrow">
			<div class="gms-industry-hero__grid">
				<div class="gms-industry-hero__copy animate-up">
					<span class="gms-industry-section__eyebrow"><?php echo esc_html( $data['hero']['eyebrow'] ?? __( 'Industry', 'grow-my-security' ) ); ?></span>
					<h1><?php echo esc_html( $data['hero']['title'] ?? get_the_title() ); ?></h1>
					<p class="gms-industry-hero__lead"><?php echo esc_html( $data['hero']['subtext'] ?? '' ); ?></p>
					<div class="gms-industry-hero__actions">
						<a href="#cta" class="gms-button"><?php esc_html_e( 'Get Started', 'grow-my-security' ); ?></a>
					</div>
				</div>
				<div class="gms-industry-hero__media animate-up" style="animation-delay: 0.12s;">
					<figure class="gms-industry-media-card gms-industry-media-card--hero">
						<img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $data['hero']['title'] ?? get_the_title() ); ?>">
					</figure>
				</div>
			</div>
		</div>
	</section>

	<section class="gms-industry-section">
		<div class="gms-shell-narrow">
			<div class="gms-industry-overview">
				<div class="gms-industry-overview__main animate-up">
					<div class="gms-industry-section-heading gms-industry-section-heading--left">
						<span class="gms-industry-section__eyebrow"><?php esc_html_e( 'Overview', 'grow-my-security' ); ?></span>
						<h2><?php echo esc_html( $data['problem']['title'] ?? __( 'Industry Overview', 'grow-my-security' ) ); ?></h2>
						<p><?php echo esc_html( $data['solution']['desc'] ?? ( $data['hero']['subtext'] ?? '' ) ); ?></p>
					</div>
					<div class="gms-industry-checklist">
						<?php foreach ( $problem_points as $point ) : ?>
							<div class="gms-industry-checklist__item">
								<div class="gms-industry-checklist__icon" aria-hidden="true"><?php echo $render_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<p><?php echo esc_html( $point ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="gms-industry-overview__side animate-up" style="animation-delay: 0.12s;">
					<figure class="gms-industry-media-card">
						<img src="<?php echo esc_url( $visual_img_url ); ?>" alt="<?php echo esc_attr( $data['problem']['title'] ?? get_the_title() ); ?>">
					</figure>
					<div class="gms-industry-note-card">
						<h3><?php esc_html_e( 'What this page helps you solve', 'grow-my-security' ); ?></h3>
						<p><?php echo esc_html( $data['hero']['subtext'] ?? '' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="solutions" class="gms-industry-section gms-industry-section--muted">
		<div class="gms-shell-narrow">
			<div class="gms-industry-section-heading animate-up">
				<span class="gms-industry-section__eyebrow"><?php esc_html_e( 'Key Services / Solutions', 'grow-my-security' ); ?></span>
				<h2><?php echo esc_html( $data['solution']['title'] ?? __( 'Key Services / Solutions', 'grow-my-security' ) ); ?></h2>
				<p><?php echo esc_html( $data['solution']['desc'] ?? '' ); ?></p>
			</div>
			<div class="gms-industry-card-grid">
				<?php foreach ( $features as $index => $feature ) : ?>
					<article class="gms-industry-info-card animate-up" style="animation-delay: <?php echo esc_attr( 0.08 * ( $index + 1 ) ); ?>s;">
						<div class="gms-industry-info-card__icon" aria-hidden="true"><?php echo $render_icon( $feature['icon'] ?? 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<h3><?php echo esc_html( $feature['title'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $feature['desc'] ?? '' ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="gms-industry-section">
		<div class="gms-shell-narrow">
			<div class="gms-industry-section-heading animate-up">
				<span class="gms-industry-section__eyebrow"><?php esc_html_e( 'Benefits', 'grow-my-security' ); ?></span>
				<h2><?php esc_html_e( 'Outcomes built for measurable growth', 'grow-my-security' ); ?></h2>
				<p><?php esc_html_e( 'Every engagement is structured to improve trust, sharpen positioning, and create better sales conversations.', 'grow-my-security' ); ?></p>
			</div>
			<div class="gms-industry-card-grid gms-industry-card-grid--benefits">
				<?php foreach ( $benefit_cards as $index => $benefit ) : ?>
					<article class="gms-industry-benefit-card animate-up" style="animation-delay: <?php echo esc_attr( 0.08 * ( $index + 1 ) ); ?>s;">
						<div class="gms-industry-benefit-card__icon" aria-hidden="true"><?php echo $render_icon( $benefit['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<?php if ( ! empty( $benefit['stat'] ) ) : ?>
							<div class="gms-industry-benefit-card__stat"><?php echo esc_html( $benefit['stat'] ); ?></div>
						<?php endif; ?>
						<h3><?php echo esc_html( $benefit['title'] ); ?></h3>
						<p><?php echo esc_html( $benefit['description'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="gms-industry-section gms-industry-section--muted">
		<div class="gms-shell-narrow">
			<div class="gms-industry-why">
				<div class="gms-industry-why__media animate-up">
					<figure class="gms-industry-media-card">
						<img src="<?php echo esc_url( $detail_img_url ); ?>" alt="<?php echo esc_attr( $data['hero']['eyebrow'] ?? get_the_title() ); ?>">
					</figure>
				</div>
				<div class="gms-industry-why__content animate-up" style="animation-delay: 0.12s;">
					<div class="gms-industry-section-heading gms-industry-section-heading--left">
						<span class="gms-industry-section__eyebrow"><?php esc_html_e( 'Why Choose Us', 'grow-my-security' ); ?></span>
						<h2><?php esc_html_e( 'A clear delivery model built for trust-sensitive buying cycles', 'grow-my-security' ); ?></h2>
						<p><?php esc_html_e( 'We combine positioning, execution, and ongoing refinement so your industry page feels aligned, credible, and conversion-ready.', 'grow-my-security' ); ?></p>
					</div>
					<div class="gms-industry-step-list">
						<?php foreach ( $process as $index => $step ) : ?>
							<div class="gms-industry-step-card">
								<div class="gms-industry-step-card__number"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></div>
								<div class="gms-industry-step-card__content">
									<h3><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
									<p><?php echo esc_html( $step['desc'] ?? '' ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="cta" class="gms-industry-section gms-industry-section--cta">
		<div class="gms-shell-narrow">
			<div class="gms-industry-cta animate-up">
				<span class="gms-industry-section__eyebrow"><?php esc_html_e( 'CTA', 'grow-my-security' ); ?></span>
				<h2><?php esc_html_e( 'Want to grow your business in this industry?', 'grow-my-security' ); ?></h2>
				<p><?php esc_html_e( 'Let us build a cleaner, more credible growth experience for your market so your next conversation starts with trust already in place.', 'grow-my-security' ); ?></p>
				<div class="gms-industry-cta__actions">
					<a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>" class="gms-button"><?php esc_html_e( 'Get Started', 'grow-my-security' ); ?></a>
				</div>
			</div>
		</div>
	</section>
</div>

<?php
get_footer();
