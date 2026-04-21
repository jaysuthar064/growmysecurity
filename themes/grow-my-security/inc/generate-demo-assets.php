<?php
/**
 * Generate Elementor page JSON and theme import data.
 *
 * Usage:
 * php wp-content/themes/grow-my-security/inc/generate-demo-assets.php
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ );
}

require_once __DIR__ . '/demo-data.php';

function gms_card_items_from_industries( array $industries ): array {
	$items = [];

	foreach ( $industries as $industry ) {
		$clean_title = gms_clean_industry_name( $industry );
		$items[]     = [
			'meta'        => '',
			'icon'        => gms_get_industry_icon( $clean_title ),
			'title'       => $clean_title,
			'text'        => gms_get_industry_summary( $clean_title ),
			'bullets'     => '',
			'button_text' => 'Learn More',
			'button_url'  => gms_button_url( '/industries/' . gms_get_industry_slug( $clean_title ) . '/' ),
		];
	}

	return $items;
}

$config   = gms_get_demo_config();
$themeDir = dirname( __DIR__ );
$pluginDir = dirname( dirname( dirname( __DIR__ ) ) ) . '/plugins/gms-demo-importer';
$pagesDir = $pluginDir . '/data/pages';

if ( ! is_dir( $pagesDir ) ) {
	mkdir( $pagesDir, 0775, true );
}

$hero_single = static function ( string $label, string $title, string $copy, string $image ) {
	return [
		[
			'label'          => $label,
			'title'          => $title,
			'copy'           => $copy,
			'image'          => [ 'url' => $image ],
			'primary_text'   => 'Explore More',
			'primary_url'    => gms_button_url( '/contact-us/' ),
			'secondary_text' => 'See How Growth Works',
			'secondary_url'  => gms_button_url( '/about-us/' ),
		],
	];
};

$pages = [
	'home' => gms_page_template(
		'Home',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-hero', [
				'slides' => [
					[
						'layout'         => 'centered',
						'label'          => 'Security Marketing Agency',
						'title'          => 'Turn Your Brand Into Your Most Powerful Strategic Asset.',
						'copy'           => 'We help cybersecurity and regulated-industry companies generate qualified pipeline through compliant, data-backed marketing systems without compromising trust, privacy, or security.',
						'image'          => [ 'url' => '{{theme_url}}/assets/images/homebg.png' ],
						'art_image'      => [ 'url' => '' ],
						'primary_text'   => 'Request a Free Consultation',
						'primary_url'    => gms_button_url( '/contact-us/' ),
						'secondary_text' => 'See How Growth Works',
						'secondary_url'  => gms_button_url( '/about-us/' ),
						'feature_points' => "Personalized onboarding\nComprehensive analytics\nDedicated support",
						'scroll_label'   => 'Scroll to explore',
					],
				],
			], 'home-hero' ) ], 'home-hero-col' ) ], 'home-hero-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'layout'        => 'problem-list',
				'eyebrow'       => 'The Problem',
				'title'         => 'Security Brands Struggle Because Their Expertise Isn’t Visible or Trusted Online.',
				'description'   => 'In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They don’t respond to loud marketing, generic promises, or copy-paste campaigns. They look for credibility, authority, and proof long before they ever reach out.',
				'supporting_text' => 'As a result, potential clients visit, hesitate, and leave. The value isn’t communicated in a way buyers believe.',
				'copy_secondary' => 'This is the gap we focus on closing.',
				'image'         => [ 'url' => '' ],
				'values'        => gms_story_values(
					[
						[ 'Invisible Profile', 'Without a clear digital footprint, high-value clients cannot find you. Your agency exists in the shadows of the internet.' ],
						[ 'Trust Deficit', 'Security is bought on trust. If your brand looks generic, clients assume your security protocols are too.' ],
						[ '“Fake” Perception', 'Generic visuals create a shell-company aesthetic. Real authority requires distinct, verified, and credible branding.' ],
					]
				),
			], 'home-problem' ) ], 'home-problem-col' ) ], 'home-problem-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'layout'         => 'media-content',
				'eyebrow'        => 'The Solution',
				'title'          => 'Smarter Marketing, Trust-led Positioning Built Specifically For the Security Industries',
				'description'    => 'We help security companies build visible trust through systems designed around how security buyers actually think and buy. From positioning and messaging to content, search visibility, and lead pathways, every element works together to answer one question:',
				'highlight_text' => '“Can I trust this company with something critical?”',
				'supporting_text'=> 'Instead of disconnected campaigns, we build a structured growth engine that makes your brand a credible authority in your niche.',
				'copy_secondary'=> 'This isn’t marketing for clicks. This is marketing that builds confidence, reduces doubt, and shortens decision cycles because in security, trust is the real conversion factor.',
				'bullets'        => "Establishes your brand as a credible authority in your niche\nCommunicates expertise without overselling or exaggeration\nAligns marketing with real security buyer intent\nConverts visibility into qualified, high-trust inquiries",
				'chips'          => "3.2x increase in leads generation\n100% User Retention\n50% less wasted ad spend",
				'button_text'    => 'Schedule a Free Consultation',
				'button_url'     => gms_button_url( '/contact-us/' ),
				'image'          => [ 'url' => '{{theme_url}}/assets/images/home-solution-media.png' ],
				'values'         => [],
			], 'home-solution' ) ], 'home-solution-col' ) ], 'home-solution-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-process-timeline', [
				'eyebrow'     => 'Our Guarantee',
				'title'       => 'How We Help Security Brands Win Trust & Growth',
				'description' => 'We work as a strategic growth partner for security-focused brands, helping them move from being technically capable to being clearly trusted in the market. Every engagement is designed around long-term authority, not short-term spikes.',
				'items'       => [
					[
						'accent'    => 'Clear Positioning',
						'title'     => 'in a Crowded Market',
						'text'      => 'We help you articulate what you do, who you’re for, and why you’re different, without jargon or overclaiming.',
						'link_text' => 'Learn More',
						'link_url'  => gms_button_url( '/about-us/' ),
						'icon'      => 'shield',
					],
					[
						'accent'    => 'Trust-First',
						'title'     => 'Brand Presence',
						'text'      => 'From messaging and content to design and digital touchpoints, we ensure your brand looks, sounds, and feels credible across every channel.',
						'link_text' => 'See How This Works',
						'link_url'  => gms_button_url( '/services/' ),
						'icon'      => 'users',
					],
					[
						'accent'    => 'Buyer-Aligned',
						'title'     => 'Demand Generation',
						'text'      => 'We focus on attracting decision-makers who are already searching for solutions like yours, guiding them with the right information at each stage.',
						'link_text' => 'Explore More',
						'link_url'  => gms_button_url( '/resources-insights/' ),
						'icon'      => 'target',
					],
					[
						'accent'    => 'Consistency Over',
						'title'     => 'Campaigns',
						'text'      => 'Instead of disconnected tactics, we build a repeatable system that compounds visibility, authority, and inbound demand over time.',
						'link_text' => 'Get Started',
						'link_url'  => gms_button_url( '/contact-us/' ),
						'icon'      => 'link',
					],
				],
			], 'home-process' ) ], 'home-process-col' ) ], 'home-process-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-testimonials', [
				'layout'          => 'featured',
				'background_word' => 'Testimonials',
				'items'           => [
					[
						'quote' => 'Anthony Rumore is exactly the kind of man you want to do business with. He is patient, professional and extremely knowledgeable. The most amazing quality, above all else, he is trustworthy and honest. In an era where it is very easy to choose the wrong person to work with, Anthony is dependable, reliable, and you can count on what he says he will accomplish for you. Who you work with truly matters.',
						'name'  => 'Valerie LaBianca',
						'role'  => 'Co-founder | VistaTech',
					],
				],
			], 'home-testimonials' ) ], 'home-testimonials-col' ) ], 'home-testimonials-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-icon-grid', [
				'eyebrow'     => 'Verticals',
				'title'       => 'Security Verticals Supported',
				'description' => 'If your buyers are technical and your product is complex, you’re in the right place.',

				'items'       => [
					[ 'title' => 'Physical Security Company', 'icon' => 'guard' ],
					[ 'title' => 'Electronic Security Companies & Integrators', 'icon' => 'bolt' ],
					[ 'title' => 'Alarm & Monitoring Companies', 'icon' => 'alarm' ],
					[ 'title' => 'Executive Protection & Personal Security', 'icon' => 'user' ],
					[ 'title' => 'Event Security & Crowd Management', 'icon' => 'team' ],
					[ 'title' => 'Loss Prevention & Retail Security', 'icon' => 'retail' ],
					[ 'title' => 'Private Investigation & Security', 'icon' => 'investigation' ],
					[ 'title' => 'Government & Municipal Security Contractors', 'icon' => 'building' ],
					[ 'title' => 'Camera Monitoring & Access Control', 'icon' => 'camera' ],
				],
				'button_text' => 'Schedule a Free Consultation',
				'button_url'  => gms_button_url( '/contact-us/' ),
				'footer_text' => 'Don’t see your industry? Contact us to see if we can help you.',
			], 'home-industries' ) ], 'home-industries-col' ) ], 'home-industries-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-services-accordion', [
				'eyebrow'     => 'Our Services',
				'title'       => 'Building Security Solutions with Intelligent Services',
				'description' => 'If your buyers are technical and your product is complex, you’re in the right place.',
				'image'       => [ 'url' => '{{theme_url}}/assets/images/home-services-media.png' ],
				'button_text' => 'View All Services',
				'button_url'  => gms_button_url( '/services/' ),
				'items'       => [
					[
						'title'   => 'Marketing Services',
						'summary' => 'If your buyers are technical and your product is complex, you’re in the right place.',
						'tags'    => "SEO\nAEO\nGEO",
					],
					[
						'title'   => 'Leads Generation',
						'summary' => 'Demand systems that attract decision-makers already looking for solutions like yours.',
						'tags'    => '',
					],
					[
						'title'   => 'Fractional CMO',
						'summary' => 'Senior strategic guidance to align positioning, content, and pipeline growth.',
						'tags'    => '',
					],
					[
						'title'   => 'Web Development',
						'summary' => 'Trust-building web experiences designed for security brands and complex services.',
						'tags'    => '',
					],
					[
						'title'   => 'AI Solutions',
						'summary' => 'Responsible AI-enhanced workflows that improve output without compromising credibility.',
						'tags'    => '',
					],
				],
			], 'home-services' ) ], 'home-services-col' ) ], 'home-services-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-faq', [
				'layout'               => 'split',
				'eyebrow'              => 'FAQ’s',
				'title'                => 'Frequently Asked Questions',
				'description'          => 'In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They don’t respond to loud marketing, generic promises, or copy-paste campaigns.',
				'primary_button_text'  => 'Schedule a Free Consultation',
				'primary_button_url'   => gms_button_url( '/contact-us/' ),
				'secondary_button_text'=> 'Check All FAQ',
				'secondary_button_url' => gms_button_url( '/faq/' ),
				'footer_text'          => 'Do you have anymore questions for us?',
				'footer_link_text'     => 'Contact Us',
				'footer_link_url'      => gms_button_url( '/contact-us/' ),
				'items'                => [
					[
						'question' => 'How does this actually drive results?',
						'answer'   => 'In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They don’t respond to loud marketing, generic promises, or copy-paste campaigns.',
					],
					[
						'question' => 'What metrics do we use to measure success?',
						'answer'   => 'We focus on qualified pipeline, buyer intent signals, branded search growth, conversion rate, and reduced wasted ad spend.',
					],
					[
						'question' => 'Can we identify studies that highlight our impact?',
						'answer'   => 'Yes. We map authority, traffic quality, lead quality, and sales outcomes into measurable case studies.',
					],
					[
						'question' => 'How long does it take to see traction?',
						'answer'   => 'The first wins usually come from clearer positioning and sharper buyer-facing pages, while compounding SEO and authority gains build over the following months.',
					],
				],
			], 'home-faq' ) ], 'home-faq-col' ) ], 'home-faq-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'     => 'Blogs',
				'title'       => 'Updated Journal',
				'description' => 'Strategists dedicated to creating stunning, functional websites that align with your unique business goals.',
				'items'       => [
					[
						'meta'    => 'Website Design',
						'title'   => 'Why UX/UI Design Can Make or Break Your Website.',
						'excerpt' => 'Why the top of the funnel isn’t broken anymore and how better trust design improves conversion before the first call.',
						'image'   => [ 'url' => '{{theme_url}}/assets/images/home-journal-1.png' ],
						'url'     => gms_button_url( '/why-ux-ui-design-can-make-or-break-your-website/' ),
					],
					[
						'meta'    => 'AI Trend',
						'title'   => '5 Must-Have Features for a Modern Business Website.',
						'excerpt' => 'The technical and credibility layers every modern security brand website needs to convert high-intent buyers.',
						'image'   => [ 'url' => '{{theme_url}}/assets/images/home-journal-2.png' ],
						'url'     => gms_button_url( '/five-must-have-features-for-a-modern-business-website/' ),
					],
					[
						'meta'    => 'AI Trend',
						'title'   => 'AI in Content Creation: Friend or Foe? in 2026',
						'excerpt' => 'A practical view of AI-assisted content systems for regulated industries that cannot afford generic messaging.',
						'image'   => [ 'url' => '{{theme_url}}/assets/images/home-journal-3.png' ],
						'url'     => gms_button_url( '/ai-in-content-creation-friend-or-foe-in-2026/' ),
					],
				],
				'button_text' => '',
				'button_url'  => gms_button_url( '/resources-insights/' ),
			], 'home-posts' ) ], 'home-posts-col' ) ], 'home-posts-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-contact-form', [
				'eyebrow'       => 'Contact Us',
				'title'         => 'Get your free quote',
				'description'   => 'Supercharge your online presence with a tailored digital marketing strategy. Fill out the form today to get a personalized consultation and quote within 24-48 hours.',
				'email'         => 'info@growmysecuritycompany.com',
				'phone'         => '(623) 282-1778',
				'address'       => 'Chicago, IL, United States',
				'hours'         => 'Monday-Friday, 09:00AM - 06:00PM',
				'services_list' => "Branding Services\nSearch Engine Optimization\nFractional CMO Services\nSocial Media Marketing\nWebsite Design\nWebsite Development\nAdvertising Services\nAI Solutions",
				'footer_chips'  => "24-48 hour response\nNo spam, ever\nLocal experts",
			], 'home-contact' ) ], 'home-contact-col' ) ], 'home-contact-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ] ] ),
		]
	),
	'about-us' => gms_page_template(
		'About Us',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'eyebrow'     => 'About Us',
				'title'       => 'Turn expertise into trust that scales',
				'description' => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
				'image'       => [ 'url' => '{{theme_url}}/assets/images/image-2.png' ],
				'values'      => gms_story_values(
					[
						[ 'Truth Over Tactics', 'Security buyers respond to clear authority, not hype.' ],
						[ 'Empathy in Action', 'We build for cautious, risk-aware audiences.' ],
						[ 'Active Listening', 'Deep discovery produces stronger positioning.' ],
					]
				),
			], 'about-mission' ) ], 'about-mission-col' ) ], 'about-mission-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'eyebrow'     => 'Our Story',
				'title'       => 'Origin Story',
				'description' => 'After nearly 20 years climbing the security corporate ladder, Anthony noticed a heightened need for strategic company growth within the security industry.',
				'image'       => [ 'url' => '{{theme_url}}/assets/images/image-3.png' ],
				'values'      => gms_story_values(
					[
						[ 'Security Industry Depth', 'Built from leadership, operations, sales, and growth experience.' ],
						[ 'Military Discipline', 'A no-nonsense approach to execution and follow-through.' ],
						[ 'Authority Through Visibility', 'Visible trust is the bridge between expertise and growth.' ],
					]
				),
			], 'about-origin' ) ], 'about-origin-col' ) ], 'about-origin-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => 'Contact Us',
				'title'       => 'Ready to build trust that drives revenue?',
				'description' => 'Let’s turn your credibility into visible authority.',
				'button_text' => 'Get a free audit',
				'button_url'  => gms_button_url( '/contact-us/' ),
				'image'       => [ 'url' => '{{theme_url}}/assets/images/image-4.png' ],
			], 'about-cta' ) ], 'about-cta-col' ) ], 'about-cta-sec' ),
		]
	),
	'services' => gms_page_template(
		'Services',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-page-hero', [
				'variant'         => 'service',
				'eyebrow'         => 'Services',
				'title'           => 'Creative Services built for impact',
				'description'     => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
				'art_image'       => [ 'url' => '{{theme_url}}/assets/images/services-hero-art.png' ],
				'background_image'=> [ 'url' => '' ],
				'primary_text'    => '',
				'primary_url'     => gms_button_url( '/contact-us/' ),
				'secondary_text'  => '',
				'secondary_url'   => gms_button_url( '/about-us/' ),
			], 'services-hero' ) ], 'services-hero-col' ) ], 'services-hero-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '16', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-service-grid', [
				'show_heading' => '',
				'cards'        => gms_get_service_grid_cards( $config['services'] ),
			], 'services-grid' ) ], 'services-grid-col' ) ], 'services-grid-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '24', 'right' => '0', 'bottom' => '48', 'left' => '0', 'isLinked' => false ] ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => 'Contact Us',
				'title'       => 'Not sure where to start?',
				'description' => 'You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.',
				'button_text' => 'Get a free audit',
				'button_url'  => gms_button_url( '/contact-us/' ),
				'image'       => [ 'url' => '{{theme_url}}/assets/images/home-services-media.png' ],
			], 'services-cta' ) ], 'services-cta-col' ) ], 'services-cta-sec' ),
		]
	),
	'industries' => gms_page_template(
		'Industries',
		[
			gms_section_node(
				[
					gms_column_node(
						[
							gms_widget_node(
								'gms-hero',
								[
									'slides' => [
										[
											'layout'         => 'split',
											'label'          => 'Verticals',
											'title'          => 'Security Verticals Supported',
											'copy'           => 'If your buyers are technical and your product is complex, you are in the right place.',

											'image'          => [ 'url' => '' ],
											'art_image'      => [ 'url' => '{{theme_url}}/assets/images/industry-hero-lock.png' ],
											'primary_text'   => '',
											'primary_url'    => gms_button_url( '/contact-us/' ),
											'secondary_text' => '',
											'secondary_url'  => gms_button_url( '/about-us/' ),
										],
									],
								],
								'industries-hero'
							),
						],
						'industries-hero-col'
					),
				],
				'industries-hero-sec',
				[ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '32', 'left' => '0', 'isLinked' => false ] ]
			),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-card-grid', [
				'eyebrow'     => 'Verticals',
				'title'       => 'Security Verticals Supported',
				'description' => 'Built for technical buyers in complex, trust-sensitive markets.',

				'cards'       => gms_card_items_from_industries( $config['industries'] ),
			], 'industries-grid' ) ], 'industries-grid-col' ) ], 'industries-grid-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => 'Contact Us',
				'title'       => 'Not sure where to start?',
				'description' => 'We bring the expertise, visibility, and measurable trust your audience needs.',
				'button_text' => 'Get a free audit',
				'button_url'  => gms_button_url( '/contact-us/' ),
				'image'       => [ 'url' => '{{theme_url}}/assets/images/image-3.png' ],
			], 'industries-cta' ) ], 'industries-cta-col' ) ], 'industries-cta-sec' ),
		]
	),
	'resources-insights' => gms_page_template(
		'Resources & Insights',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'     => 'Blog',
				'title'       => 'Resources & Insights',
				'description' => 'News, commentary, and insight built to help security companies find measurable trust online.',
				'items'       => gms_post_items( $config['blog_posts'] ),
			], 'resources-grid' ) ], 'resources-grid-col' ) ], 'resources-grid-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => 'Ready',
				'title'       => 'Ready to build trust that drives revenue?',
				'description' => 'The right strategy compounds visibility and demand.',
				'button_text' => 'Schedule a Free Consultation',
				'button_url'  => gms_button_url( '/contact-us/' ),
				'image'       => [ 'url' => '{{theme_url}}/assets/images/image-3.png' ],
			], 'resources-cta' ) ], 'resources-cta-col' ) ], 'resources-cta-sec' ),
		]
	),
	'faq' => gms_page_template(
		'FAQ',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-faq', [
				'eyebrow'     => 'Faq',
				'title'       => 'Read Frequently Asked Question',
				'description' => 'Straight answers about how trust-led positioning works in complex security markets.',
				'items'       => $config['faqs'],
			], 'faq-list' ) ], 'faq-list-col' ) ], 'faq-list-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => 'Faqs',
				'title'       => 'Ready to build trust that drives revenue?',
				'description' => 'You bring the expertise, we turn it into visibility, credibility, and pipeline momentum.',
				'button_text' => 'Schedule a Free Consultation',
				'button_url'  => gms_button_url( '/contact-us/' ),
				'image'       => [ 'url' => '{{theme_url}}/assets/images/image-3.png' ],
			], 'faq-cta' ) ], 'faq-cta-col' ) ], 'faq-cta-sec' ),
		]
	),
	'contact-us' => gms_page_template(
		'Contact Us',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-contact-form', [
				'eyebrow'     => 'Contact Us',
				'title'       => 'Ready to build trust-led pipeline?',
				'description' => 'Let’s build a stronger pipeline for your security business that is secure, scalable, and measurable.',
				'email'       => $config['branding']['email'],
				'phone'       => $config['branding']['phone'],
			], 'contact-page' ) ], 'contact-page-col' ) ], 'contact-page-sec' ),
		]
	),
	'press-media' => gms_page_template(
		'Press & Media',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'     => 'Press & Media',
				'title'       => 'Media Coverage',
				'description' => 'Coverage, interviews, and commentary around trust-led growth in the security industry.',
				'items'       => gms_post_items( $config['press_items'] ),
			], 'press-grid' ) ], 'press-grid-col' ) ], 'press-grid-sec' ),
		]
	),
	'podcast' => gms_page_template(
		'Podcast',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'     => 'Podcast',
				'title'       => 'Podcast Episodes',
				'description' => 'Conversations about authority, trust, and growth for security companies.',
				'items'       => gms_post_items( $config['podcasts'] ),
			], 'podcast-grid' ) ], 'podcast-grid-col' ) ], 'podcast-grid-sec' ),
		]
	),
	'single-service-page' => array_merge(
		gms_get_service_template( $config['services'][0], $config ),
		[ 'title' => 'Single Service Page' ]
	),
	'single-blog-page' => gms_page_template(
		'Single Blog Page',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'eyebrow'     => 'Article',
				'title'       => $config['blog_posts'][0]['title'],
				'description' => $config['blog_posts'][0]['excerpt'],
				'image'       => [ 'url' => $config['blog_posts'][0]['image'] ],
				'values'      => gms_story_values(
					[
						[ 'Trust Design', 'Credibility starts before the buyer ever clicks Contact.' ],
						[ 'Authority Signals', 'High-intent buyers read visual trust cues fast.' ],
						[ 'Structured Conversion', 'The strongest pages remove doubt section by section.' ],
					]
				),
			], 'single-blog-story' ) ], 'single-blog-story-col' ) ], 'single-blog-story-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'     => 'Related Articles',
				'title'       => 'Related Articles',
				'description' => 'More insight from the journal.',
				'items'       => gms_post_items( $config['blog_posts'] ),
			], 'single-blog-related' ) ], 'single-blog-related-col' ) ], 'single-blog-related-sec' ),
		]
	),
	'single-press-page' => gms_page_template(
		'Single Press Page',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'eyebrow'     => 'Press',
				'title'       => $config['press_items'][0]['title'],
				'description' => $config['press_items'][0]['excerpt'],
				'image'       => [ 'url' => $config['press_items'][0]['image'] ],
				'values'      => gms_story_values(
					[
						[ 'Media Coverage', 'Designed to showcase authority and market positioning.' ],
						[ 'High-Trust Messaging', 'Built for cautious, technical readers.' ],
						[ 'Repeatable Layouts', 'A reusable editorial system for future press coverage.' ],
					]
				),
			], 'single-press-story' ) ], 'single-press-story-col' ) ], 'single-press-story-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'     => 'Press',
				'title'       => 'More Coverage',
				'description' => 'Additional press-ready content blocks for future updates.',
				'items'       => gms_post_items( $config['press_items'] ),
			], 'single-press-related' ) ], 'single-press-related-col' ) ], 'single-press-related-sec' ),
		]
	),
];

foreach ( $pages as $slug => $data ) {
	file_put_contents(
		$pagesDir . '/' . $slug . '.json',
		json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
	);
}

$customizer = [
	'theme_mods' => [
		'gms_accent_color'            => $config['tokens']['accent'],
		'gms_background_color'        => $config['tokens']['background'],
		'gms_background_alt_color'    => $config['tokens']['background_2'],
		'gms_surface_color'           => $config['tokens']['surface'],
		'gms_surface_alt_color'       => $config['tokens']['surface_soft'],
		'gms_text_color'              => $config['tokens']['text'],
		'gms_text_muted_color'        => $config['tokens']['text_muted'],
		'gms_heading_font'            => $config['tokens']['heading_font'],
		'gms_body_font'               => $config['tokens']['body_font'],
		'gms_base_font_size'          => 15.5,
		'gms_content_width'           => 1288,
		'gms_content_gutter'          => 32,
		'gms_section_gap'             => 104,
		'gms_button_radius'           => 8,
		'gms_header_background_color' => '#1a0b0a',
		'gms_footer_background_color' => '#090505',
		'gms_site_background_image'   => '',
	],
];

file_put_contents(
	$pluginDir . '/data/customizer.dat',
	json_encode( $customizer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
);

echo "Generated " . count( $pages ) . " Elementor templates and customizer.dat\n";
