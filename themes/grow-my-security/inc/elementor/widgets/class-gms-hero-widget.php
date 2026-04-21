<?php
/**
 * Hero slider widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hero_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-hero';
	}

	public function get_title() {
		return __( 'GMS Hero', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	protected function get_slide_video_url( array $slide ): string {
		$video = $slide['art_video_url'] ?? '';

		if ( is_array( $video ) ) {
			return (string) ( $video['url'] ?? '' );
		}

		return is_string( $video ) ? $video : '';
	}

	protected function get_feature_points( array $slide ): array {
		$points = [];

		foreach ( preg_split( '/\r\n|\r|\n/', (string) ( $slide['feature_points'] ?? '' ) ) as $point ) {
			$point = trim( $point );

			if ( '' !== $point ) {
				$points[] = $point;
			}
		}

		if ( ! empty( $points ) ) {
			return $points;
		}

		return [
			__( 'Trust-led growth', 'grow-my-security' ),
			__( 'Authority positioning', 'grow-my-security' ),
			__( 'Compliance-focused', 'grow-my-security' ),
		];
	}

	protected function normalize_media_url( string $url ): string {
		if ( function_exists( '\gms_normalize_media_url' ) ) {
			return (string) \gms_normalize_media_url( $url );
		}

		return $url;
	}

	protected function render_video_controls( string $modifier = '' ): void {
		$controls_class = 'gms-approved-video-card__controls';

		if ( '' !== $modifier ) {
			$controls_class .= ' ' . $modifier;
		}
		?>
		<div class="<?php echo esc_attr( $controls_class ); ?>">
			<button class="gms-approved-video-card__toggle gms-approved-video-card__toggle--mute" type="button" data-gms-video-mute-toggle aria-pressed="true" aria-label="<?php esc_attr_e( 'Unmute video', 'grow-my-security' ); ?>">
				<span class="gms-approved-video-card__mute-icon" aria-hidden="true"></span>
				<span class="gms-approved-video-card__toggle-label"><?php esc_html_e( 'Muted', 'grow-my-security' ); ?></span>
			</button>
			<button class="gms-approved-video-card__toggle" type="button" data-gms-video-toggle aria-pressed="true" aria-label="<?php esc_attr_e( 'Pause video', 'grow-my-security' ); ?>">
				<span class="gms-approved-video-card__toggle-icon" aria-hidden="true"></span>
				<span class="gms-approved-video-card__toggle-label"><?php esc_html_e( 'Pause', 'grow-my-security' ); ?></span>
			</button>
		</div>
		<?php
	}

	protected function render_primary_button( string $key, array $link, string $text ): void {
		$text = trim( $text );

		if ( '' === $text || ! $this->has_valid_link( $link ) ) {
			return;
		}

		$this->add_render_attribute( $key, 'class', 'gms-homepage-button gms-homepage-button--primary' );
		$this->add_link_attributes( $key, $link );
		?>
		<a <?php echo $this->get_render_attribute_string( $key ); ?>>
			<span><?php echo esc_html( $text ); ?></span>
			<span class="gms-homepage-button__arrow" aria-hidden="true"></span>
		</a>
		<?php
	}

	protected function render_secondary_button( string $key, array $link, string $text ): void {
		$text = trim( $text );

		if ( '' === $text || ! $this->has_valid_link( $link ) ) {
			return;
		}

		$this->add_render_attribute( $key, 'class', 'gms-homepage-button gms-homepage-button--secondary gms-homepage-button--leading' );
		$this->add_link_attributes( $key, $link );
		?>
		<a <?php echo $this->get_render_attribute_string( $key ); ?>>
			<span class="gms-homepage-button__play" aria-hidden="true"></span>
			<span><?php echo esc_html( $text ); ?></span>
		</a>
		<?php
	}

	protected function render_split_media( array $slide, bool $is_first_slide ): void {
		$image_url  = trim( (string) ( $slide['art_image']['url'] ?? '' ) );
		$video_url  = trim( $this->get_slide_video_url( $slide ) );
		$media_type = ! empty( $slide['art_media_type'] ) ? (string) $slide['art_media_type'] : ( $video_url ? 'video' : 'image' );

		if ( 'video' === $media_type && '' !== $video_url ) {
			$video_type = wp_check_filetype( $video_url )['type'] ?? 'video/mp4';
			?>
			<div class="gms-approved-video-card">
				<div class="gms-approved-video-card__frame" data-gms-video-frame>
					<video class="gms-approved-video-card__media gms-hero-art__video" autoplay muted loop playsinline preload="metadata">
						<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $video_type ?: 'video/mp4' ); ?>">
					</video>
					<?php $this->render_video_controls(); ?>
				</div>
			</div>
			<?php
			return;
		}

		if ( '' === $image_url ) {
			return;
		}
		?>
		<div class="gms-approved-art-card">
			<?php $this->render_media_image( $slide['art_image'], [ 'alt' => '', 'fetchpriority' => $is_first_slide ? 'high' : 'low', 'loading' => $is_first_slide ? 'eager' : 'lazy', 'size' => 'large' ] ); ?>
		</div>
		<?php
	}

	protected function render_banner_media( array $slide ): void {
		$image_url  = trim( $this->normalize_media_url( (string) ( $slide['art_image']['url'] ?? '' ) ) );
		$video_url  = trim( $this->get_slide_video_url( $slide ) );
		$media_type = ! empty( $slide['art_media_type'] ) ? (string) $slide['art_media_type'] : ( $video_url ? 'video' : 'image' );

		if ( 'video' === $media_type && '' !== $video_url ) {
			$video_type = wp_check_filetype( $video_url )['type'] ?? 'video/mp4';
			?>
			<div class="gms-approved-intro__banner-media" data-gms-video-frame>
				<video class="gms-approved-intro__banner-video" autoplay muted loop playsinline preload="metadata"<?php echo '' !== $image_url ? ' poster="' . esc_url( $image_url ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $video_type ?: 'video/mp4' ); ?>">
				</video>
				<span class="gms-approved-intro__banner-overlay" aria-hidden="true"></span>
				<span class="gms-approved-intro__banner-glow" aria-hidden="true"></span>
			</div>
			<?php $this->render_video_controls( 'gms-approved-intro__banner-controls' ); ?>
			<?php
			return;
		}

		if ( '' === $image_url ) {
			$image_url = trim( $this->normalize_media_url( (string) ( $slide['image']['url'] ?? '' ) ) );
		}

		if ( '' === $image_url ) {
			return;
		}
		?>
		<div class="gms-approved-intro__banner-media">
			<img class="gms-approved-intro__banner-image" src="<?php echo esc_url( $image_url ); ?>" alt="" loading="eager" decoding="async" fetchpriority="high">
			<span class="gms-approved-intro__banner-overlay" aria-hidden="true"></span>
			<span class="gms-approved-intro__banner-glow" aria-hidden="true"></span>
		</div>
		<?php
	}

	protected function register_controls() {
		$config = \gms_get_demo_config();
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Hero Content', 'grow-my-security' ),
			]
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'layout',
			[
				'label'   => __( 'Layout', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'centered',
				'options' => [
					'centered' => __( 'Centered Slider', 'grow-my-security' ),
					'split'    => __( 'Split Hero', 'grow-my-security' ),
					'banner'   => __( 'Full-Width Banner', 'grow-my-security' ),
				],
			]
		);
		$repeater->add_control(
			'content_align',
			[
				'label'   => __( 'Content Alignment', 'grow-my-security' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => __( 'Left', 'grow-my-security' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'grow-my-security' ),
						'icon'  => 'eicon-text-align-center',
					],
				],
				'default' => 'left',
				'toggle'  => false,
			]
		);
		$repeater->add_control( 'label', [ 'label' => __( 'Label', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'label_block' => true ] );
		$repeater->add_control( 'copy', [ 'label' => __( 'Copy', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'label_block' => true ] );
		$repeater->add_control( 'image', [ 'label' => __( 'Background Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA ] );
		$repeater->add_control(
			'art_media_type',
			[
				'label'   => __( 'Split Hero Media', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => __( 'Image', 'grow-my-security' ),
					'video' => __( 'Self-Hosted Video URL', 'grow-my-security' ),
				],
				'condition' => [
					'layout' => [ 'split', 'banner' ],
				],
			]
		);
		$repeater->add_control(
			'art_image',
			[
				'label' => __( 'Art Image', 'grow-my-security' ),
				'type' => Controls_Manager::MEDIA,
				'condition' => [
					'layout' => [ 'split', 'banner' ],
					'art_media_type' => 'image',
				],
			]
		);
		$repeater->add_control(
			'art_video_url',
			[
				'label' => __( 'Video URL', 'grow-my-security' ),
				'type' => Controls_Manager::URL,
				'placeholder' => home_url( '/wp-content/uploads/industry-video.mp4' ),
				'show_external' => false,
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'layout' => [ 'split', 'banner' ],
					'art_media_type' => 'video',
				],
			]
		);
		$repeater->add_control( 'primary_text', [ 'label' => __( 'Primary CTA Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Explore More' ] );
		$repeater->add_control( 'primary_url', [ 'label' => __( 'Primary CTA URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$repeater->add_control( 'secondary_text', [ 'label' => __( 'Secondary CTA Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'See How Growth Works' ] );
		$repeater->add_control( 'secondary_url', [ 'label' => __( 'Secondary CTA URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$repeater->add_control( 'feature_points', [ 'label' => __( 'Feature Points', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$repeater->add_control( 'scroll_label', [ 'label' => __( 'Scroll Label', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );

		$slides = array_map(
			static function ( $slide ) {
				return [
					'layout'         => 'centered',
					'label'          => $slide['label'],
					'title'          => $slide['title'],
					'copy'           => $slide['copy'],
					'image'          => [ 'url' => $slide['image'] ],
					'art_media_type' => 'image',
					'art_image'      => [ 'url' => '' ],
					'art_video_url'  => [ 'url' => '' ],
					'primary_text'   => $slide['primary_cta'],
					'secondary_text' => $slide['secondary_cta'],
					'feature_points' => '',
					'scroll_label'   => '',
				];
			},
			$config['hero_slides']
		);

		$this->add_control(
			'slides',
			[
				'label'       => __( 'Slides', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $slides,
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();

		$this->add_widget_style_controls( 'hero_section_style', '{{WRAPPER}} .gms-hero, {{WRAPPER}} .gms-homepage-hero, {{WRAPPER}} .gms-approved-intro' );
		$this->add_box_style_controls( 'hero_box', '{{WRAPPER}} .gms-hero, {{WRAPPER}} .gms-homepage-hero, {{WRAPPER}} .gms-approved-intro' );
	}

	protected function render() {
		$settings = $this->get_settings();
		$slides   = $settings['slides'] ?? [];

		if ( empty( $slides ) ) {
			return;
		}

		$first_slide = $slides[0];
		$first_layout = $first_slide['layout'] ?? 'centered';

		if ( in_array( $first_layout, [ 'split', 'banner' ], true ) ) {
			$section_classes = [
				'gms-widget',
				'gms-approved-intro',
				'gms-approved-intro--industries',
			];

			if ( 'banner' === $first_layout ) {
				$section_classes[] = 'gms-approved-intro--industries-banner';
			}

			// For the banner layout, we want to default to center alignment if not explicitly set.
			$alignment = $first_slide['content_align'] ?? ( 'banner' === $first_layout ? 'center' : 'left' );

			if ( 'center' === $alignment ) {
				$section_classes[] = 'gms-approved-intro--align-center';
			}

			if ( 'split' === $first_layout && 'video' === ( $first_slide['art_media_type'] ?? '' ) ) {
				$section_classes[] = 'gms-approved-intro--industries-banner';
			}
			?>
			<section class="<?php echo esc_attr( implode( ' ', array_unique( $section_classes ) ) ); ?>">
				<div class="gms-approved-intro__grid">
					<div class="gms-approved-intro__main">
						<?php if ( ! empty( $first_slide['label'] ) ) : ?>
							<div class="gms-eyebrow"><?php echo esc_html( $first_slide['label'] ); ?></div>
						<?php endif; ?>
						<h1><?php echo esc_html( $first_slide['title'] ?? '' ); ?></h1>
						<?php if ( ! empty( $first_slide['copy'] ) ) : ?>
							<div class="gms-approved-intro__lede"><p><?php echo esc_html( $first_slide['copy'] ); ?></p></div>
						<?php endif; ?>
						<?php if ( ! empty( $first_slide['primary_text'] ) || ! empty( $first_slide['secondary_text'] ) ) : ?>
							<div class="gms-page-hero__actions gms-page-hero__actions--banner">
								<?php $this->render_link( 'hero-primary-' . $this->get_id(), $first_slide['primary_url'] ?? [], $first_slide['primary_text'] ?? '', 'gms-button' ); ?>
								<?php $this->render_link( 'hero-secondary-' . $this->get_id(), $first_slide['secondary_url'] ?? [], $first_slide['secondary_text'] ?? '', 'gms-button-outline' ); ?>
							</div>
						<?php endif; ?>
					</div>
					<?php if ( ! in_array( 'gms-approved-intro--industries-banner', $section_classes, true ) ) : ?>
						<div class="gms-approved-intro__side">
							<?php $this->render_split_media( $first_slide, true ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( in_array( 'gms-approved-intro--industries-banner', $section_classes, true ) ) : ?>
					<div class="gms-approved-intro__banner-stage">
						<?php $this->render_banner_media( $first_slide ); ?>
					</div>
				<?php endif; ?>
			</section>
			<?php
			return;
		}

		?>
		<section class="gms-widget gms-homepage-hero">
			<div class="swiper gms-hero-swiper">
				<div class="swiper-wrapper">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<?php $bg_url = $this->normalize_media_url( (string) ( $slide['image']['url'] ?? '' ) ); ?>
						<div class="swiper-slide">
							<?php if ( '' !== $bg_url ) : ?>
								<div class="gms-homepage-hero__bg" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');"></div>
							<?php endif; ?>
							<div class="gms-homepage-hero__overlay"></div>
							<div class="gms-homepage-shell gms-homepage-shell--hero">
								<div class="gms-homepage-hero__content">
									<div class="gms-homepage-chip gms-homepage-chip--hero">
										<span class="gms-homepage-chip__icon gms-homepage-chip__icon--hero" aria-hidden="true"></span>
										<span><?php echo esc_html( $slide['label'] ?? '' ); ?></span>
									</div>
									<h2><?php echo esc_html( $slide['title'] ?? '' ); ?></h2>
									<p><?php echo esc_html( $slide['copy'] ?? '' ); ?></p>
									<div class="gms-homepage-hero__actions">
										<?php $this->render_primary_button( 'hero-home-primary-' . $this->get_id() . '-' . $index, $slide['primary_url'] ?? [], $slide['primary_text'] ?? '' ); ?>
										<?php $this->render_secondary_button( 'hero-home-secondary-' . $this->get_id() . '-' . $index, $slide['secondary_url'] ?? [], $slide['secondary_text'] ?? '' ); ?>
									</div>
									<ul class="gms-homepage-hero__trust">
										<?php foreach ( $this->get_feature_points( $slide ) as $point ) : ?>
											<li><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><span><?php echo esc_html( $point ); ?></span></li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( count( $slides ) > 1 ) : ?>
					<div class="gms-hero-pagination"></div>
					<div class="gms-hero-button-prev">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z" fill="currentColor"/></svg>
					</div>
					<div class="gms-hero-button-next">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" fill="currentColor"/></svg>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $first_slide['scroll_label'] ) ) : ?>
				<a class="gms-homepage-hero__scroll" href="#problem"><span class="gms-homepage-button__arrow gms-homepage-button__arrow--down" aria-hidden="true"></span><span><?php echo esc_html( $first_slide['scroll_label'] ); ?></span></a>
			<?php endif; ?>
		</section>
		<?php
	}
}
