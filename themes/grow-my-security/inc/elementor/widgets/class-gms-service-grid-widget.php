<?php
/**
 * Service archive grid widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Service_Grid_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-service-grid';
	}

	public function get_title() {
		return __( 'GMS Service Grid', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
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
			'data_type',
			[
				'label'   => __( 'Data Type', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'services'   => __( 'Services', 'grow-my-security' ),
					'industries' => __( 'Industries', 'grow-my-security' ),
				],
				'default' => 'services',
			]
		);

		$this->add_control(
			'show_heading',
			[
				'label'        => __( 'Show Heading', 'grow-my-security' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);


		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Services',
				'title'       => 'Creative services built for impact',
				'description' => 'A full stack of trust-led growth services designed for security brands.',
			]
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'icon_type',
			[
				'label'   => __( 'Icon', 'grow-my-security' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-shield-alt',
					'library' => 'fa-solid',
				],
			]
		);
		$repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'label_block' => true ] );

		$repeater->add_control( 'text', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$repeater->add_control( 'bullets', [ 'label' => __( 'Bullets', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$repeater->add_control( 'button_text', [ 'label' => __( 'Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Learn More' ] );
		$repeater->add_control( 'button_url', [ 'label' => __( 'Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );

		$defaults_services = array_map(
			static function ( $service ) {
				return [
					'icon_type'   => [ 'value' => 'fas fa-shield-alt', 'library' => 'fa-solid' ],
					'title'       => $service['title'],
					'text'        => $service['description'],
					'bullets'     => implode( "\n", $service['bullets'] ),
					'button_text' => 'Learn More',
					'button_url'  => [ 'url' => home_url( '/services/' . $service['slug'] . '/' ) ],
				];
			},
			$config['services']
		);

		$defaults_industries = array_map(
			static function ( $industry ) {
				return [
					'icon_type'   => \gms_get_industry_icon( (string) $industry ),
					'title'       => $industry,
					'text'        => 'We help technical teams earn credibility and belief, turning expertise into visible trust.',
					'bullets'     => "Strategic Positioning\nAuthority Architecture\nDemand Generation",
					'button_text' => 'Learn More',
					'button_url'  => [ 'url' => '#' ],
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
				'default'     => $defaults_services, // Default to services, but can be switched
				'title_field' => '{{{ title }}}',
			]
		);


		$this->end_controls_section();

		$this->add_widget_style_controls( 'service_grid_section_style', '{{WRAPPER}} .gms-widget' );
	}

	private function get_card_title( $title ): string {
		if ( is_array( $title ) ) {
			return (string) ( $title['title'] ?? '' );
		}

		return (string) $title;
	}

	private function get_card_lines( string $text ): array {
		$lines = [];

		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			$line = trim( $line );

			if ( '' !== $line ) {
				$lines[] = $line;
			}
		}

		return $lines;
	}

	private function get_card_slug( array $card ): string {
		$card_url = (string) ( $card['button_url']['url'] ?? '' );

		if ( '' === $card_url ) {
			return '';
		}

		$path = (string) wp_parse_url( $card_url, PHP_URL_PATH );

		if ( '' === $path ) {
			return '';
		}

		$segments = array_values(
			array_filter(
				explode( '/', trim( $path, '/' ) ),
				static function ( $segment ) {
					return '' !== $segment;
				}
			)
		);

		if ( empty( $segments ) ) {
			return '';
		}

		return sanitize_title( (string) end( $segments ) );
	}

	private function render_service_icon( array $card, string $slug ): void {
		if ( '' !== $slug && function_exists( 'gms_render_service_card_icon' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe inline SVG from theme helper.
			echo gms_render_service_card_icon( $slug );
			return;
		}

		if ( ! empty( $card['icon_type']['value'] ) ) {
			\Elementor\Icons_Manager::render_icon( $card['icon_type'], [ 'aria-hidden' => 'true' ] );
		}
	}

	private function render_service_cards( array $settings ): void {
		?>
		<div class="gms-service-grid__items">
			<?php foreach ( (array) ( $settings['cards'] ?? [] ) as $card ) : ?>
				<?php
				$title        = $this->get_card_title( $card['title'] ?? '' );
				$card_url_raw = (string) ( $card['button_url']['url'] ?? '' );
				$card_url     = '' !== $card_url_raw ? esc_url( $card_url_raw ) : '#';
				$slug         = $this->get_card_slug( $card );
				$bullets      = $this->get_card_lines( (string) ( $card['bullets'] ?? '' ) );
				$card_classes = [ 'gms-service-tile', 'gms-service-tile--premium' ];

				if ( '' !== $slug ) {
					$card_classes[] = 'gms-service-tile--' . sanitize_html_class( $slug );
				}

				$tag = (string) ( $card['meta'] ?? '' );

				if ( '' === $tag && '' !== $slug && function_exists( 'gms_get_service_card_tag' ) ) {
					$tag = gms_get_service_card_tag( $slug );
				}
				?>
				<article class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
					<a class="gms-service-tile__overlay" href="<?php echo $card_url; ?>" aria-label="<?php echo esc_attr( $title ); ?>"></a>
					<div class="gms-service-tile__surface">
						<div class="gms-service-tile__top">
							<?php if ( '' !== $tag ) : ?>
								<span class="gms-service-tile__tag"><?php echo esc_html( $tag ); ?></span>
							<?php endif; ?>
							<div class="gms-service-tile__icon" aria-hidden="true">
								<?php $this->render_service_icon( $card, $slug ); ?>
							</div>
						</div>
						<div class="gms-service-tile__body">
							<?php if ( '' !== $title ) : ?>
								<h3><?php echo esc_html( $title ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $card['text'] ) ) : ?>
								<p><?php echo esc_html( (string) $card['text'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $bullets ) ) : ?>
								<ul class="gms-service-tile__list">
									<?php foreach ( $bullets as $bullet ) : ?>
										<li><span><?php echo esc_html( $bullet ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $card['button_text'] ) ) : ?>
							<div class="gms-service-tile__footer">
								<a class="gms-service-tile__button" href="<?php echo $card_url; ?>">
									<span><?php echo esc_html( (string) $card['button_text'] ); ?></span>
									<span class="gms-service-tile__button-arrow" aria-hidden="true"></span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
	}

	private function render_industry_cards( array $settings ): void {
		?>
		<div class="gms-service-grid__items">
			<?php foreach ( (array) ( $settings['cards'] ?? [] ) as $card ) : ?>
				<?php
				$card_url = ! empty( $card['button_url']['url'] ) ? esc_url( $card['button_url']['url'] ) : '#';
				$title    = $this->get_card_title( $card['title'] ?? '' );
				?>
				<article class="gms-service-tile gms-industry-card">
					<div class="gms-industry-card__inner">
						<a href="<?php echo $card_url; ?>" class="gms-stretched-link" aria-label="<?php echo esc_attr( $title ); ?>"></a>
						<div class="gms-industry-card__icon-wrap">
							<div class="gms-industry-card__icon" aria-hidden="true">
								<?php
								$icon = \gms_get_industry_icon( $title );

								if ( 'fas fa-shield-alt' === $icon['value'] && ! empty( $card['icon_type']['value'] ) ) {
									$icon = $card['icon_type'];
								}

								\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
								?>
							</div>
						</div>
						<div class="gms-industry-card__body">
							<?php if ( ! empty( $card['meta'] ) ) : ?>
								<div class="gms-industry-card__meta"><?php echo esc_html( str_replace( [ 'Industry', 'Industries' ], '', (string) $card['meta'] ) ); ?></div>
							<?php endif; ?>
							<h3 class="gms-industry-card__title"><?php echo esc_html( str_replace( [ ' Industry', ' Industries' ], '', $title ) ); ?></h3>
							<p class="gms-industry-card__text"><?php echo esc_html( (string) ( $card['text'] ?? '' ) ); ?></p>
							<?php if ( ! empty( $card['bullets'] ) ) : ?>
								<ul class="gms-industry-card__list">
									<?php foreach ( $this->get_card_lines( (string) $card['bullets'] ) as $line ) : ?>
										<li><?php echo esc_html( $line ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $card['button_text'] ) ) : ?>
							<div class="gms-industry-card__action">
								<span class="gms-button-outline"><?php echo esc_html( (string) $card['button_text'] ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$data_type = $settings['data_type'] ?? 'services';
		?>
		<section class="gms-widget gms-service-grid-section">
			<?php if ( 'yes' === ( $settings['show_heading'] ?? '' ) ) : ?>
				<?php
				if ( 'industries' === $data_type ) {
					$settings['eyebrow'] = str_replace( 'Industry', 'Verticals', $settings['eyebrow'] ?? 'Verticals' );
					$settings['title']   = str_replace( ' Industry', '', $settings['title'] ?? '' );
				}

				$this->render_section_heading( $settings );
				?>
			<?php endif; ?>

			<?php if ( 'industries' === $data_type ) : ?>
				<?php $this->render_industry_cards( $settings ); ?>
			<?php else : ?>
				<?php $this->render_service_cards( $settings ); ?>
			<?php endif; ?>
		</section>
		<?php
	}
}
