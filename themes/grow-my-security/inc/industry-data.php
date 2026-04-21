<?php
/**
 * Industry Specific Data Mapping
 *
 * This file contains the localized data for each dynamically generated industry service page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gms_get_industry_data( $slug ) {

	$default_process = [
		[ 'title' => 'Analysis & Audit', 'desc' => 'We audit your existing digital footprint to find specific trust gaps and conversion blockers.' ],
		[ 'title' => 'Strategic Positioning', 'desc' => 'We reposition your brand to speak directly to risk-averse security buyers.' ],
		[ 'title' => 'System Execution', 'desc' => 'We implement SEO, targeted advertising, and high-quality content systems.' ],
		[ 'title' => 'Growth & Optimization', 'desc' => 'We continually refine the system based on data to drive higher quality inbound leads.' ]
	];

	$default_results = [
		[ 'stat' => '3X', 'label' => 'Increase in qualified inbound leads' ],
		[ 'stat' => '65%', 'label' => 'Reduction in wasted advertising spend' ],
		[ 'stat' => '#1', 'label' => 'Authority positioning in local markets' ],
	];

	$industries = [
		'contract-security' => [
			'hero' => [
				'eyebrow' => 'Contract Security',
				'title'   => 'Marketing Solutions for Contract Security Companies',
				'subtext' => 'Build trust, generate qualified local leads, and outpace regional competitors with compliant, data-driven brand visibility.'
			],
			'images' => [
				'hero'    => 'contract-security-hero.png',
				'visual1' => 'security-tech-visual.png',
				'visual2' => 'security-dashboard-visual.png',
			],
			'problem' => [
				'title' => 'The Visibility Struggle in Guard Services',
				'points' => [
					'Buyers only see "guards" as a commodity, leading to race-to-the-bottom pricing margins.',
					'Lack of digital trust components makes it hard to close premium commercial contracts.',
					'Outdated websites fail to convey the elite professional standards of your personnel.'
				]
			],
			'solution' => [
				'title' => 'Elevating Authority over Price',
				'desc'  => 'We develop digital ecosystems that position your guard firm as the elite regional choice.',
				'points' => [
					'Local SEO dominance to capture active commercial intent.',
					'Authority-driven messaging to highlight your strict hiring standards.',
					'Conversion-focused landing pages for specific patrol and stationary services.'
				]
			],
			'features' => [
				[ 'icon' => 'search', 'title' => 'Local Search Dominance', 'desc' => 'Rank #1 in your city when commercial buyers search for immediate patrol services.' ],
				[ 'icon' => 'shield', 'title' => 'Trust-First Web Design', 'desc' => 'A visually premium website that matches the discipline of your physical guards.' ],
				[ 'icon' => 'target', 'title' => 'B2B Lead Generation', 'desc' => 'Targeted campaigns aimed directly at property managers and facility directors.' ]
			],
			'process' => $default_process,
			'results' => $default_results,
		],
		'electronic-security' => [
			'hero' => [
				'eyebrow' => 'Electronic Security',
				'title'   => 'Scaling Systems Integrators & Electronic Security',
				'subtext' => 'Capture high-value commercial integration projects by proving deep technical competence before the proposal phase.'
			],
			'images' => [
				'hero'    => 'electronic-security-hero.png',
				'visual1' => 'security-tech-visual.png',
				'visual2' => 'security-dashboard-visual.png',
			],
			'problem' => [
				'title' => 'Selling Complex Tech in a Noisy Market',
				'points' => [
					'Buyers are uneducated on the nuances of disparate system integrations.',
					'Generic marketing fails to communicate your technical engineering superiority.',
					'Marketing is historically disconnected from long-cycle enterprise sales.'
				]
			],
			'solution' => [
				'title' => 'Technical Competence Made Visible',
				'desc'  => 'We craft messaging and visibility systems that demonstrate your integration mastery to technical buyers.',
				'points' => [
					'Case-study driven content marketing.',
					'Highly-targeted B2B campaigns reaching IT and Facility directors.',
					'Educational lead-nurturing funnels.'
				]
			],
			'features' => [
				[ 'icon' => 'cpu', 'title' => 'Technical Content Strategy', 'desc' => 'Deep-dive authoritative content that speaks directly to IT and security managers.' ],
				[ 'icon' => 'users', 'title' => 'Account-Based Ads', 'desc' => 'Precision advertising to target decision makers at specific enterprise facilities.' ],
				[ 'icon' => 'zap', 'title' => 'Frictionless Funnels', 'desc' => 'Streamline the long-cycle sales process with automated B2B nurture systems.' ]
			],
			'process' => $default_process,
			'results' => $default_results,
		],
		'alarm-monitoring' => [
			'hero' => [
				'eyebrow' => 'Alarm & Monitoring',
				'title'   => 'Growth Systems for Alarm & Monitoring Centers',
				'subtext' => 'Reduce customer acquisition costs (CAC) and scale your Recurring Monthly Revenue (RMR) with precision marketing.'
			],
			'images' => [
				'hero'    => 'alarm-monitoring-hero.png',
				'visual1' => 'security-tech-visual.png',
				'visual2' => 'security-dashboard-visual.png',
			],
			'problem' => [
				'title' => 'The Cost of Customer Acquisition',
				'points' => [
					'Escalating ad costs make direct-to-consumer monitoring sales unprofitable.',
					'Extremely high competition relies on aggressive, outdated sales tactics.',
					'High churn rates dilute the value of new subscriber acquisitions.'
				]
			],
			'solution' => [
				'title' => 'Optimizing the RMR Growth Engine',
				'desc'  => 'We build scalable lead generation pathways that lower CAC while targeting higher-LTV subscriber bases.',
				'points' => [
					'Hyper-targeted geo-fenced marketing campaigns.',
					'Retargeting systems to maximize marketing ROI.',
					'Review-generation systems to build dominant local proof.'
				]
			],
			'features' => [
				[ 'icon' => 'trending-down', 'title' => 'CAC Reduction', 'desc' => 'Implementing data-driven strategies to lower the cost to acquire new subscribers.' ],
				[ 'icon' => 'star', 'title' => 'Reputation Management', 'desc' => 'Automated systems pushing high-rating reviews to boost local trust instantly.' ],
				[ 'icon' => 'mouse-pointer', 'title' => 'High-Converting Ads', 'desc' => 'Search ads triggered precisely when homeowners and businesses seek immediate upgrades.' ]
			],
			'process' => $default_process,
			'results' => $default_results,
		],
	];

	// Create generic fallback for the other 6.
	$generic_fallback = [
		'hero' => [
			'eyebrow' => 'Security Services',
			'title'   => 'Strategic B2B Marketing for Security Providers',
			'subtext' => 'Build trust, generate qualified leads, and grow your specialized security business with compliant, data-driven systems.'
		],
		'problem' => [
			'title' => 'The Growth Hurdle in Modern Security',
			'points' => [
				'Security buyers require immense trust before making contact.',
				'Standard marketing tactics fail to convey your specific operational expertise.',
				'Unpredictable inward lead flow makes scaling operations difficult.'
			]
		],
		'solution' => [
			'title' => 'A Tailored Trust-Building Framework',
			'desc'  => 'We deploy high-end digital infrastructure to ensure you capture and close high-intent security leads.',
			'points' => [
				'Authority positioning and premium messaging.',
				'Compliance-focused strategic campaigns.',
				'Predictable and targeted lead generation funnels.'
			]
		],
		'features' => [
			[ 'icon' => 'search', 'title' => 'Targeted SEO Optimization', 'desc' => 'Dominating search results where your exact highly-qualified prospects are looking.' ],
			[ 'icon' => 'layers', 'title' => 'Premium Web Presence', 'desc' => 'Translating your real-world capability into a trusted digital experience.' ],
			[ 'icon' => 'pie-chart', 'title' => 'Analytics & Data', 'desc' => 'Transparent reporting tying marketing investments directly to signed contracts.' ]
		],
		'process' => $default_process,
		'results' => $default_results,
	];

	if ( isset( $industries[ $slug ] ) ) {
		return $industries[ $slug ];
	}

	// For the remaining generic pages, replace the title with a dynamic one just to look somewhat tailored.
	$page = get_page_by_path( 'services/' . $slug );
	if ( $page ) {
		$generic_fallback['hero']['eyebrow'] = $page->post_title;
		$generic_fallback['hero']['title'] = 'Growth Marketing for ' . $page->post_title;
	}

	return $generic_fallback;
}
