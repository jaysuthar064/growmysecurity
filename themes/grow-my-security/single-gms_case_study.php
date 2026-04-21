<?php
/**
 * The template for displaying single Case Studies.
 * Fully Dynamic "Intelligence Hub" with Elementor Hybrid Support.
 *
 * @package GrowMySecurity
 */

get_header();

$gms_cs_single_has_elementor = function_exists( 'gms_post_has_elementor_content' )
	? gms_post_has_elementor_content( get_the_ID() )
	: (bool) get_post_meta( get_the_ID(), '_elementor_edit_mode', true );

/*
 * When Elementor controls this post (widget synced), let the widget render
 * the full case study layout. Skip the PHP template to avoid duplication.
 */
if ( $gms_cs_single_has_elementor ) :
	while ( have_posts() ) :
		the_post();
	?>
		<div class="gms-page-content--elementor gms-cs-single-elementor">
			<?php the_content(); ?>
		</div>
	<?php
	endwhile;
	get_footer();
	return;
endif;

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();

	// Retrieve Custom Meta Fields
	$short_desc   = get_post_meta( $post_id, 'gms_cs_short_desc', true );
	$challenge    = get_post_meta( $post_id, 'gms_cs_challenge', true );
	$strategy     = get_post_meta( $post_id, 'gms_cs_strategy', true );
	$execution    = get_post_meta( $post_id, 'gms_cs_execution', true );
    $visual_url   = gms_normalize_case_study_asset_url( (string) get_post_meta( $post_id, 'gms_cs_visual_url', true ) );

    // Results Dashboard Data
    $results = [
        [ 'val' => get_post_meta( $post_id, 'gms_cs_result_1_val', true ), 'lab' => get_post_meta( $post_id, 'gms_cs_result_1_lab', true ) ],
        [ 'val' => get_post_meta( $post_id, 'gms_cs_result_2_val', true ), 'lab' => get_post_meta( $post_id, 'gms_cs_result_2_lab', true ) ],
        [ 'val' => get_post_meta( $post_id, 'gms_cs_result_3_val', true ), 'lab' => get_post_meta( $post_id, 'gms_cs_result_3_lab', true ) ],
    ];
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'gms-cs-report gms-cs-intelligence-hub' ); ?>>
		
        <!-- Scroll Progress Bar -->
        <div class="gms-cs-scroll-progress-container">
            <div class="gms-cs-scroll-progress-bar"></div>
        </div>

		<!-- 1. HERO SECTION -->
		<header class="gms-cs-report-hero" style="background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.9)), url('<?php echo esc_url( gms_normalize_case_study_asset_url( (string) get_the_post_thumbnail_url( $post_id, 'full' ) ) ); ?>');">
			<div class="gms-container">
				<div class="gms-cs-report-hero__content">
					<div class="gms-cs-report-eyebrow">
                        <a href="<?php echo esc_url( home_url( '/case-studies/' ) ); ?>" class="gms-cs-report-back">&larr; Back to Portfolio</a>
                        <span>• Security Intelligence Report</span>
                    </div>
					<h1 class="gms-cs-report-title"><?php the_title(); ?></h1>
					<?php if ( $short_desc ) : ?>
						<p class="gms-cs-report-subtitle"><?php echo esc_html( $short_desc ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<!-- 2. THE CHALLENGE -->
		<?php if ( $challenge ) : ?>
		<section class="gms-cs-report-section gms-glass-v2 gms-mt-4">
			<div class="gms-container">
				<div class="gms-cs-report-grid">
					<div class="gms-cs-report-label">01 // The Challenge</div>
					<div class="gms-cs-report-body">
						<div class="gms-typography gms-cs-report-text">
							<?php echo wp_kses_post( wpautop( $challenge ) ); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<!-- 3. THE STRATEGY -->
		<?php if ( $strategy ) : ?>
		<section class="gms-cs-report-section">
			<div class="gms-container">
				<div class="gms-cs-report-grid">
					<div class="gms-cs-report-label">02 // The Strategy</div>
					<div class="gms-cs-report-body">
						<div class="gms-typography gms-cs-report-text">
							<?php echo wp_kses_post( wpautop( $strategy ) ); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<!-- 4. EXECUTION (Elementor Component Area) -->
		<section class="gms-cs-report-section">
			<div class="gms-container">
				<div class="gms-cs-report-grid">
					<div class="gms-cs-report-label">03 // Execution</div>
					<div class="gms-cs-report-body">
						<div class="gms-cs-report-elementor-area">
							<?php the_content(); ?>
						</div>
						<?php if ( $execution ) : ?>
							<div class="gms-typography gms-cs-report-text gms-mt-4">
								<?php echo wp_kses_post( wpautop( $execution ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

		<!-- 5. RESULTS (Interactive Dashboard) -->
		<section class="gms-cs-report-section">
			<div class="gms-container">
				<div class="gms-cs-report-grid">
					<div class="gms-cs-report-label">04 // Final Results</div>
					<div class="gms-cs-report-body">
						<div class="gms-cs-results-dashboard">
							<?php foreach ( $results as $res ) : if ( empty( $res['val'] ) ) continue; ?>
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

		<!-- 6. VISUAL SECTION (Interactive 3D Visualize) -->
		<?php if ( $visual_url ) : ?>
		<section class="gms-cs-report-section">
			<div class="gms-container">
				<div class="gms-cs-report-visual">
					<img src="<?php echo esc_url( $visual_url ); ?>" alt="Data Visualization">
				</div>
			</div>
		</section>
		<?php endif; ?>

		<!-- 7. CTA SECTION -->
		<section class="gms-cs-report-section">
			<div class="gms-container">
				<div class="gms-cs-report-cta-card gms-glass-v2">
					<h2>Ready to achieve similar results?</h2>
					<p>Our tailored cyber-marketing strategies drive conversion through technical authority.</p>
					<a href="/contact/" class="gms-cs-report-cta-btn">
						<span>Schedule a Free Consultation</span>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
				</div>
			</div>
		</section>

	</article>

	<?php
endwhile;

get_footer();
