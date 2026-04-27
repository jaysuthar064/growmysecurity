<?php
/**
 * Industry detail layout widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Industry_Detail_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-industry-detail';
	}

	public function get_title() {
		return __( 'GMS Industry Detail', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-single-page';
	}

	private function get_default_settings(): array {
		if ( function_exists( '\gms_get_industry_detail_widget_settings' ) ) {
			return \gms_get_industry_detail_widget_settings( 'contract-security', \get_template_directory_uri(), \home_url( '/' ) );
		}

		return [];
	}

	protected function register_controls() {
		$defaults = $this->get_default_settings();

		$this->start_controls_section(
			'section_hero',
			[
				'label' => __( 'Hero', 'grow-my-security' ),
			]
		);

		$this->add_control( 'hero_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['hero_eyebrow'] ?? '' ] );
		$this->add_control( 'hero_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['hero_title'] ?? '', 'label_block' => true ] );
		$this->add_control( 'hero_subtext', [ 'label' => __( 'Subtext', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['hero_subtext'] ?? '' ] );
		$this->add_control( 'hero_image', [ 'label' => __( 'Hero Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA, 'default' => $defaults['hero_image'] ?? [ 'url' => \gms_asset( 'assets/images/Industry.png' ) ] ] );
		$this->add_control( 'hero_primary_text', [ 'label' => __( 'Primary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['hero_primary_text'] ?? '' ] );
		$this->add_control( 'hero_primary_url', [ 'label' => __( 'Primary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['hero_primary_url'] ?? [ 'url' => '#cta' ] ] );
		$this->add_control( 'hero_secondary_text', [ 'label' => __( 'Secondary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['hero_secondary_text'] ?? '' ] );
		$this->add_control( 'hero_secondary_url', [ 'label' => __( 'Secondary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['hero_secondary_url'] ?? [ 'url' => '#solutions' ] ] );
		$this->end_controls_section();

		$this->start_controls_section(
			'section_overview',
			[
				'label' => __( 'Overview', 'grow-my-security' ),
			]
		);

		$this->add_control( 'overview_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['overview_eyebrow'] ?? '' ] );
		$this->add_control( 'overview_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['overview_title'] ?? '', 'label_block' => true ] );
		$this->add_control( 'overview_text', [ 'label' => __( 'Description', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['overview_text'] ?? '' ] );
		$this->add_control(
			'overview_points',
			[
				'label'       => __( 'Checklist Points', 'grow-my-security' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => $defaults['overview_points'] ?? '',
				'description' => __( 'One point per line.', 'grow-my-security' ),
			]
		);
		$this->add_control( 'overview_image', [ 'label' => __( 'Side Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA, 'default' => $defaults['overview_image'] ?? [ 'url' => \gms_asset( 'assets/images/security-tech-visual.png' ) ] ] );
		$this->add_control( 'overview_note_title', [ 'label' => __( 'Note Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['overview_note_title'] ?? '' ] );
		$this->add_control( 'overview_note_text', [ 'label' => __( 'Note Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['overview_note_text'] ?? '' ] );
		$this->end_controls_section();

		$this->start_controls_section(
			'section_solutions',
			[
				'label' => __( 'Solutions', 'grow-my-security' ),
			]
		);

		$this->add_control( 'solutions_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['solutions_eyebrow'] ?? '' ] );
		$this->add_control( 'solutions_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['solutions_title'] ?? '', 'label_block' => true ] );
		$this->add_control( 'solutions_text', [ 'label' => __( 'Description', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['solutions_text'] ?? '' ] );

		$feature_repeater = new Repeater();
		$feature_repeater->add_control( 'icon', [ 'label' => __( 'Icon Key', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'shield' ] );
		$feature_repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$feature_repeater->add_control( 'desc', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );

		$this->add_control(
			'solutions_features',
			[
				'label'       => __( 'Feature Cards', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $feature_repeater->get_controls(),
				'default'     => $defaults['solutions_features'] ?? [],
				'title_field' => '{{{ title }}}',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_benefits',
			[
				'label' => __( 'Benefits', 'grow-my-security' ),
			]
		);

		$this->add_control( 'benefits_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['benefits_eyebrow'] ?? '' ] );
		$this->add_control( 'benefits_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['benefits_title'] ?? '', 'label_block' => true ] );
		$this->add_control( 'benefits_text', [ 'label' => __( 'Description', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['benefits_text'] ?? '' ] );

		$benefit_repeater = new Repeater();
		$benefit_repeater->add_control( 'icon', [ 'label' => __( 'Icon Key', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'shield' ] );
		$benefit_repeater->add_control( 'stat', [ 'label' => __( 'Stat', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$benefit_repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$benefit_repeater->add_control( 'description', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );

		$this->add_control(
			'benefits_items',
			[
				'label'       => __( 'Benefit Cards', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $benefit_repeater->get_controls(),
				'default'     => $defaults['benefits_items'] ?? [],
				'title_field' => '{{{ title }}}',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_why',
			[
				'label' => __( 'Why Choose Us', 'grow-my-security' ),
			]
		);

		$this->add_control( 'why_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['why_eyebrow'] ?? '' ] );
		$this->add_control( 'why_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['why_title'] ?? '', 'label_block' => true ] );
		$this->add_control( 'why_text', [ 'label' => __( 'Description', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['why_text'] ?? '' ] );
		$this->add_control( 'why_image', [ 'label' => __( 'Section Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA, 'default' => $defaults['why_image'] ?? [ 'url' => \gms_asset( 'assets/images/security-dashboard-visual.png' ) ] ] );

		$step_repeater = new Repeater();
		$step_repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$step_repeater->add_control( 'desc', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );

		$this->add_control(
			'why_steps',
			[
				'label'       => __( 'Steps', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $step_repeater->get_controls(),
				'default'     => $defaults['why_steps'] ?? [],
				'title_field' => '{{{ title }}}',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_cta',
			[
				'label' => __( 'CTA', 'grow-my-security' ),
			]
		);

		$this->add_control( 'cta_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['cta_eyebrow'] ?? '' ] );
		$this->add_control( 'cta_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['cta_title'] ?? '', 'label_block' => true ] );
		$this->add_control( 'cta_text', [ 'label' => __( 'Description', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['cta_text'] ?? '' ] );
		$this->add_control( 'cta_primary_text', [ 'label' => __( 'Primary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['cta_primary_text'] ?? '' ] );
		$this->add_control( 'cta_primary_url', [ 'label' => __( 'Primary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['cta_primary_url'] ?? [ 'url' => \home_url( '/contact-us/' ) ] ] );
		$this->add_control( 'cta_secondary_text', [ 'label' => __( 'Secondary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['cta_secondary_text'] ?? '' ] );
		$this->add_control( 'cta_secondary_url', [ 'label' => __( 'Secondary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['cta_secondary_url'] ?? [ 'url' => \home_url( '/contact-us/' ) ] ] );
		$this->end_controls_section();

		$this->add_widget_style_controls( 'industry_detail_section_style', '{{WRAPPER}} .gms-service-page' );
	}

	private function render_icon( string $icon ): string {
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
	}

	private function get_lines( string $text ): array {
		$lines = preg_split( '/\r\n|\r|\n/', trim( $text ) );

		return array_values(
			array_filter(
				array_map( 'trim', is_array( $lines ) ? $lines : [] )
			)
		);
	}

	protected function render() {
		$settings        = $this->get_settings_for_display();
		$hero_title      = trim( (string) ( $settings['hero_title'] ?? '' ) );
		$overview_points = $this->get_lines( (string) ( $settings['overview_points'] ?? '' ) );
		$features        = is_array( $settings['solutions_features'] ?? null ) ? $settings['solutions_features'] : [];
		$benefits        = is_array( $settings['benefits_items'] ?? null ) ? $settings['benefits_items'] : [];
		$steps           = is_array( $settings['why_steps'] ?? null ) ? $settings['why_steps'] : [];

		if ( '' === $hero_title ) {
			return;
		}
		?>
		<div class="gms-service-page gms-industry-detail-page">
			<section class="gms-industry-hero">
				<div class="gms-shell-narrow">
					<div class="gms-industry-hero__grid">
						<div class="gms-industry-hero__copy animate-up">
							<?php if ( ! empty( $settings['hero_eyebrow'] ) ) : ?>
								<span class="gms-industry-section__eyebrow"><?php echo esc_html( $settings['hero_eyebrow'] ); ?></span>
							<?php endif; ?>
							<h1><?php echo esc_html( $hero_title ); ?></h1>
							<?php if ( ! empty( $settings['hero_subtext'] ) ) : ?>
								<p class="gms-industry-hero__lead"><?php echo esc_html( $settings['hero_subtext'] ); ?></p>
							<?php endif; ?>
							<div class="gms-industry-hero__actions">
								<?php $this->render_link( 'industry-hero-primary-' . $this->get_id(), $settings['hero_primary_url'] ?? [], (string) ( $settings['hero_primary_text'] ?? '' ), 'gms-button' ); ?>
							</div>
						</div>
						<div class="gms-industry-hero__media animate-up" style="animation-delay: 0.12s;">
							<figure class="gms-industry-media-card gms-industry-media-card--hero">
								<?php $this->render_media_image( $settings['hero_image'] ?? [], [ 'size' => 'full', 'alt' => $hero_title, 'loading' => 'eager', 'fetchpriority' => 'high' ] ); ?>
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
								<?php if ( ! empty( $settings['overview_eyebrow'] ) ) : ?>
									<span class="gms-industry-section__eyebrow"><?php echo esc_html( $settings['overview_eyebrow'] ); ?></span>
								<?php endif; ?>
								<h2><?php echo esc_html( (string) ( $settings['overview_title'] ?? '' ) ); ?></h2>
								<?php if ( ! empty( $settings['overview_text'] ) ) : ?>
									<p><?php echo esc_html( $settings['overview_text'] ); ?></p>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $overview_points ) ) : ?>
								<div class="gms-industry-checklist">
									<?php foreach ( $overview_points as $point ) : ?>
										<div class="gms-industry-checklist__item">
											<div class="gms-industry-checklist__icon" aria-hidden="true"><?php echo $this->render_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
											<p><?php echo esc_html( $point ); ?></p>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="gms-industry-overview__side animate-up" style="animation-delay: 0.12s;">
							<figure class="gms-industry-media-card">
								<?php $this->render_media_image( $settings['overview_image'] ?? [], [ 'size' => 'large', 'alt' => (string) ( $settings['overview_title'] ?? $hero_title ) ] ); ?>
							</figure>
							<div class="gms-industry-note-card">
								<?php if ( ! empty( $settings['overview_note_title'] ) ) : ?>
									<h3><?php echo esc_html( $settings['overview_note_title'] ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $settings['overview_note_text'] ) ) : ?>
									<p><?php echo esc_html( $settings['overview_note_text'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section id="solutions" class="gms-industry-section gms-industry-section--muted">
				<div class="gms-shell-narrow">
					<div class="gms-industry-section-heading animate-up">
						<?php if ( ! empty( $settings['solutions_eyebrow'] ) ) : ?>
							<span class="gms-industry-section__eyebrow"><?php echo esc_html( $settings['solutions_eyebrow'] ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( (string) ( $settings['solutions_title'] ?? '' ) ); ?></h2>
						<?php if ( ! empty( $settings['solutions_text'] ) ) : ?>
							<p><?php echo esc_html( $settings['solutions_text'] ); ?></p>
						<?php endif; ?>
					</div>
					<div class="gms-industry-card-grid">
						<?php foreach ( $features as $index => $feature ) : ?>
							<article class="gms-industry-info-card animate-up" style="animation-delay: <?php echo esc_attr( 0.08 * ( $index + 1 ) ); ?>s;">
								<div class="gms-industry-info-card__icon" aria-hidden="true"><?php echo $this->render_icon( (string) ( $feature['icon'] ?? 'shield' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<h3><?php echo esc_html( (string) ( $feature['title'] ?? '' ) ); ?></h3>
								<p><?php echo esc_html( (string) ( $feature['desc'] ?? '' ) ); ?></p>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="gms-industry-section">
				<div class="gms-shell-narrow">
					<div class="gms-industry-section-heading animate-up">
						<?php if ( ! empty( $settings['benefits_eyebrow'] ) ) : ?>
							<span class="gms-industry-section__eyebrow"><?php echo esc_html( $settings['benefits_eyebrow'] ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( (string) ( $settings['benefits_title'] ?? '' ) ); ?></h2>
						<?php if ( ! empty( $settings['benefits_text'] ) ) : ?>
							<p><?php echo esc_html( $settings['benefits_text'] ); ?></p>
						<?php endif; ?>
					</div>
					<div class="gms-industry-card-grid gms-industry-card-grid--benefits">
						<?php foreach ( $benefits as $index => $benefit ) : ?>
							<article class="gms-industry-benefit-card animate-up" style="animation-delay: <?php echo esc_attr( 0.08 * ( $index + 1 ) ); ?>s;">
								<div class="gms-industry-benefit-card__icon" aria-hidden="true"><?php echo $this->render_icon( (string) ( $benefit['icon'] ?? 'shield' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<?php if ( ! empty( $benefit['stat'] ) ) : ?>
									<div class="gms-industry-benefit-card__stat"><?php echo esc_html( (string) $benefit['stat'] ); ?></div>
								<?php endif; ?>
								<h3><?php echo esc_html( (string) ( $benefit['title'] ?? '' ) ); ?></h3>
								<p><?php echo esc_html( (string) ( $benefit['description'] ?? '' ) ); ?></p>
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
								<?php $this->render_media_image( $settings['why_image'] ?? [], [ 'size' => 'large', 'alt' => (string) ( $settings['why_title'] ?? $hero_title ) ] ); ?>
							</figure>
						</div>
						<div class="gms-industry-why__content animate-up" style="animation-delay: 0.12s;">
							<div class="gms-industry-section-heading gms-industry-section-heading--left">
								<?php if ( ! empty( $settings['why_eyebrow'] ) ) : ?>
									<span class="gms-industry-section__eyebrow"><?php echo esc_html( $settings['why_eyebrow'] ); ?></span>
								<?php endif; ?>
								<h2><?php echo esc_html( (string) ( $settings['why_title'] ?? '' ) ); ?></h2>
								<?php if ( ! empty( $settings['why_text'] ) ) : ?>
									<p><?php echo esc_html( $settings['why_text'] ); ?></p>
								<?php endif; ?>
							</div>
							<div class="gms-industry-step-list">
								<?php foreach ( $steps as $index => $step ) : ?>
									<div class="gms-industry-step-card">
										<div class="gms-industry-step-card__number"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></div>
										<div class="gms-industry-step-card__content">
											<h3><?php echo esc_html( (string) ( $step['title'] ?? '' ) ); ?></h3>
											<p><?php echo esc_html( (string) ( $step['desc'] ?? '' ) ); ?></p>
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
						<?php if ( ! empty( $settings['cta_eyebrow'] ) ) : ?>
							<span class="gms-industry-section__eyebrow"><?php echo esc_html( $settings['cta_eyebrow'] ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( (string) ( $settings['cta_title'] ?? '' ) ); ?></h2>
						<?php if ( ! empty( $settings['cta_text'] ) ) : ?>
							<p><?php echo esc_html( $settings['cta_text'] ); ?></p>
						<?php endif; ?>
						<div class="gms-industry-cta__actions">
							<?php $this->render_link( 'industry-cta-primary-' . $this->get_id(), $settings['cta_primary_url'] ?? [], (string) ( $settings['cta_primary_text'] ?? '' ), 'gms-button' ); ?>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
