<?php
/**
 * Template Name: Website Audit
 * Template for the website audit lead-generation page.
 *
 * @package GrowMySecurity
 */

get_header();

if ( function_exists( 'gms_render_elementor_content_fallback' ) && gms_render_elementor_content_fallback() ) {
	get_footer();
	return;
}

$contact_url = home_url( '/contact-us/' );
$logo_url    = function_exists( 'gms_get_brand_asset_url' ) ? gms_get_brand_asset_url( 'logo' ) : get_theme_file_uri( 'assets/images/logo.png' );
?>

<div class="gms-audit-page" id="gms-audit-app" data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-lead-nonce="<?php echo esc_attr( wp_create_nonce( 'gms_audit_lead' ) ); ?>" data-audit-nonce="<?php echo esc_attr( wp_create_nonce( 'gms_audit_fetch' ) ); ?>" data-default-strategy="desktop" data-contact-url="<?php echo esc_url( $contact_url ); ?>">

	<!-- ═══════════════ AMBIENT BACKGROUND ═══════════════ -->
	<div class="gms-audit-ambient" aria-hidden="true">
		<div class="gms-audit-ambient__grid"></div>
		<div class="gms-audit-ambient__glow gms-audit-ambient__glow--1"></div>
		<div class="gms-audit-ambient__glow gms-audit-ambient__glow--2"></div>
		<div class="gms-audit-ambient__scanline"></div>
	</div>

	<!-- ═══════════════ STEP 1 — HERO / CTA BUTTON ═══════════════ -->
	<section class="gms-audit-step gms-audit-step--hero is-active" id="gms-audit-step-hero" aria-labelledby="gms-audit-hero-title">
		<div class="gms-audit-container">
			<div class="gms-audit-hero">
				<div class="gms-audit-hero__badge">
					<span class="gms-audit-hero__badge-dot" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Free Security & Website Audit', 'grow-my-security' ); ?></span>
				</div>

				<h1 id="gms-audit-hero-title" class="gms-audit-hero__title">
					<?php esc_html_e( 'Get Your Free Website', 'grow-my-security' ); ?>
					<span class="gms-audit-hero__title-accent"><?php esc_html_e( 'Security & SEO Audit', 'grow-my-security' ); ?></span>
				</h1>

				<p class="gms-audit-hero__subtitle">
					<?php esc_html_e( 'Uncover hidden security vulnerabilities, performance bottlenecks, and SEO gaps that cost you traffic and trust. Our AI-powered scanner delivers actionable insights in under 60 seconds.', 'grow-my-security' ); ?>
				</p>

				<button type="button" class="gms-audit-hero__cta-btn" id="gms-audit-start-btn">
					<span class="gms-audit-hero__cta-btn-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Z" fill="currentColor"/></svg>
					</span>
					<span class="gms-audit-hero__cta-btn-text"><?php esc_html_e( 'Start Your Free Audit', 'grow-my-security' ); ?></span>
					<span class="gms-audit-hero__cta-btn-arrow" aria-hidden="true">→</span>
				</button>
				<p class="gms-audit-url-form__error" id="gms-audit-url-error" hidden></p>

				<div class="gms-audit-hero__trust">
					<div class="gms-audit-hero__trust-item">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Z" fill="currentColor"/></svg>
						<span><?php esc_html_e( '256-bit encrypted', 'grow-my-security' ); ?></span>
					</div>
					<div class="gms-audit-hero__trust-item">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 7h2v2h-2V7Zm0 4h2v6h-2v-6Zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Z" fill="currentColor"/></svg>
						<span><?php esc_html_e( 'No signup needed', 'grow-my-security' ); ?></span>
					</div>
					<div class="gms-audit-hero__trust-item">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2ZM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8Zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7Z" fill="currentColor"/></svg>
						<span><?php esc_html_e( 'Results in 60 seconds', 'grow-my-security' ); ?></span>
					</div>
				</div>

				<div class="gms-audit-hero__stats">
					<div class="gms-audit-hero__stat">
						<?php $sites_scanned = get_option( 'gms_audit_sites_scanned', 12400 ); ?>
						<strong><?php echo esc_html( number_format_i18n( $sites_scanned ) . '+' ); ?></strong>
						<span><?php esc_html_e( 'Sites Scanned', 'grow-my-security' ); ?></span>
					</div>
					<div class="gms-audit-hero__stat-divider" aria-hidden="true"></div>
					<div class="gms-audit-hero__stat">
						<strong>98%</strong>
						<span><?php esc_html_e( 'Satisfaction', 'grow-my-security' ); ?></span>
					</div>
					<div class="gms-audit-hero__stat-divider" aria-hidden="true"></div>
					<div class="gms-audit-hero__stat">
						<strong>4.9★</strong>
						<span><?php esc_html_e( 'Avg. Rating', 'grow-my-security' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══════════════ STEP 2 — LEAD CAPTURE MODAL (with URL field) ═══════════════ -->
	<div class="gms-audit-modal" id="gms-audit-modal" hidden>
		<div class="gms-audit-modal__backdrop" data-audit-modal-close></div>
		<div class="gms-audit-modal__card">
			<button class="gms-audit-modal__close" type="button" aria-label="<?php esc_attr_e( 'Close', 'grow-my-security' ); ?>" data-audit-modal-close>
				<svg viewBox="0 0 24 24"><path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41Z" fill="currentColor"/></svg>
			</button>

			<div class="gms-audit-modal__header">
				<div class="gms-audit-modal__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm6 9c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25 6 2.25V11Zm-8 .5 2 2 4-4-1.41-1.41L12 10.67l-.59-.59L10 11.5Z" fill="currentColor"/></svg>
				</div>
				<h2 class="gms-audit-modal__title"><?php esc_html_e( 'Start Your Free Audit', 'grow-my-security' ); ?></h2>
				<p class="gms-audit-modal__desc"><?php esc_html_e( 'Enter your details and website URL to receive your personalized security and SEO audit report.', 'grow-my-security' ); ?></p>
			</div>

			<form class="gms-audit-lead-form" id="gms-audit-lead-form" novalidate>
				<input type="hidden" name="action" value="gms_audit_lead">
				<input type="hidden" name="gms_audit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'gms_audit_lead' ) ); ?>">

				<div class="gms-audit-field">
					<label for="gms-audit-lead-name"><?php esc_html_e( 'Full Name', 'grow-my-security' ); ?> <span aria-hidden="true">*</span></label>
					<input type="text" id="gms-audit-lead-name" name="name" placeholder="<?php esc_attr_e( 'John Doe', 'grow-my-security' ); ?>" required autocomplete="name">
					<span class="gms-audit-field__error" data-error-for="name"></span>
				</div>

				<div class="gms-audit-field">
					<label for="gms-audit-lead-email"><?php esc_html_e( 'Email Address', 'grow-my-security' ); ?> <span aria-hidden="true">*</span></label>
					<input type="email" id="gms-audit-lead-email" name="email" placeholder="<?php esc_attr_e( 'john@company.com', 'grow-my-security' ); ?>" required autocomplete="email">
					<span class="gms-audit-field__error" data-error-for="email"></span>
				</div>

				<div class="gms-audit-field">
					<label for="gms-audit-lead-company"><?php esc_html_e( 'Company', 'grow-my-security' ); ?> <span class="gms-audit-field__optional">(<?php esc_html_e( 'Optional', 'grow-my-security' ); ?>)</span></label>
					<input type="text" id="gms-audit-lead-company" name="company" placeholder="<?php esc_attr_e( 'Acme Security Inc.', 'grow-my-security' ); ?>" autocomplete="organization">
				</div>

				<div class="gms-audit-field">
					<label for="gms-audit-lead-url"><?php esc_html_e( 'Website URL', 'grow-my-security' ); ?> <span aria-hidden="true">*</span></label>
					<input type="url" id="gms-audit-lead-url" name="website_url" placeholder="<?php esc_attr_e( 'https://example.com', 'grow-my-security' ); ?>" required autocomplete="url">
					<span class="gms-audit-field__error" data-error-for="website_url"></span>
				</div>

				<button type="submit" class="gms-audit-lead-form__submit" id="gms-audit-lead-submit">
					<span class="gms-audit-lead-form__submit-text"><?php esc_html_e( 'Start My Free Audit', 'grow-my-security' ); ?></span>
					<span class="gms-audit-lead-form__submit-icon" aria-hidden="true">→</span>
				</button>

				<p class="gms-audit-lead-form__privacy">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2ZM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9Z" fill="currentColor"/></svg>
					<?php esc_html_e( 'Your data is encrypted and never shared. We respect your privacy.', 'grow-my-security' ); ?>
				</p>
			</form>
		</div>
	</div>

	<!-- ═══════════════ STEP 3 — AUDIT LOADER ═══════════════ -->
	<section class="gms-audit-step gms-audit-step--loader" id="gms-audit-step-loader" hidden aria-labelledby="gms-audit-loader-title">
		<div class="gms-audit-container">
			<div class="gms-audit-loader">
				<div class="gms-audit-loader__shield" aria-hidden="true">
					<svg viewBox="0 0 80 96" class="gms-audit-loader__shield-svg">
						<path d="M40 4 8 18v24c0 22.2 15.36 42.96 32 48 16.64-5.04 32-25.8 32-48V18L40 4Z" fill="none" stroke="currentColor" stroke-width="2"/>
						<path d="M40 4 8 18v24c0 22.2 15.36 42.96 32 48 16.64-5.04 32-25.8 32-48V18L40 4Z" fill="none" stroke="var(--gms-accent, #ef2014)" stroke-width="2" class="gms-audit-loader__shield-progress" stroke-dasharray="220" stroke-dashoffset="220"/>
					</svg>
					<div class="gms-audit-loader__shield-icon">
						<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9Z" fill="currentColor"/></svg>
					</div>
				</div>

				<h2 id="gms-audit-loader-title" class="gms-audit-loader__title">
					<?php esc_html_e( 'Analyzing Your Website', 'grow-my-security' ); ?>
				</h2>
				<p class="gms-audit-loader__note" id="gms-audit-loader-note">
					<?php esc_html_e( 'Running live Google PageSpeed Insights and Mozilla Observatory checks.', 'grow-my-security' ); ?>
				</p>
				<p class="gms-audit-loader__url" id="gms-audit-loader-url"></p>

				<div class="gms-audit-loader__progress">
					<div class="gms-audit-loader__progress-bar" id="gms-audit-progress-bar"></div>
				</div>
				<p class="gms-audit-loader__percent" id="gms-audit-progress-percent">0%</p>

				<ul class="gms-audit-loader__steps" id="gms-audit-scan-steps">
					<li class="gms-audit-scan-step" data-scan-step="security">
						<span class="gms-audit-scan-step__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Z" fill="currentColor"/></svg>
						</span>
						<span class="gms-audit-scan-step__label"><?php esc_html_e( 'Scanning for vulnerabilities...', 'grow-my-security' ); ?></span>
						<span class="gms-audit-scan-step__check" aria-hidden="true">✓</span>
					</li>
					<li class="gms-audit-scan-step" data-scan-step="ssl">
						<span class="gms-audit-scan-step__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2Zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2Z" fill="currentColor"/></svg>
						</span>
						<span class="gms-audit-scan-step__label"><?php esc_html_e( 'Checking SSL & security headers...', 'grow-my-security' ); ?></span>
						<span class="gms-audit-scan-step__check" aria-hidden="true">✓</span>
					</li>
					<li class="gms-audit-scan-step" data-scan-step="performance">
						<span class="gms-audit-scan-step__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2ZM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8Zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7Z" fill="currentColor"/></svg>
						</span>
						<span class="gms-audit-scan-step__label"><?php esc_html_e( 'Analyzing page speed & performance...', 'grow-my-security' ); ?></span>
						<span class="gms-audit-scan-step__check" aria-hidden="true">✓</span>
					</li>
					<li class="gms-audit-scan-step" data-scan-step="seo">
						<span class="gms-audit-scan-step__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5Zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14Z" fill="currentColor"/></svg>
						</span>
						<span class="gms-audit-scan-step__label"><?php esc_html_e( 'Reviewing SEO signals & metadata...', 'grow-my-security' ); ?></span>
						<span class="gms-audit-scan-step__check" aria-hidden="true">✓</span>
					</li>
					<li class="gms-audit-scan-step" data-scan-step="ai">
						<span class="gms-audit-scan-step__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="M21 11.18V9.72c0-.47-.16-.92-.46-1.28L16.6 3.72c-.38-.46-.94-.72-1.54-.72H8.94c-.6 0-1.16.26-1.54.72L3.46 8.44c-.3.36-.46.81-.46 1.28v1.46c0 .83.67 1.5 1.5 1.5h.04c.83 0 1.5-.67 1.5-1.5V11h1v8c0 1.1.9 2 2 2h5c1.1 0 2-.9 2-2v-8h1v.18c0 .83.67 1.5 1.5 1.5h.04c.83 0 1.5-.67 1.5-1.5Z" fill="currentColor"/></svg>
						</span>
						<span class="gms-audit-scan-step__label"><?php esc_html_e( 'Analyzing AI visibility & GEO signals...', 'grow-my-security' ); ?></span>
						<span class="gms-audit-scan-step__check" aria-hidden="true">✓</span>
					</li>
				</ul>
			</div>
		</div>
	</section>

	<!-- ═══════════════ STEP 4 — RESULTS ═══════════════ -->
	<section class="gms-audit-step gms-audit-step--results" id="gms-audit-step-results" hidden aria-labelledby="gms-audit-results-title">
		<div class="gms-audit-container">
			<div class="gms-audit-results">
				<div class="gms-audit-results__header">
					<div class="gms-audit-results__badge">
						<span class="gms-audit-results__badge-dot" aria-hidden="true"></span>
						<span><?php esc_html_e( 'Audit Complete', 'grow-my-security' ); ?></span>
					</div>
					<h2 id="gms-audit-results-title" class="gms-audit-results__title">
						<?php esc_html_e( 'Your Website Audit Report', 'grow-my-security' ); ?>
					</h2>
					<p class="gms-audit-results__url" id="gms-audit-results-url"></p>
				</div>

				<div class="gms-audit-results__controls">
					<div class="gms-audit-strategy-toggle" id="gms-audit-strategy-toggle" role="tablist" aria-label="<?php esc_attr_e( 'Audit strategy', 'grow-my-security' ); ?>">
						<button class="gms-audit-strategy-toggle__btn is-active" type="button" data-strategy-button data-strategy="desktop" aria-pressed="true">
							<?php esc_html_e( 'Desktop', 'grow-my-security' ); ?>
						</button>
						<button class="gms-audit-strategy-toggle__btn" type="button" data-strategy-button data-strategy="mobile" aria-pressed="false">
							<?php esc_html_e( 'Mobile', 'grow-my-security' ); ?>
						</button>
					</div>
					<p class="gms-audit-results__meta" id="gms-audit-results-meta"></p>
				</div>

				<!-- Score Cards -->
				<div class="gms-audit-scores" id="gms-audit-scores">
					<div class="gms-audit-score-card" data-score-type="security">
						<div class="gms-audit-score-card__ring">
							<svg viewBox="0 0 120 120">
								<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
								<circle cx="60" cy="60" r="52" fill="none" stroke-width="8" stroke-linecap="round" class="gms-audit-score-card__arc" data-arc="security" stroke-dasharray="326.73" stroke-dashoffset="326.73" transform="rotate(-90 60 60)"/>
							</svg>
							<span class="gms-audit-score-card__value" data-score-value="security">0</span>
						</div>
						<h3><?php esc_html_e( 'Security Score', 'grow-my-security' ); ?></h3>
						<span class="gms-audit-score-card__grade" data-score-grade="security"></span>
					</div>

					<div class="gms-audit-score-card" data-score-type="performance">
						<div class="gms-audit-score-card__ring">
							<svg viewBox="0 0 120 120">
								<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
								<circle cx="60" cy="60" r="52" fill="none" stroke-width="8" stroke-linecap="round" class="gms-audit-score-card__arc" data-arc="performance" stroke-dasharray="326.73" stroke-dashoffset="326.73" transform="rotate(-90 60 60)"/>
							</svg>
							<span class="gms-audit-score-card__value" data-score-value="performance">0</span>
						</div>
						<h3><?php esc_html_e( 'Performance', 'grow-my-security' ); ?></h3>
						<span class="gms-audit-score-card__grade" data-score-grade="performance"></span>
					</div>

					<div class="gms-audit-score-card" data-score-type="seo">
						<div class="gms-audit-score-card__ring">
							<svg viewBox="0 0 120 120">
								<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
								<circle cx="60" cy="60" r="52" fill="none" stroke-width="8" stroke-linecap="round" class="gms-audit-score-card__arc" data-arc="seo" stroke-dasharray="326.73" stroke-dashoffset="326.73" transform="rotate(-90 60 60)"/>
							</svg>
							<span class="gms-audit-score-card__value" data-score-value="seo">0</span>
						</div>
						<h3><?php esc_html_e( 'SEO Score', 'grow-my-security' ); ?></h3>
						<span class="gms-audit-score-card__grade" data-score-grade="seo"></span>
					</div>

					<div class="gms-audit-score-card" data-score-type="ai_visibility">
						<div class="gms-audit-score-card__ring">
							<svg viewBox="0 0 120 120">
								<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
								<circle cx="60" cy="60" r="52" fill="none" stroke-width="8" stroke-linecap="round" class="gms-audit-score-card__arc" data-arc="ai_visibility" stroke-dasharray="326.73" stroke-dashoffset="326.73" transform="rotate(-90 60 60)"/>
							</svg>
							<span class="gms-audit-score-card__value" data-score-value="ai_visibility">0</span>
						</div>
						<h3><?php esc_html_e( 'AI Visibility', 'grow-my-security' ); ?></h3>
						<span class="gms-audit-score-card__grade" data-score-grade="ai_visibility"></span>
					</div>
				</div>

				<!-- Issues List -->
				<div class="gms-audit-issues" id="gms-audit-issues">
					<h3 class="gms-audit-issues__title">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 21h22L12 2 1 21Zm12-3h-2v-2h2v2Zm0-4h-2v-4h2v4Z" fill="currentColor"/></svg>
						<span><?php esc_html_e( 'Issues Found', 'grow-my-security' ); ?></span>
					</h3>
					<ul class="gms-audit-issues__list" id="gms-audit-issues-list">
						<!-- Populated by JS -->
					</ul>
				</div>

				<!-- CTA -->
				<div class="gms-audit-results__cta">
					<div class="gms-audit-results__cta-content">
						<h3><?php esc_html_e( 'Want Expert Help Fixing These Issues?', 'grow-my-security' ); ?></h3>
						<p><?php esc_html_e( 'Our cybersecurity marketing specialists can help you resolve every issue identified in this audit and build a stronger, more secure online presence.', 'grow-my-security' ); ?></p>
					</div>
					<div class="gms-audit-results__cta-actions">
						<a class="gms-audit-btn gms-audit-btn--primary gms-audit-btn--lg" href="<?php echo esc_url( $contact_url ); ?>" id="gms-audit-book-cta">
							<span><?php esc_html_e( 'Book Free Consultation', 'grow-my-security' ); ?></span>
							<span class="gms-audit-btn__arrow" aria-hidden="true">→</span>
						</a>
						<button class="gms-audit-btn gms-audit-btn--download" type="button" id="gms-audit-download-report">
							<svg viewBox="0 0 24 24" aria-hidden="true" width="18" height="18"><path d="M19 9h-4V3H9v6H5l7 7 7-7ZM5 18v2h14v-2H5Z" fill="currentColor"/></svg>
							<span><?php esc_html_e( 'Download Full Report', 'grow-my-security' ); ?></span>
						</button>
						<button class="gms-audit-btn gms-audit-btn--outline" type="button" id="gms-audit-restart">
							<span><?php esc_html_e( 'Scan Another Website', 'grow-my-security' ); ?></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</section>

</div>

<?php get_footer(); ?>
