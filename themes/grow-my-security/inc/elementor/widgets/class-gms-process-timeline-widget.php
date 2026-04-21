<?php
/**
 * Process timeline widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Process_Timeline_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-process-timeline';
	}

	public function get_title() {
		return __( 'GMS Process Timeline', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'grow-my-security' ),
			]
		);

		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Our Guarantee',
				'title'       => 'How We Help Security Brands Win Trust & Growth',
				'description' => 'We work as a strategic growth partner for security-focused brands, helping them move from being technically capable to being clearly trusted in the market.',
			]
		);

		$repeater = new Repeater();
		$repeater->add_control( 'accent', [ 'label' => __( 'Accent Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$repeater->add_control( 'text', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$repeater->add_control( 'link_text', [ 'label' => __( 'Link Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$repeater->add_control( 'link_url', [ 'label' => __( 'Link URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$repeater->add_control(
			'icon',
			[
				'label'   => __( 'Icon', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'shield',
				'options' => [
					'shield' => __( 'Shield', 'grow-my-security' ),
					'users'  => __( 'Users', 'grow-my-security' ),
					'target' => __( 'Target', 'grow-my-security' ),
					'link'   => __( 'Link', 'grow-my-security' ),
				],
			]
		);

		$this->add_control(
			'items',
			[
				'label'       => __( 'Timeline Items', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'accent'    => 'Clear Positioning',
						'title'     => 'in a Crowded Market',
						'text'      => 'We help you articulate what you do, who you are for, and why you are different.',
						'link_text' => 'Learn More',
						'icon'      => 'shield',
					],
					[
						'accent'    => 'Trust-First',
						'title'     => 'Brand Presence',
						'text'      => 'From messaging and content to design and digital touchpoints, we ensure your brand looks and feels credible.',
						'link_text' => 'See How This Works',
						'icon'      => 'users',
					],
					[
						'accent'    => 'Buyer-Aligned',
						'title'     => 'Demand Generation',
						'text'      => 'We focus on attracting decision-makers already searching for solutions like yours.',
						'link_text' => 'Explore More',
						'icon'      => 'target',
					],
					[
						'accent'    => 'Consistency Over',
						'title'     => 'Campaigns',
						'text'      => 'We build a repeatable system that compounds visibility, authority, and inbound demand over time.',
						'link_text' => 'Get Started',
						'icon'      => 'link',
					],
				],
				'title_field' => '{{{ accent }}} {{{ title }}}',
			]
		);

		$this->end_controls_section();

		$this->add_widget_style_controls( 'process_timeline_section_style', '{{WRAPPER}} .gms-widget' );
	}

	private function render_icon( string $icon ): string {
		switch ( $icon ) {
			case 'users':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-2.829-6.829A4 4 0 0 0 16 11Zm-8 0A4 4 0 1 0 8 3a4 4 0 0 0 0 8Zm8 2c-2.673 0-8 1.343-8 4v2h16v-2c0-2.657-5.327-4-8-4Zm-8 0c-.29 0-.62.017-.972.05C4.04 13.246 0 14.525 0 17v2h6v-2c0-1.512.805-2.85 2.111-3.843A8.374 8.374 0 0 0 8 13Z" fill="currentColor"/></svg>';
			case 'target':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10h-2a8 8 0 1 1-8-8V2Zm7 1-2.586 2.586A7.96 7.96 0 0 1 20 12h2a9.96 9.96 0 0 0-2.293-6.414L22 3h-3Zm-7 4a5 5 0 1 0 5 5h-2a3 3 0 1 1-3-3V7Zm0-5v2a8 8 0 0 1 8 8h2A10 10 0 0 0 12 2Z" fill="currentColor"/></svg>';
			case 'link':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.59 13.41a1.996 1.996 0 0 0 2.82 0l3.59-3.59a2 2 0 1 0-2.83-2.83l-1.17 1.17-1.41-1.41 1.17-1.17a4 4 0 1 1 5.66 5.66l-3.59 3.59a4 4 0 0 1-5.66 0l1.42-1.42Zm2.82-2.82-2.82 2.82-1.41-1.41 2.82-2.82 1.41 1.41Zm-4.24 7.07 1.17-1.17-1.41-1.41-1.17 1.17a2 2 0 0 1-2.83-2.83l3.59-3.59a2 2 0 0 1 2.83 0l1.17-1.17 1.41 1.41-1.17 1.17a4 4 0 0 0-5.66 0l-3.59 3.59a4 4 0 1 0 5.66 5.66Z" fill="currentColor"/></svg>';
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
		<section class="gms-widget gms-homepage-section gms-homepage-section--guarantee">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
					<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--guarantee" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
					<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
					<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				</div>
				<div class="gms-homepage-guarantee__timeline">
					<div class="gms-homepage-guarantee__line-bg" aria-hidden="true"></div>
					<div class="gms-homepage-guarantee__progress-line" aria-hidden="true"></div>
					<?php foreach ( $items as $index => $item ) : ?>
						<article class="gms-homepage-guarantee__step<?php echo 0 === $index ? ' is-active' : ''; ?>">
							<div class="gms-homepage-guarantee__node-wrap">
								<div class="gms-homepage-guarantee__node" aria-hidden="true">
									<?php echo $this->render_icon( $item['icon'] ?? 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							</div>
							<div class="gms-homepage-guarantee__body">
								<h3><span><?php echo esc_html( $item['accent'] ?? '' ); ?></span><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
								<p><?php echo esc_html( $item['text'] ?? '' ); ?></p>
								<?php if ( ! empty( $item['link_text'] ) && $this->has_valid_link( $item['link_url'] ?? [] ) ) : ?>
									<?php $this->add_render_attribute( 'process-link-' . $this->get_id() . '-' . $index, 'class', 'gms-homepage-guarantee__link' ); ?>
									<?php $this->add_link_attributes( 'process-link-' . $this->get_id() . '-' . $index, $item['link_url'] ); ?>
									<a <?php echo $this->get_render_attribute_string( 'process-link-' . $this->get_id() . '-' . $index ); ?>><span><?php echo esc_html( $item['link_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}