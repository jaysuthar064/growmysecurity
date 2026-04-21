<?php
/**
 * Senior UI/UX Redesign (v3.0.1) - RESTORED
 * Premium Industry Authority Landing Page.
 * 
 * @package GrowMySecurity
 */

get_header();

if ( function_exists( 'gms_render_elementor_content_fallback' ) && gms_render_elementor_content_fallback() ) {
	get_footer();
	return;
}

// Fetch dynamic industry data once at the top
$title    = get_the_title();
$slug     = get_post_field('post_name', get_the_ID());
$has_thumb = has_post_thumbnail();
$img_url  = $has_thumb ? get_the_post_thumbnail_url(get_the_ID(), 'full') : '';

// Asset base path (Optimized call)
$theme_img_path = get_template_directory_uri() . '/assets/images/';

// Universal Image Mapping (v3.0)
$hero_asset      = $theme_img_path . $slug . '-hero.png';
$proof_dashboard = $theme_img_path . 'security-dashboard-visual.png';
$strategy_visual = $theme_img_path . 'security-tech-visual.png';

// Fallback logic
if (empty($img_url)) {
    $img_url = $hero_asset;
}

// Industry Challenges Data (Optimized Array)
$all_industry_data = [
    'contract-security-guards' => [
        'label' => 'Guard Services',
        'challenges' => ['Commoditization of Services', 'Low Retention of High-Tier Clients', 'Visibility in Dense Markets']
    ],
    'electronic-security-integrators' => [
        'label' => 'Integrators',
        'challenges' => ['Long Sales Cycles', 'Technical Trust Gaps', 'Complex Spec Overload']
    ],
    'alarm-monitoring-companies' => [
        'label' => 'Monitoring',
        'challenges' => ['Customer Churn Fatigue', 'Market Saturation', 'Lack of Service Differentiation']
    ]
];

$industry_data = $all_industry_data[$slug] ?? [
    'label' => 'Security Authority',
    'challenges' => ['Sales Momentum Stalls', 'Lower Profit Margins', 'Market Trust Erosion']
];
?>

<main id="primary" class="site-main gms-industry-v3">

    <!-- HERO SECTION: Cinema-Grade 60/40 Split -->
    <header class="gms-v3-hero">
        <div class="gms-v3-hero-overlay-radial"></div>
        <div class="container">
            <div class="gms-v3-hero-grid">
                <!-- Left: Authority Content -->
                <article class="gms-v3-hero-content gms-animate-fade-up">
                    <span class="chip"><?php echo esc_html($industry_data['label']); ?></span>
                    <h1 class="gms-v3-title"><?php echo esc_html($title); ?></h1>
                    <p class="gms-v3-hook">Winning High-Tier Contracts through Technical Authority.</p>
                    <p class="gms-v3-desc">We turn your security expertise into the measurable credibility that closes sales before you even enter the room.</p>
                    <div class="gms-v3-actions">
                        <a href="<?php echo home_url('/contact-us/'); ?>" class="btn btn-primary btn-glow-large">Get Qualified Security Leads</a>
                    </div>
                </article>
                <!-- Right: Static High-End Visual -->
                <div class="gms-v3-hero-media gms-animate-fade-left">
                    <div class="gms-v3-media-card">
                         <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>" onerror="this.src='<?php echo esc_url($theme_img_path); ?>industry.png';">
                         <div class="gms-v3-media-glow"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- SECTION 1: Industry Challenges -->
    <section class="section gms-v3-problem">
        <div class="container">
            <div class="solution-header">
                <span class="chip">The Challenge</span>
                <h2>Dominating Your <span>Vertical</span></h2>
            </div>
            <div class="gms-v3-challenges-grid">
                <?php foreach ($industry_data['challenges'] as $index => $challenge): ?>
                    <div class="gms-v3-challenge-card gms-animate-fade-up" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <div class="gms-v3-card-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef2014" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                        <p><?php echo esc_html($challenge); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SECTION 2: The Logic (How We Help) -->
    <section class="section gms-v3-solution">
        <div class="container">
            <div class="gms-v3-split-layout">
                <div class="gms-v3-help-content gms-animate-fade-right">
                    <span class="chip">The Solution</span>
                    <h2>The Authority <span>Engine</span></h2>
                    <div class="gms-v3-solution-items">
                         <div class="gms-v3-sol-item"><h3>Vertical Dominance</h3><p>Ranking for the specific high-consequence keywords your buyers use.</p></div>
                         <div class="gms-v3-sol-item"><h3>Precision Lead Gen</h3><p>Structured funnels that capture active market demand in your niche.</p></div>
                    </div>
                </div>
                <div class="gms-v3-help-media gms-animate-fade-left">
                     <div class="gms-v3-glass-container">
                          <img src="<?php echo esc_url($strategy_visual); ?>" alt="Authority Strategy">
                          <div class="glass-overlay"></div>
                     </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: Performance Methodology -->
    <section class="section gms-v3-strategy">
        <div class="container">
            <div class="solution-header">
                <span class="chip">Process</span>
                <h2>Three Steps to <span>Scale</span></h2>
            </div>
            <div class="gms-v3-steps-row">
                 <div class="gms-v3-step"><span>01</span><h3>Positioning</h3><p>Locking in your unique technical value proposition for high-tier contracts.</p></div>
                 <div class="gms-v3-step"><span>02</span><h3>Authority Proof</h3><p>Creating visual documentation and proof that builds buyer trust immediately.</p></div>
                 <div class="gms-v3-step"><span>03</span><h3>Lead Funnels</h3><p>Turning visibility into a measurable revenue pipeline.</p></div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: Result Metrics -->
    <section class="section gms-v3-metrics">
        <div class="container">
            <div class="stats-row">
                  <div class="stat-item v3-stat">
                      <div class="stat-value">+3x</div>
                      <div class="stat-label">Qualified Leads</div>
                  </div>
                  <div class="stat-item v3-stat">
                      <div class="stat-value">50%</div>
                      <div class="stat-label">Conv. Rate</div>
                  </div>
                  <div class="stat-item v3-stat">
                      <div class="stat-value">60%</div>
                      <div class="stat-label">CPA Reduction</div>
                  </div>
            </div>
            <div class="gms-v3-dashboard-visual gms-animate-fade-up">
                 <div class="dashboard-label">Market Intelligence Dashboard [Authority Proof]</div>
                 <img src="<?php echo esc_url($proof_dashboard); ?>" alt="Dashboard Proof">
                 <div class="glow-underneath"></div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: Authority Audit CTA -->
    <section class="section gms-v3-footer-cta">
        <div class="container">
            <div class="gms-v3-cta-card">
                 <span class="chip">Strategic Opportunity</span>
                 <h2>Ready to Capture Your <span>Market Dominance?</span></h2>
                 <p>Schedule your confidential Industry Audit and stop settling for vendor-tier results.</p>
                 <div class="gms-v3-actions">
                      <a href="<?php echo home_url('/contact-us/'); ?>" class="btn btn-primary btn-glow-large">Book Your Authority Audit</a>
                 </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
