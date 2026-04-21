<?php
/**
 * Icon grid widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Icon_Grid_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-icon-grid';
	}

	public function get_title() {
		return __( 'GMS Icon Grid', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	protected function register_controls() {
		$config = \gms_get_demo_config();

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'grow-my-security' ),
			]
		);

		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Who We Serve',
				'title'       => 'Security Verticals Supported',
				'description' => "If your buyers are technical and your product is complex, you're in the right place.",
			]
		);

		$defaults = [];
		$icons    = [ 'guard', 'bolt', 'alarm', 'user', 'team', 'retail', 'investigation', 'building', 'camera' ];

		foreach ( $config['industries'] as $index => $industry ) {
			$defaults[] = [
				'title' => str_replace( ' Industry', '', $industry ),
				'icon'  => $icons[ $index ] ?? 'shield',
			];
		}

		$repeater = new Repeater();
		$repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$repeater->add_control(
			'icon',
			[
				'label'   => __( 'Icon', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'shield',
				'options' => [
					'guard'         => __( 'Guard', 'grow-my-security' ),
					'bolt'          => __( 'Bolt', 'grow-my-security' ),
					'alarm'         => __( 'Alarm', 'grow-my-security' ),
					'user'          => __( 'User', 'grow-my-security' ),
					'team'          => __( 'Team', 'grow-my-security' ),
					'retail'        => __( 'Retail', 'grow-my-security' ),
					'investigation' => __( 'Investigation', 'grow-my-security' ),
					'building'      => __( 'Building', 'grow-my-security' ),
					'camera'        => __( 'Camera', 'grow-my-security' ),
					'shield'        => __( 'Shield', 'grow-my-security' ),
				],
			]
		);

		$this->add_control(
			'items',
			[
				'label'       => __( 'Items', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $defaults,
				'title_field' => '{{{ title }}}',
			]
		);

		$this->add_control( 'button_text', [ 'label' => __( 'Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Schedule a Free Consultation' ] );
		$this->add_control( 'button_url', [ 'label' => __( 'Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$this->add_control( 'footer_text', [ 'label' => __( 'Footer Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => "Don't see your industry? Contact us to see if we can help you." ] );

		$this->end_controls_section();

		$this->add_widget_style_controls( 'icon_grid_section_style', '{{WRAPPER}} .gms-widget' );
	}

	private function render_icon( string $icon ): string {
		switch ( $icon ) {
			case 'guard':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 5 5v5c0 4.86 3.4 9.45 7 10.73C15.6 19.45 19 14.86 19 10V5l-7-3Zm0 2.18 5 2.14V10c0 3.83-2.46 7.48-5 8.7-2.54-1.22-5-4.87-5-8.7V6.32l5-2.14Zm-1 3.82h2v2h2v2h-2v2h-2v-2H9v-2h2V8Z" fill="currentColor"/></svg>';
			case 'bolt':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" fill="currentColor"/></svg>';
			case 'alarm':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 1 22 6l-1.41 1.41L15.59 2.4 17 1ZM7 1l1.41 1.41L3.41 7.41 2 6l5-5Zm5 4a8 8 0 1 0 8 8 8.009 8.009 0 0 0-8-8Zm1 8V8h-2v6l4.2 2.52 1-1.64L13 13Z" fill="currentColor"/></svg>';
			case 'user':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.33 0-8 2.17-8 5v2h16v-2c0-2.83-3.67-5-8-5Z" fill="currentColor"/></svg>';
			case 'team':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-2.83-6.83A4 4 0 0 0 16 11Zm-8 0A4 4 0 1 0 8 3a4 4 0 0 0 0 8Zm0 2c-2.67 0-8 1.34-8 4v2h8v-2a4.96 4.96 0 0 1 1.58-3.64A10.54 10.54 0 0 0 8 13Zm8 0c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" fill="currentColor"/></svg>';
			case 'retail':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 4H7L4 8v2h1v10h14V10h1V8l-3-4Zm0 2 1.5 2H5.5L7 6h10Zm-8 4h6v8H9v-8Z" fill="currentColor"/></svg>';
			case 'investigation':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2a8 8 0 1 0 5.293 14.002L21 21.707 22.707 20l-5.705-5.707A8 8 0 0 0 10 2Zm0 2a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm1 3H9v4l3.5 2.1 1-1.64L11 10.4V7Z" fill="currentColor"/></svg>';
			case 'building':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 3 2 8v13h20V8l-8-5-4 2-2-2Zm4 3.2 6 3.75V19h-3v-5H7v5H4V9.95L10 6.2V11h4V6.2Z" fill="currentColor"/></svg>';
			case 'camera':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 7h-2.17l-1.84-2H9L7.17 7H5a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3v-7a3 3 0 0 0-3-3Zm0 11H5v-8h12v8Zm-6-7a3 3 0 1 0 3 3 3 3 0 0 0-3-3Z" fill="currentColor"/></svg>';
			case 'shield':
			default:
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Z" fill="currentColor"/></svg>';
		}
	}

	protected function render() {
		$settings = $this->get_settings();
		$items    = $settings['items'] ?? [];

		if ( empty( $items ) ) {
			return;
		}
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--serve">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
					<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--serve" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
					<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
					<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				</div>
				<div class="gms-homepage-serve__grid">
					<?php foreach ( $items as $index => $item ) : ?>
						<?php $card_url = function_exists( '\gms_get_industry_url' ) ? \gms_get_industry_url( (string) ( $item['title'] ?? '' ) ) : '#'; ?>
						<a href="<?php echo esc_url( $card_url ); ?>" class="gms-homepage-serve__item<?php echo 0 === $index ? ' is-accent' : ''; ?>">
							<div class="gms-homepage-serve__icon" aria-hidden="true"><?php echo $this->render_icon( $item['icon'] ?? 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
							<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
						</a>
					<?php endforeach; ?>
				</div>
				<div class="gms-homepage-serve__footer">
					<?php if ( $this->has_valid_link( $settings['button_url'] ?? [] ) && ! empty( $settings['button_text'] ) ) : ?>
						<?php $this->add_render_attribute( 'icon-grid-button-' . $this->get_id(), 'class', 'gms-homepage-button gms-homepage-button--primary' ); ?>
						<?php $this->add_link_attributes( 'icon-grid-button-' . $this->get_id(), $settings['button_url'] ?? [] ); ?>
						<a <?php echo $this->get_render_attribute_string( 'icon-grid-button-' . $this->get_id() ); ?>><span><?php echo esc_html( $settings['button_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
					<?php endif; ?>
					<?php if ( ! empty( $settings['footer_text'] ) ) : ?><p><?php echo esc_html( $settings['footer_text'] ); ?></p><?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}