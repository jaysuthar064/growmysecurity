<?php
/**
 * Single Case Study widget.
 *
 * Replicates the layout from single-gms_case_study.php so the Elementor editor
 * preview matches the live site without changing front-end rendering.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Case_Study_Single_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-case-study-single';
	}

	public function get_title() {
		return __( 'GMS Case Study Single', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-single-post';
	}

	protected function register_controls() {
		/* ── Hero ── */
		$this->start_controls_section( 'section_hero', [
			'label' => __( 'Hero', 'grow-my-security' ),
		] );

		$this->add_control( 'hero_title', [
			'label'       => __( 'Title (leave blank = post title)', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => '',
			'label_block' => true,
		] );

		$this->add_control( 'hero_subtitle', [
			'label'       => __( 'Subtitle (leave blank = short desc)', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => '',
			'label_block' => true,
		] );

		$this->add_control( 'hero_bg_image', [
			'label'       => __( 'Background Image (leave blank = post thumbnail)', 'grow-my-security' ),
			'type'        => Controls_Manager::MEDIA,
			'default'     => [ 'url' => '' ],
		] );

		$this->end_controls_section();

		/* ── Content ── */
		$this->start_controls_section( 'section_content', [
			'label' => __( 'Case Study Content', 'grow-my-security' ),
		] );

		$this->add_control( 'challenge', [
			'label'       => __( '01 // The Challenge', 'grow-my-security' ),
			'type'        => Controls_Manager::WYSIWYG,
			'default'     => '',
			'label_block' => true,
		] );

		$this->add_control( 'strategy', [
			'label'       => __( '02 // The Strategy', 'grow-my-security' ),
			'type'        => Controls_Manager::WYSIWYG,
			'default'     => '',
			'label_block' => true,
		] );

		$this->add_control( 'execution', [
			'label'       => __( '03 // Execution', 'grow-my-security' ),
			'type'        => Controls_Manager::WYSIWYG,
			'default'     => '',
			'label_block' => true,
		] );

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( 'val', [
			'label' => __( 'Value (e.g. 92%)', 'grow-my-security' ),
			'type' => Controls_Manager::TEXT,
			'default' => '',
		] );

		$repeater->add_control( 'lab', [
			'label' => __( 'Label (e.g. Reduction)', 'grow-my-security' ),
			'type' => Controls_Manager::TEXT,
			'default' => '',
		] );

		$this->add_control( 'results', [
			'label' => __( '04 // Final Results', 'grow-my-security' ),
			'type' => Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(),
			'default' => [],
			'title_field' => '{{{ val }}} {{{ lab }}}',
		] );

		$this->add_control( 'visual_image', [
			'label' => __( 'Visual Image / Attachment', 'grow-my-security' ),
			'type' => Controls_Manager::MEDIA,
			'default' => [ 'url' => '' ],
		] );

		$this->end_controls_section();

		/* ── CTA ── */
		$this->start_controls_section( 'section_cta', [
			'label' => __( 'CTA', 'grow-my-security' ),
		] );

		$this->add_control( 'cta_title', [
			'label'       => __( 'CTA Title', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Ready to achieve similar results?',
			'label_block' => true,
		] );

		$this->add_control( 'cta_text', [
			'label'       => __( 'CTA Text', 'grow-my-security' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Our tailored cyber-marketing strategies drive conversion through technical authority.',
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

		$this->end_controls_section();

		$this->add_widget_style_controls( 'cs_single_style', '{{WRAPPER}} .gms-cs-report' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		// Robust ID detection for Elementor Editor context
		$post_id = 0;
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$post_id = \Elementor\Plugin::$instance->editor->get_post_id();
		}

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			echo '<p style="color:#999;text-align:center;padding:40px;">Select a Case Study post to preview.</p>';
			return;
		}

		$short_desc = get_post_meta( $post_id, 'gms_cs_short_desc', true );
		
		// Prioritize settings (Elementor) over post meta
		$challenge  = ! empty( $s['challenge'] ) ? $s['challenge'] : get_post_meta( $post_id, 'gms_cs_challenge', true );
		$strategy   = ! empty( $s['strategy'] ) ? $s['strategy'] : get_post_meta( $post_id, 'gms_cs_strategy', true );
		$execution  = ! empty( $s['execution'] ) ? $s['execution'] : get_post_meta( $post_id, 'gms_cs_execution', true );
		
		$visual_url = ! empty( $s['visual_image']['url'] ) 
			? $s['visual_image']['url'] 
			: get_post_meta( $post_id, 'gms_cs_visual_url', true );

		if ( function_exists( '\gms_normalize_case_study_asset_url' ) ) {
			$visual_url = \gms_normalize_case_study_asset_url( (string) $visual_url );
		}

		$results = ! empty( $s['results'] ) ? $s['results'] : [
			[ 'val' => get_post_meta( $post_id, 'gms_cs_result_1_val', true ), 'lab' => get_post_meta( $post_id, 'gms_cs_result_1_lab', true ) ],
			[ 'val' => get_post_meta( $post_id, 'gms_cs_result_2_val', true ), 'lab' => get_post_meta( $post_id, 'gms_cs_result_2_lab', true ) ],
			[ 'val' => get_post_meta( $post_id, 'gms_cs_result_3_val', true ), 'lab' => get_post_meta( $post_id, 'gms_cs_result_3_lab', true ) ],
		];

		$hero_title    = trim( (string) ( $s['hero_title'] ?? '' ) );
		$hero_subtitle = trim( (string) ( $s['hero_subtitle'] ?? '' ) );

		if ( '' === $hero_title ) {
			$hero_title = get_the_title( $post_id );
		}
		if ( '' === $hero_subtitle ) {
			$hero_subtitle = (string) $short_desc;
		}

		$thumb_url = '';
		if ( ! empty( $s['hero_bg_image']['url'] ) ) {
			$thumb_url = $s['hero_bg_image']['url'];
		} else {
			if ( function_exists( '\gms_normalize_case_study_asset_url' ) ) {
				$thumb_url = \gms_normalize_case_study_asset_url( (string) get_the_post_thumbnail_url( $post_id, 'full' ) );
			} else {
				$thumb_url = (string) get_the_post_thumbnail_url( $post_id, 'full' );
			}
		}

		if ( function_exists( '\gms_normalize_case_study_asset_url' ) ) {
			$thumb_url = \gms_normalize_case_study_asset_url( (string) $thumb_url );
		}

		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		?>
		<article class="gms-cs-report gms-cs-intelligence-hub">
			<!-- Scroll Progress Bar -->
			<div class="gms-cs-scroll-progress-container">
				<div class="gms-cs-scroll-progress-bar"></div>
			</div>

			<!-- 1. HERO SECTION -->
			<header class="gms-cs-report-hero" style="background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.9)), url('<?php echo esc_url( $thumb_url ); ?>');">
				<div class="gms-container">
					<div class="gms-cs-report-hero__content">
						<div class="gms-cs-report-eyebrow">
							<a href="<?php echo esc_url( \home_url( '/case-studies/' ) ); ?>" class="gms-cs-report-back">&larr; Back to Portfolio</a>
							<span>• Security Intelligence Report</span>
						</div>
						<h1 class="gms-cs-report-title"><?php echo esc_html( $hero_title ); ?></h1>
						<?php if ( '' !== $hero_subtitle ) : ?>
							<p class="gms-cs-report-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
						<?php elseif ( $is_editor ) : ?>
							<p class="gms-cs-report-subtitle" style="opacity:0.5; font-style:italic;">[Add subtitle in Elementor Hero section]</p>
						<?php endif; ?>
					</div>
				</div>
			</header>

			<!-- 2. THE CHALLENGE -->
			<?php if ( $challenge || $is_editor ) : ?>
			<section class="gms-cs-report-section gms-glass-v2 <?php echo ($challenge) ? 'gms-mt-4' : ''; ?>">
				<div class="gms-container">
					<div class="gms-cs-report-grid">
						<div class="gms-cs-report-label">01 // The Challenge</div>
						<div class="gms-cs-report-body">
							<div class="gms-typography gms-cs-report-text">
								<?php if ( $challenge ) : ?>
									<?php echo wp_kses_post( wpautop( $challenge ) ); ?>
								<?php elseif ( $is_editor ) : ?>
									<div class="gms-editor-placeholder" style="border: 2px dashed rgba(255,255,255,0.2); padding: 40px; text-align: center; opacity: 0.5;">
										Click "Challenge" in sidebar to add content...
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
			<?php endif; ?>

			<!-- 3. THE STRATEGY -->
			<?php if ( $strategy || $is_editor ) : ?>
			<section class="gms-cs-report-section">
				<div class="gms-container">
					<div class="gms-cs-report-grid">
						<div class="gms-cs-report-label">02 // The Strategy</div>
						<div class="gms-cs-report-body">
							<div class="gms-typography gms-cs-report-text">
								<?php if ( $strategy ) : ?>
									<?php echo wp_kses_post( wpautop( $strategy ) ); ?>
								<?php elseif ( $is_editor ) : ?>
									<div class="gms-editor-placeholder" style="border: 2px dashed rgba(255,255,255,0.1); padding: 40px; text-align: center; opacity: 0.5;">
										Add Strategy content in sidebar...
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
			<?php endif; ?>

			<!-- 4. EXECUTION -->
			<section class="gms-cs-report-section">
				<div class="gms-container">
					<div class="gms-cs-report-grid">
						<div class="gms-cs-report-label">03 // Execution</div>
						<div class="gms-cs-report-body">
							<?php if ( $execution ) : ?>
								<div class="gms-typography gms-cs-report-text">
									<?php echo wp_kses_post( wpautop( $execution ) ); ?>
								</div>
							<?php elseif ( $is_editor ) : ?>
								<div class="gms-editor-placeholder" style="border: 2px dashed rgba(255,255,255,0.1); padding: 40px; text-align: center; opacity: 0.5;">
									Add Execution content in sidebar...
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</section>

			<!-- 5. RESULTS -->
			<section class="gms-cs-report-section">
				<div class="gms-container">
					<div class="gms-cs-report-grid">
						<div class="gms-cs-report-label">04 // Final Results</div>
						<div class="gms-cs-report-body">
							<div class="gms-cs-results-dashboard">
								<?php foreach ( $results as $res ) :
									if ( empty( $res['val'] ) ) { continue; }
								?>
									<div class="gms-cs-result-item gms-glass-v2" style="padding: 30px;">
										<span class="gms-cs-result-item__val"><?php echo esc_html( $res['val'] ); ?></span>
										<span class="gms-cs-result-item__lab"><?php echo esc_html( $res['lab'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</section>

			<!-- 6. VISUAL -->
			<?php if ( $visual_url ) : ?>
			<section class="gms-cs-report-section">
				<div class="gms-container">
					<div class="gms-cs-report-visual">
						<img src="<?php echo esc_url( $visual_url ); ?>" alt="Data Visualization">
					</div>
				</div>
			</section>
			<?php endif; ?>

			<!-- 7. CTA -->
			<section class="gms-cs-report-section">
				<div class="gms-container">
					<div class="gms-cs-report-cta-card gms-glass-v2">
						<h2><?php echo esc_html( (string) ( $s['cta_title'] ?? '' ) ); ?></h2>
						<?php if ( ! empty( $s['cta_text'] ) ) : ?>
							<p><?php echo esc_html( $s['cta_text'] ); ?></p>
						<?php endif; ?>
						<?php
						$cta_url  = $s['cta_btn_url']['url'] ?? '/contact/';
						$cta_text = trim( (string) ( $s['cta_btn_text'] ?? '' ) );
						if ( '' !== $cta_text ) :
						?>
							<a href="<?php echo esc_url( $cta_url ); ?>" class="gms-cs-report-cta-btn">
								<span><?php echo esc_html( $cta_text ); ?></span>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</section>
		</article>
		<?php
	}
}
