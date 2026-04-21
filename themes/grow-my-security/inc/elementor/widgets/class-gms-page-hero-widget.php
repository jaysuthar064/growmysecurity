<?php
/**
 * Compact page hero widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Page_Hero_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-page-hero';
	}

	public function get_title() {
		return __( 'GMS Page Hero', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'grow-my-security' ),
			]
		);

		$this->add_control(
			'alignment',
			[
				'label'   => __( 'Alignment', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'   => __( 'Left', 'grow-my-security' ),
					'center' => __( 'Center', 'grow-my-security' ),
				],
			]
		);

		$this->add_control(
			'variant',
			[
				'label'   => __( 'Variant', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'services',
				'options' => [
					'services' => __( 'Service Archive', 'grow-my-security' ),
					'detail'  => __( 'Service Detail', 'grow-my-security' ),
					'faq'     => __( 'FAQ', 'grow-my-security' ),
				],
			]
		);

		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Services',
				'title'       => "Creative Services\nbuilt for impact",
				'description' => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
			]
		);

		$this->add_control(
			'art_image',
			[
				'label'   => __( 'Art Image', 'grow-my-security' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => \gms_get_service_archive_hero_media(),
				'condition' => [
					'alignment' => 'left',
				],
			]
		);

		$this->add_control(
			'background_image',
			[
				'label' => __( 'Background Image', 'grow-my-security' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);

		$this->add_control(
			'primary_text',
			[
				'label'   => __( 'Primary Button Text', 'grow-my-security' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Get a free audit',
			]
		);

		$this->add_control(
			'primary_url',
			[
				'label' => __( 'Primary Button URL', 'grow-my-security' ),
				'type'  => Controls_Manager::URL,
			]
		);

		$this->add_control(
			'secondary_text',
			[
				'label' => __( 'Secondary Button Text', 'grow-my-security' ),
				'type'  => Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'secondary_url',
			[
				'label' => __( 'Secondary Button URL', 'grow-my-security' ),
				'type'  => Controls_Manager::URL,
			]
		);

		$this->end_controls_section();

		$this->add_widget_style_controls( 'page_hero_section_style', '{{WRAPPER}} .gms-page-hero, {{WRAPPER}} .gms-approved-intro' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$variant  = $settings['variant'] ?? 'services';
		$alignment = $settings['alignment'] ?? 'left';
		$modifier = $variant;

		$this->add_render_attribute( 'section', 'class', 'gms-widget gms-approved-intro gms-approved-intro--' . $modifier );
		$this->add_render_attribute( 'section', 'class', 'gms-approved-intro--align-' . $alignment );

		if ( 'center' === $alignment ) {
			$this->add_render_attribute( 'section', 'class', 'gms-approved-intro--centered' );
		}
		?>
		<section <?php echo $this->get_render_attribute_string( 'section' ); ?>>
			<div class="gms-approved-intro__grid">
				<div class="gms-approved-intro__main">
					<?php if ( ! empty( $settings['eyebrow'] ) ) : ?><div class="gms-eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></div><?php endif; ?>
					<?php if ( ! empty( $settings['title'] ) ) : ?><h1><?php echo wp_kses_post( nl2br( esc_html( (string) $settings['title'] ) ) ); ?></h1><?php endif; ?>
					<?php if ( ! empty( $settings['description'] ) ) : ?><div class="gms-approved-intro__lede"><p><?php echo esc_html( $settings['description'] ); ?></p></div><?php endif; ?>
					<?php if ( ! empty( $settings['primary_text'] ) || ! empty( $settings['secondary_text'] ) ) : ?>
						<div class="gms-page-hero__actions">
							<?php $this->render_link( 'page-hero-primary-' . $this->get_id(), $settings['primary_url'] ?? [], $settings['primary_text'] ?? '', 'gms-button' ); ?>
							<?php $this->render_link( 'page-hero-secondary-' . $this->get_id(), $settings['secondary_url'] ?? [], $settings['secondary_text'] ?? '', 'gms-button-outline' ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $settings['art_image']['url'] ) && 'center' !== $alignment ) : ?>
					<div class="gms-approved-intro__side">
						<div class="gms-approved-art-card">
							<?php $this->render_media_image( $settings['art_image'], [ 'alt' => '', 'fetchpriority' => 'high', 'loading' => 'eager', 'size' => 'large' ] ); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
