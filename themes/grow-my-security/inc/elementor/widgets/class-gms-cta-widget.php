<?php
/**
 * CTA banner widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cta_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-cta-banner';
	}

	public function get_title() {
		return __( 'GMS CTA Banner', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'CTA Content', 'grow-my-security' ),
			]
		);
		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Contact Us',
				'title'       => 'Ready to build trust that drives revenue?',
				'description' => 'You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.',
			]
		);
		$this->add_control( 'button_text', [ 'label' => __( 'Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Schedule a Free Consultation' ] );
		$this->add_control( 'button_url', [ 'label' => __( 'Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$this->add_control(
			'trust_text',
			[
				'label'       => __( 'Trust Text', 'grow-my-security' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '82% of B2B buyers ignore vendors they do not trust. We help your people earn belief before the first conversation, because trust is what drives every deal.',
				'label_block' => true,
			]
		);
		$this->add_control(
			'image',
			[
				'label'   => __( 'Image', 'grow-my-security' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [ 'url' => \gms_asset( 'assets/images/security-dashboard-visual.png' ) ],
			]
		);
		$this->end_controls_section();

		$this->add_widget_style_controls( 'cta_section_style', '{{WRAPPER}} .gms-widget' );
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$eyebrow    = trim( (string) ( $settings['eyebrow'] ?? '' ) );
		$title      = trim( (string) ( $settings['title'] ?? '' ) );
		$copy       = trim( (string) ( $settings['description'] ?? '' ) );
		$trust_text = trim( (string) ( $settings['trust_text'] ?? '' ) );
		$image      = is_array( $settings['image'] ?? null ) ? $settings['image'] : [];
		?>
		<section class="gms-widget gms-approved-cta gms-approved-cta--industry-premium gms-cta-banner--premium">
			<div class="gms-approved-cta-premium__shell">
				<div class="gms-approved-cta-premium__content">
					<?php if ( '' !== $eyebrow ) : ?>
						<div class="gms-approved-cta-premium__tag"><?php echo esc_html( $eyebrow ); ?></div>
					<?php endif; ?>

					<?php if ( '' !== $title ) : ?>
						<h2 class="gms-approved-cta-premium__title"><?php echo esc_html( $title ); ?></h2>
					<?php endif; ?>

					<?php if ( '' !== $copy ) : ?>
						<p class="gms-approved-cta-premium__description"><?php echo esc_html( $copy ); ?></p>
					<?php endif; ?>

					<?php
					$this->render_link(
						'cta-banner-button-' . $this->get_id(),
						$settings['button_url'] ?? [],
						$settings['button_text'] ?? '',
						'gms-button gms-approved-cta-premium__button'
					);
					?>

					<?php if ( '' !== $trust_text ) : ?>
						<div class="gms-approved-cta-premium__trust">
							<div class="gms-approved-cta-premium__trust-icon" aria-hidden="true">
								<svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Zm-1.23 4.41-1.41 1.41 2.64 2.64 4.24-4.24-1.41-1.41L12 10.82l-1.23-1.23Z" fill="currentColor"/></svg>
							</div>
							<p><?php echo esc_html( $trust_text ); ?></p>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $image['url'] ) ) : ?>
					<div class="gms-approved-cta-premium__visual">
						<div class="gms-approved-cta-premium__visual-frame">
							<?php $this->render_media_image( $image, [ 'size' => 'large', 'class' => 'gms-approved-cta-premium__media' ] ); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
