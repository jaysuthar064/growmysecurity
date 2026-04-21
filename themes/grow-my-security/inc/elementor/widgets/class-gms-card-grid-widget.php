<?php
/**
 * Reusable card grid widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Card_Grid_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-card-grid';
	}

	public function get_title() {
		return __( 'GMS Card Grid', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	protected function register_controls() {
		$config = \gms_get_demo_config();

		$this->start_controls_section(
			'section_heading',
			[
				'label' => __( 'Section Heading', 'grow-my-security' ),
			]
		);
		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Industries',
				'title'       => 'Security Verticals Supported',
				'description' => 'Choose the sector you serve and open its dedicated industry page.',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_cards',
			[
				'label' => __( 'Cards', 'grow-my-security' ),
			]
		);

		$repeater = new Repeater();
		$repeater->add_control( 'meta', [ 'label' => __( 'Meta', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$repeater->add_control(
			'icon',
			[
				'label'   => __( 'Icon', 'grow-my-security' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-shield-alt',
					'library' => 'fa-solid',
				],
			]
		);
		$repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$repeater->add_control( 'text', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$repeater->add_control( 'bullets', [ 'label' => __( 'Bullets', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$repeater->add_control( 'button_text', [ 'label' => __( 'Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Learn More' ] );
		$repeater->add_control( 'button_url', [ 'label' => __( 'Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );

		$cards = array_map(
			static function ( $industry ) {
				$clean_title = \gms_clean_industry_name( (string) $industry );

				return [
					'meta'        => '',
					'icon'        => \gms_get_industry_icon( $clean_title ),
					'title'       => $clean_title,
					'text'        => \gms_get_industry_summary( $clean_title ),
					'bullets'     => '',
					'button_text' => 'Learn More',
					'button_url'  => [ 'url' => \gms_get_industry_url( $clean_title ) ],
				];
			},
			$config['industries']
		);

		$this->add_control(
			'cards',
			[
				'label'       => __( 'Cards', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $cards,
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();

		$this->add_widget_style_controls( 'card_grid_section_style', '{{WRAPPER}} .gms-widget' );
		$this->add_box_style_controls( 'card_box', '{{WRAPPER}} .gms-service-card' );
	}

	private function get_card_title( $title ): string {
		if ( is_array( $title ) ) {
			return (string) ( $title['title'] ?? '' );
		}

		return (string) $title;
	}

	private function render_industry_icon( string $clean_title, array $card ): void {
		$user_icon = $card['icon']['value'] ?? '';
		$is_default = ( 'fas fa-shield-alt' === $user_icon || '' === $user_icon );

		// Use dynamic theme icon if it exists and the user hasn't explicitly overridden it with a different icon.
		if ( $is_default && function_exists( '\gms_render_industry_card_icon' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe inline SVG from theme helper.
			echo \gms_render_industry_card_icon( $clean_title );
			return;
		}

		// Otherwise, respect the Elementor icon choice.
		\Elementor\Icons_Manager::render_icon( $card['icon'], [ 'aria-hidden' => 'true' ] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$cards    = $settings['cards'] ?? [];

		if ( empty( $cards ) ) {
			return;
		}

		$settings['eyebrow'] = str_replace( 'Industry', 'Industries', $settings['eyebrow'] ?? 'Industries' );
		$settings['title']   = str_replace( ' Industry', '', $settings['title'] ?? 'Security Verticals Supported' );
		?>
		<section class="gms-widget gms-card-grid-section gms-card-grid-section--industries">
			<?php if ( 'yes' === ( $settings['show_heading'] ?? '' ) ) : ?>
				<?php $this->render_section_heading( $settings ); ?>
			<?php endif; ?>

			<div class="gms-approved-industry-grid">
				<?php foreach ( $cards as $card ) : ?>
					<?php
					$title        = $this->get_card_title( $card['title'] ?? '' );
					$clean_title  = \gms_clean_industry_name( (string) $title );
					$card_url_raw = $card['button_url']['url'] ?? '';
					$button_text  = trim( (string) ( $card['button_text'] ?? '' ) );
					$card_text    = trim( (string) ( $card['text'] ?? '' ) );

					if ( '' === $card_url_raw || '#' === $card_url_raw || false !== strpos( $card_url_raw, '/contact-us/' ) ) {
						$card_url_raw = \gms_get_industry_url( $clean_title );
					}

					if ( '' === $button_text || 'Contact Us' === $button_text ) {
						$button_text = __( 'Learn More', 'grow-my-security' );
					}

					if ( '' === $card_text || false !== strpos( $card_text, 'earn credibility' ) ) {
						$card_text = \gms_get_industry_summary( $clean_title );
					}

					$card_url = esc_url( $card_url_raw );
					?>
					<article class="gms-approved-industry-card">
						<div class="gms-approved-industry-card__inner">
							<a href="<?php echo $card_url; ?>" class="gms-approved-industry-card__link" aria-label="<?php echo esc_attr( $clean_title ); ?>"></a>

							<div class="gms-approved-industry-card__icon" aria-hidden="true">
								<?php $this->render_industry_icon( $clean_title, $card ); ?>
							</div>

							<div class="gms-approved-industry-card__content">
								<h3><?php echo esc_html( $clean_title ); ?></h3>
								<?php if ( '' !== $card_text ) : ?>
									<p><?php echo esc_html( $card_text ); ?></p>
								<?php endif; ?>
							</div>

							<div class="gms-approved-industry-card__action">
								<a href="<?php echo $card_url; ?>" class="gms-approved-industry-card__button" aria-label="<?php echo esc_attr( sprintf( __( 'Learn more about %s', 'grow-my-security' ), $clean_title ) ); ?>">
									<span><?php echo esc_html( $button_text ); ?></span>
									<span class="gms-button__arrow" aria-hidden="true"></span>
								</a>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
