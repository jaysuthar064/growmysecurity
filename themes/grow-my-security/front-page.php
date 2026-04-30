<?php
/**
 * Front page template.
 *
 * @package GrowMySecurity
 */

get_header();

if ( function_exists( 'gms_render_elementor_content_fallback' ) && gms_render_elementor_content_fallback() ) {
	get_footer();
	return;
}

$asset_url = static function ( string $filename ): string {
	$relative_path = 'assets/images/' . ltrim( $filename, '/' );
	$asset_url     = get_theme_file_uri( $relative_path );
	$asset_path    = get_theme_file_path( $relative_path );

	if ( is_string( $asset_path ) && '' !== $asset_path && file_exists( $asset_path ) ) {
		$asset_url = add_query_arg( 'ver', (string) filemtime( $asset_path ), $asset_url );
	}

	return $asset_url;
};

$service_image_url = static function ( $image ) use ( $asset_url ): string {
	$image = is_string( $image ) ? trim( $image ) : '';

	if ( '' === $image ) {
		return '';
	}

	if ( preg_match( '#^(?:https?:)?//#i', $image ) || str_starts_with( $image, '/' ) ) {
		return function_exists( 'gms_normalize_media_url' ) ? (string) gms_normalize_media_url( $image ) : $image;
	}

	return $asset_url( $image );
};

$page_url = static function ( string $path, string $fallback = '/' ): string {
	$url = function_exists( 'gms_get_page_url_by_path' ) ? gms_get_page_url_by_path( $path ) : '';

	return $url ?: home_url( $fallback );
};
$homepage_post_id = (int) get_queried_object_id();

if ( $homepage_post_id <= 0 ) {
	$homepage_post_id = (int) get_option( 'page_on_front' );
}

$render_process_icon = static function ( string $icon ): string {
	switch ( $icon ) {
		case 'users':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-2.829-6.829A4 4 0 0 0 16 11Zm-8 0A4 4 0 1 0 8 3a4 4 0 0 0 0 8Zm8 2c-2.673 0-8 1.343-8 4v2h16v-2c0-2.657-5.327-4-8-4Zm-8 0c-.29 0-.62.017-.972.05C4.04 13.246 0 14.525 0 17v2h6v-2c0-1.512.805-2.85 2.111-3.843A8.374 8.374 0 0 0 8 13Z" fill="currentColor"/></svg>';
		case 'target':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10h-2a8 8 0 1 1-8-8V2Zm7 1-2.586 2.586A7.96 7.96 0 0 1 20 12h2a9.96 9.96 0 0 0-2.293-6.414L22 3h-3Zm-7 4a5 5 0 1 0 5 5h-2a3 3 0 1 1-3-3V7Zm0-5v2a8 8 0 0 1 8 8h2A10 10 0 0 0 12 2Z" fill="currentColor"/></svg>';
		case 'link':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.59 13.41a1.996 1.996 0 0 0 2.82 0l3.59-3.59a2 2 0 1 0-2.83-2.83l-1.17 1.17-1.41-1.41 1.17-1.17a4 4 0 1 1 5.66 5.66l-3.59 3.59a4 4 0 0 1-5.66 0l1.42-1.42Zm2.82-2.82-2.82 2.82-1.41-1.41 2.82-2.82 1.41 1.41Zm-4.24 7.07 1.17-1.17-1.41-1.41-1.17 1.17a2 2 0 0 1-2.83-2.83l3.59-3.59a2 2 0 0 1 2.83 0l1.17-1.17 1.41 1.41-1.17 1.17a4 4 0 0 0-5.66 0l-3.59 3.59a4 4 0 1 0 5.66 5.66Z" fill="currentColor"/></svg>';
		case 'shield':
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Z" fill="currentColor"/></svg>';
	}
};

$render_problem_icon = static function ( array $problem_item ): string {
	$icon  = strtolower( trim( (string) ( $problem_item['icon'] ?? '' ) ) );
	$title = strtolower( trim( (string) ( $problem_item['title'] ?? '' ) ) );

	if ( '' === $icon ) {
		if ( false !== strpos( $title, 'invisible' ) ) {
			$icon = 'eye-off';
		} elseif ( false !== strpos( $title, 'trust' ) ) {
			$icon = 'shield-off';
		} else {
			$icon = 'file-warning';
		}
	}

	switch ( $icon ) {
		case 'eye-off':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="M16.68 16.69A10.94 10.94 0 0 1 12 17.75C7.5 17.75 4 14.6 2.25 12c.85-1.25 1.97-2.55 3.39-3.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="M9.87 5.08A11.34 11.34 0 0 1 12 4.87c4.5 0 8 3.15 9.75 5.75a18.32 18.32 0 0 1-2.11 2.56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>';
		case 'shield-off':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="M12 3.25 5.5 5.95v5.16c0 4.18 2.58 8.32 6.5 9.64 1.24-.42 2.4-1.14 3.4-2.09" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="M17.7 15.74A13.1 13.1 0 0 0 18.5 11.1V5.95L12 3.25l-2 .83" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>';
		case 'file-warning':
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3.75H7.75A1.75 1.75 0 0 0 6 5.5v13A1.75 1.75 0 0 0 7.75 20.25h8.5A1.75 1.75 0 0 0 18 18.5V7.75L14 3.75Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="M14 3.75V8h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="m9.5 11.5 5 5m0-5-5 5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>';
	}
};

$render_industry_icon = static function ( string $icon ): string {
	switch ( $icon ) {
		case 'guard':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 5 5v5c0 4.86 3.4 9.45 7 10.73C15.6 19.45 19 14.86 19 10V5l-7-3Zm0 2.18 5 2.14V10c0 3.83-2.46 7.48-5 8.7-2.54-1.22-5-4.87-5-8.7V6.32l5-2.14Zm-1 3.82h2v2h2v2h-2v2h-2v-2H9v-2h2V8Z" fill="currentColor"/></svg>';
		case 'bolt':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" fill="currentColor"/></svg>';
		case 'alarm':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 1 22 6l-1.41 1.41L15.59 2.4 17 1ZM7 1l1.41 1.41L3.41 7.41 2 6l5-5Zm5 4a8 8 0 1 0 8 8 8.009 8.009 0 0 0-8-8Zm1 8V8h-2v6l4.2 2.52 1-1.64L13 13Z" fill="currentColor"/></svg>';
		case 'user':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.33 0-8 2.17-8 5v2h16v-2c0-2.83-3.67-5-8-5Z" fill="currentColor"/></svg>';
		case 'team':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-2.83-6.83A4 4 0 0 0 16 11Zm-8 0A4 4 0 1 0 8 3a4 4 0 0 0 0 8Zm0 2c-2.67 0-8 1.34-8 4v2h8v-2a4.96 4.96 0 0 1 1.58-3.64A10.54 10.54 0 0 0 8 13Zm8 0c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" fill="currentColor"/></svg>';
		case 'retail':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 4H7L4 8v2h1v10h14V10h1V8l-3-4Zm0 2 1.5 2H5.5L7 6h10Zm-8 4h6v8H9v-8Z" fill="currentColor"/></svg>';
		case 'investigation':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2a8 8 0 1 0 5.293 14.002L21 21.707 22.707 20l-5.705-5.707A8 8 0 0 0 10 2Zm0 2a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm1 3H9v4l3.5 2.1 1-1.64L11 10.4V7Z" fill="currentColor"/></svg>';
		case 'building':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 3 2 8v13h20V8l-8-5-4 2-2-2Zm4 3.2 6 3.75V19h-3v-5H7v5H4V9.95L10 6.2V11h4V6.2Z" fill="currentColor"/></svg>';
		case 'camera':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 7h-2.17l-1.84-2H9L7.17 7H5a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3v-7a3 3 0 0 0-3-3Zm0 11H5v-8h12v8Zm-6-7a3 3 0 1 0 3 3 3 3 0 0 0-3-3Z" fill="currentColor"/></svg>';
		case 'shield':
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Z" fill="currentColor"/></svg>';
	}
};

$render_contact_icon = static function ( string $icon ): string {
	switch ( $icon ) {
		case 'email':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 2v.2l8 5.34 8-5.34V8H4Zm16 8V10.6l-7.44 4.96a1 1 0 0 1-1.12 0L4 10.6V16h16Z" fill="currentColor"/></svg>';
		case 'office':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3h8a2 2 0 0 1 2 2v16h-3v-4h-2v4H5V5a2 2 0 0 1 2-2h1Zm0 4v2h2V7H8Zm4 0v2h2V7h-2Zm-4 4v2h2v-2H8Zm4 0v2h2v-2h-2Z" fill="currentColor"/></svg>';
		case 'hours':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 2h2v3h-2V2Zm0 17h2v3h-2v-3Zm8-8h3v2h-3v-2ZM2 11h3v2H2v-2Zm14.95-5.54 1.42 1.42-2.12 2.12-1.41-1.41 2.11-2.13ZM7.17 14.84l1.41 1.41-2.12 2.12-1.41-1.41 2.12-2.12ZM18.37 17.96l-1.41 1.41-2.12-2.12 1.41-1.41 2.12 2.12ZM8.58 7.29 7.17 8.7 5.05 6.58l1.41-1.41 2.12 2.12ZM12 6a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm0 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z" fill="currentColor"/></svg>';
		case 'phone':
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2c.28-.28.67-.37 1.02-.24 1.12.37 2.32.56 3.57.56.55 0 1 .45 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.19 2.45.56 3.57.13.35.04.74-.24 1.02l-2.2 2.2Z" fill="currentColor"/></svg>';
	}
};

$logo_markup = static function ( string $context = 'default' ) use ( $asset_url ): string {
	if ( function_exists( 'gms_get_logo_markup' ) ) {
		return gms_get_logo_markup( $context );
	}

	return sprintf(
		'<img class="gms-logo-image" src="%1$s" alt="%2$s" decoding="async" loading="%3$s">',
		esc_url( $asset_url( 'logo.png' ) ),
		esc_attr( get_bloginfo( 'name' ) ),
		esc_attr( 'header' === $context ? 'eager' : 'lazy' )
	);
};

$testimonial_logo_markup = static function ( array $testimonial ) use ( $logo_markup ): string {
	$logo_url = trim( (string) ( $testimonial['logo'] ?? '' ) );

	if ( '' !== $logo_url ) {
		if ( function_exists( 'gms_normalize_media_url' ) ) {
			$logo_url = (string) gms_normalize_media_url( $logo_url );
		}

		return sprintf(
			'<img class="gms-testimonial-card__logo-image" src="%1$s" alt="%2$s" decoding="async" loading="lazy">',
			esc_url( $logo_url ),
			esc_attr( $testimonial['name'] ?? get_bloginfo( 'name' ) )
		);
	}

	return str_replace( 'gms-logo-image', 'gms-testimonial-card__logo-image', $logo_markup( 'footer' ) );
};

$home_url       = home_url( '/' );
$contact_url    = $page_url( 'contact-us', '/contact-us/' );
$services_url   = $page_url( 'services', '/services/' );
$industries_url = $page_url( 'industries', '/industries/' );
$resources_url      = $page_url( 'resources-insights', '/resources-insights/' );
$case_studies_url   = home_url( '/case-studies/' );
$faq_url            = $page_url( 'faq', '/faq/' );
$about_url      = $page_url( 'about-us', '/about-us/' );
$web_development_url = home_url( '/services/web-development/' );
$terms_url      = $page_url( 'terms-conditions', '/terms-conditions/' );
$privacy_url    = get_privacy_policy_url() ?: home_url( '/privacy-policy/' );

$nav_links = [
	[ 'label' => 'Home', 'url' => $home_url, 'current' => true, 'chevron' => false, 'children' => [] ],
	[ 'label' => 'Services', 'url' => $services_url, 'current' => false, 'chevron' => true, 'children' => [
		[ 'label' => 'SEO & Content Marketing', 'url' => home_url( '/services/seo-content-marketing/' ) ],
		[ 'label' => 'Web Development', 'url' => $web_development_url ],
		[ 'label' => 'Social Media Marketing', 'url' => home_url( '/services/social-media-marketing/' ) ],
		[ 'label' => 'PPC Advertising', 'url' => home_url( '/services/ppc-advertising/' ) ],
		[ 'label' => 'Branding & Design', 'url' => home_url( '/services/branding-design/' ) ],
	]],
	[ 'label' => 'Industries', 'url' => $industries_url, 'current' => false, 'chevron' => true, 'children' => [
		[ 'label' => 'Cybersecurity Firms', 'url' => home_url( '/industries/cybersecurity/' ) ],
		[ 'label' => 'Private Security & Guarding', 'url' => home_url( '/industries/private-security/' ) ],
		[ 'label' => 'Alarm & Monitoring Companies', 'url' => home_url( '/industries/alarm-monitoring/' ) ],
		[ 'label' => 'Managed Service Providers', 'url' => home_url( '/industries/managed-service-providers/' ) ],
		[ 'label' => 'Investigative Services', 'url' => home_url( '/industries/investigative-services/' ) ],
	]],
	[ 'label' => 'Resources', 'url' => $resources_url, 'current' => false, 'chevron' => true, 'children' => [
		[ 'label' => 'Blog', 'url' => home_url( '/blog/' ) ],
		[ 'label' => 'Case Studies', 'url' => $case_studies_url ],
		[ 'label' => 'FAQ', 'url' => $faq_url ],
	]],
	[ 'label' => 'CommandGrid', 'url' => 'https://www.godaddy.com/reseller-program', 'current' => false, 'chevron' => false, 'children' => [] ],
	[ 'label' => 'Contact Us', 'url' => $contact_url, 'current' => false, 'chevron' => false, 'children' => [] ],
];

if ( function_exists( 'gms_get_primary_navigation_items' ) ) {
	$nav_links = gms_get_primary_navigation_items();
}

$elementor_home_data = function_exists('gms_get_theme_home_data_from_elementor') ? gms_get_theme_home_data_from_elementor() : [];

$hero_slides = !empty($elementor_home_data['hero_slides']) ? $elementor_home_data['hero_slides'] : [
	[
		'label'          => 'Security Marketing Agency',
		'title'          => 'Turn Your Brand Into Your Most Powerful Strategic Asset',
		'copy'           => 'We help cybersecurity and regulated-industry companies generate qualified pipeline through compliant, data-backed marketing systems — without compromising trust, privacy, or security.',
		'primary_text'   => 'Request a Free Consultation',
		'primary_url'    => $contact_url,
		'secondary_text' => 'See How Growth Works',
		'secondary_url'  => '#problem',
		'image'          => $asset_url( 'slide1-bg.png' ),
	],
	[
		'label'          => 'Authority Positioning',
		'title'          => 'Be the Security Brand Buyers Trust Before They Ever Contact You',
		'copy'           => 'Establish authority, build credibility, and stay top-of-mind with security-conscious buyers through strategic, compliant marketing.',
		'primary_text'   => 'Get Started',
		'primary_url'    => $contact_url,
		'secondary_text' => 'Learn More',
		'secondary_url'  => $services_url,
		'image'          => $asset_url( 'slide2-bg.png' ),
	],
	[
		'label'          => 'Data-Backed Pipeline',
		'title'          => 'Build Qualified Pipeline With Compliant, Data-Backed Marketing',
		'copy'           => 'Drive consistent, high-quality leads while maintaining strict compliance and protecting your brand reputation.',
		'primary_text'   => 'Get Your Free Audit',
		'primary_url'    => home_url( '/website-audit/' ),
		'secondary_text' => 'View Services',
		'secondary_url'  => $services_url,
		'image'          => $asset_url( 'slide3-bg.png' ),
	],
];

$problem_intro = !empty($elementor_home_data['problem_intro']) ? $elementor_home_data['problem_intro'] : [
	"In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They don't respond to loud marketing, generic promises, or copy-paste campaigns. They look for credibility, authority, and proof long before they ever reach out.",
	"As a result, potential clients visit... hesitate... and leave. The value isn't communicated in a way buyers believe.",
	'This is the gap we focus on closing.',
];

$problem_items = !empty($elementor_home_data['problem_items']) ? $elementor_home_data['problem_items'] : [
	[ 'title' => 'Invisible Profile', 'copy' => 'Without a clear digital footprint, high-value clients cannot find you. Your agency exists in the shadows of the internet.', 'icon' => 'eye-off' ],
	[ 'title' => 'Trust Deficit', 'copy' => 'Security is bought on trust. If your brand looks generic, clients assume your security protocols are too.', 'icon' => 'shield-off' ],
	[ 'title' => '"False" Perception', 'copy' => 'Generic visuals create a "shell company" aesthetic. Real authority requires distinct, verified, and credible branding.', 'icon' => 'file-warning' ],
];

$solution_items = !empty($elementor_home_data['solution_items']) ? $elementor_home_data['solution_items'] : [
	'Establishes your brand as a <strong>credible authority</strong> in your niche',
	'Communicates expertise without overselling or exaggeration',
	'Aligns marketing with real security buyer intent',
	'Converts visibility into <span class="gms-highlight">qualified, high-trust inquiries</span>',
];

$solution_metrics = !empty($elementor_home_data['solution_metrics']) ? $elementor_home_data['solution_metrics'] : [
	'3.2X Increase in Lead Generation',
	'100% User Retention in Key Campaigns',
	'50% Reduction in Wasted Ad Spend',
];

$guarantee_items = !empty($elementor_home_data['guarantee_items']) ? $elementor_home_data['guarantee_items'] : [
	[ 'accent' => 'Clear Positioning', 'title' => 'in a Crowded Market', 'copy' => "We help you articulate what you do, who you're for, and why you're different - without jargon or overclaiming. Your buyers should understand your value in seconds.", 'cta' => 'Learn More', 'url' => home_url( '/positioning/' ), 'icon' => 'shield' ],
	[ 'accent' => 'Trust-First Brand', 'title' => 'Presence', 'copy' => 'From messaging and content to design and digital touchpoints, we ensure your brand looks, sounds, and feels credible across every channel where buyers research you.', 'cta' => 'See How This Works', 'url' => home_url( '/presence/' ), 'icon' => 'users' ],
	[ 'accent' => 'Buyer-Aligned Demand', 'title' => 'Generation', 'copy' => 'We focus on attracting decision-makers who are already searching for solutions like yours, guiding them with the right information at each stage of their journey.', 'cta' => 'Explore More', 'url' => home_url( '/demand/' ), 'icon' => 'target' ],
	[ 'accent' => 'Consistency Over', 'title' => 'Campaigns', 'copy' => 'Instead of disconnected tactics, we build a repeatable system that compounds visibility, authority, and inbound demand over time.', 'cta' => 'Get Started', 'url' => home_url( '/consistency/' ), 'icon' => 'link' ],
];

$industries = !empty($elementor_home_data['industries']) ? $elementor_home_data['industries'] : [
	[ 'title' => 'Physical Security Company', 'url' => gms_get_industry_url( 'Physical Security Company' ), 'icon' => 'guard' ],
	[ 'title' => 'Electronic Security Companies & Integrators', 'url' => gms_get_industry_url( 'Electronic Security Companies & Integrators' ), 'icon' => 'bolt' ],
	[ 'title' => 'Alarm & Monitoring Companies', 'url' => gms_get_industry_url( 'Alarm & Monitoring Companies' ), 'icon' => 'alarm' ],
	[ 'title' => 'Executive Protection & Personal Security', 'url' => gms_get_industry_url( 'Executive Protection & Personal Security' ), 'icon' => 'user' ],
	[ 'title' => 'Event Security & Crowd Management', 'url' => gms_get_industry_url( 'Event Security & Crowd Management' ), 'icon' => 'team' ],
	[ 'title' => 'Loss Prevention & Retail Security', 'url' => gms_get_industry_url( 'Loss Prevention & Retail Security' ), 'icon' => 'retail' ],
	[ 'title' => 'Private Investigation & Security', 'url' => gms_get_industry_url( 'Private Investigation & Security' ), 'icon' => 'investigation' ],
	[ 'title' => 'Government & Municipal Security Contractors', 'url' => gms_get_industry_url( 'Government & Municipal Security Contractors' ), 'icon' => 'building' ],
	[ 'title' => 'Camera Monitoring & Access Control', 'url' => gms_get_industry_url( 'Camera Monitoring & Access Control' ), 'icon' => 'camera' ],
];

$service_items = !empty($elementor_home_data['service_items']) ? $elementor_home_data['service_items'] : [
	[ 
		'id' => 'marketing',
		'title' => 'Strategic Marketing', 
		'subtitle' => 'SEO, AEO & GEO Mastery',
		'summary' => "Dominate the Answer Engines and traditional search results where your buyers are already asking critical questions.", 
		'benefits' => [ 'AEO for Voice & AI search visibility', 'GEO to be the "source of truth" for LLMs', 'Authority-based SEO for long-term equity' ],
		'image' => 'service-strategic-marketing.png'
	],
	[ 
		'id' => 'leads',
		'title' => 'Precision Lead Gen', 
		'subtitle' => 'High-Intent Pathways',
		'summary' => 'We don\'t just find "leads"; we engineer systems that attract decision-makers ready to sign contracts.', 
		'benefits' => [ 'ABM for high-value commercial targets', 'Multi-channel attribution tracking', 'High-converting landing pages' ],
		'image' => 'service-precision-leads.png'
	],
	[ 
		'id' => 'cmo',
		'title' => 'Fractional CMO', 
		'subtitle' => 'Executive-Level Strategy',
		'summary' => 'Gain executive-level marketing leadership without the $300k+ overhead. We guide your mission-critical growth.', 
		'benefits' => [ 'Quarterly strategic roadmaps', 'Budget optimization & waste reduction', 'Team mentorship & management' ],
		'image' => 'service-fractional-cmo.png'
	],
	[ 
		'id' => 'web',
		'title' => 'Authority Web Dev', 
		'subtitle' => 'High-Performance Engines',
		'summary' => 'Your website is your #1 sales rep. We build secure, military-grade platforms that convert traffic into trust.', 
		'benefits' => [ 'Industry-specific UX for security', 'Military-grade security hardening', 'Integrated conversion tracking' ],
		'image' => 'service-authority-webdev.png'
	],
	[ 
		'id' => 'ai',
		'title' => 'AI Growth Solutions', 
		'subtitle' => 'Future-Proof Systems',
		'summary' => 'Leverage AI models trained on industry insights to automate workflows and gain a competitive edge.', 
		'benefits' => [ 'Automated human-like outreach', 'Predictive market analytics', 'Custom AI content engines' ],
		'image' => 'service-ai-growth.png'
	],
];

$faq_items = !empty($elementor_home_data['faq_items']) ? $elementor_home_data['faq_items'] : [
	[ 'question' => 'How does this actually drive results?', 'answer' => "In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They don't respond to loud marketing, generic promises, or copy-paste campaigns." ],
	[ 'question' => 'What metrics do we use to measure success?', 'answer' => 'We focus on meaningful metrics that impact your bottom line: qualified lead volume, conversion rates, customer acquisition cost (CAC), and overall return on investment (ROI). We don\'t just report on vanity metrics like impressions.' ],
	[ 'question' => 'Can we identify studies that highlight our impact?', 'answer' => 'Yes, our strategy involves creating detailed, data-backed case studies that demonstrate how your solutions have solved complex security challenges for other clients, building immediate trust with prospects.' ],
	[ 'question' => 'How long does it take to see results?', 'answer' => 'While some foundational changes can yield immediate improvements, a comprehensive security marketing strategy typically takes 3-6 months to build sustainable momentum and consistent inbound pipeline.' ],
	[ 'question' => 'Do you work with startups or established enterprises?', 'answer' => 'We work across the spectrum, from scaling security startups needing go-to-market strategies to established enterprises looking to modernize their demand generation and digital presence.' ],
];

$journal_cards = !empty($elementor_home_data['journal_cards']) ? $elementor_home_data['journal_cards'] : [
	[ 'category' => 'Website Design', 'title' => 'Why UX/UI Design Can Make or Break Your Website.', 'image' => 'home-journal-1.png', 'url' => home_url( '/why-ux-ui-design-can-make-or-break-your-website/' ) ],
	[ 'category' => 'AI Trend', 'title' => '5 Must-Have Features for a Modern Business Website.', 'image' => 'home-journal-2.png', 'url' => home_url( '/five-must-have-features-for-a-modern-business-website/' ) ],
	[ 'category' => 'AI Trend', 'title' => 'AI in Content Creation: Friend or Foe? in 2026', 'image' => 'home-journal-3.png', 'url' => home_url( '/ai-in-content-creation-friend-or-foe-in-2026/' ) ],
];

$quote_services = !empty($elementor_home_data['quote_services']) ? $elementor_home_data['quote_services'] : [ 'Branding Services', 'Search Engine Optimisation', 'Fractional CMO Services', 'Social Media Marketing', 'Website Design', 'Website Development', 'Advertising Services', 'AI Solutions' ];
$contact_details = !empty($elementor_home_data['contact_details']) ? $elementor_home_data['contact_details'] : [ [ 'icon' => 'phone', 'label' => 'Phone', 'value' => '(623) 282-1778' ], [ 'icon' => 'email', 'label' => 'Email', 'value' => 'info@growmysecuritycompany.com' ], [ 'icon' => 'office', 'label' => 'Head Office', 'value' => 'Chicago, IL, United States' ], [ 'icon' => 'hours', 'label' => 'Working Hours', 'value' => 'Monday-Friday: 09:00AM - 06:00PM' ] ];
$testimonials = !empty($elementor_home_data['testimonials']) ? $elementor_home_data['testimonials'] : [
	[
		'quote' => "Anthony's integrity, transparency, and consistency have helped to maintain our property patrols running seamlessly & efficiently at various locations throughout the valley. He is available when needed, informative in response to all our requests.",
		'name'  => 'Kayra Z.',
		'role'  => 'Satisfied Customer',
	],
	[
		'quote' => "I wanted to take a moment to commend you on your outstanding job in the Fractional CMO role for our security company. Your expertise in marketing strategy and your ability to understand our unique needs has been invaluable to our business.",
		'name'  => 'Mike D.',
		'role'  => 'Satisfied Customer',
	],
	[
		'quote' => "I have known Anthony for several years in his capacity with another agency, as a Vice-President, and have nothing to say but positive things about him and his ethics, attention to detail, and looking out for the bottom line of his clients.",
		'name'  => 'Bill B.',
		'role'  => 'Satisfied Customer',
	],
	[
		'quote' => "Anthony has done a great job for us in managing our security needs and sometimes on short notice! As part of a $5 Billion a year company, it is nice to know that Anthony has our back! Thank you for your ongoing professional support.",
		'name'  => 'Donnie W.',
		'role'  => 'Satisfied Customer',
	],
	[
		'quote' => "Grow My Security Company has been undoubtedly a leader in digital marketing strategy and I am extremely proud to have this outstanding company collaborating with my business. Anthony has helped me drive my company forward.",
		'name'  => 'Nelson V.',
		'role'  => 'Satisfied Customer',
	],
	[
		'quote' => "I would like to recommend Anthony J. Rumore for his expertise in Security Consulting. I have had the pleasure of working with Anthony since 2015 when we contracted with him for our night and weekend patrols in Anthem Parkside.",
		'name'  => 'Mary Beth Z.',
		'role'  => 'Satisfied Customer',
	],
];
$solution_media_url = ! empty( $elementor_home_data['solution_media_url'] )
	? $service_image_url( $elementor_home_data['solution_media_url'] )
	: $asset_url( 'home-solution-trust-engine-watermark.png' );
$services_media_url = $asset_url( 'home-services-media.png' );
$use_elementor_homepage = function_exists( 'gms_should_use_elementor_builder_on_theme_route' ) && gms_should_use_elementor_builder_on_theme_route( get_post( get_queried_object_id() ) );
?>
<div class="gms-homepage">
	<?php if ( function_exists( 'gms_render_submission_notice' ) ) : ob_start(); gms_render_submission_notice(); $notice_html = ob_get_clean(); if ( '' !== trim( $notice_html ) ) : ?><div class="gms-homepage-notice"><?php echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; endif; ?>
	<?php if ( $use_elementor_homepage ) : ?>
		<main class="gms-homepage-content gms-homepage-content--elementor">
			<?php
			$elementor_homepage_markup = '';

			if ( $homepage_post_id > 0 && function_exists( 'gms_get_elementor_builder_markup' ) ) {
				$elementor_homepage_markup = gms_get_elementor_builder_markup( $homepage_post_id );

				if ( '' !== $elementor_homepage_markup && function_exists( 'gms_strip_elementor_layout_wrappers' ) ) {
					$elementor_homepage_markup = gms_strip_elementor_layout_wrappers( $elementor_homepage_markup );
				}
			}
			?>
			<?php if ( '' !== trim( $elementor_homepage_markup ) ) : ?>
				<?php echo $elementor_homepage_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; ?>
			<?php endif; ?>
		</main>
	<?php else : ?>
	<section id="gms-home-hero" class="gms-homepage-hero">
		<div class="swiper gms-hero-swiper">
			<div class="swiper-wrapper">
				<?php foreach ( $hero_slides as $index => $slide ) : ?>
					<?php $hero_bg_url = trim( (string) ( $slide['image'] ?? '' ) ); ?>
					<div class="swiper-slide">
						<?php if ( '' !== $hero_bg_url ) : ?>
							<div class="gms-homepage-hero__bg" style="background-image: url('<?php echo esc_url( $hero_bg_url ); ?>');"></div>
						<?php endif; ?>
						<div class="gms-homepage-hero__overlay"></div>
						<div class="gms-homepage-shell gms-homepage-shell--hero">
							<div class="gms-homepage-hero__content">
								<div class="gms-homepage-chip gms-homepage-chip--hero">
									<span class="gms-homepage-chip__icon gms-homepage-chip__icon--hero" aria-hidden="true"></span>
									<span><?php echo esc_html( $slide['label'] ); ?></span>
								</div>
								<h2><?php echo esc_html( $slide['title'] ); ?></h2>
								<p><?php echo esc_html( $slide['copy'] ); ?></p>
								<div class="gms-homepage-hero__actions">
									<a class="gms-homepage-button gms-homepage-button--primary" href="<?php echo esc_url( $slide['primary_url'] ); ?>">
										<span><?php echo esc_html( $slide['primary_text'] ); ?></span>
										<span class="gms-homepage-button__arrow" aria-hidden="true"></span>
									</a>
									<a class="gms-homepage-button gms-homepage-button--secondary gms-homepage-button--leading" href="<?php echo esc_url( $slide['secondary_url'] ); ?>">
										<span class="gms-homepage-button__play" aria-hidden="true"></span>
										<span><?php echo esc_html( $slide['secondary_text'] ); ?></span>
									</a>
								</div>
								<ul class="gms-homepage-hero__trust">
									<li><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><span><?php esc_html_e( 'Trust-led growth', 'grow-my-security' ); ?></span></li>
									<li><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><span><?php esc_html_e( 'Authority positioning', 'grow-my-security' ); ?></span></li>
									<li><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><span><?php esc_html_e( 'Compliance-focused', 'grow-my-security' ); ?></span></li>
								</ul>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			
			<div class="gms-hero-pagination"></div>
			
			<div class="gms-hero-button-prev">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z" fill="currentColor"/></svg>
			</div>
			<div class="gms-hero-button-next">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" fill="currentColor"/></svg>
			</div>
		</div>
		<a class="gms-homepage-hero__scroll" href="#problem"><span class="gms-homepage-button__arrow gms-homepage-button__arrow--down" aria-hidden="true"></span><span><?php esc_html_e( 'Scroll to explore', 'grow-my-security' ); ?></span></a>
	</section>	<section id="problem" class="gms-homepage-section gms-homepage-section--problem">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-problem">
				<div class="gms-homepage-section-heading gms-homepage-section-heading--left gms-homepage-problem__intro">
					<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--problem" aria-hidden="true"></span><span><?php esc_html_e( 'The Problem', 'grow-my-security' ); ?></span></div>
					<h2><?php esc_html_e( "Security Brands Struggle Because Their Expertise Isn't Visible or Trusted Online.", 'grow-my-security' ); ?></h2>
					<div class="gms-homepage-problem__copy"><?php foreach ( $problem_intro as $problem_paragraph ) : ?><p><?php echo esc_html( $problem_paragraph ); ?></p><?php endforeach; ?></div>
				</div>
				<ol class="gms-homepage-problem__list">
					<?php foreach ( $problem_items as $problem_item ) : ?>
						<li class="gms-homepage-problem__item"><div class="gms-homepage-problem__marker" aria-hidden="true"></div><div class="gms-homepage-problem__body"><div class="gms-homepage-problem__title"><span class="gms-homepage-problem__title-icon" aria-hidden="true"><?php echo $render_problem_icon( $problem_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3><?php echo esc_html( $problem_item['title'] ); ?></h3></div><p><?php echo esc_html( $problem_item['copy'] ); ?></p></div></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</section>
	<section id="solution" class="gms-homepage-section gms-homepage-section--solution">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
                <div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--solution" aria-hidden="true"></span><span><?php esc_html_e( 'The Solution', 'grow-my-security' ); ?></span></div>
                <h2><?php esc_html_e( 'Smarter Marketing, Trust-led Positioning Built Specifically For the Security Industries', 'grow-my-security' ); ?></h2>
                <div class="gms-homepage-solution__lead">
                    <p class="gms-solution-lead-text gms-animate-stagger-1"><?php echo wp_kses_post( __( 'We help security companies build <span class="gms-highlight-anim">visible trust</span> through systems designed around how <span class="gms-highlight-anim">security buyers</span> <span class="gms-highlight-anim">actually think and buy</span>.', 'grow-my-security' ) ); ?></p>
                    <p class="gms-solution-lead-text gms-animate-stagger-2"><?php echo wp_kses_post( __( 'From positioning and messaging to content, search visibility, and <span class="gms-highlight-anim">lead pathways</span>&mdash;', 'grow-my-security' ) ); ?></p>
                    <p class="gms-solution-lead-emphasis gms-animate-stagger-3"><?php echo wp_kses_post( __( 'Every element works together to answer one question:', 'grow-my-security' ) ); ?></p>
                    <div class="gms-solution-lead-callout gms-animate-stagger-4"><?php echo esc_html( __( '"Can I trust this company with something critical?"', 'grow-my-security' ) ); ?></div>
                </div>
            </div>
			<div class="gms-homepage-solution">
				<figure class="gms-homepage-solution__media">
					<img src="<?php echo esc_url( $solution_media_url ); ?>" alt="<?php esc_attr_e( 'Security marketing dashboard highlighting trust, authority, and conversion performance', 'grow-my-security' ); ?>" loading="eager" decoding="async">
				</figure>
				<div class="gms-homepage-solution__content">
					<p><?php echo wp_kses_post( __( "Security brands don't win by chasing trends or generic growth tactics. They win by earning <strong>credible authority</strong> before the first conversation ever happens.", 'grow-my-security' ) ); ?></p>
					<p><?php echo wp_kses_post( __( 'That means showing up with clarity and consistency &mdash; building <span class="gms-highlight">visible trust</span> everywhere your buyers are researching, comparing, and forming opinions.', 'grow-my-security' ) ); ?></p>
					<p><?php echo wp_kses_post( __( 'Instead of disconnected campaigns, we build a structured growth engine that:', 'grow-my-security' ) ); ?></p>
					<ul class="gms-homepage-solution__list"><?php foreach ( $solution_items as $solution_item ) : ?><li><?php echo wp_kses_post( $solution_item ); ?></li><?php endforeach; ?></ul>
					<p class="gms-homepage-solution__closing"><?php echo wp_kses_post( __( "This isn't marketing for clicks. This is marketing that builds confidence, reduces doubt, and shortens decision cycles &mdash; because in security, <span class=\"gms-highlight\">trust is the real conversion factor.</span>", 'grow-my-security' ) ); ?></p>
					<div class="gms-homepage-solution__proof"><h3 class="gms-homepage-solution__proof-heading"><?php esc_html_e( 'Proven Outcomes from Our Marketing Systems', 'grow-my-security' ); ?></h3><p class="gms-homepage-solution__proof-note"><?php esc_html_e( 'Data-backed results from security industry clients', 'grow-my-security' ); ?></p><div class="gms-homepage-solution__metrics"><?php foreach ( $solution_metrics as $solution_metric ) : ?><div class="gms-homepage-solution__metric"><?php echo esc_html( $solution_metric ); ?></div><?php endforeach; ?></div></div>
					<a class="gms-homepage-button gms-homepage-button--primary" href="<?php echo esc_url( $contact_url ); ?>"><span><?php esc_html_e( 'Schedule a Free Consultation', 'grow-my-security' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
				</div>
			</div>
		</div>
	</section>
	<section id="guarantee" class="gms-homepage-section gms-homepage-section--guarantee">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide"><div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--guarantee" aria-hidden="true"></span><span><?php esc_html_e( 'Our Guarantee', 'grow-my-security' ); ?></span></div><h2><?php esc_html_e( 'How We Help Security Brands Win Trust & Growth', 'grow-my-security' ); ?></h2><p><?php esc_html_e( 'We work as a strategic growth partner for security-focused brands, helping them move from being technically capable to being clearly trusted in the market. Every engagement is designed around long-term authority, not short-term spikes.', 'grow-my-security' ); ?></p></div>
			<div class="gms-homepage-guarantee__timeline">
			<div class="gms-homepage-guarantee__line-bg" aria-hidden="true"></div>
			<div class="gms-homepage-guarantee__progress-line" aria-hidden="true"></div>
			<?php foreach ( $guarantee_items as $index => $guarantee_item ) : ?>
				<article class="gms-homepage-guarantee__step">
					<div class="gms-homepage-guarantee__node-wrap">
						<div class="gms-homepage-guarantee__node" aria-hidden="true">
							<?php echo $render_process_icon( $guarantee_item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
					<div class="gms-homepage-guarantee__body">
						<h3><span><?php echo esc_html( $guarantee_item['accent'] ); ?></span><?php echo esc_html( $guarantee_item['title'] ); ?></h3>
						<p><?php echo esc_html( $guarantee_item['copy'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		</div>
	</section>
	<section id="testimonials" class="gms-homepage-section gms-homepage-section--testimonials">
		<div class="gms-homepage-testimonials__glow" aria-hidden="true"></div>
		<div class="gms-homepage-shell">
			<p class="gms-homepage-testimonials__watermark" aria-hidden="true">Testimonials</p>
			
			<div class="swiper gms-testimonials-swiper">
				<div class="swiper-wrapper">
					<?php foreach ( $testimonials as $testimonial ) : ?>
						<div class="swiper-slide">
							<article class="gms-testimonial-card">
								<div class="gms-testimonial-card__logo">
									<?php echo $testimonial_logo_markup( $testimonial ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<div class="gms-testimonial-card__content">
									<p><?php echo esc_html( $testimonial['quote'] ); ?></p>
								</div>
								<div class="gms-testimonial-card__footer">
									<div class="gms-testimonial-card__stars" aria-hidden="true">
										<?php for ( $i = 0; $i < 5; $i++ ) : ?>
											<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="#FF2A1F"/></svg>
										<?php endfor; ?>
									</div>
									<div class="gms-testimonial-card__author">
										<p class="gms-testimonial-card__name"><?php echo esc_html( $testimonial['name'] ); ?></p>
										<p class="gms-testimonial-card__role"><?php echo esc_html( $testimonial['role'] ); ?></p>
									</div>
								</div>
							</article>
						</div>
					<?php endforeach; ?>
				</div>
                <div class="gms-testimonials-controls">
                    <button class="gms-testimonials-button-prev" aria-label="Previous testimonial"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
                    <div class="gms-testimonials-swiper-pagination"></div>
                    <button class="gms-testimonials-button-next" aria-label="Next testimonial"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
                </div>
			</div>
		</div>
	</section>	<section id="case-studies" class="gms-homepage-section gms-homepage-section--case-studies">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-section-heading gms-homepage-section-heading--center">
				<div class="gms-homepage-chip">
					<span class="gms-homepage-chip__icon gms-homepage-chip__icon--solution" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Case Studies', 'grow-my-security' ); ?></span>
				</div>
				<h2><?php esc_html_e( 'Proven Results for Security Brands', 'grow-my-security' ); ?></h2>
				<p><?php esc_html_e( 'Real outcomes from trust-led marketing strategies', 'grow-my-security' ); ?></p>
			</div>

			<div class="gms-cs-grid">
				<?php
				$cs_query = new WP_Query( [
					'post_type'      => 'gms_case_study',
					'posts_per_page' => 3,
				] );

				if ( $cs_query->have_posts() ) :
					while ( $cs_query->have_posts() ) : $cs_query->the_post();
						$metric_val = get_post_meta( get_the_ID(), 'gms_cs_metric_value', true );
						$metric_lab = get_post_meta( get_the_ID(), 'gms_cs_metric_label', true );
						$short_desc = get_post_meta( get_the_ID(), 'gms_cs_short_desc', true );
						$image_url  = function_exists( 'gms_get_case_study_image_url' ) ? gms_get_case_study_image_url( get_post(), 'large' ) : '';
						$card_url   = get_permalink() ?: home_url( '/case-studies/' );
						?>
						<article class="gms-cs-card">
							<a href="<?php echo esc_url( $card_url ); ?>" class="gms-cs-card-overlay-link" aria-label="<?php the_title_attribute(); ?>"></a>
							<div class="gms-cs-card__image">
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>">
								<?php else : ?>
									<div class="gms-cs-card__placeholder" style="background: #222; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #444; min-height: 200px;">
										<svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
									</div>
								<?php endif; ?>
								<div class="gms-cs-card__metric-float">
									<span class="gms-cs-card__metric-value"><?php echo esc_html( $metric_val ); ?></span>
									<span class="gms-cs-card__metric-label"><?php echo esc_html( $metric_lab ); ?></span>
								</div>
							</div>
							<div class="gms-cs-card__content">
								<h3><?php the_title(); ?></h3>
								<p><?php echo esc_html( $short_desc ); ?></p>
								<a href="<?php echo esc_url( $card_url ); ?>" class="gms-cs-card__link">
									<?php esc_html_e( 'View Case Study →', 'grow-my-security' ); ?>
								</a>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>
		</div>
	</section>

	<section id="who-we-serve" class="gms-homepage-section gms-homepage-section--serve">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide"><div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--serve" aria-hidden="true"></span><span><?php esc_html_e( 'Who We Serve', 'grow-my-security' ); ?></span></div><h2><?php esc_html_e( 'Security Verticals Supported', 'grow-my-security' ); ?></h2><p><?php esc_html_e( "If your buyers are technical and your product is complex, you're in the right place.", 'grow-my-security' ); ?></p></div>

			<div class="gms-homepage-serve__grid"><?php foreach ( $industries as $index => $industry ) : ?><a href="<?php echo esc_url( $industry['url'] ); ?>" class="gms-homepage-serve__item<?php echo 0 === $index ? ' is-accent' : ''; ?>"><div class="gms-homepage-serve__icon" aria-hidden="true"><?php echo $render_industry_icon( $industry['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><h3><?php echo esc_html( $industry['title'] ); ?></h3></a><?php endforeach; ?></div>
			<div class="gms-homepage-serve__footer"><a class="gms-homepage-button gms-homepage-button--primary" href="<?php echo esc_url( $contact_url ); ?>"><span><?php esc_html_e( 'Schedule a Free Consultation', 'grow-my-security' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a><p><?php esc_html_e( "Don't see your industry? Contact us to see if we can help you.", 'grow-my-security' ); ?></p></div>
		</div>
	</section>
	<section id="services" class="gms-homepage-section gms-homepage-section--services">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
				<div class="gms-homepage-chip">
					<span class="gms-homepage-chip__icon gms-homepage-chip__icon--services" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Our Services', 'grow-my-security' ); ?></span>
				</div>
				<h2><?php esc_html_e( 'Building Security Solutions with Intelligent Services', 'grow-my-security' ); ?></h2>
				<p><?php esc_html_e( "If your buyers are technical and your product is complex, you're in the right place.", 'grow-my-security' ); ?></p>
			</div>
			
			<div class="gms-services-tabs" data-gms-tabs>
				<div class="gms-services-tabs__nav" role="tablist">
					<?php foreach ( $service_items as $index => $item ) : ?>
						<button 
							class="gms-services-tabs__control<?php echo 0 === $index ? ' is-active' : ''; ?>" 
							role="tab" 
							aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" 
							aria-controls="gms-service-panel-<?php echo esc_attr( $item['id'] ); ?>"
							id="gms-service-tab-<?php echo esc_attr( $item['id'] ); ?>"
							data-tab-target="<?php echo esc_attr( $item['id'] ); ?>"
						>
							<span class="gms-services-tabs__control-index">0<?php echo $index + 1; ?></span>
							<span class="gms-services-tabs__control-label"><?php echo esc_html( $item['title'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="gms-services-tabs__display">
					<?php foreach ( $service_items as $index => $item ) : ?>
						<div 
							class="gms-services-tabs__panel<?php echo 0 === $index ? ' is-active' : ''; ?>" 
							id="gms-service-panel-<?php echo esc_attr( $item['id'] ); ?>" 
							role="tabpanel" 
							aria-labelledby="gms-service-tab-<?php echo esc_attr( $item['id'] ); ?>"
							data-tab-panel="<?php echo esc_attr( $item['id'] ); ?>"
							<?php echo 0 === $index ? '' : 'hidden'; ?>
						>
							<div class="gms-services-tabs__grid">
								<div class="gms-services-tabs__info">
									<div class="gms-services-tabs__header">
										<p class="gms-services-tabs__subtitle"><?php echo esc_html( $item['subtitle'] ); ?></p>
										<h3><?php echo esc_html( $item['title'] ); ?></h3>
										<p class="gms-services-tabs__summary"><?php echo esc_html( $item['summary'] ); ?></p>
									</div>
									<div class="gms-services-tabs__benefits">
										<h4>Key Strategic Benefits:</h4>
										<ul>
											<?php foreach ( $item['benefits'] as $benefit ) : ?>
												<li>
													<span class="gms-services-tabs__benefit-icon" aria-hidden="true"></span>
													<span><?php echo esc_html( $benefit ); ?></span>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
									<a class="gms-homepage-button gms-homepage-button--primary" href="<?php echo esc_url( $services_url ); ?>">
										<span>Learn More</span>
										<span class="gms-homepage-button__arrow" aria-hidden="true"></span>
									</a>
								</div>
								<div class="gms-services-tabs__media">
									<div class="gms-services-tabs__image-wrap">
										<img src="<?php echo esc_url( $service_image_url( $item['image'] ?? '' ) ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" decoding="async">
										<div class="gms-services-tabs__image-overlay"></div>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="gms-homepage-services__footer">
				<a class="gms-homepage-button gms-homepage-button--primary gms-homepage-button--fixed" href="<?php echo esc_url( $services_url ); ?>">
					<span><?php esc_html_e( 'View All Services', 'grow-my-security' ); ?></span>
					<span class="gms-homepage-button__arrow" aria-hidden="true"></span>
				</a>
			</div>
		</div>
	</section>
	<section id="faq" class="gms-homepage-section gms-homepage-section--faq">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-faq">
				<div class="gms-homepage-faq__intro">
					<div class="gms-homepage-faq__intro-card">
						<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--faq" aria-hidden="true"></span><span><?php esc_html_e( "FAQ's", 'grow-my-security' ); ?></span></div>
						<h2><?php echo wp_kses_post( __( 'Frequently Asked <span class="gms-faq-heading-accent">Questions</span>', 'grow-my-security' ) ); ?></h2>
						<div class="gms-homepage-faq__desc">
							<p><?php echo wp_kses_post( __( 'In the security industry, <strong>trust is everything</strong>. Buyers are cautious, risk-aware, and slow to commit.', 'grow-my-security' ) ); ?></p>
							<p><?php echo wp_kses_post( __( 'They don\'t respond to loud marketing, generic promises, or copy-paste campaigns. They look for <span class="gms-faq-highlight">credibility</span>, <span class="gms-faq-highlight">authority</span>, and <span class="gms-faq-highlight">proof</span> long before they ever reach out.', 'grow-my-security' ) ); ?></p>
						</div>
						<div class="gms-homepage-faq__trust-strip">
							<div class="gms-homepage-faq__trust-stat">
								<span class="gms-homepage-faq__trust-number">200+</span>
								<span class="gms-homepage-faq__trust-label"><?php esc_html_e( 'Security Brands Served', 'grow-my-security' ); ?></span>
							</div>
							<div class="gms-homepage-faq__trust-stat">
								<span class="gms-homepage-faq__trust-number">98%</span>
								<span class="gms-homepage-faq__trust-label"><?php esc_html_e( 'Client Satisfaction', 'grow-my-security' ); ?></span>
							</div>
							<div class="gms-homepage-faq__trust-stat">
								<span class="gms-homepage-faq__trust-number">5+</span>
								<span class="gms-homepage-faq__trust-label"><?php esc_html_e( 'Years of Experience', 'grow-my-security' ); ?></span>
							</div>
						</div>
						<div class="gms-homepage-faq__actions">
							<a class="gms-homepage-button gms-homepage-button--primary gms-homepage-button--faq-cta" href="<?php echo esc_url( $contact_url ); ?>"><span><?php esc_html_e( 'Schedule a Free Consultation', 'grow-my-security' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
							<a class="gms-homepage-button gms-homepage-button--secondary" href="<?php echo esc_url( $faq_url ); ?>"><span><?php esc_html_e( 'Check All FAQ', 'grow-my-security' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
						</div>
					</div>
				</div>
				<div class="gms-homepage-faq__accordion" data-faq-accordion><?php foreach ( $faq_items as $index => $faq_item ) : ?><?php $is_open = 0 === $index; ?><article class="gms-homepage-faq__item<?php echo $is_open ? ' is-open' : ''; ?>"><h3><button class="gms-homepage-faq__question" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" data-faq-trigger><span><?php echo esc_html( $faq_item['question'] ); ?></span><span class="gms-homepage-faq__icon" aria-hidden="true"></span></button></h3><div class="gms-homepage-faq__answer"<?php echo $is_open ? '' : ' hidden'; ?> data-faq-panel><?php if ( '' !== $faq_item['answer'] ) : ?><p><?php echo esc_html( $faq_item['answer'] ); ?></p><?php endif; ?></div></article><?php endforeach; ?><div class="gms-homepage-faq__prompt"><p><?php esc_html_e( 'Do you have anymore questions for us?', 'grow-my-security' ); ?></p><a href="<?php echo esc_url( $contact_url ); ?>"><span><?php esc_html_e( 'Contact Us', 'grow-my-security' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a></div></div>
			</div>
		</div>
	</section>	<section id="journal" class="gms-homepage-section gms-homepage-section--journal">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide"><div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--blogs" aria-hidden="true"></span><span><?php esc_html_e( 'Blogs', 'grow-my-security' ); ?></span></div><h2><?php esc_html_e( 'Updated Journal', 'grow-my-security' ); ?></h2><p><?php esc_html_e( 'Strategists dedicated to creating stunning, functional websites that align with your unique business goals.', 'grow-my-security' ); ?></p></div>
			<div class="gms-homepage-journal__grid"><?php foreach ( $journal_cards as $journal_card ) : ?><article class="gms-homepage-journal__card"><a href="<?php echo esc_url( $journal_card['url'] ); ?>"><img src="<?php echo esc_url( $asset_url( $journal_card['image'] ) ); ?>" alt="<?php echo esc_attr( $journal_card['title'] ); ?>" loading="lazy" decoding="async"><div class="gms-homepage-journal__meta"><?php echo esc_html( $journal_card['category'] ); ?></div><h3><?php echo esc_html( $journal_card['title'] ); ?></h3></a></article><?php endforeach; ?></div>
			<div class="gms-homepage-journal__actions"><a class="gms-homepage-button gms-homepage-button--primary gms-homepage-button--fixed" href="<?php echo esc_url( $resources_url ); ?>"><span><?php esc_html_e( 'View All Services', 'grow-my-security' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a></div>
		</div>
	</section>
	<section id="quote" class="gms-homepage-section gms-homepage-section--quote">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-quote"><div class="gms-homepage-quote__content"><div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--contact" aria-hidden="true"></span><span><?php esc_html_e( 'Contact Us', 'grow-my-security' ); ?></span></div><h2><?php esc_html_e( 'Get your free quote', 'grow-my-security' ); ?></h2><p><?php esc_html_e( 'Supercharge your online presence with a tailored digital marketing strategy. Fill out the form today to get a personalized consultation and quote within 24-48 hours - no hidden fees, no obligations.', 'grow-my-security' ); ?></p><div class="gms-homepage-quote__services"><h3><?php esc_html_e( "What services you'll get", 'grow-my-security' ); ?></h3><ul><?php foreach ( $quote_services as $quote_service ) : ?><li><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><span><?php echo esc_html( $quote_service ); ?></span></li><?php endforeach; ?></ul></div><div class="gms-homepage-quote__details"><?php foreach ( $contact_details as $contact_detail ) : ?><div class="gms-homepage-quote__detail"><div class="gms-homepage-quote__detail-icon" aria-hidden="true"><?php echo $render_contact_icon( $contact_detail['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div><p><?php echo esc_html( $contact_detail['label'] ); ?></p><h3><?php echo esc_html( $contact_detail['value'] ); ?></h3></div></div><?php endforeach; ?></div></div><div class="gms-homepage-quote__form-card"><h3><?php esc_html_e( 'Start your project today - no obligation', 'grow-my-security' ); ?></h3><form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><input type="hidden" name="action" value="gms_contact_form"><input type="hidden" name="privacy_acceptance" value="1"><input type="hidden" name="bot_check" value="1"><?php wp_nonce_field( 'gms_contact_form', 'gms_contact_nonce' ); ?><label class="gms-homepage-field gms-homepage-field--active"><span><?php esc_html_e( 'Full Name', 'grow-my-security' ); ?></span><input type="text" name="full_name" placeholder="<?php esc_attr_e( 'Enter full name', 'grow-my-security' ); ?>" autocomplete="name" required></label><label class="gms-homepage-field"><span><?php esc_html_e( 'Email Address', 'grow-my-security' ); ?></span><input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter email address', 'grow-my-security' ); ?>" autocomplete="email" required></label><label class="gms-homepage-field"><span><?php esc_html_e( 'Phone', 'grow-my-security' ); ?></span><input type="tel" name="phone" placeholder="<?php esc_attr_e( '(555) 123-4567', 'grow-my-security' ); ?>" autocomplete="tel"></label><label class="gms-homepage-field"><span><?php esc_html_e( "Services you're interested in", 'grow-my-security' ); ?></span><span class="gms-homepage-select-wrap"><select name="service_interest"><?php foreach ( $quote_services as $quote_service ) : ?><option value="<?php echo esc_attr( $quote_service ); ?>"<?php selected( 'Fractional CMO Services', $quote_service ); ?>><?php echo esc_html( $quote_service ); ?></option><?php endforeach; ?></select></span></label><label class="gms-homepage-field"><span><?php esc_html_e( 'Project description', 'grow-my-security' ); ?></span><textarea name="message" placeholder="<?php esc_attr_e( 'Tell us more about your project, timeline, budget, or other specific requirements', 'grow-my-security' ); ?>"></textarea></label><button class="gms-homepage-button gms-homepage-button--primary gms-homepage-button--full" type="submit"><span><?php esc_html_e( 'Get My Free Quote', 'grow-my-security' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></button></form><div class="gms-homepage-quote__reassurance"><span><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><?php esc_html_e( '24-48 hour response', 'grow-my-security' ); ?></span><span><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><?php esc_html_e( 'No spam, ever', 'grow-my-security' ); ?></span><span><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><?php esc_html_e( 'Local experts', 'grow-my-security' ); ?></span></div></div></div>
		</div>
	</section>	<?php endif; ?>
	<?php if ( function_exists( 'gms_render_homepage_footer' ) ) : ?>
		<?php gms_render_homepage_footer(); ?>
	<?php endif; ?>
</div>
<?php
get_footer();





