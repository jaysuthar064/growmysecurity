<?php
/**
 * Services accordion widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Services_Accordion_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-services-accordion';
	}

	public function get_title() {
		return __( 'GMS Services Accordion', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-accordion';
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
				'eyebrow'     => 'Our Services',
				'title'       => 'Building Security Solutions with Intelligent Services',
				'description' => 'If your buyers are technical and your product is complex, you are in the right place.',
			]
		);

		$this->add_control(
			'image',
			[
				'label'   => __( 'Fallback Image', 'grow-my-security' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [ 'url' => \gms_asset( 'assets/images/image-3.png' ) ],
			]
		);
		$this->add_control( 'button_text', [ 'label' => __( 'Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'View All Services' ] );
		$this->add_control( 'button_url', [ 'label' => __( 'Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );

		$repeater = new Repeater();
		$repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$repeater->add_control( 'subtitle', [ 'label' => __( 'Subtitle', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$repeater->add_control( 'summary', [ 'label' => __( 'Summary', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$repeater->add_control( 'benefits', [ 'label' => __( 'Benefits', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$repeater->add_control( 'tags', [ 'label' => __( 'Legacy Tags', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'Fallback benefits. One per line.', 'grow-my-security' ) ] );
		$repeater->add_control( 'image', [ 'label' => __( 'Panel Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA ] );

		$this->add_control(
			'items',
			[
				'label'       => __( 'Items', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'title'    => 'Strategic Marketing',
						'subtitle' => 'SEO, AEO & GEO Mastery',
						'summary'  => 'Dominate the answer engines and traditional search results where your buyers are already asking critical questions.',
						'benefits' => "AEO for Voice & AI search visibility\nGEO to be the source of truth for LLMs\nAuthority-based SEO for long-term equity",
						'image'    => [ 'url' => \gms_asset( 'assets/images/service-strategic-marketing.png' ) ],
					],
					[
						'title'    => 'Precision Lead Gen',
						'subtitle' => 'High-Intent Pathways',
						'summary'  => 'We do not just find leads; we engineer systems that attract decision-makers ready to sign contracts.',
						'benefits' => "ABM for high-value commercial targets\nMulti-channel attribution tracking\nHigh-converting landing pages",
						'image'    => [ 'url' => \gms_asset( 'assets/images/service-precision-leads.png' ) ],
					],
					[
						'title'    => 'Fractional CMO',
						'subtitle' => 'Executive-Level Strategy',
						'summary'  => 'Gain executive-level marketing leadership without the overhead of a full in-house senior team.',
						'benefits' => "Quarterly strategic roadmaps\nBudget optimization and waste reduction\nTeam mentorship and management",
						'image'    => [ 'url' => \gms_asset( 'assets/images/service-fractional-cmo.png' ) ],
					],
					[
						'title'    => 'Authority Web Dev',
						'subtitle' => 'High-Performance Engines',
						'summary'  => 'Your website is your number one sales rep. We build secure platforms that convert traffic into trust.',
						'benefits' => "Industry-specific UX for security\nSecurity hardening for trust-sensitive brands\nIntegrated conversion tracking",
						'image'    => [ 'url' => \gms_asset( 'assets/images/service-authority-webdev.png' ) ],
					],
					[
						'title'    => 'AI Growth Solutions',
						'subtitle' => 'Future-Proof Systems',
						'summary'  => 'Leverage AI systems trained on industry insight to automate workflows and increase market visibility.',
						'benefits' => "Automated human-like outreach\nPredictive market analytics\nCustom AI content engines",
						'image'    => [ 'url' => \gms_asset( 'assets/images/service-ai-growth.png' ) ],
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();

		$this->add_widget_style_controls( 'services_accordion_section_style', '{{WRAPPER}} .gms-widget' );
	}

	private function parse_lines( string $text ): array {
		$lines = [];

		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			$line = trim( $line );

			if ( '' !== $line ) {
				$lines[] = $line;
			}
		}

		return $lines;
	}

	private function get_benefits( array $item ): array {
		$benefits = $this->parse_lines( (string) ( $item['benefits'] ?? '' ) );

		if ( ! empty( $benefits ) ) {
			return $benefits;
		}

		$legacy_benefits = $this->parse_lines( (string) ( $item['tags'] ?? '' ) );

		if ( ! empty( $legacy_benefits ) ) {
			return $legacy_benefits;
		}

		return [
			__( 'Authority-first messaging', 'grow-my-security' ),
			__( 'Buyer-aligned positioning', 'grow-my-security' ),
			__( 'Measured pipeline growth', 'grow-my-security' ),
		];
	}

	protected function render() {
		$settings = $this->get_settings();
		$items    = $settings['items'] ?? [];

		if ( empty( $items ) ) {
			return;
		}
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--services">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
					<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--services" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
					<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
					<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				</div>
				<div class="gms-services-tabs" data-gms-tabs>
					<div class="gms-services-tabs__nav" role="tablist">
						<?php foreach ( $items as $index => $item ) : ?>
							<?php $panel_key = $this->get_id() . '-' . $index; ?>
							<?php $tab_id = 'gms-service-tab-' . $panel_key; ?>
							<?php $panel_id = 'gms-service-panel-' . $panel_key; ?>
							<button class="gms-services-tabs__control<?php echo 0 === $index ? ' is-active' : ''; ?>" id="<?php echo esc_attr( $tab_id ); ?>" type="button" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-tab-target="<?php echo esc_attr( $panel_key ); ?>">
								<span class="gms-services-tabs__control-index">0<?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
								<span class="gms-services-tabs__control-label"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
					<div class="gms-services-tabs__display">
						<?php foreach ( $items as $index => $item ) : ?>
							<?php $panel_key = $this->get_id() . '-' . $index; ?>
							<?php $panel_id = 'gms-service-panel-' . $panel_key; ?>
							<?php $panel_image = ! empty( $item['image']['url'] ) ? $item['image'] : (array) ( $settings['image'] ?? [] ); ?>
							<div class="gms-services-tabs__panel<?php echo 0 === $index ? ' is-active' : ''; ?>" id="<?php echo esc_attr( $panel_id ); ?>" role="tabpanel" data-tab-panel="<?php echo esc_attr( $panel_key ); ?>" <?php echo 0 === $index ? '' : 'hidden'; ?>>
								<div class="gms-services-tabs__grid">
									<div class="gms-services-tabs__info">
										<div class="gms-services-tabs__header">
											<p class="gms-services-tabs__subtitle"><?php echo esc_html( $item['subtitle'] ?? sprintf( __( 'Service %02d', 'grow-my-security' ), $index + 1 ) ); ?></p>
											<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
											<p class="gms-services-tabs__summary"><?php echo esc_html( $item['summary'] ?? '' ); ?></p>
										</div>
										<div class="gms-services-tabs__benefits">
											<h4><?php esc_html_e( 'Key Strategic Benefits:', 'grow-my-security' ); ?></h4>
											<ul>
												<?php foreach ( $this->get_benefits( $item ) as $benefit ) : ?>
													<li><span class="gms-services-tabs__benefit-icon" aria-hidden="true"></span><span><?php echo esc_html( $benefit ); ?></span></li>
												<?php endforeach; ?>
											</ul>
										</div>
										<?php if ( $this->has_valid_link( $settings['button_url'] ?? [] ) && ! empty( $settings['button_text'] ) ) : ?>
											<?php $this->add_render_attribute( 'services-tab-button-' . $this->get_id() . '-' . $index, 'class', 'gms-homepage-button gms-homepage-button--primary' ); ?>
											<?php $this->add_link_attributes( 'services-tab-button-' . $this->get_id() . '-' . $index, $settings['button_url'] ?? [] ); ?>
											<a <?php echo $this->get_render_attribute_string( 'services-tab-button-' . $this->get_id() . '-' . $index ); ?>><span><?php echo esc_html( $settings['button_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
										<?php endif; ?>
									</div>
									<div class="gms-services-tabs__media">
										<div class="gms-services-tabs__image-wrap">
											<?php if ( ! empty( $panel_image['url'] ) ) : ?>
												<?php $this->render_media_image( $panel_image, [ 'alt' => (string) ( $item['title'] ?? '' ), 'size' => 'large' ] ); ?>
											<?php endif; ?>
											<div class="gms-services-tabs__image-overlay"></div>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}