<?php
/**
 * Story widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Story_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-story';
	}

	public function get_title() {
		return __( 'GMS Story', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-image-rollover';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'grow-my-security' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => __( 'Layout', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'split-cards',
				'options' => [
					'split-cards'   => __( 'Split Cards', 'grow-my-security' ),
					'problem-list'  => __( 'Problem List', 'grow-my-security' ),
					'media-content' => __( 'Media + Content', 'grow-my-security' ),
				],
			]
		);

		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Our Story',
				'title'       => 'Origin Story',
				'description' => 'After spending nearly 20 years climbing the security corporate ladder, Anthony noticed a heightened need for strategic company growth within the security industry.',
			]
		);

		$this->add_control(
			'image',
			[
				'label'   => __( 'Image', 'grow-my-security' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [ 'url' => \gms_asset( 'assets/images/image-3.png' ) ],
			]
		);
		$this->add_control( 'supporting_text', [ 'label' => __( 'Supporting Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$this->add_control( 'copy_secondary', [ 'label' => __( 'Secondary Copy', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$this->add_control( 'highlight_text', [ 'label' => __( 'Highlight Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$this->add_control( 'bullets', [ 'label' => __( 'Bullets', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$this->add_control( 'chips', [ 'label' => __( 'Chips', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$this->add_control( 'button_text', [ 'label' => __( 'Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$this->add_control( 'button_url', [ 'label' => __( 'Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$this->add_control( 'value_label', [ 'label' => __( 'Value Label', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Value' ] );

		$repeater = new Repeater();
		$repeater->add_control( 'title', [ 'label' => __( 'Value Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$repeater->add_control( 'text', [ 'label' => __( 'Value Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$this->add_control(
			'values',
			[
				'label'       => __( 'Value Cards', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[ 'title' => 'Truth Over Tactics', 'text' => 'Integrity-driven strategy designed for high-trust markets.' ],
					[ 'title' => 'Empathy in Action', 'text' => 'Messaging that respects cautious, risk-aware buyers.' ],
					[ 'title' => 'Collective Growth', 'text' => 'Systems that compound visibility and authority over time.' ],
				],
				'title_field' => '{{{ title }}}',
			]
		);
		$this->end_controls_section();

		$this->add_widget_style_controls( 'story_section_style', '{{WRAPPER}} .gms-widget' );
		$this->add_box_style_controls( 'story_box', '{{WRAPPER}} .gms-story-card' );
	}

	private function render_rich_copy( string $text ): void {
		if ( '' === trim( $text ) ) {
			return;
		}

		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $paragraph ) {
			if ( '' === trim( $paragraph ) ) {
				continue;
			}
			echo '<p>' . esc_html( trim( $paragraph ) ) . '</p>';
		}
	}

	private function get_lines( string $text ): array {
		$lines = [];

		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $item ) {
			$item = trim( $item );

			if ( '' !== $item ) {
				$lines[] = $item;
			}
		}

		return $lines;
	}

	private function render_problem_layout( array $settings, array $values ): void {
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--problem">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-problem">
					<div class="gms-homepage-section-heading gms-homepage-section-heading--left gms-homepage-problem__intro">
						<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--problem" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
						<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
						<div class="gms-homepage-problem__copy">
							<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
							<?php $this->render_rich_copy( $settings['supporting_text'] ?? '' ); ?>
							<?php $this->render_rich_copy( $settings['copy_secondary'] ?? '' ); ?>
						</div>
					</div>
					<ol class="gms-homepage-problem__list">
						<?php foreach ( $values as $value ) : ?>
							<li class="gms-homepage-problem__item">
								<div class="gms-homepage-problem__marker" aria-hidden="true"></div>
								<div class="gms-homepage-problem__body">
									<h3><?php echo esc_html( $value['title'] ?? '' ); ?></h3>
									<p><?php echo esc_html( $value['text'] ?? '' ); ?></p>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			</div>
		</section>
		<?php
	}

	private function render_media_layout( array $settings ): void {
		$metrics = $this->get_lines( (string) ( $settings['chips'] ?? '' ) );
		$bullets = $this->get_lines( (string) ( $settings['bullets'] ?? '' ) );
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--solution">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
					<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--solution" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
					<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
					<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
					<?php if ( ! empty( $settings['highlight_text'] ) ) : ?><div class="gms-solution-lead-callout"><?php echo esc_html( $settings['highlight_text'] ); ?></div><?php endif; ?>
				</div>
				<div class="gms-homepage-solution">
					<figure class="gms-homepage-solution__media">
						<?php if ( ! empty( $settings['image']['url'] ) ) : ?>
							<?php $this->render_media_image( $settings['image'], [ 'alt' => '', 'size' => 'large' ] ); ?>
						<?php endif; ?>
					</figure>
					<div class="gms-homepage-solution__content">
						<?php $this->render_rich_copy( $settings['supporting_text'] ?? '' ); ?>
						<?php $this->render_rich_copy( $settings['copy_secondary'] ?? '' ); ?>
						<?php if ( ! empty( $bullets ) ) : ?>
							<ul class="gms-homepage-solution__list">
								<?php foreach ( $bullets as $bullet ) : ?>
									<li><?php echo esc_html( $bullet ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( ! empty( $metrics ) ) : ?>
							<div class="gms-homepage-solution__proof">
								<h3 class="gms-homepage-solution__proof-heading"><?php esc_html_e( 'Proven Outcomes from Our Marketing Systems', 'grow-my-security' ); ?></h3>
								<div class="gms-homepage-solution__metrics">
									<?php foreach ( $metrics as $metric ) : ?>
										<div class="gms-homepage-solution__metric"><?php echo esc_html( $metric ); ?></div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $settings['button_text'] ) ) : ?>
							<?php $this->add_render_attribute( 'story-media-button-' . $this->get_id(), 'class', 'gms-homepage-button gms-homepage-button--primary' ); ?>
							<?php $this->add_link_attributes( 'story-media-button-' . $this->get_id(), $settings['button_url'] ?? [] ); ?>
							<a <?php echo $this->get_render_attribute_string( 'story-media-button-' . $this->get_id() ); ?>><span><?php echo esc_html( $settings['button_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings();
		$layout   = $settings['layout'] ?? 'split-cards';
		$values   = $settings['values'] ?? [];

		if ( 'problem-list' === $layout ) {
			$this->render_problem_layout( $settings, $values );
			return;
		}

		if ( 'media-content' === $layout ) {
			$this->render_media_layout( $settings );
			return;
		}
		?>
		<?php if ( ! empty( $settings['image']['url'] ) ) : ?>
			<figure class="gms-approved-media-panel gms-approved-media-panel--wide">
				<?php $this->render_media_image( $settings['image'], [ 'size' => 'large' ] ); ?>
			</figure>
		<?php endif; ?>
		<section class="gms-widget gms-approved-section gms-approved-section--mission">
			<div class="gms-approved-section__intro">
				<?php if ( ! empty( $settings['eyebrow'] ) ) : ?><div class="gms-eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></div><?php endif; ?>
				<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
			</div>
			<div class="gms-approved-section__content">
				<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				<?php $this->render_rich_copy( $settings['supporting_text'] ?? '' ); ?>
				<?php if ( ! empty( $settings['highlight_text'] ) ) : ?><div class="gms-approved-quote"><?php echo esc_html( $settings['highlight_text'] ); ?></div><?php endif; ?>
				<div class="gms-approved-value-grid">
					<?php foreach ( $values as $value ) : ?>
						<article class="gms-approved-value-card">
							<h3><?php echo esc_html( $value['title'] ?? '' ); ?></h3>
							<p><?php echo esc_html( $value['text'] ?? '' ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
				<?php $this->render_link( 'story-button-' . $this->get_id(), $settings['button_url'] ?? [], $settings['button_text'] ?? '', 'gms-button' ); ?>
			</div>
		</section>
		<?php
	}
}