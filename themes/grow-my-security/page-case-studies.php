<?php
/**
 * Template Name: Case Studies Page
 * 
 * A clean, structured template for the Case Studies listing.
 * Routes to: /case-studies
 *
 * @package GrowMySecurity
 */

get_header();

$gms_cs_has_elementor = function_exists( 'gms_post_has_elementor_content' )
	? gms_post_has_elementor_content( get_the_ID() )
	: (bool) get_post_meta( get_the_ID(), '_elementor_edit_mode', true );

/*
 * When Elementor controls this page (widget synced), let the widget render
 * the hero + grid + CTA. This avoids double-rendering in the editor and
 * keeps the live site output identical via the widget's render() method.
 */
if ( $gms_cs_has_elementor ) :
?>
	<div class="gms-page-shell gms-case-studies-page">
		<div class="gms-page-content--elementor">
			<?php the_content(); ?>
		</div>
	</div>
<?php
	get_footer();
	return;
endif;

// Hero Section Configuration
$hero_title    = "Case Studies";
$hero_subtitle = "Real results from cybersecurity marketing strategies";
?>

<div class="gms-page-shell gms-case-studies-page">
	<div class="gms-page-container">

		<!-- 1. HERO SECTION -->
		<section class="gms-cs-hero-alt">
			<div class="gms-container">
				<div class="gms-cs-hero-alt__content">
					<span class="gms-cs-single-eyebrow"><?php esc_html_e( 'Intelligence Reports', 'grow-my-security' ); ?></span>
					<h1 class="gms-cs-hero-alt__title"><?php echo esc_html( $hero_title ); ?></h1>
					<p class="gms-cs-hero-alt__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
				</div>
			</div>
		</section>

	<!-- 2. CASE STUDIES GRID -->
	<section class="gms-cs-grid-section">
		<div class="gms-container">
			<div class="gms-cs-grid">
				<?php
				$cs_query = new WP_Query( [
					'post_type'      => 'gms_case_study',
					'posts_per_page' => 12,
					'post_status'    => 'publish',
				] );

				if ( $cs_query->have_posts() ) :
					while ( $cs_query->have_posts() ) : $cs_query->the_post();
						$metric_val = get_post_meta( get_the_ID(), 'gms_cs_metric_value', true );
						$metric_lab = get_post_meta( get_the_ID(), 'gms_cs_metric_label', true );
						$short_desc = get_post_meta( get_the_ID(), 'gms_cs_short_desc', true );
						$badge      = get_post_meta( get_the_ID(), 'gms_cs_badge', true );
						$image_url  = gms_normalize_case_study_asset_url( (string) get_post_meta( get_the_ID(), 'gms_cs_image_url', true ) );
						?>
						<article class="gms-cs-card">
							<a href="<?php the_permalink(); ?>" class="gms-cs-card-overlay-link" aria-label="<?php the_title_attribute(); ?>"></a>
							
							<?php if ( $badge ) : ?>
								<div class="gms-cs-card__badge"><?php echo esc_html( $badge ); ?></div>
							<?php endif; ?>

							<div class="gms-cs-card__image">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php $thumbnail_url = gms_normalize_case_study_asset_url( (string) get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>
									<?php if ( '' !== $thumbnail_url ) : ?>
										<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php the_title_attribute(); ?>" decoding="async" loading="lazy" fetchpriority="low">
									<?php else : ?>
										<?php the_post_thumbnail( 'large' ); ?>
									<?php endif; ?>
								<?php elseif ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>">
								<?php else : ?>
									<!-- Hard fallback to curated Unsplash tech patterns -->
									<img src="https://images.unsplash.com/photo-<?php echo 0 === $cs_query->current_post % 3 ? '1550751827-4bd374c3f58b' : ( 1 === $cs_query->current_post % 3 ? '1563986768609-322da13575f3' : '1558494949-ef010cbdcc51' ); ?>?q=80&w=1200&auto=format&fit=crop" alt="<?php the_title_attribute(); ?>">
								<?php endif; ?>
								
								<?php if ( $metric_val ) : ?>
									<div class="gms-cs-card__metric-float">
										<span class="gms-cs-card__metric-value"><?php echo esc_html( $metric_val ); ?></span>
										<span class="gms-cs-card__metric-label"><?php echo esc_html( $metric_lab ); ?></span>
									</div>
								<?php endif; ?>
							</div>

							<div class="gms-cs-card__content">
								<h3><?php the_title(); ?></h3>
								<p><?php echo esc_html( $short_desc ); ?></p>
								<div class="gms-cs-card__actions">
									<span class="gms-cs-card__link">
										<?php esc_html_e( 'Read Case Study', 'grow-my-security' ); ?>
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
									</span>
								</div>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="gms-no-posts">' . esc_html__( 'No case studies found. Check back soon!', 'grow-my-security' ) . '</p>';
				endif;
				?>
			</div>
		</div>
	</section>

	<!-- 3. CTA SECTION (BOTTOM) -->
	<!-- Interactive CTA Section -->
	<section class="gms-cs-cta-section">
		<div class="gms-cs-cta-container">
			<div class="gms-cs-cta-content">
				<h2>Ready to build trust that drives revenue?</h2>
				<a href="/contact/" class="gms-cs-cta-btn">
					<span>Schedule a Free Consultation</span>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
				</a>
			</div>
			<div class="gms-cs-cta-image">
				<!-- Floating Trust Badges -->
				<div class="gms-floating-badge" data-pos="1">
					<span>+320%</span>
					<span>Revenue Growth</span>
				</div>
				<div class="gms-floating-badge" data-pos="2">
					<span>ZERO</span>
					<span>Breaches Occurred</span>
				</div>
				<div class="gms-floating-badge" data-pos="3">
					<span>45%</span>
					<span>Efficiency Gain</span>
				</div>

				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/case-studies/cs-revenue.png' ) ); ?>" alt="Growth and Revenue visual" loading="lazy">
			</div>
		</div>
	</section>

    <!-- Elementor Content Hook -->
    <div class="gms-elementor-content">
        <?php the_content(); ?>
    </div>

</div>

<style>
/* Page Specific Layout Cleanup */
.gms-case-studies-page {
    background: #0d0605;
    color: #fff;
    min-height: 100vh;
    padding-top: 100px; /* Offset for fixed header */
}

.gms-cs-hero-alt {
    padding: 100px 0 60px;
    text-align: center;
}

.gms-cs-hero-alt__title {
    font-size: 56px;
    font-weight: 800;
    margin-bottom: 20px;
    color: #fff;
    letter-spacing: -1px;
}

.gms-cs-hero-alt__subtitle {
    font-size: 20px;
    color: #888;
    max-width: 600px;
    margin: 0 auto;
}

.gms-cs-grid-section {
    padding-bottom: 100px;
}

.gms-cs-bottom-cta {
    background: linear-gradient(180deg, #000 0%, #0a0a0a 100%);
    padding: 80px 0;
    border-top: 1px solid #1a1a1a;
}

.gms-cs-cta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 60px;
}

.gms-cs-cta-text h2 {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 32px;
    line-height: 1.2;
}

.gms-cs-cta-image img {
    width: 100%;
    border-radius: 12px;
}

@media (max-width: 768px) {
    .gms-cs-cta-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .gms-cs-hero-alt__title {
        font-size: 36px;
    }
}
</style>

<?php
get_footer();
