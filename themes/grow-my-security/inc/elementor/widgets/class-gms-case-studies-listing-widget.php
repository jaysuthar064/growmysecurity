<?php
/**
 * Case Studies listing page widget.
 *
 * Replicates the layout from page-case-studies.php so the Elementor editor
 * preview matches the live site without changing front-end rendering.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Case_Studies_Listing_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-case-studies-listing';
	}

	public function get_title() {
		return __( 'GMS Case Studies Listing', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function register_controls() {
		/* ── Hero Section ── */
		$this->start_controls_section( 'section_hero', [
			'label' => __( 'Hero', 'grow-my-security' ),
		] );

		$this->add_control( 'hero_eyebrow', [
			'label'   => __( 'Eyebrow', 'grow-my-security' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Intelligence Reports',
		] );

		$this->add_control( 'hero_title', [
			'label'       => __( 'Title', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Case Studies',
			'label_block' => true,
		] );

		$this->add_control( 'hero_subtitle', [
			'label'       => __( 'Subtitle', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Real results from cybersecurity marketing strategies',
			'label_block' => true,
		] );

		$this->end_controls_section();

		/* ── Query/Source Section ── */
		$this->start_controls_section( 'section_source', [
			'label' => __( 'Source & Cards', 'grow-my-security' ),
		] );

		$this->add_control( 'source', [
			'label'   => __( 'Source', 'grow-my-security' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'dynamic',
			'options' => [
				'dynamic' => __( 'Dynamic (Auto-fetch Case Study Posts)', 'grow-my-security' ),
				'manual'  => __( 'Manual (Custom Cards below)', 'grow-my-security' ),
			],
		] );

		$this->add_control( 'posts_per_page', [
			'label'   => __( 'Posts Per Page (Dynamic mode)', 'grow-my-security' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 12,
			'min'     => 1,
			'max'     => 50,
			'condition' => [ 'source' => 'dynamic' ],
		] );

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( 'title', [
			'label'       => __( 'Title', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Case Study Title',
			'label_block' => true,
		] );

		$repeater->add_control( 'excerpt', [
			'label'       => __( 'Excerpt', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Short description of the case study...',
			'label_block' => true,
		] );

		$repeater->add_control( 'image', [
			'label'   => __( 'Card Image', 'grow-my-security' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => [ 'url' => '' ],
		] );

		$repeater->add_control( 'badge', [
			'label'   => __( 'Badge (e.g. FINTECH)', 'grow-my-security' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
		] );

		$repeater->add_control( 'val', [
			'label'   => __( 'Metric Value (e.g. +340%)', 'grow-my-security' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
		] );

		$repeater->add_control( 'lab', [
			'label'   => __( 'Metric Label (e.g. Growth)', 'grow-my-security' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
		] );

		$repeater->add_control( 'link', [
			'label'   => __( 'Link URL', 'grow-my-security' ),
			'type'    => Controls_Manager::URL,
			'default' => [ 'url' => '#' ],
		] );

		$this->add_control( 'cards', [
			'label'       => __( 'Manual Cards', 'grow-my-security' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [],
			'title_field' => '{{{ title }}}',
			'condition'   => [ 'source' => 'manual' ],
		] );

		$this->end_controls_section();

		/* ── CTA Section ── */
		$this->start_controls_section( 'section_cta', [
			'label' => __( 'CTA', 'grow-my-security' ),
		] );

		$this->add_control( 'cta_title', [
			'label'       => __( 'CTA Title', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Ready to build trust that drives revenue?',
			'label_block' => true,
		] );

		$this->add_control( 'cta_btn_text', [
			'label'   => __( 'Button Text', 'grow-my-security' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Schedule a Free Consultation',
		] );

		$this->add_control( 'cta_btn_url', [
			'label'   => __( 'Button URL', 'grow-my-security' ),
			'type'    => Controls_Manager::URL,
			'default' => [ 'url' => \home_url( '/contact-us/' ) ],
		] );

		$this->add_control( 'cta_image', [
			'label'   => __( 'CTA Image', 'grow-my-security' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => [ 'url' => \get_theme_file_uri( 'assets/images/case-studies/cs-revenue.png' ) ],
		] );

		$this->add_control( 'badge_1_val', [ 'label' => __( 'Badge 1 Value', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => '+320%' ] );
		$this->add_control( 'badge_1_label', [ 'label' => __( 'Badge 1 Label', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Revenue Growth' ] );
		$this->add_control( 'badge_2_val', [ 'label' => __( 'Badge 2 Value', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'ZERO' ] );
		$this->add_control( 'badge_2_label', [ 'label' => __( 'Badge 2 Label', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Breaches Occurred' ] );
		$this->add_control( 'badge_3_val', [ 'label' => __( 'Badge 3 Value', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => '45%' ] );
		$this->add_control( 'badge_3_label', [ 'label' => __( 'Badge 3 Label', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Efficiency Gain' ] );

		$this->end_controls_section();

		$this->add_widget_style_controls( 'cs_listing_style', '{{WRAPPER}} .gms-case-studies-page' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$hero_eyebrow  = trim( (string) ( $s['hero_eyebrow'] ?? '' ) );
		$hero_title    = trim( (string) ( $s['hero_title'] ?? '' ) );
		$hero_subtitle = trim( (string) ( $s['hero_subtitle'] ?? '' ) );
		
		$source        = $s['source'] ?? 'dynamic';
		$per_page      = max( 1, (int) ( $s['posts_per_page'] ?? 12 ) );
		?>
		<div class="gms-page-shell gms-case-studies-page">
			<div class="gms-page-container">

				<!-- 1. HERO SECTION -->
				<section class="gms-cs-hero-alt">
					<div class="gms-container">
						<div class="gms-cs-hero-alt__content">
							<?php if ( '' !== $hero_eyebrow ) : ?>
								<span class="gms-cs-single-eyebrow"><?php echo esc_html( $hero_eyebrow ); ?></span>
							<?php endif; ?>
							<h1 class="gms-cs-hero-alt__title"><?php echo esc_html( $hero_title ); ?></h1>
							<?php if ( '' !== $hero_subtitle ) : ?>
								<p class="gms-cs-hero-alt__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</section>

				<!-- 2. CASE STUDIES GRID -->
				<section class="gms-cs-grid-section">
					<div class="gms-container">
						<div class="gms-cs-grid">
							<?php
							if ( 'dynamic' === $source ) :
								$cs_query = new \WP_Query( [
									'post_type'      => 'gms_case_study',
									'posts_per_page' => $per_page,
									'post_status'    => 'publish',
								] );

								if ( $cs_query->have_posts() ) :
									while ( $cs_query->have_posts() ) :
										$cs_query->the_post();
										$metric_val = get_post_meta( get_the_ID(), 'gms_cs_metric_value', true );
										$metric_lab = get_post_meta( get_the_ID(), 'gms_cs_metric_label', true );
										$short_desc = get_post_meta( get_the_ID(), 'gms_cs_short_desc', true );
										$badge      = get_post_meta( get_the_ID(), 'gms_cs_badge', true );
										$image_url  = function_exists( '\gms_normalize_case_study_asset_url' )
											? \gms_normalize_case_study_asset_url( (string) get_post_meta( get_the_ID(), 'gms_cs_image_url', true ) )
											: (string) get_post_meta( get_the_ID(), 'gms_cs_image_url', true );

										$thumb_url = '';
										if ( has_post_thumbnail() ) {
											$thumb_url = function_exists( '\gms_normalize_case_study_asset_url' )
												? \gms_normalize_case_study_asset_url( (string) get_the_post_thumbnail_url( get_the_ID(), 'large' ) )
												: (string) get_the_post_thumbnail_url( get_the_ID(), 'large' );
										}
										
										$card_title = get_the_title();
										$card_link  = get_the_permalink();

										$this->render_card_html( $card_title, $short_desc, $thumb_url ?: $image_url, $badge, $metric_val, $metric_lab, $card_link );
									endwhile;
									wp_reset_postdata();
								else :
									echo '<p class="gms-no-posts">' . esc_html__( 'No case studies found.', 'grow-my-security' ) . '</p>';
								endif;
							else :
								// Manual mode
								$cards = $s['cards'] ?? [];
								if ( ! empty( $cards ) ) :
									foreach ( $cards as $card ) :
										$this->render_card_html(
											$card['title'],
											$card['excerpt'],
											$card['image']['url'] ?? '',
											$card['badge'],
											$card['val'],
											$card['lab'],
											$card['link']['url'] ?? '#'
										);
									endforeach;
								else :
									echo '<p class="gms-no-posts">' . esc_html__( 'No manual cards added yet.', 'grow-my-security' ) . '</p>';
								endif;
							endif;
							?>
						</div>
					</div>
				</section>

				<!-- 3. CTA SECTION -->
				<section class="gms-cs-cta-section">
					<div class="gms-cs-cta-container">
						<div class="gms-cs-cta-content">
							<h2><?php echo esc_html( (string) ( $s['cta_title'] ?? '' ) ); ?></h2>
							<?php
							$cta_url  = $s['cta_btn_url']['url'] ?? '/contact/';
							$cta_text = trim( (string) ( $s['cta_btn_text'] ?? '' ) );
							if ( '' !== $cta_text ) :
							?>
								<a href="<?php echo esc_url( $cta_url ); ?>" class="gms-cs-cta-btn">
									<span><?php echo esc_html( $cta_text ); ?></span>
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
								</a>
							<?php endif; ?>
						</div>
						<div class="gms-cs-cta-image">
							<?php
							$badges = [
								[ 'pos' => '1', 'val' => $s['badge_1_val'] ?? '+320%', 'lab' => $s['badge_1_label'] ?? 'Revenue Growth' ],
								[ 'pos' => '2', 'val' => $s['badge_2_val'] ?? 'ZERO',  'lab' => $s['badge_2_label'] ?? 'Breaches Occurred' ],
								[ 'pos' => '3', 'val' => $s['badge_3_val'] ?? '45%',   'lab' => $s['badge_3_label'] ?? 'Efficiency Gain' ],
							];
							foreach ( $badges as $b ) :
								if ( '' !== trim( (string) $b['val'] ) ) :
							?>
								<div class="gms-floating-badge" data-pos="<?php echo esc_attr( $b['pos'] ); ?>">
									<span><?php echo esc_html( $b['val'] ); ?></span>
									<span><?php echo esc_html( $b['lab'] ); ?></span>
								</div>
							<?php endif; endforeach; ?>

							<?php $this->render_media_image( $s['cta_image'] ?? [], [ 'alt' => 'Growth and Revenue visual', 'loading' => 'lazy' ] ); ?>
						</div>
					</div>
				</section>

			</div>
		</div>
		<?php
	}

	protected function render_card_html( $title, $excerpt, $image_url, $badge, $metric_val, $metric_lab, $link ) {
		?>
		<article class="gms-cs-card">
			<a href="<?php echo esc_url( $link ); ?>" class="gms-cs-card-overlay-link" aria-label="<?php echo esc_attr( $title ); ?>"></a>

			<?php if ( ! empty( $badge ) ) : ?>
				<div class="gms-cs-card__badge"><?php echo esc_html( $badge ); ?></div>
			<?php endif; ?>

			<div class="gms-cs-card__image">
				<?php if ( ! empty( $image_url ) ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" decoding="async" loading="lazy" fetchpriority="low">
				<?php endif; ?>

				<?php if ( ! empty( $metric_val ) ) : ?>
					<div class="gms-cs-card__metric-float">
						<span class="gms-cs-card__metric-value"><?php echo esc_html( $metric_val ); ?></span>
						<span class="gms-cs-card__metric-label"><?php echo esc_html( $metric_lab ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="gms-cs-card__content">
				<h3><?php echo esc_html( $title ); ?></h3>
				<p><?php echo esc_html( $excerpt ); ?></p>
				<div class="gms-cs-card__actions">
					<span class="gms-cs-card__link">
						<?php esc_html_e( 'Read Case Study', 'grow-my-security' ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</span>
				</div>
			</div>
		</article>
		<?php
	}
}
