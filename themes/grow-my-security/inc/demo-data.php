<?php
/**
 * Shared demo data used by templates, Elementor widgets, and the importer.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gms_asset( $path ) {
	if ( function_exists( 'get_template_directory_uri' ) ) {
		return trailingslashit( get_template_directory_uri() ) . ltrim( $path, '/' );
	}

	return '{{theme_url}}/' . ltrim( $path, '/' );
}

if ( ! function_exists( 'gms_demo_id' ) ) {
	function gms_demo_id( string $seed ): string {
		return substr( md5( $seed ), 0, 8 );
	}
}

if ( ! function_exists( 'gms_widget_node' ) ) {
	function gms_widget_node( string $type, array $settings, string $seed ): array {
		return [
			'id'         => gms_demo_id( $seed ),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => [],
		];
	}
}

if ( ! function_exists( 'gms_column_node' ) ) {
	function gms_column_node( array $widgets, string $seed, int $size = 100 ): array {
		return [
			'id'       => gms_demo_id( $seed ),
			'elType'   => 'column',
			'settings' => [
				'_column_size' => $size,
			],
			'elements' => $widgets,
		];
	}
}

if ( ! function_exists( 'gms_section_node' ) ) {
	function gms_section_node( array $columns, string $seed, array $settings = [] ): array {
		return [
			'id'       => gms_demo_id( $seed ),
			'elType'   => 'section',
			'settings' => array_merge(
				[
					'content_width' => 'boxed',
					'gap'           => 'extended',
					'padding'       => [
						'unit'     => 'px',
						'top'      => '48',
						'right'    => '0',
						'bottom'   => '48',
						'left'     => '0',
						'isLinked' => false,
					],
				],
				$settings
			),
			'elements' => $columns,
		];
	}
}

if ( ! function_exists( 'gms_page_template' ) ) {
	function gms_page_template( string $title, array $content, array $page_settings = [] ): array {
		return [
			'version'       => '0.4',
			'title'         => $title,
			'type'          => 'page',
			'page_settings' => $page_settings,
			'content'       => $content,
		];
	}
}

if ( ! function_exists( 'gms_button_url' ) ) {
	function gms_button_url( string $path ): array {
		return [
			'url' => '{{site_url}}' . $path,
		];
	}
}

if ( ! function_exists( 'gms_internal_link' ) ) {
	function gms_internal_link( string $path, string $site_url = '{{site_url}}' ): string {
		$path = '/' . ltrim( $path, '/' );

		if ( false !== strpos( $site_url, '{{site_url}}' ) ) {
			return rtrim( $site_url, '/' ) . $path;
		}

		return $path;
	}
}

if ( ! function_exists( 'gms_theme_asset_link' ) ) {
	function gms_theme_asset_link( string $path, string $theme_url = '{{theme_url}}' ): string {
		$path = '/' . ltrim( $path, '/' );

		if ( false !== strpos( $theme_url, '{{theme_url}}' ) ) {
			return rtrim( $theme_url, '/' ) . $path;
		}

		$theme_path = (string) wp_parse_url( $theme_url, PHP_URL_PATH );

		if ( '' !== $theme_path ) {
			return rtrim( $theme_path, '/' ) . $path;
		}

		return $path;
	}
}

if ( ! function_exists( 'gms_service_link' ) ) {
	function gms_service_link( string $slug, string $site_url = '{{site_url}}' ): string {
		return gms_internal_link( '/services/' . $slug . '/', $site_url );
	}
}

if ( ! function_exists( 'gms_story_values' ) ) {
	function gms_story_values( array $values ): array {
		$output = [];

		foreach ( $values as $value ) {
			$output[] = [
				'title' => $value[0],
				'text'  => $value[1],
			];
		}

		return $output;
	}
}

if ( ! function_exists( 'gms_post_items' ) ) {
	function gms_post_items( array $items ): array {
		$output = [];

	foreach ( $items as $item ) {
		$output[] = [
			'meta'    => $item['meta'] ?? '',
			'title'   => $item['title'],
			'excerpt' => $item['excerpt'],
			'image'   => [ 'url' => $item['image'] ],
			'url'     => gms_button_url( '/' . $item['slug'] . '/' ),
		];
		}

		return $output;
	}
}

function gms_get_service_feature_map(): array {
	return [
		'advertising-solutions'      => [
			[ 'AD', 'Custom Advertising' ],
			[ 'CS', 'Creative Solutions' ],
		],
		'ai-solutions'               => [
			[ 'AI', 'Custom AI Systems' ],
			[ 'GEO', 'AI Search Readiness' ],
		],
		'fractional-cmo-services'    => [
			[ 'CMO', 'Strategic Leadership' ],
			[ 'OPS', 'Executive Planning' ],
		],
		'digital-marketing-solutions' => [
			[ 'DM', 'Demand Campaigns' ],
			[ 'ROAS', 'Channel Performance' ],
		],
		'leads-generation-services'  => [
			[ 'LEAD', 'Funnel Optimization' ],
			[ 'SQL', 'Qualified Outreach' ],
		],
		'seo-solutions'              => [
			[ 'SEO', 'Technical SEO' ],
			[ 'AUTH', 'Content Authority' ],
		],
		'aeo'                        => [
			[ 'AEO', 'Answer Engine Strategy' ],
			[ 'SERP', 'Knowledge Structuring' ],
		],
		'geo'                        => [
			[ 'GEO', 'Generative Search Optimization' ],
			[ 'AI', 'AI Citation Signals' ],
		],
		'web-development'            => [
			[ 'DEV', 'Secure Build Systems' ],
			[ 'UX', 'Conversion Architecture' ],
		],
		'content-marketing'          => [
			[ 'CNT', 'Editorial Strategy' ],
			[ 'SERP', 'Search Visibility' ],
		],
		'marketing-strategies'       => [
			[ 'PLAN', 'Growth Planning' ],
			[ 'ROI', 'Channel Priorities' ],
		],
		'social-media-marketing'     => [
			[ 'SMM', 'Channel Strategy' ],
			[ 'ENG', 'Audience Engagement' ],
		],
		'public-relations'           => [
			[ 'PR', 'Media Outreach' ],
			[ 'REP', 'Reputation Building' ],
		],
		'website-audit'              => [
			[ 'AUD', 'Technical Review' ],
			[ 'UX', 'Conversion Insights' ],
		],
		'brand-authority-development' => [
			[ 'AUTH', 'Authority Positioning' ],
			[ 'TRUST', 'Proof Architecture' ],
		],
		'website-hosting-maintenance' => [
			[ 'HOST', 'Managed Hosting' ],
			[ 'UPTM', 'Ongoing Maintenance' ],
		],
		'gbp-management'             => [
			[ 'GBP', 'Local Visibility' ],
			[ 'MAP', 'Profile Optimization' ],
		],
		'sales-funnel-development'   => [
			[ 'FUN', 'Funnel Design' ],
			[ 'CVR', 'Conversion Flow' ],
		],
		'crm-integration-optimization' => [
			[ 'CRM', 'Lifecycle Design' ],
			[ 'OPS', 'Automation Logic' ],
		],
		'sales-coaching'              => [
			[ 'COACH', 'Sales Enablement' ],
			[ 'CLOSE', 'Conversion Coaching' ],
		],
		'growth-consultation-security-company' => [
			[ 'GROW', 'Growth Planning' ],
			[ 'CONS', 'Strategic Consulting' ],
		],
	];
}

function gms_get_service_nav_items( array $services, string $active_slug, string $site_url = '{{site_url}}' ): array {
	$items = [];

	foreach ( $services as $service ) {
		$items[] = [
			'title'     => $service['nav_title'] ?? $service['title'],
			'url'       => [ 'url' => gms_service_link( $service['slug'], $site_url ) ],
			'is_active' => $service['slug'] === $active_slug ? 'yes' : '',
		];
	}

	return $items;
}

function gms_get_service_grid_cards( array $services, string $site_url = '{{site_url}}' ): array {
	$cards = [];

	foreach ( $services as $service ) {
		$cards[] = [
			'icon'        => $service['icon'] ?? 'SRV',
			'title'       => $service['title'],
			'text'        => $service['description'],
			'bullets'     => implode( "\n", $service['bullets'] ),
			'button_text' => 'Learn More',
			'button_url'  => [ 'url' => gms_service_link( $service['slug'], $site_url ) ],
		];
	}

	return $cards;
}

function gms_get_service_detail_widget_settings( array $service, array $config, string $theme_url = '{{theme_url}}', string $site_url = '{{site_url}}' ): array {
	$content       = gms_get_service_detail_content( $service );
	$contact_url   = gms_internal_link( '/contact-us/', $site_url );
	$features      = is_array( $content['features'] ?? null ) ? $content['features'] : [];
	$industries    = is_array( $content['industries'] ?? null ) ? $content['industries'] : [];
	$why_items     = is_array( $content['why_items'] ?? null ) ? $content['why_items'] : [];
	$process_items = is_array( $content['process_items'] ?? null ) ? $content['process_items'] : [];

	return [
		'hero_eyebrow'        => (string) ( $content['hero_eyebrow'] ?? '' ),
		'hero_title'          => (string) ( $content['hero_title'] ?? '' ),
		'hero_subtext'        => (string) ( $content['hero_subtext'] ?? '' ),
		'hero_badges'         => implode( "\n", (array) ( $content['hero_badges'] ?? [] ) ),
		'hero_image'          => [ 'url' => gms_theme_asset_link( '/assets/images/' . (string) ( $content['hero_image'] ?? 'services-hero-art.png' ), $theme_url ) ],
		'hero_primary_text'   => (string) ( $content['cta_primary_text'] ?? __( 'Get Quote', 'grow-my-security' ) ),
		'hero_primary_url'    => [ 'url' => $contact_url ],
		'hero_secondary_text' => __( 'Explore Process', 'grow-my-security' ),
		'hero_secondary_url'  => [ 'url' => '#process' ],
		'about_eyebrow'       => (string) ( $content['about_eyebrow'] ?? __( 'About the Service', 'grow-my-security' ) ),
		'about_title'         => (string) ( $content['about_title'] ?? '' ),
		'about_text'          => implode( "\n\n", (array) ( $content['about_paragraphs'] ?? [] ) ),
		'about_points'        => implode( "\n", (array) ( $content['about_points'] ?? [] ) ),
		'about_image'         => [ 'url' => gms_theme_asset_link( '/assets/images/' . (string) ( $content['about_image'] ?? 'service-overview.png' ), $theme_url ) ],
		'features_eyebrow'    => (string) ( $content['features_eyebrow'] ?? __( 'Key Features', 'grow-my-security' ) ),
		'features_title'      => (string) ( $content['features_title'] ?? '' ),
		'features_text'       => (string) ( $content['features_text'] ?? '' ),
		'features'            => $features,
		'industries_eyebrow'  => (string) ( $content['industries_eyebrow'] ?? __( 'Industries We Serve', 'grow-my-security' ) ),
		'industries_title'    => (string) ( $content['industries_title'] ?? '' ),
		'industries_text'     => (string) ( $content['industries_text'] ?? '' ),
		'industries'          => $industries,
		'why_eyebrow'         => (string) ( $content['why_eyebrow'] ?? __( 'Why Choose Us', 'grow-my-security' ) ),
		'why_title'           => (string) ( $content['why_title'] ?? '' ),
		'why_text'            => (string) ( $content['why_text'] ?? '' ),
		'why_items'           => $why_items,
		'process_eyebrow'     => (string) ( $content['process_eyebrow'] ?? __( 'How It Works', 'grow-my-security' ) ),
		'process_title'       => (string) ( $content['process_title'] ?? '' ),
		'process_text'        => (string) ( $content['process_text'] ?? '' ),
		'process_items'       => $process_items,
		'cta_eyebrow'         => (string) ( $content['cta_eyebrow'] ?? __( 'Request Service', 'grow-my-security' ) ),
		'cta_title'           => (string) ( $content['cta_title'] ?? '' ),
		'cta_text'            => (string) ( $content['cta_text'] ?? '' ),
		'cta_primary_text'    => (string) ( $content['cta_primary_text'] ?? __( 'Get Quote', 'grow-my-security' ) ),
		'cta_primary_url'     => [ 'url' => $contact_url ],
		'cta_secondary_text'  => (string) ( $content['cta_secondary_text'] ?? __( 'Contact Us', 'grow-my-security' ) ),
		'cta_secondary_url'   => [ 'url' => $contact_url ],
		'contact_phone'       => $config['branding']['phone'] ?? '',
	];
}

function gms_get_service_page_hero_settings( array $service, string $theme_url = '{{theme_url}}', string $site_url = '{{site_url}}' ): array {
	return [
		'variant'          => 'detail',
		'eyebrow'          => 'Service',
		'title'            => $service['title'],
		'description'      => $service['description'],
		'art_image'        => [ 'url' => $theme_url . '/assets/images/services-icon.png' ],
		'background_image' => [ 'url' => '' ],
		'primary_text'     => '',
		'primary_url'      => [ 'url' => rtrim( $site_url, '/' ) . '/contact-us/' ],
		'secondary_text'   => '',
		'secondary_url'    => [ 'url' => gms_service_link( $service['slug'], $site_url ) ],
	];
}

function gms_get_service_template( array $service, array $config, string $theme_url = '{{theme_url}}', string $site_url = '{{site_url}}' ): array {
	return gms_page_template(
		$service['title'],
		[
			gms_section_node(
				[
					gms_column_node(
						[
							gms_widget_node( 'gms-service-detail', gms_get_service_detail_widget_settings( $service, $config, $theme_url, $site_url ), 'service-detail-' . $service['slug'] ),
						],
						'service-detail-col-' . $service['slug']
					),
				],
				'service-detail-sec-' . $service['slug'],
				[
					'content_width' => 'full_width',
					'gap'           => 'no',
					'padding'       => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ],
				]
			),
		]
	);
}

function gms_get_demo_config() {
	static $config = null;

	if ( null !== $config ) {
		return $config;
	}

	$config = [
		'branding'      => [
			'company' => 'Grow My Security Company',
			'tagline' => 'Security marketing agency as a strategic asset.',
			'email'   => 'info@growmysecuritycompany.com',
			'phone'   => '+1 (623) 282-1778',
			'address' => 'Chicago, IL, United States',
		],
		'tokens'        => [
			'accent'        => '#ef2014',
			'accent_soft'   => '#aa3d3a',
			'background'    => '#130b0a',
			'background_2'  => '#241816',
			'surface'       => '#2a1110',
			'surface_soft'  => '#332c2b',
			'border'        => 'rgba(255,255,255,0.12)',
			'text'          => '#f5f1ee',
			'text_muted'    => '#c2b8b2',
			'text_subtle'   => '#a5a5a3',
			'heading_font'  => 'Manrope',
			'body_font'     => 'Inter',
			'radius_large'  => '28px',
			'radius_medium' => '18px',
		],
		'hero_slides'   => [
			[
				'label'        => 'Web Development Services',
				'title'        => 'Security-Focused Development for Mission-Critical Systems',
				'copy'         => 'We design, develop, and deploy secure applications for brands where failure is not an option.',
				'primary_cta'  => 'Explore More',
				'secondary_cta'=> 'See How Growth Works',
				'image'        => gms_asset( 'assets/images/homebg.png' ),
			],
			[
				'label'        => 'Trust-Led Positioning',
				'title'        => 'Security-Focused Development for Mission-Critical Systems',
				'copy'         => 'We help technical teams earn credibility and belief, turning what they know into visible trust.',
				'primary_cta'  => 'Explore More',
				'secondary_cta'=> 'See How Growth Works',
				'image'        => gms_asset( 'assets/images/hero-slide-2.png' ),
			],
			[
				'label'        => 'Growth Systems',
				'title'        => 'Security-Focused Development for Mission-Critical Systems',
				'copy'         => 'Built for cybersecurity and regulated-industry companies that need measurable pipeline without compromising trust.',
				'primary_cta'  => 'Explore More',
				'secondary_cta'=> 'See How Growth Works',
				'image'        => gms_asset( 'assets/images/hero-slide-3.png' ),
			],
		],
		'stats'         => [
			[ 'value' => '48', 'label' => 'Personalized onboarding' ],
			[ 'value' => '3.2x', 'label' => 'Increase in leads generation' ],
			[ 'value' => '50%', 'label' => 'Less wasted ad spend' ],
			[ 'value' => '100%', 'label' => 'User retention' ],
		],
		'services'      => [
			[
				'title'       => 'Advertising Solutions',
				'slug'        => 'advertising-solutions',
				'nav_title'   => 'Advertising Solutions',
				'icon'        => 'AD',
				'description' => 'We create tailored marketing strategies that enhance online visibility and drive customer engagement.',
				'bullets'     => [ 'SEO Optimization', 'Content Marketing', 'Social Media Management' ],
			],
			[
				'title'       => 'AI Solutions',
				'slug'        => 'ai-solutions',
				'nav_title'   => 'AI Solutions',
				'icon'        => 'AI',
				'description' => 'Transform your ideas into engaging mobile experiences, providing users with seamless interactions and high functionality.',
				'bullets'     => [ 'SEO Optimization', 'Content Marketing', 'Social Media Management' ],
			],
			[
				'title'       => 'Fractional CMO Services',
				'slug'        => 'fractional-cmo-services',
				'nav_title'   => 'Fractional CMO Services',
				'icon'        => 'CMO',
				'description' => 'We create tailored marketing strategies that enhance online visibility and drive customer engagement.',
				'bullets'     => [ 'SEO Optimization', 'Content Marketing', 'Social Media Management' ],
			],
			[
				'title'       => 'Digital Marketing Solutions',
				'slug'        => 'digital-marketing-solutions',
				'nav_title'   => 'Digital Marketing Solutions',
				'icon'        => 'DM',
				'description' => 'We create tailored marketing strategies that enhance online visibility and drive customer engagement.',
				'bullets'     => [ 'SEO Optimization', 'Content Marketing', 'Social Media Management' ],
			],
			[
				'title'       => 'Leads Generation Services',
				'slug'        => 'leads-generation-services',
				'nav_title'   => 'Leads Generation Services',
				'icon'        => 'LEAD',
				'description' => 'Transform your ideas into engaging mobile experiences, providing users with seamless interactions and high functionality.',
				'bullets'     => [ 'SEO Optimization', 'Content Marketing', 'Social Media Management' ],
			],
			[
				'title'       => 'SEO Solutions',
				'slug'        => 'seo-solutions',
				'nav_title'   => 'SEO Services',
				'icon'        => 'SEO',
				'description' => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
				'bullets'     => [ 'SEO Optimization', 'Content Marketing', 'Social Media Management' ],
			],
			[
				'title'       => 'AEO',
				'slug'        => 'aeo',
				'nav_title'   => 'AEO Services',
				'icon'        => 'AEO',
				'description' => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
				'bullets'     => [ 'Custom Web Design', 'Theme Creation', 'Fast Loading' ],
			],
			[
				'title'       => 'GEO',
				'slug'        => 'geo',
				'nav_title'   => 'GEO Services',
				'icon'        => 'GEO',
				'description' => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
				'bullets'     => [ 'Custom Web Design', 'Theme Creation', 'Fast Loading' ],
			],
			[
				'title'       => 'Web Development',
				'slug'        => 'web-development',
				'nav_title'   => 'Website Development',
				'icon'        => 'DEV',
				'description' => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
				'bullets'     => [ 'Custom Web Design', 'Theme Creation', 'Fast Loading' ],
			],
			[
				'title'       => 'Content Marketing',
				'slug'        => 'content-marketing',
				'nav_title'   => 'Content Marketing',
				'icon'        => 'CNT',
				'description' => 'Build authority-led content systems that answer buyer questions, support SEO, and move prospects toward qualified conversations.',
				'bullets'     => [ 'Editorial strategy', 'Thought leadership content', 'Conversion-focused assets' ],
			],
			[
				'title'       => 'Marketing Strategies',
				'slug'        => 'marketing-strategies',
				'nav_title'   => 'Marketing Strategies',
				'icon'        => 'PLAN',
				'description' => 'Create growth plans that align positioning, channels, budget, and quarterly priorities around measurable business outcomes.',
				'bullets'     => [ 'Market positioning', 'Go-to-market planning', 'Channel prioritization' ],
			],
			[
				'title'       => 'Social Media Marketing',
				'slug'        => 'social-media-marketing',
				'nav_title'   => 'Social Media Marketing',
				'icon'        => 'SMM',
				'description' => 'Turn social channels into trust-building distribution engines that keep your expertise visible to buyers, partners, and recruits.',
				'bullets'     => [ 'Platform strategy', 'Content calendars', 'Paid and organic campaigns' ],
			],
			[
				'title'       => 'Public Relations',
				'slug'        => 'public-relations',
				'nav_title'   => 'Public Relations',
				'icon'        => 'PR',
				'description' => 'Shape a stronger market narrative through media positioning, press outreach, and reputation-building campaigns that elevate credibility.',
				'bullets'     => [ 'Media outreach', 'Thought leadership PR', 'Reputation management' ],
			],
			[
				'title'       => 'Website Audit',
				'slug'        => 'website-audit',
				'nav_title'   => 'Website Audit',
				'icon'        => 'AUD',
				'description' => 'Audit performance, SEO, UX, and trust signals so you can see exactly where your website is losing visibility and conversions.',
				'bullets'     => [ 'Technical diagnostics', 'Conversion review', 'Priority roadmap' ],
			],
			[
				'title'       => 'Brand Authority Development',
				'slug'        => 'brand-authority-development',
				'nav_title'   => 'Brand Authority Development',
				'icon'        => 'AUTH',
				'description' => 'Develop the proof, positioning, and messaging systems that make your company feel established, credible, and easier to choose.',
				'bullets'     => [ 'Authority messaging', 'Proof architecture', 'Brand differentiation' ],
			],
			[
				'title'       => 'Website Hosting and Maintenance',
				'slug'        => 'website-hosting-maintenance',
				'nav_title'   => 'Hosting and Maintenance',
				'icon'        => 'HOST',
				'description' => 'Keep your website fast, secure, updated, and stable with proactive hosting oversight and ongoing technical support.',
				'bullets'     => [ 'Security monitoring', 'Core and plugin updates', 'Performance maintenance' ],
			],
			[
				'title'       => 'GBP Management (Google Business Profile)',
				'slug'        => 'gbp-management',
				'nav_title'   => 'GBP Management',
				'icon'        => 'GBP',
				'description' => 'Improve local visibility and conversion quality through optimized Google Business Profile management, review strategy, and ongoing updates.',
				'bullets'     => [ 'Profile optimization', 'Review response workflows', 'Local visibility reporting' ],
			],
			[
				'title'       => 'Sales Funnel Development',
				'slug'        => 'sales-funnel-development',
				'nav_title'   => 'Sales Funnel Development',
				'icon'        => 'FUN',
				'description' => 'Design full-funnel journeys that guide buyers from first click to booked call with less friction and better intent capture.',
				'bullets'     => [ 'Offer architecture', 'Landing page systems', 'Automation handoffs' ],
			],
			[
				'title'       => 'CRM Integration and Optimization',
				'slug'        => 'crm-integration-optimization',
				'nav_title'   => 'CRM Integration and Optimization',
				'icon'        => 'CRM',
				'description' => 'Connect marketing, sales, and reporting with CRM systems that improve routing, follow-up, and revenue visibility.',
				'bullets'     => [ 'Lifecycle mapping', 'Automation setup', 'Pipeline reporting' ],
			],
			[
				'title'       => 'Sales Coaching',
				'slug'        => 'sales-coaching',
				'nav_title'   => 'Sales Coaching',
				'icon'        => 'COACH',
				'description' => 'Equip your team with the messaging, process discipline, and conversation structure needed to convert more qualified opportunities.',
				'bullets'     => [ 'Sales process coaching', 'Objection handling', 'Closer conversion discipline' ],
			],
			[
				'title'       => 'Growth Consultation For Security Company',
				'slug'        => 'growth-consultation-security-company',
				'nav_title'   => 'Growth Consultation',
				'icon'        => 'GROW',
				'description' => 'Get strategic guidance tailored to security businesses that need clearer positioning, smarter growth priorities, and faster commercial momentum.',
				'bullets'     => [ 'Growth strategy consulting', 'Security-market positioning', 'Quarterly action planning' ],
			],
		],
		'industries'    => [
			'Physical Security Company',
			'Electronic Security Companies & Integrators',
			'Alarm & Monitoring Companies',
			'Executive Protection & Personal Security',
			'Event Security & Crowd Management',
			'Loss Prevention & Retail Security',
			'Private Investigation & Security',
			'Government & Municipal Security Contractors',
			'Camera Monitoring & Access Control',
		],


		'faqs'          => [
			[
				'question' => 'How does this actually drive results?',
				'answer'   => 'In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They do not respond to loud marketing, generic promises, or copy-paste campaigns.',
			],
			[
				'question' => 'What metrics do we use to measure success?',
				'answer'   => 'We focus on qualified pipeline, buyer intent signals, branded search growth, conversion rate, and reduced wasted ad spend.',
			],
			[
				'question' => 'Can we identify studies that highlight our impact?',
				'answer'   => 'Yes. We map content, authority, traffic quality, and lead quality against business outcomes so case studies stay measurable and credible.',
			],
		],
		'testimonials'  => [
			[
				'quote'  => 'This isn’t marketing that chases clicks. It builds confidence, reduces doubt, and shortens decision cycles for security buyers.',
				'name'   => 'Co-founder',
				'role'   => 'Vista Tech',
			],
			[
				'quote'  => 'Grow My Security gave our team a trust-first brand presence that finally matched our technical credibility.',
				'name'   => 'Marketing Director',
				'role'   => 'Secure Systems Group',
			],
		],
		'blog_posts'    => [
			[
				'title'   => 'Why UX/UI Design Can Make or Break Your Website.',
				'slug'    => 'why-ux-ui-design-can-make-or-break-your-website',
				'meta'    => 'Website Design',
				'excerpt' => 'Why the top of the funnel isn’t broken anymore and how better trust design improves conversion before the first call.',
				'content' => "Trust does not start at the form. It starts in the visual and structural cues a buyer sees before they know your team.\n\nFor security brands, that means tighter message hierarchy, stronger proof architecture, and page design that reduces doubt instead of adding it.",
				'image'   => gms_asset( 'assets/images/image-2.png' ),
			],
			[
				'title'   => '5 Must-Have Features for a Modern Business Website.',
				'slug'    => 'five-must-have-features-for-a-modern-business-website',
				'meta'    => 'AI Trend',
				'excerpt' => 'The technical and credibility layers every modern security brand website needs to convert high-intent buyers.',
				'content' => "Modern sites do more than look current. They organize proof, create momentum, and make the next step obvious for serious buyers.\n\nThat means stronger service framing, better conversion pathways, clearer calls to action, and content blocks that support decision-making.",
				'image'   => gms_asset( 'assets/images/image-3.png' ),
			],
			[
				'title'   => 'AI in Content Creation: Friend or Foe? in 2026',
				'slug'    => 'ai-in-content-creation-friend-or-foe-in-2026',
				'meta'    => 'AI Trend',
				'excerpt' => 'A practical view of AI-assisted content systems for regulated industries that cannot afford generic messaging.',
				'content' => "AI is useful when it accelerates research, structure, and iteration. It becomes a liability when it replaces expertise or smooths over critical nuance.\n\nThe winning approach is supervised AI: systems that increase output while keeping the brand voice, claims, and domain accuracy under human control.",
				'image'   => gms_asset( 'assets/images/image-4.png' ),
			],
		],
		'press_items'   => [
			[
				'meta'    => 'Industry Coaching',
				'title'   => 'Grow My Security Company Coaching For The Security Industry',
				'slug'    => 'coaching-for-the-security-industry',
				'excerpt' => 'Authority-focused growth systems built specifically for security companies.',
				'image'   => gms_asset( 'assets/images/image-2.png' ),
			],
			[
				'meta'    => 'Growth Strategy',
				'title'   => 'Grow My Security Company How To Scale Security Marketing',
				'slug'    => 'how-to-scale-security-marketing',
				'excerpt' => 'A field-tested approach to visibility, credibility, and measurable inbound demand.',
				'image'   => gms_asset( 'assets/images/image-3.png' ),
			],
		],
		'podcasts'      => [
			[
				'title'   => 'Building Trust In Security Marketing',
				'slug'    => 'building-trust-in-security-marketing',
				'excerpt' => 'A conversation about authority, trust, and buyer confidence in complex security markets.',
				'image'   => gms_asset( 'assets/images/resources.png' ),
			],
		],
		'pages'         => [
			'home'       => [ 'title' => 'Home', 'slug' => 'home' ],
			'about-us'   => [ 'title' => 'About Us', 'slug' => 'about-us' ],
			'services'   => [ 'title' => 'Services', 'slug' => 'services' ],
			'industries' => [ 'title' => 'Industries', 'slug' => 'industries' ],
			'resources'  => [ 'title' => 'Resources & Insights', 'slug' => 'resources-insights' ],
			'faq'        => [ 'title' => 'FAQ', 'slug' => 'faq' ],
			'contact'    => [ 'title' => 'Contact Us', 'slug' => 'contact-us' ],
			'press'      => [ 'title' => 'Press & Media', 'slug' => 'press-media' ],
			'podcast'    => [ 'title' => 'Podcast', 'slug' => 'podcast' ],
			'single-service' => [ 'title' => 'Single Service Page', 'slug' => 'single-service-page' ],
			'single-blog'    => [ 'title' => 'Single Blog Page', 'slug' => 'single-blog-page' ],
			'single-press'   => [ 'title' => 'Single Press Page', 'slug' => 'single-press-page' ],
			'website-audit'  => [ 'title' => 'Website Audit', 'slug' => 'website-audit' ],
		],
	];

	return $config;
}


function gms_get_demo_page( $slug ) {
	$config = gms_get_demo_config();

	return $config['pages'][ $slug ] ?? [];
}

/**
 * Shared industry helpers used by Elementor, templates, and demo imports.
 */
if ( ! function_exists( 'gms_clean_industry_name' ) ) {
	function gms_clean_industry_name( string $industry_name ): string {
		$clean = function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $industry_name ) : strip_tags( $industry_name );
		$clean = preg_replace( '/\bindustr(?:y|ies)\b/i', '', (string) $clean );
		$clean = preg_replace( '/\s+/', ' ', (string) $clean );

		return trim( (string) $clean, ' -' );
	}
}

if ( ! function_exists( 'gms_slugify_industry_name' ) ) {
	function gms_slugify_industry_name( string $industry_name ): string {
		$clean = strtolower( gms_clean_industry_name( $industry_name ) );
		$clean = str_replace( '&', ' and ', $clean );

		if ( function_exists( 'sanitize_title' ) ) {
			return sanitize_title( $clean );
		}

		$clean = preg_replace( '/[^a-z0-9]+/', '-', (string) $clean );

		return trim( (string) $clean, '-' );
	}
}

if ( ! function_exists( 'gms_get_industry_page_map' ) ) {
	function gms_get_industry_page_map(): array {
		return [
			'Physical Security Company' => [
				'slug'    => 'contract-security',
				'icon'    => 'fas fa-user-shield',
				'summary' => 'Position guarding and patrol services as the trusted choice for commercial buyers.',
			],
			'Electronic Security Companies & Integrators' => [
				'slug'    => 'electronic-security',
				'icon'    => 'fas fa-microchip',
				'summary' => 'Showcase integration expertise and win larger electronic security projects.',
			],
			'Alarm & Monitoring Companies' => [
				'slug'    => 'alarm-monitoring',
				'icon'    => 'fas fa-broadcast-tower',
				'summary' => 'Capture high-intent monitoring demand with a trust-led digital presence.',
			],
			'Executive Protection & Personal Security' => [
				'slug'    => 'executive-protection',
				'icon'    => 'fas fa-user-secret',
				'summary' => 'Signal discretion, readiness, and premium authority for personal protection services.',
			],
			'Event Security & Crowd Management' => [
				'slug'    => 'event-security',
				'icon'    => 'fas fa-users',
				'summary' => 'Present large-scale event coverage as a polished, dependable service offering.',
			],
			'Loss Prevention & Retail Security' => [
				'slug'    => 'loss-prevention',
				'icon'    => 'fas fa-shopping-cart',
				'summary' => 'Demonstrate operational control for retail, shrink reduction, and site protection.',
			],
			'Private Investigation & Security' => [
				'slug'    => 'private-investigation',
				'icon'    => 'fas fa-search',
				'summary' => 'Frame investigative services with clarity, credibility, and high-trust positioning.',
			],
			'Government & Municipal Security Contractors' => [
				'slug'    => 'government-security',
				'icon'    => 'fas fa-landmark',
				'summary' => 'Highlight compliance, public-sector experience, and mission-ready delivery.',
			],
			'Camera Monitoring & Access Control' => [
				'slug'    => 'camera-monitoring',
				'icon'    => 'fas fa-video',
				'summary' => 'Convert surveillance and access control expertise into clear buyer confidence.',
			],
		];
	}
}

if ( ! function_exists( 'gms_get_industry_card_data' ) ) {
	function gms_get_industry_card_data( string $industry_name ): array {
		$map           = gms_get_industry_page_map();
		$requested_key = gms_slugify_industry_name( $industry_name );

		foreach ( $map as $label => $data ) {
			if ( $requested_key === gms_slugify_industry_name( $label ) || $requested_key === ( $data['slug'] ?? '' ) ) {
				return array_merge(
					[
						'label'   => gms_clean_industry_name( $label ),
						'slug'    => $requested_key,
						'icon'    => 'fas fa-shield-alt',
						'summary' => 'Specialized trust-first growth systems for security-focused buyers.',
					],
					$data
				);
			}
		}

		return [
			'label'   => gms_clean_industry_name( $industry_name ),
			'slug'    => $requested_key ?: 'security-industry',
			'icon'    => 'fas fa-shield-alt',
			'summary' => 'Specialized trust-first growth systems for security-focused buyers.',
		];
	}
}

if ( ! function_exists( 'gms_get_industry_summary' ) ) {
	function gms_get_industry_summary( string $industry_name ): string {
		$data = gms_get_industry_card_data( $industry_name );

		return $data['summary'];
	}
}

if ( ! function_exists( 'gms_get_industry_slug' ) ) {
	function gms_get_industry_slug( string $industry_name ): string {
		$data = gms_get_industry_card_data( $industry_name );

		return $data['slug'];
	}
}

if ( ! function_exists( 'gms_get_industry_url' ) ) {
	function gms_get_industry_url( string $industry_name, string $site_url = '' ): string {
		$base = $site_url;

		if ( '' === $base ) {
			$base = function_exists( 'home_url' ) ? home_url( '/' ) : '{{site_url}}/';
		}

		$base = rtrim( $base, '/' );

		return $base . '/industries/' . gms_get_industry_slug( $industry_name ) . '/';
	}
}

/**
 * Get Font Awesome icon mapping for industries.
 */
function gms_get_industry_icon( string $industry_name ): array {
	$data = gms_get_industry_card_data( $industry_name );

	return [
		'value'   => $data['icon'] ?? 'fas fa-shield-alt',
		'library' => 'fa-solid',
	];
}

if ( ! function_exists( 'gms_get_industry_detail_widget_settings' ) ) {
	function gms_get_industry_detail_widget_settings( string $slug, string $theme_url = '{{theme_url}}', string $site_url = '{{site_url}}' ): array {
		$data        = gms_get_industry_data( $slug );
		$card_data   = gms_get_industry_card_data( $slug );
		$hero_image  = $data['images']['hero'] ?? 'Industry.png';
		$visual_one  = $data['images']['visual1'] ?? 'Services-1.png';
		$visual_two  = $data['images']['visual2'] ?? 'Resources.png';
		$results     = is_array( $data['results'] ?? null ) ? $data['results'] : [];
		$features    = is_array( $data['features'] ?? null ) ? $data['features'] : [];
		$process     = is_array( $data['process'] ?? null ) ? $data['process'] : [];
		$hero_title  = (string) ( $data['hero']['title'] ?? $card_data['label'] ?? '' );
		$hero_copy   = (string) ( $data['hero']['subtext'] ?? '' );
		$problem     = is_array( $data['problem'] ?? null ) ? $data['problem'] : [];
		$solution    = is_array( $data['solution'] ?? null ) ? $data['solution'] : [];
		$benefit_map = [
			[ 'icon' => 'target', 'description' => 'Stronger positioning helps your team turn visibility into better-fit enquiries.' ],
			[ 'icon' => 'layers', 'description' => 'A clearer buyer journey reduces wasted effort and improves conversion quality.' ],
			[ 'icon' => 'star', 'description' => 'Consistent authority signals build trust earlier and shorten buying cycles.' ],
		];
		$benefits    = [];

		foreach ( $results as $index => $result ) {
			$benefits[] = [
				'icon'        => $benefit_map[ $index ]['icon'] ?? 'shield',
				'stat'        => (string) ( $result['stat'] ?? '' ),
				'title'       => (string) ( $result['label'] ?? __( 'Measurable growth', 'grow-my-security' ) ),
				'description' => $benefit_map[ $index ]['description'] ?? __( 'A cleaner digital experience supports stronger pipeline conversations.', 'grow-my-security' ),
			];
		}

		return [
			'hero_eyebrow'          => (string) ( $data['hero']['eyebrow'] ?? __( 'Industry', 'grow-my-security' ) ),
			'hero_title'            => $hero_title,
			'hero_subtext'          => $hero_copy,
			'hero_image'            => [ 'url' => $theme_url . '/assets/images/' . $hero_image ],
			'hero_primary_text'     => __( 'Get Started', 'grow-my-security' ),
			'hero_primary_url'      => [ 'url' => '#cta' ],
			'hero_secondary_text'   => __( 'View Solutions', 'grow-my-security' ),
			'hero_secondary_url'    => [ 'url' => '#solutions' ],
			'overview_eyebrow'      => __( 'Overview', 'grow-my-security' ),
			'overview_title'        => (string) ( $problem['title'] ?? __( 'Industry Overview', 'grow-my-security' ) ),
			'overview_text'         => (string) ( $solution['desc'] ?? $hero_copy ),
			'overview_points'       => implode( "\n", (array) ( $problem['points'] ?? [] ) ),
			'overview_image'        => [ 'url' => $theme_url . '/assets/images/' . $visual_one ],
			'overview_note_title'   => __( 'What this page helps you solve', 'grow-my-security' ),
			'overview_note_text'    => $hero_copy,
			'solutions_eyebrow'     => __( 'Key Services / Solutions', 'grow-my-security' ),
			'solutions_title'       => (string) ( $solution['title'] ?? __( 'Key Services / Solutions', 'grow-my-security' ) ),
			'solutions_text'        => (string) ( $solution['desc'] ?? '' ),
			'solutions_features'    => $features,
			'benefits_eyebrow'      => __( 'Benefits', 'grow-my-security' ),
			'benefits_title'        => __( 'Outcomes built for measurable growth', 'grow-my-security' ),
			'benefits_text'         => __( 'Every engagement is structured to improve trust, sharpen positioning, and create better sales conversations.', 'grow-my-security' ),
			'benefits_items'        => $benefits,
			'why_eyebrow'           => __( 'Why Choose Us', 'grow-my-security' ),
			'why_title'             => __( 'A clear delivery model built for trust-sensitive buying cycles', 'grow-my-security' ),
			'why_text'              => __( 'We combine positioning, execution, and ongoing refinement so your industry page feels aligned, credible, and conversion-ready.', 'grow-my-security' ),
			'why_image'             => [ 'url' => $theme_url . '/assets/images/' . $visual_two ],
			'why_steps'             => $process,
			'cta_eyebrow'           => __( 'CTA', 'grow-my-security' ),
			'cta_title'             => __( 'Want to grow your business in this industry?', 'grow-my-security' ),
			'cta_text'              => __( 'Let us build a cleaner, more credible growth experience for your market so your next conversation starts with trust already in place.', 'grow-my-security' ),
			'cta_primary_text'      => __( 'Get Started', 'grow-my-security' ),
			'cta_primary_url'       => [ 'url' => rtrim( $site_url, '/' ) . '/contact-us/' ],
			'cta_secondary_text'    => __( 'Contact Us', 'grow-my-security' ),
			'cta_secondary_url'     => [ 'url' => rtrim( $site_url, '/' ) . '/contact-us/' ],
		];
	}
}

if ( ! function_exists( 'gms_get_industry_template' ) ) {
	function gms_get_industry_template( string $page_title, string $slug, string $theme_url = '{{theme_url}}', string $site_url = '{{site_url}}' ): array {
		return gms_page_template(
			$page_title,
			[
				gms_section_node(
					[
						gms_column_node(
							[
								gms_widget_node(
									'gms-industry-detail',
									gms_get_industry_detail_widget_settings( $slug, $theme_url, $site_url ),
									'industry-detail-' . $slug
								),
							],
							'industry-detail-col-' . $slug
						),
					],
					'industry-detail-sec-' . $slug,
					[
						'content_width' => 'full_width',
						'gap'           => 'no',
						'padding'       => [
							'unit'     => 'px',
							'top'      => '0',
							'right'    => '0',
							'bottom'   => '0',
							'left'     => '0',
							'isLinked' => false,
						],
					]
				),
			]
		);
	}
}
