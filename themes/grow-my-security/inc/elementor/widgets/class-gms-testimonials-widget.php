<?php
/**
 * Testimonials widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Testimonials_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-testimonials';
	}

	public function get_title() {
		return __( 'GMS Testimonials', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-testimonial-carousel';
	}

	protected function register_controls() {
		$config = \gms_get_demo_config();

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
				'default' => 'grid',
				'options' => [
					'grid'     => __( 'Grid', 'grow-my-security' ),
					'featured' => __( 'Featured Quote', 'grow-my-security' ),
				],
			]
		);
		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Testimonials',
				'title'       => 'Who you work with matters.',
				'description' => 'Trust-first outcomes for security brands that need authority before the first conversation.',
			]
		);
		$this->add_control( 'background_word', [ 'label' => __( 'Background Word', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Testimonials' ] );

		$repeater = new Repeater();
		$repeater->add_control(
			'logo',
			[
				'label' => __( 'Logo', 'grow-my-security' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);
		$repeater->add_control( 'quote', [ 'label' => __( 'Quote', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$repeater->add_control( 'name', [ 'label' => __( 'Name', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$repeater->add_control( 'role', [ 'label' => __( 'Role', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );

		$this->add_control(
			'items',
			[
				'label'       => __( 'Testimonials', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $config['testimonials'],
				'title_field' => '{{{ name }}}',
			]
		);
		$this->end_controls_section();

		$this->add_widget_style_controls( 'testimonials_section_style', '{{WRAPPER}} .gms-widget' );
		$this->add_box_style_controls( 'testimonials_box', '{{WRAPPER}} .gms-testimonial-card' );
	}

	private function render_stars(): void {
		for ( $i = 0; $i < 5; $i++ ) {
			echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="var(--gms-home-accent)"/></svg>';
		}
	}

	private function render_logo( array $item ): void {
		$logo = is_array( $item['logo'] ?? null ) ? $item['logo'] : [];
		$url  = trim( (string) ( $logo['url'] ?? '' ) );

		if ( '' !== $url ) {
			$this->render_media_image(
				$logo,
				[
					'class' => 'gms-testimonial-card__logo-image',
					'alt'   => (string) ( $item['name'] ?? get_bloginfo( 'name' ) ),
					'size'  => 'medium',
				]
			);
			return;
		}

		if ( function_exists( '\gms_get_logo_markup' ) ) {
			echo str_replace( 'gms-logo-image', 'gms-testimonial-card__logo-image', \gms_get_logo_markup( 'footer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	protected function render() {
		$settings = $this->get_settings();
		$items    = $settings['items'] ?? [];

		if ( empty( $items ) ) {
			return;
		}
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--testimonials">
			<div class="gms-homepage-testimonials__glow" aria-hidden="true"></div>
			<div class="gms-homepage-shell">
				<p class="gms-homepage-testimonials__watermark" aria-hidden="true"><?php echo esc_html( $settings['background_word'] ?? 'Testimonials' ); ?></p>
				<div class="swiper gms-testimonials-swiper">
					<div class="swiper-wrapper">
						<?php foreach ( $items as $item ) : ?>
							<div class="swiper-slide">
								<article class="gms-testimonial-card">
									<div class="gms-testimonial-card__logo">
										<?php $this->render_logo( (array) $item ); ?>
									</div>
									<div class="gms-testimonial-card__content"><p><?php echo esc_html( $item['quote'] ?? '' ); ?></p></div>
									<div class="gms-testimonial-card__footer">
										<div class="gms-testimonial-card__stars" aria-hidden="true"><?php $this->render_stars(); ?></div>
										<div class="gms-testimonial-card__author">
											<p class="gms-testimonial-card__name"><?php echo esc_html( $item['name'] ?? '' ); ?></p>
											<?php if ( ! empty( $item['role'] ) ) : ?><p class="gms-testimonial-card__role"><?php echo esc_html( $item['role'] ); ?></p><?php endif; ?>
										</div>
									</div>
								</article>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="gms-testimonials-controls">
						<button class="gms-testimonials-button-prev" aria-label="<?php esc_attr_e( 'Previous testimonial', 'grow-my-security' ); ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
						<div class="gms-testimonials-swiper-pagination"></div>
						<button class="gms-testimonials-button-next" aria-label="<?php esc_attr_e( 'Next testimonial', 'grow-my-security' ); ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
