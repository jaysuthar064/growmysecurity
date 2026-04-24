<?php
/**
 * Theme-controlled public page rendering helpers.
 *
 * @package GrowMySecurity
 */

if (!defined('ABSPATH')) {
	exit;
}

function gms_get_service_config_by_slug(string $slug): ?array
{
	$config = gms_get_demo_config();

	foreach ($config['services'] as $service) {
		if ($service['slug'] === $slug) {
			return $service;
		}
	}

	return null;
}

function gms_get_public_page_context(?WP_Post $post = null): array
{
	$post = $post instanceof WP_Post ? $post : get_post();
	$slug = $post instanceof WP_Post ? $post->post_name : '';
	$parent = $post instanceof WP_Post && $post->post_parent ? get_post($post->post_parent) : null;
	$service = gms_get_service_config_by_slug($slug);
	$type = 'default';

	if (is_front_page()) {
		$type = 'front';
	} elseif ($post instanceof WP_Post) {
		switch ($slug) {
			case 'about-us':
				$type = 'about';
				break;
			case 'services':
				$type = 'services';
				break;
			case 'industries':
				$type = 'industries';
				break;
			case 'resources-insights':
				$type = 'resources';
				break;
			case 'faq':
				$type = 'faq';
				break;
			case 'contact-us':
				$type = 'contact';
				break;
			case 'press-media':
				$type = 'press';
				break;
			case 'podcast':
				$type = 'podcast';
				break;
		}

		if ('services' === ($parent->post_name ?? '') && $service) {
			$type = 'service-detail';
		}
	}

	return [
		'post' => $post,
		'slug' => $slug,
		'parent' => $parent,
		'service' => $service,
		'type' => $type,
	];
}

function gms_is_elementor_preview_request(): bool
{
	if (isset($_GET['elementor-preview'])) {
		return true;
	}

	if (!class_exists('\Elementor\Plugin')) {
		return false;
	}

	$plugin = \Elementor\Plugin::$instance ?? null;

	if (!$plugin) {
		return false;
	}

	$preview = $plugin->preview ?? null;
	if ($preview && method_exists($preview, 'is_preview_mode') && $preview->is_preview_mode()) {
		return true;
	}

	$editor = $plugin->editor ?? null;

	return $editor && method_exists($editor, 'is_edit_mode') && $editor->is_edit_mode();
}

function gms_should_bypass_theme_controlled_rendering(?WP_Post $post = null): bool
{
	if (is_admin() || wp_doing_ajax()) {
		return false;
	}

	if (is_preview() || gms_is_elementor_preview_request()) {
		return true;
	}

	return false;
}
function gms_is_theme_controlled_public_route(?WP_Post $post = null): bool
{
	if (gms_should_bypass_theme_controlled_rendering($post)) {
		return false;
	}

	$context = gms_get_public_page_context($post);

	return in_array(
		$context['type'],
		[
			'front',
			'about',
			'services',
			'service-detail',
			'industries',
			'resources',
			'faq',
			'contact',
			'press',
			'podcast',
		],
		true
	);
}

function gms_should_use_elementor_builder_on_theme_route($post = null): bool
{
	$post = $post instanceof WP_Post ? $post : get_post($post);

	if (!($post instanceof WP_Post) || is_admin() || wp_doing_ajax()) {
		return false;
	}

	if (gms_should_bypass_theme_controlled_rendering($post)) {
		return false;
	}

	if ('page' !== $post->post_type) {
		return false;
	}

	// Select public pages use Elementor-driven rendering on the live site.
	// Their custom widgets render the approved layouts so the Elementor
	// editor stays visually synced with the public page output.
	$elementor_rendered_slugs = ['resources-insights', 'contact-us', 'services', 'about-us', 'press-media', 'podcast'];

	if (!in_array($post->post_name, $elementor_rendered_slugs, true)) {
		return false;
	}

	return function_exists('gms_post_has_elementor_content') && gms_post_has_elementor_content((int) $post->ID);
}

function gms_disable_elementor_frontend_for_theme_routes(): void
{
	if (is_admin() || !is_singular() || gms_should_bypass_theme_controlled_rendering() || !gms_is_theme_controlled_public_route() || gms_should_use_elementor_builder_on_theme_route()) {
		return;
	}

	if (!class_exists('\Elementor\Plugin') || !class_exists('\Elementor\Frontend')) {
		return;
	}

	$frontend = \Elementor\Plugin::$instance->frontend ?? null;

	if (!$frontend) {
		return;
	}

	remove_action('wp_enqueue_scripts', [$frontend, 'enqueue_styles'], \Elementor\Frontend::ENQUEUED_STYLES_PRIORITY);
	remove_action('wp_head', [$frontend, 'print_fonts_links'], 7);
}
add_action('template_redirect', 'gms_disable_elementor_frontend_for_theme_routes', 30);

function gms_add_theme_controlled_body_classes(array $classes): array
{
	$context = gms_get_public_page_context();

	if (gms_is_theme_controlled_public_route()) {
		$classes[] = 'gms-approved-route';
		$classes[] = 'gms-approved-route--' . sanitize_html_class($context['type']);
	}

	return $classes;
}
add_filter('body_class', 'gms_add_theme_controlled_body_classes');

function gms_get_current_request_url(): string
{
	$request_uri = wp_unslash($_SERVER['REQUEST_URI'] ?? '/');
	$request_uri = '/' . ltrim($request_uri, '/');

	return home_url($request_uri);
}

function gms_get_generated_asset_url(string $filename, string $fallback = ''): string
{
	$resolved = function_exists('gms_find_upload_asset_filename') ? gms_find_upload_asset_filename($filename) : '';

	if ('' !== $resolved) {
		return gms_upload_asset_url($resolved);
	}

	return $fallback;
}

function gms_get_theme_controlled_post_image_url(WP_Post $post): string
{
	$overrides = [
		'why-ux-ui-design-can-make-or-break-your-website' => 'home-journal-1.png',
		'five-must-have-features-for-a-modern-business-website' => 'home-journal-2.png',
		'ai-in-content-creation-friend-or-foe-in-2026' => 'home-journal-3.png',
		'coaching-for-the-security-industry' => 'press-feature-1.png',
		'how-to-scale-security-marketing' => 'press-feature-2.png',
		'building-trust-in-security-marketing' => 'podcast-episode-1.png',
	];
	$filename = $overrides[$post->post_name] ?? '';

	if ('' === $filename) {
		return '';
	}

	return gms_get_generated_asset_url($filename, gms_upload_asset_url($filename));
}

function gms_is_editorial_resources_context(): bool
{
	return is_page('resources-insights') || is_search() || is_archive() || is_category() || is_tag() || is_home() || (is_single() && 'post' === get_post_type());
}

function gms_primary_nav_item_is_active(string $slug): bool
{
	switch ($slug) {
		case 'home':
			return is_front_page();
		case 'services':
			return is_page('services') || 'service-detail' === gms_get_public_page_context()['type'];
		case 'industries':
			return is_page('industries');
		case 'resources':
			return gms_is_editorial_resources_context() || is_page('press-media') || is_page('podcast') || is_page('faq');
		case 'contact':
			return is_page('contact-us');
		default:
			return false;
	}
}

function gms_get_primary_navigation_items(): array
{
	$footer_groups = gms_get_footer_groups();

	return [
		[
			'slug' => 'home',
			'label' => __('Home', 'grow-my-security'),
			'url' => home_url('/'),
			'has_caret' => false,
		],
		[
			'slug' => 'services',
			'label' => __('Services', 'grow-my-security'),
			'url' => home_url('/services/'),
			'has_caret' => true,
			'submenu_columns' => 2,
			'children' => array_merge($footer_groups['services']['links'], $footer_groups['services']['sub_links'] ?? []),
		],
		[
			'slug' => 'industries',
			'label' => __('Industries', 'grow-my-security'),
			'url' => home_url('/industries/'),
			'has_caret' => true,
			'submenu_columns' => 2,
			'children' => $footer_groups['industries']['links'],
		],
		[
			'slug' => 'resources',
			'label' => __('Resources', 'grow-my-security'),
			'url' => home_url('/resources-insights/'),
			'has_caret' => true,
			'submenu_columns' => 1,
			'children' => $footer_groups['resources']['links'],
		],
		[
			'slug' => 'commandgrid',
			'label' => __('CommandGrid', 'grow-my-security'),
			'url' => 'https://www.godaddy.com/reseller-program',
			'has_caret' => false,
		],
		[
			'slug' => 'contact',
			'label' => __('Contact Us', 'grow-my-security'),
			'url' => home_url('/contact-us/'),
			'has_caret' => false,
		],
	];
}

function gms_render_theme_primary_nav(): void
{
	echo '<ul class="gms-nav-list">';

	foreach (gms_get_primary_navigation_items() as $item) {
		$item_classes = ['menu-item'];
		$children = array_values(
			array_filter(
				$item['children'] ?? [],
				static function ($child): bool {
					return is_array($child) && !empty($child['label']) && !empty($child['url']);
				}
			)
		);

		if (gms_primary_nav_item_is_active($item['slug'])) {
			$item_classes[] = 'current-menu-item';
		}

		if ($children) {
			$item_classes[] = 'menu-item-has-children';
		}

		$submenu_markup = '';

		if ($children) {
			$submenu_classes = [
				'gms-nav-submenu',
				'gms-nav-submenu--' . sanitize_html_class($item['slug']),
			];

			if ((int) ($item['submenu_columns'] ?? 1) > 1) {
				$submenu_classes[] = 'gms-nav-submenu--columns-' . absint($item['submenu_columns']);
			}

			$submenu_markup .= '<ul class="' . esc_attr(implode(' ', $submenu_classes)) . '" aria-label="' . esc_attr(sprintf(__('%s submenu', 'grow-my-security'), $item['label'])) . '">';

			foreach ($children as $child) {
				$submenu_markup .= sprintf(
					'<li class="gms-nav-submenu__item"><a href="%1$s">%2$s</a></li>',
					esc_url($child['url']),
					esc_html($child['label'])
				);
			}

			$submenu_markup .= '</ul>';
		}

		printf(
			'<li class="%1$s"><a href="%2$s">%3$s%4$s</a>%5$s</li>',
			esc_attr(implode(' ', $item_classes)),
			esc_url($item['url']),
			esc_html($item['label']),
			!empty($item['has_caret']) ? '<span class="gms-nav-caret" aria-hidden="true"></span>' : '',
			$submenu_markup
		);
	}

	echo '</ul>';
}

function gms_get_footer_groups(): array
{
	$terms_url = gms_get_page_url_by_path('terms-conditions');
	$config = gms_get_demo_config();

	return [
		'company' => [
			'title' => __('Company', 'grow-my-security'),
			'links' => [
				['label' => __('About', 'grow-my-security'), 'url' => home_url('/about-us/')],
				['label' => __('Contact Us', 'grow-my-security'), 'url' => home_url('/contact-us/')],
				['label' => __('Privacy Policy', 'grow-my-security'), 'url' => get_privacy_policy_url() ?: home_url('/privacy-policy/')],
				['label' => __('Terms & Conditions', 'grow-my-security'), 'url' => $terms_url ?: home_url('/terms-conditions/')],
			],
		],
		'services' => [
			'title' => __('Services', 'grow-my-security'),
			'links' => [
				['label' => __('Lead Generation', 'grow-my-security'), 'url' => home_url('/services/leads-generation-services/')],
				['label' => __('SEO Services', 'grow-my-security'), 'url' => home_url('/services/seo-solutions/')],
				['label' => __('Fractional CMO Services', 'grow-my-security'), 'url' => home_url('/services/fractional-cmo-services/')],
				['label' => __('Digital Marketing Solutions', 'grow-my-security'), 'url' => home_url('/services/digital-marketing-solutions/')],
				['label' => __('Advertising Services', 'grow-my-security'), 'url' => home_url('/services/advertising-solutions/')],
				['label' => __('Web Development', 'grow-my-security'), 'url' => home_url('/services/web-development/')],
				['label' => __('Content Marketing', 'grow-my-security'), 'url' => home_url('/services/content-marketing/')],
				['label' => __('Marketing Strategies', 'grow-my-security'), 'url' => home_url('/services/marketing-strategies/')],
				['label' => __('Public Relations', 'grow-my-security'), 'url' => home_url('/services/public-relations/')],
				['label' => __('Website Audit', 'grow-my-security'), 'url' => home_url('/services/website-audit/')],
				['label' => __('Brand Authority Development', 'grow-my-security'), 'url' => home_url('/services/brand-authority-development/')],
				['label' => __('Sales Coaching', 'grow-my-security'), 'url' => home_url('/services/sales-coaching/')],
			],
			'subtitle' => __('AI Search & LLM Optimization', 'grow-my-security'),
			'sub_links' => [
				['label' => __('AI Solutions', 'grow-my-security'), 'url' => home_url('/services/ai-solutions/')],
				['label' => __('AEO (Answer Engine Optimization)', 'grow-my-security'), 'url' => home_url('/services/aeo/')],
				['label' => __('GEO (Generative Engine Optimization)', 'grow-my-security'), 'url' => home_url('/services/geo/')],
				['label' => __('Social Media Marketing', 'grow-my-security'), 'url' => home_url('/services/social-media-marketing/')],
				['label' => __('Website Hosting and Maintenance', 'grow-my-security'), 'url' => home_url('/services/website-hosting-maintenance/')],
				['label' => __('GBP Management (Google Business Profile)', 'grow-my-security'), 'url' => home_url('/services/gbp-management/')],
				['label' => __('Sales Funnel Development', 'grow-my-security'), 'url' => home_url('/services/sales-funnel-development/')],
				['label' => __('CRM Integration and Optimization', 'grow-my-security'), 'url' => home_url('/services/crm-integration-optimization/')],
				['label' => __('Growth Consultation For Security Company', 'grow-my-security'), 'url' => home_url('/services/growth-consultation-security-company/')],
			],
		],
		'industries' => [
			'title' => __('Industries', 'grow-my-security'),
			'links' => array_map(
				static function (string $industry): array {
					return [
						'label' => $industry,
						'url' => gms_get_industry_url($industry),
					];
				},
				$config['industries']
			),


		],
		'resources' => [
			'title' => __('Resources', 'grow-my-security'),
			'links' => [
				['label' => __('Blogs', 'grow-my-security'), 'url' => home_url('/resources-insights/')],
				['label' => __('Case Studies', 'grow-my-security'), 'url' => home_url('/case-studies/')],
				['label' => __('Media', 'grow-my-security'), 'url' => home_url('/press-media/')],
				['label' => __('Podcast', 'grow-my-security'), 'url' => home_url('/podcast/')],
				['label' => __('FAQ', 'grow-my-security'), 'url' => home_url('/faq/')],
			],
		],
	];
}

function gms_get_extended_faq_items(): array
{
	return [
		[
			'question' => __('How does this actually drive results?', 'grow-my-security'),
			'answer' => __('In the security industry, trust is everything. Buyers are cautious, risk-aware, slow to commit, and they do not respond to loud marketing, generic promises, or copy-paste campaigns.', 'grow-my-security'),
		],
		[
			'question' => __('What metrics do we use to measure success?', 'grow-my-security'),
			'answer' => __('We focus on qualified pipeline, buyer-intent signals, branded demand, conversion quality, and the authority signals that shorten decision cycles for high-consideration buyers.', 'grow-my-security'),
		],
		[
			'question' => __('Do you provide SEO specifically for security companies?', 'grow-my-security'),
			'answer' => __('Yes. We focus on authority-led SEO that answers high-intent buyer questions and builds defensible rankings, ensuring you are visible when decision-makers are researching solutions.', 'grow-my-security'),
		],
		[
			'question' => __('Can you help with lead generation for government contracts?', 'grow-my-security'),
			'answer' => __('We help frame your company as a mission-ready, compliant, and dependable contractor through a strategic digital presence that signals credibility to public-sector evaluators.', 'grow-my-security'),
		],
		[
			'question' => __('What makes your agency different from generic marketing firms?', 'grow-my-security'),
			'answer' => __('We have a deep domain understanding of the security market. We don’t need a "learning phase" to understand why your buyers are cautious—we build the systems that earn their belief.', 'grow-my-security'),
		],
		[
			'question' => __('Can we identify studies that highlight our impact?', 'grow-my-security'),
			'answer' => __('Yes. We tie visibility, trust cues, traffic quality, and lead quality back to measurable business outcomes so the work stays defensible instead of anecdotal.', 'grow-my-security'),
		],
		[
			'question' => __('How long does it take to see traction?', 'grow-my-security'),
			'answer' => __('Sharper positioning and stronger page structure can lift conversion early, while search visibility, authority content, and trust compounding build over the months that follow.', 'grow-my-security'),
		],
		[
			'question' => __('Do you work only with security companies?', 'grow-my-security'),
			'answer' => __('The system was designed for security and adjacent high-trust industries where credibility, nuance, and buyer confidence matter before any call is booked.', 'grow-my-security'),
		],
		[
			'question' => __('Do you handle website hosting and security?', 'grow-my-security'),
			'answer' => __('Yes. We provide managed hosting and proactive security monitoring to ensure your brand’s digital storefront remains stable, fast, and trusted by your visitors.', 'grow-my-security'),
		],
	];
}

function gms_get_about_value_cards(): array
{
	return [
		['title' => __('Truth Over Tactics', 'grow-my-security'), 'text' => __('We build visibility around what is real, defensible, and useful to a serious buyer.', 'grow-my-security')],
		['title' => __('Empathy in Action', 'grow-my-security'), 'text' => __('Every page and message is shaped for cautious, risk-aware decision makers.', 'grow-my-security')],
		['title' => __('Authenticity in Action', 'grow-my-security'), 'text' => __('Authority grows faster when the brand sounds like the team behind it.', 'grow-my-security')],
		['title' => __('Embracing Vulnerability', 'grow-my-security'), 'text' => __('We are willing to say what is missing, unclear, or underperforming so the work gets sharper.', 'grow-my-security')],
		['title' => __('Collective Growth', 'grow-my-security'), 'text' => __('The system is designed to support clients, teams, and the market around them at the same time.', 'grow-my-security')],
		['title' => __('Active Listening Skills', 'grow-my-security'), 'text' => __('The strongest positioning comes from paying attention before prescribing a solution.', 'grow-my-security')],
	];
}

function gms_get_press_feature_items(): array
{
	return [
		[
			'title' => __('Grow My Security Company Coaching for the Security Industry', 'grow-my-security'),
			'url' => home_url('/coaching-for-the-security-industry/'),
			'image' => gms_get_generated_asset_url('press-feature-1.png', gms_get_brand_asset_url('page-single-press')),
		],
		[
			'title' => __('Grow My Security Company How to Scale Security Marketing', 'grow-my-security'),
			'url' => home_url('/how-to-scale-security-marketing/'),
			'image' => gms_get_generated_asset_url('press-feature-2.png', gms_get_brand_asset_url('page-single-press')),
		],
		[
			'title' => __('Authority, Trust, and Brand Visibility for Security Teams', 'grow-my-security'),
			'url' => home_url('/resources-insights/'),
			'image' => gms_get_generated_asset_url('press-feature-3.png', gms_get_brand_asset_url('page-single-press')),
		],
	];
}

function gms_get_press_coverage_items(): array
{
	return [
		['title' => __('Our CISO Success: Trusted Media Answers for Visionary Leaders', 'grow-my-security'), 'url' => home_url('/coaching-for-the-security-industry/')],
		['title' => __('World Tech Expo: How to improve buyer movement in a high-trust sales environment', 'grow-my-security'), 'url' => home_url('/how-to-scale-security-marketing/')],
		['title' => __('FOX 10 Coverage', 'grow-my-security'), 'url' => home_url('/press-media/')],
		['title' => __('FOX News Review', 'grow-my-security'), 'url' => home_url('/press-media/')],
		['title' => __('ABC 15 Arizona', 'grow-my-security'), 'url' => home_url('/press-media/')],
		['title' => __('Phoenix Business Journal', 'grow-my-security'), 'url' => home_url('/press-media/')],
	];
}

function gms_get_podcast_episode_items(): array
{
	return [
		[
			'title' => __('David Meltzer\'s Office Hours #5891', 'grow-my-security'),
			'url' => home_url('/building-trust-in-security-marketing/'),
			'image' => gms_get_generated_asset_url('podcast-episode-1.png', gms_get_brand_asset_url('page-podcast')),
		],
		[
			'title' => __('EP Mixer Orlando Sponsor Spotlight', 'grow-my-security'),
			'url' => home_url('/press-media/'),
			'image' => gms_get_generated_asset_url('podcast-episode-2.png', gms_get_brand_asset_url('page-podcast')),
		],
	];
}

function gms_render_submission_notice(): void
{
	$contact_status = isset($_GET['gms_contact']) ? sanitize_key(wp_unslash($_GET['gms_contact'])) : '';
	$subscribe_status = isset($_GET['gms_subscribe']) ? sanitize_key(wp_unslash($_GET['gms_subscribe'])) : '';

	if (!in_array($contact_status, ['success', 'error'], true) && !in_array($subscribe_status, ['success', 'error'], true)) {
		return;
	}

	$is_success = 'success' === $contact_status || 'success' === $subscribe_status;
	$message = 'success' === $contact_status
		? __('Thanks. Your request has been sent.', 'grow-my-security')
		: ('error' === $contact_status
			? __('Something went wrong while sending your request. Please try again.', 'grow-my-security')
			: ($is_success
				? __('Thanks. Your subscription request has been received.', 'grow-my-security')
				: __('The subscription request could not be completed. Please try again.', 'grow-my-security')));
	?>
	<div class="gms-container">
		<div class="gms-notice <?php echo esc_attr($is_success ? 'gms-notice--success' : 'gms-notice--error'); ?>">
			<?php echo esc_html($message); ?>
		</div>
	</div>
	<?php
}

function gms_render_internal_intro(array $args): void
{
	$args = wp_parse_args(
		$args,
		[
			'eyebrow' => '',
			'title' => '',
			'lede' => '',
			'support_html' => '',
			'modifier' => '',
		]
	);

	$classes = ['gms-approved-intro'];

	if ('' !== trim($args['modifier'])) {
		$classes[] = 'gms-approved-intro--' . sanitize_html_class($args['modifier']);
	}
	?>
	<section class="<?php echo esc_attr(implode(' ', $classes)); ?>">
		<div class="gms-approved-intro__grid">
			<div class="gms-approved-intro__main">
				<?php if ('' !== trim($args['eyebrow'])): ?>
					<div class="gms-eyebrow"><?php echo esc_html($args['eyebrow']); ?></div>
				<?php endif; ?>
				<h1><?php echo wp_kses_post(nl2br(esc_html($args['title']))); ?></h1>
				<?php if ('' !== trim($args['lede'])): ?>
					<div class="gms-approved-intro__lede">
						<p><?php echo esc_html($args['lede']); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<?php if ('' !== trim((string) $args['support_html'])): ?>
				<div class="gms-approved-intro__side">
					<?php echo $args['support_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function gms_get_theme_link_attributes(array $link): string
{
	$url = trim((string) ($link['url'] ?? ''));

	if ('' === $url || '#' === $url) {
		return '';
	}

	$attributes = [
		'href="' . esc_url($url) . '"',
	];

	$is_external = !empty($link['is_external']);
	$nofollow    = !empty($link['nofollow']);

	if ($is_external) {
		$attributes[] = 'target="_blank"';
	}

	if ($is_external || $nofollow) {
		$rel = [];

		if ($is_external) {
			$rel[] = 'noopener';
			$rel[] = 'noreferrer';
		}

		if ($nofollow) {
			$rel[] = 'nofollow';
		}

		$attributes[] = 'rel="' . esc_attr(implode(' ', array_unique($rel))) . '"';
	}

	return implode(' ', $attributes);
}

function gms_render_theme_button_link(array $link, string $text, string $class_name): void
{
	$text       = trim($text);
	$attributes = gms_get_theme_link_attributes($link);

	if ('' === $text || '' === $attributes) {
		return;
	}
	?>
	<a class="<?php echo esc_attr($class_name); ?>" <?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($text); ?></a>
	<?php
}

function gms_render_newsletter_form(string $source, string $button_label = 'Subscribe', array $args = []): void
{
	$args = wp_parse_args(
		$args,
		[
			'placeholder'  => __('Enter your email...', 'grow-my-security'),
			'show_consent' => true,
			'variant'      => '',
		]
	);

	$form_classes = ['gms-approved-newsletter'];
	if ('' !== trim((string) $args['variant'])) {
		$form_classes[] = 'gms-approved-newsletter--' . sanitize_html_class((string) $args['variant']);
	}

	?>
	<form class="<?php echo esc_attr(implode(' ', $form_classes)); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<input type="hidden" name="action" value="gms_newsletter_form">
		<input type="hidden" name="source" value="<?php echo esc_attr($source); ?>">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url(gms_get_current_request_url()); ?>">
		<?php wp_nonce_field('gms_newsletter_form', 'gms_newsletter_nonce'); ?>
		<?php if (empty($args['show_consent'])) : ?>
			<input type="hidden" name="consent" value="1">
		<?php endif; ?>
		<div class="gms-approved-newsletter__row">
			<input type="email" name="email" placeholder="<?php echo esc_attr((string) $args['placeholder']); ?>"
				autocomplete="email" required>
			<button class="gms-button" type="submit"><?php echo esc_html($button_label); ?></button>
		</div>
		<?php if (!empty($args['show_consent'])) : ?>
			<label class="gms-approved-checkbox">
				<input type="checkbox" name="consent" value="1" required>
				<span><?php esc_html_e('I\'m not a robot', 'grow-my-security'); ?></span>
			</label>
		<?php endif; ?>
	</form>
	<?php
}

function gms_render_money_cta(array $args = []): void
{
	$args = wp_parse_args(
		$args,
		[
			'eyebrow' => __('Contact Us', 'grow-my-security'),
			'title' => __('Ready to build trust that drives revenue?', 'grow-my-security'),
			'title_html' => '',
			'copy' => __('You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.', 'grow-my-security'),
			'copy_html' => '',
			'button' => __('Schedule a Free Consultation', 'grow-my-security'),
			'url' => home_url('/contact-us/'),
			'footnote' => '',
			'footnote_html' => '',
			'image_url' => get_theme_file_uri('assets/images/security-dashboard-visual.png'),
		]
	);

	gms_render_industries_premium_cta($args);
}
function gms_render_industries_premium_cta(array $args = []): void
{
	$args = wp_parse_args(
		$args,
		[
			'eyebrow' => __('FAQ\'s', 'grow-my-security'),
			'title' => __('Ready to build trust that drives revenue?', 'grow-my-security'),
			'title_html' => '',
			'copy' => __('You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.', 'grow-my-security'),
			'copy_html' => '',
			'button' => __('Schedule a Free Consultation', 'grow-my-security'),
			'url' => home_url('/contact-us/'),
			'footnote' => __('82% of B2B buyers ignore vendors they do not trust. We help your people earn belief before the first conversation, because trust is what drives every deal.', 'grow-my-security'),
			'footnote_html' => '',
		]
	);
	$image_url = !empty($args['image_url']) ? (string) $args['image_url'] : get_theme_file_uri('assets/images/security-dashboard-visual.png');

	if (function_exists('gms_normalize_media_url')) {
		$image_url = (string) gms_normalize_media_url($image_url);
	}
	?>
	<section class="gms-approved-cta gms-approved-cta--industry-premium">
		<div class="gms-approved-cta-premium__shell">
			<div class="gms-approved-cta-premium__content">
				<?php if ('' !== trim((string) $args['eyebrow'])): ?>
					<div class="gms-approved-cta-premium__tag"><?php echo esc_html($args['eyebrow']); ?></div>
				<?php endif; ?>
				<h2 class="gms-approved-cta-premium__title">
					<?php if ('' !== trim((string) $args['title_html'])): ?>
						<?php echo wp_kses_post($args['title_html']); ?>
					<?php else: ?>
						<?php echo esc_html($args['title']); ?>
					<?php endif; ?>
				</h2>
				<p class="gms-approved-cta-premium__description">
					<?php if ('' !== trim((string) $args['copy_html'])): ?>
						<?php echo wp_kses_post($args['copy_html']); ?>
					<?php else: ?>
						<?php echo esc_html($args['copy']); ?>
					<?php endif; ?>
				</p>
				<a class="gms-button gms-approved-cta-premium__button" href="<?php echo esc_url($args['url']); ?>"><?php echo esc_html($args['button']); ?></a>
				<?php if ('' !== trim((string) $args['footnote']) || '' !== trim((string) $args['footnote_html'])): ?>
					<div class="gms-approved-cta-premium__trust">
						<div class="gms-approved-cta-premium__trust-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Zm-1.23 4.41-1.41 1.41 2.64 2.64 4.24-4.24-1.41-1.41L12 10.82l-1.23-1.23Z" fill="currentColor"/></svg>
						</div>
						<p>
							<?php if ('' !== trim((string) $args['footnote_html'])): ?>
								<?php echo wp_kses_post($args['footnote_html']); ?>
							<?php else: ?>
								<?php echo esc_html($args['footnote']); ?>
							<?php endif; ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
			<div class="gms-approved-cta-premium__visual">
				<div class="gms-approved-cta-premium__visual-frame">
					<img class="gms-approved-cta-premium__media" src="<?php echo esc_url($image_url); ?>" alt="" loading="lazy" decoding="async">
				</div>
			</div>
		</div>
	</section>
	<?php
}
function gms_get_resources_query(string $filter = '', string $search = ''): WP_Query
{
	$args = [
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => 6,
		'ignore_sticky_posts' => true,
		'category__not_in' => array_values(
			array_filter(
				[
					get_cat_ID('Press'),
					get_cat_ID('Podcast'),
					get_cat_ID('Uncategorized'),
				]
			)
		),
	];

	if ('' !== $filter) {
		$args['category_name'] = $filter;
	}

	if ('' !== $search) {
		$args['s'] = $search;
	}

	return new WP_Query($args);
}

function gms_get_service_card_tag(string $slug): string
{
	$tags = [
		'advertising-solutions'       => __('Demand Capture', 'grow-my-security'),
		'ai-solutions'                => __('Automation', 'grow-my-security'),
		'fractional-cmo-services'     => __('Leadership', 'grow-my-security'),
		'digital-marketing-solutions' => __('Visibility', 'grow-my-security'),
		'leads-generation-services'   => __('Pipeline', 'grow-my-security'),
		'seo-solutions'               => __('Authority', 'grow-my-security'),
		'aeo'                         => __('Answer Engine', 'grow-my-security'),
		'geo'                         => __('Generative Search', 'grow-my-security'),
		'web-development'             => __('Experience', 'grow-my-security'),
		'content-marketing'           => __('Narrative', 'grow-my-security'),
		'marketing-strategies'        => __('Strategy', 'grow-my-security'),
		'social-media-marketing'      => __('Social', 'grow-my-security'),
		'public-relations'            => __('Reputation', 'grow-my-security'),
		'website-audit'               => __('Insight', 'grow-my-security'),
		'brand-authority-development' => __('Authority', 'grow-my-security'),
		'website-hosting-maintenance' => __('Reliability', 'grow-my-security'),
		'gbp-management'              => __('Local Search', 'grow-my-security'),
		'sales-funnel-development'    => __('Conversion', 'grow-my-security'),
		'crm-integration-optimization' => __('Enablement', 'grow-my-security'),
		'sales-coaching'              => __('Coaching', 'grow-my-security'),
		'growth-consultation-security-company' => __('Consulting', 'grow-my-security'),
	];

	return $tags[$slug] ?? __('Growth System', 'grow-my-security');
}

function gms_render_service_card_icon(string $slug): string
{
	switch ($slug) {
		case 'advertising-solutions':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5V13.5H6L12.5 18V6L6 10.5H3ZM14 8.23V15.77C15.76 15.15 17 13.49 17 12C17 10.51 15.76 8.85 14 8.23ZM14 5.06V7.12C17.39 7.8 20 9.64 20 12C20 14.36 17.39 16.2 14 16.88V18.94C18.45 18.21 22 15.47 22 12C22 8.53 18.45 5.79 14 5.06Z" fill="currentColor"/></svg>';
		case 'ai-solutions':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 2H11V5H13V2H15V5.08C16.74 5.5 18.13 6.89 18.55 8.63H21.5V10.63H18.78V12.63H21.5V14.63H18.55C18.13 16.37 16.74 17.76 15 18.18V21H13V18.26H11V21H9V18.18C7.26 17.76 5.87 16.37 5.45 14.63H2.5V12.63H5.22V10.63H2.5V8.63H5.45C5.87 6.89 7.26 5.5 9 5.08V2ZM9.5 7C8.12 7 7 8.12 7 9.5V13.5C7 14.88 8.12 16 9.5 16H14.5C15.88 16 17 14.88 17 13.5V9.5C17 8.12 15.88 7 14.5 7H9.5ZM10 9.5A1.5 1.5 0 1 1 10 12.5A1.5 1.5 0 0 1 10 9.5ZM14 9.5A1.5 1.5 0 1 1 14 12.5A1.5 1.5 0 0 1 14 9.5Z" fill="currentColor"/></svg>';
		case 'fractional-cmo-services':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19H20V21H4V19ZM6 10H9V17H6V10ZM10.5 6H13.5V17H10.5V6ZM15 12H18V17H15V12ZM12 2L16.5 5.5L15.28 7.08L12 4.54L8.72 7.08L7.5 5.5L12 2Z" fill="currentColor"/></svg>';
		case 'digital-marketing-solutions':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4H10V10H4V4ZM14 4H20V10H14V4ZM4 14H10V20H4V14ZM14 14H20V20H14V14ZM6 6V8H8V6H6ZM16 6V8H18V6H16ZM6 16V18H8V16H6ZM12 11L13.41 12.41L11.83 14H15V16H11.83L13.41 17.59L12 19L8 15L12 11ZM16 16H18V18H16V16Z" fill="currentColor"/></svg>';
		case 'leads-generation-services':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 2A10 10 0 1 0 21 12H19A8 8 0 1 1 11 4V2ZM13 2V8H19C19 4.69 16.31 2 13 2ZM10 8H12V11H15V13H12V16H10V13H7V11H10V8Z" fill="currentColor"/></svg>';
		case 'seo-solutions':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2A8 8 0 1 0 15.29 16.29L20.59 21.59L22 20.17L16.71 14.88C17.52 13.53 18 11.95 18 10A8 8 0 0 0 10 2ZM10 4A6 6 0 1 1 4 10A6 6 0 0 1 10 4ZM8.75 7.5H11.5V9H8.75C8.34 9 8 9.34 8 9.75C8 10.16 8.34 10.5 8.75 10.5H10.25A2.25 2.25 0 1 1 10.25 15H7.5V13.5H10.25C10.66 13.5 11 13.16 11 12.75C11 12.34 10.66 12 10.25 12H8.75A2.25 2.25 0 1 1 8.75 7.5Z" fill="currentColor"/></svg>';
		case 'aeo':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4H20A2 2 0 0 1 22 6V14A2 2 0 0 1 20 16H13L8 20V16H4A2 2 0 0 1 2 14V6A2 2 0 0 1 4 4ZM7.2 8.1A2.2 2.2 0 0 0 5 10.3H6.8C6.8 10.08 6.98 9.9 7.2 9.9H9.3C9.52 9.9 9.7 10.08 9.7 10.3C9.7 10.45 9.62 10.58 9.49 10.66L7.23 12.02C6.54 12.44 6.12 13.18 6.12 13.99V14.5H7.92V13.99C7.92 13.81 8.01 13.65 8.16 13.56L10.42 12.2C11.09 11.8 11.5 11.07 11.5 10.3A2.2 2.2 0 0 0 9.3 8.1H7.2ZM14 8H19V9.8H14V8ZM14 11.1H19V12.9H14V11.1Z" fill="currentColor"/></svg>';
		case 'geo':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2A10 10 0 1 0 22 12A10 10 0 0 0 12 2ZM18.93 11H15.95A15.6 15.6 0 0 0 14.85 5.77A8.04 8.04 0 0 1 18.93 11ZM12 4.07C12.82 5.25 13.72 7.61 13.95 11H10.05C10.28 7.61 11.18 5.25 12 4.07ZM5.07 13H8.05A15.6 15.6 0 0 0 9.15 18.23A8.04 8.04 0 0 1 5.07 13ZM8.05 11H5.07A8.04 8.04 0 0 1 9.15 5.77A15.6 15.6 0 0 0 8.05 11ZM12 19.93C11.18 18.75 10.28 16.39 10.05 13H13.95C13.72 16.39 12.82 18.75 12 19.93ZM14.85 18.23A15.6 15.6 0 0 0 15.95 13H18.93A8.04 8.04 0 0 1 14.85 18.23Z" fill="currentColor"/></svg>';
		case 'web-development':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4H21A2 2 0 0 1 23 6V18A2 2 0 0 1 21 20H3A2 2 0 0 1 1 18V6A2 2 0 0 1 3 4ZM3 6V8H21V6H3ZM3 18H21V10H3V18ZM9.41 12.59L10.83 14L8.83 16L10.83 18L9.41 19.41L6 16L9.41 12.59ZM14.59 12.59L18 16L14.59 19.41L13.17 18L15.17 16L13.17 14L14.59 12.59Z" fill="currentColor"/></svg>';
		case 'content-marketing':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2H15L20 7V22H6A2 2 0 0 1 4 20V4A2 2 0 0 1 6 2ZM14 3.5V8H18.5L14 3.5ZM8 11H16V13H8V11ZM8 15H14V17H8V15Z" fill="currentColor"/></svg>';
		case 'marketing-strategies':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2A10 10 0 1 0 22 12A10 10 0 0 0 12 2ZM15.9 8.1L14 14L8.1 15.9L10 10L15.9 8.1ZM12 6A6 6 0 1 1 6 12A6 6 0 0 1 12 6Z" fill="currentColor"/></svg>';
		case 'social-media-marketing':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 16A3 3 0 0 0 15.64 17.15L9.91 13.91A3.36 3.36 0 0 0 10 13A3.36 3.36 0 0 0 9.91 12.09L15.64 8.85A3 3 0 1 0 15 7A3.36 3.36 0 0 0 15.09 7.91L9.36 11.15A3 3 0 1 0 6 16A3 3 0 0 0 9.36 14.85L15.09 18.09A3 3 0 1 0 18 16Z" fill="currentColor"/></svg>';
		case 'public-relations':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5V13.5H6L12.5 18V6L6 10.5H3ZM14 8.23V15.77C15.76 15.15 17 13.49 17 12C17 10.51 15.76 8.85 14 8.23ZM18.5 6.5L20 5L21.5 6.5L20 8L18.5 6.5Z" fill="currentColor"/></svg>';
		case 'website-audit':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3H16L21 8V13H19V9H15V5H7V19H13V21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3ZM17.5 14A4.5 4.5 0 1 0 22 18.5A4.5 4.5 0 0 0 17.5 14ZM17.5 16A2.5 2.5 0 1 1 15 18.5A2.5 2.5 0 0 1 17.5 16ZM21.59 20.17L24 22.59L22.59 24L20.17 21.59L21.59 20.17Z" fill="currentColor"/></svg>';
		case 'brand-authority-development':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 23C16.16 21.74 20 16.55 20 11V5L12 2ZM12 8L13.54 11.12L17 11.62L14.5 14.06L15.09 17.5L12 15.88L8.91 17.5L9.5 14.06L7 11.62L10.46 11.12L12 8Z" fill="currentColor"/></svg>';
		case 'website-hosting-maintenance':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4H20A2 2 0 0 1 22 6V10A2 2 0 0 1 20 12H4A2 2 0 0 1 2 10V6A2 2 0 0 1 4 4ZM4 14H20A2 2 0 0 1 22 16V20A2 2 0 0 1 20 22H4A2 2 0 0 1 2 20V16A2 2 0 0 1 4 14ZM6 7H10V9H6V7ZM6 17H10V19H6V17ZM17 7A1.5 1.5 0 1 0 17 10A1.5 1.5 0 0 0 17 7ZM17 17A1.5 1.5 0 1 0 17 20A1.5 1.5 0 0 0 17 17Z" fill="currentColor"/></svg>';
		case 'gbp-management':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2A7 7 0 0 0 5 9C5 13.42 12 22 12 22S19 13.42 19 9A7 7 0 0 0 12 2ZM12 11.5A2.5 2.5 0 1 1 14.5 9A2.5 2.5 0 0 1 12 11.5Z" fill="currentColor"/></svg>';
		case 'sales-funnel-development':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4H21L14 12V19L10 21V12L3 4Z" fill="currentColor"/></svg>';
		case 'crm-integration-optimization':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2A3 3 0 0 1 10 5A3.02 3.02 0 0 1 9.82 6H14.18A3.02 3.02 0 0 1 14 5A3 3 0 1 1 17 8A3.02 3.02 0 0 1 16.82 7H16V10.18A3 3 0 1 1 14 10.18V7H10V14.18A3 3 0 1 1 8 14.18V7.82A3 3 0 1 1 7 2ZM7 4A1 1 0 1 0 8 5A1 1 0 0 0 7 4ZM17 4A1 1 0 1 0 18 5A1 1 0 0 0 17 4ZM7 16A1 1 0 1 0 8 17A1 1 0 0 0 7 16ZM17 12A1 1 0 1 0 18 13A1 1 0 0 0 17 12Z" fill="currentColor"/></svg>';
		case 'sales-coaching':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3A4 4 0 1 1 8 7A4 4 0 0 1 12 3ZM6 19A6 6 0 0 1 18 19V21H6V19ZM20.5 5.5L22 7L18 11L16 9L17.5 7.5L18 8L20.5 5.5Z" fill="currentColor"/></svg>';
		case 'growth-consultation-security-company':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3L5 12H10V21L19 12H14V3ZM4 4H8V6H6V8H4V4ZM16 18H20V20H16V18Z" fill="currentColor"/></svg>';
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 2.18 6 2.25V11c0 4.4-2.89 8.82-6 10.17C8.89 19.82 6 15.4 6 11V6.43l6-2.25Z" fill="currentColor"/></svg>';
	}
}

function gms_render_service_archive_cards(): void
{
	$config = gms_get_demo_config();
	?>
	<div class="gms-service-grid__items">
		<?php foreach ($config['services'] as $service): ?>
			<?php
			$slug = (string) ($service['slug'] ?? '');
			$url  = home_url('/services/' . $slug . '/');
			?>
			<article class="gms-service-tile gms-service-tile--premium gms-service-tile--<?php echo esc_attr(sanitize_html_class($slug)); ?>">
				<a class="gms-service-tile__overlay" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($service['title']); ?>"></a>
				<div class="gms-service-tile__surface">
					<div class="gms-service-tile__top">
						<span class="gms-service-tile__tag"><?php echo esc_html(gms_get_service_card_tag($slug)); ?></span>
						<div class="gms-service-tile__icon" aria-hidden="true">
							<?php echo gms_render_service_card_icon($slug); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
					<div class="gms-service-tile__body">
						<h3><?php echo esc_html($service['title']); ?></h3>
						<p><?php echo esc_html($service['description']); ?></p>
						<ul class="gms-service-tile__list">
							<?php foreach ($service['bullets'] as $bullet): ?>
								<li><span><?php echo esc_html($bullet); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="gms-service-tile__footer">
						<a class="gms-service-tile__button" href="<?php echo esc_url($url); ?>">
							<span><?php esc_html_e('Learn More', 'grow-my-security'); ?></span>
							<span class="gms-service-tile__button-arrow" aria-hidden="true"></span>
						</a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
	<?php
}

function gms_render_service_detail_content(array $service): void
{
	$config = gms_get_demo_config();
	$feature_map = gms_get_service_feature_map();
	$feature_pairs = $feature_map[$service['slug']] ?? [
		[$service['icon'] ?? 'SRV', __('Custom Strategy', 'grow-my-security')],
		['OPS', __('Execution Support', 'grow-my-security')],
	];
	?>
	<section
		class="gms-page-hero gms-page-hero--detail <?php echo 'advertising-solutions' === $service['slug'] ? 'gms-page-hero--advertising-trail' : ''; ?>">
		<div class="gms-page-hero__copy">
			<?php if ('advertising-solutions' === $service['slug']): ?>
				<h1 class="gms-page-hero__trail-title"><?php echo esc_html($service['title']); ?></h1>
				<nav class="gms-page-hero__trail" aria-label="<?php esc_attr_e('Breadcrumb', 'grow-my-security'); ?>">
					<a class="gms-page-hero__trail-item" href="<?php echo esc_url(home_url('/')); ?>">
						<span class="gms-page-hero__trail-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" role="presentation" focusable="false">
								<path d="M3 10.5 12 3l9 7.5" fill="none" stroke="currentColor" stroke-linecap="round"
									stroke-linejoin="round" stroke-width="1.8" />
								<path d="M5.25 9.75V21h13.5V9.75" fill="none" stroke="currentColor" stroke-linecap="round"
									stroke-linejoin="round" stroke-width="1.8" />
								<path d="M9.75 21v-5.25h4.5V21" fill="none" stroke="currentColor" stroke-linecap="round"
									stroke-linejoin="round" stroke-width="1.8" />
							</svg>
						</span>
						<span><?php esc_html_e('Home', 'grow-my-security'); ?></span>
					</a>
					<span class="gms-page-hero__trail-sep" aria-hidden="true">&rsaquo;</span>
					<a class="gms-page-hero__trail-item" href="<?php echo esc_url(home_url('/services/')); ?>">
						<span class="gms-page-hero__trail-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" role="presentation" focusable="false">
								<path
									d="M12 3.75l1.33 2.7 2.98.43-2.15 2.1.51 2.97L12 10.55 9.33 11.95l.51-2.97-2.15-2.1 2.98-.43L12 3.75z"
									fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.8" />
								<path
									d="M5.2 14.2 3.75 15l1.45.8.8 1.45.8-1.45 1.45-.8-1.45-.8-.8-1.45-.8 1.45zm13 0-1.45.8.8 1.45.8-1.45 1.45-.8-1.45-.8-.8-1.45-.8 1.45z"
									fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.5" />
								<circle cx="12" cy="15.25" r="4.5" fill="none" stroke="currentColor" stroke-width="1.8" />
							</svg>
						</span>
						<span><?php esc_html_e('Services', 'grow-my-security'); ?></span>
					</a>
					<span class="gms-page-hero__trail-sep" aria-hidden="true">&rsaquo;</span>
					<span class="gms-page-hero__trail-item gms-page-hero__trail-item--current" aria-current="page">
						<span class="gms-page-hero__trail-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" role="presentation" focusable="false">
								<path
									d="M4.5 11.25V18A1.5 1.5 0 0 0 6 19.5h3V9.75H6a1.5 1.5 0 0 0-1.5 1.5zm4.5 0L18.75 6v12L9 12.75v-1.5zm9.75-2.25 1.5-1.5m-1.5 8.25 1.5 1.5M20.25 12h2.25"
									fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
									stroke-width="1.8" />
							</svg>
						</span>
						<span><?php echo esc_html($service['title']); ?></span>
					</span>
				</nav>
			<?php else: ?>
				<div class="gms-eyebrow"><?php esc_html_e('Service', 'grow-my-security'); ?></div>
				<h1><?php echo esc_html($service['title']); ?></h1>
				<p><?php echo esc_html($service['description']); ?></p>
			<?php endif; ?>
		</div>
		<div class="gms-page-hero__art">
			<img src="<?php echo esc_url(gms_get_generated_asset_url('service-detail-hero.png', get_theme_file_uri('assets/images/services-hero-art.png'))); ?>"
				alt="" loading="eager" decoding="async" fetchpriority="high">
		</div>
	</section>

	<section class="gms-service-detail">
		<aside class="gms-service-detail__sidebar">
			<nav class="gms-service-detail__nav"
				aria-label="<?php esc_attr_e('Service navigation', 'grow-my-security'); ?>">
				<?php foreach ($config['services'] as $nav_item): ?>
					<?php $is_active = $nav_item['slug'] === $service['slug']; ?>
					<a class="gms-service-detail__nav-link <?php echo esc_attr($is_active ? 'is-active' : ''); ?>"
						href="<?php echo esc_url(home_url('/services/' . $nav_item['slug'] . '/')); ?>" <?php echo $is_active ? ' aria-current="page"' : ''; ?>>
						<span
							class="gms-service-detail__nav-label"><?php echo esc_html($nav_item['nav_title'] ?? $nav_item['title']); ?></span>
						<span class="gms-service-detail__nav-arrow" aria-hidden="true">&rarr;</span>
					</a>
				<?php endforeach; ?>
			</nav>
			<div class="gms-service-detail__contact">
				<h3><?php esc_html_e('Contact with us for any advice', 'grow-my-security'); ?></h3>
				<p><?php esc_html_e('Need to move fast? Talk to an expert.', 'grow-my-security'); ?></p>
				<a
					href="https://meetings.hubspot.com/rumore/grow-my-security-company-?uuid=fa51c8d1-f823-42df-91ac-85496638ef83" target="_blank" rel="noopener noreferrer"><?php echo esc_html($config['branding']['phone']); ?></a>
			</div>
		</aside>
		<div class="gms-service-detail__content">
			<div class="gms-service-detail__overview-media">
				<img src="<?php echo esc_url(get_theme_file_uri('assets/images/service-overview.png')); ?>" alt=""
					loading="lazy" decoding="async">
			</div>
			<section class="gms-service-detail__section">
				<h2><?php esc_html_e('Service Overview', 'grow-my-security'); ?></h2>
				<div class="gms-service-detail__prose">
					<p><?php echo esc_html($service['description']); ?></p>
					<p><?php esc_html_e('Our approach connects positioning, trust cues, and conversion structure so buyers understand why your brand feels safer to choose before the first conversation happens.', 'grow-my-security'); ?>
					</p>
					<p><?php esc_html_e('That means category-aware messaging, cleaner demand pathways, and a website system that supports authority instead of diluting it.', 'grow-my-security'); ?>
					</p>
				</div>
			</section>
			<section class="gms-service-detail__section">
				<h2><?php esc_html_e('Key Features', 'grow-my-security'); ?></h2>
				<p class="gms-service-detail__intro">
					<?php esc_html_e('Our services are designed to empower your brand with strategy, clarity, and the credibility signals that matter in technical, trust-sensitive markets.', 'grow-my-security'); ?>
				</p>
				<div class="gms-service-detail__features">
					<?php foreach ($feature_pairs as $index => $feature_pair): ?>
						<article class="gms-service-feature-card">
							<div class="gms-service-feature-card__icon" aria-hidden="true">
								<?php echo esc_html($feature_pair[0]); ?></div>
							<h3><?php echo esc_html($feature_pair[1]); ?></h3>
							<p><?php echo esc_html(0 === $index ? __('Built for category clarity, stronger trust signals, and cleaner buyer understanding across the full journey.', 'grow-my-security') : __('Execution stays aligned to measurable authority, stronger conversion pathways, and long-term demand compounding.', 'grow-my-security')); ?>
							</p>
							<ul>
								<?php foreach ($service['bullets'] as $bullet): ?>
									<li><?php echo esc_html($bullet); ?></li>
								<?php endforeach; ?>
							</ul>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
			<section class="gms-service-detail__section gms-service-detail__benefits">
				<div class="gms-service-detail__benefits-copy">
					<h2><?php esc_html_e('Our Benefits', 'grow-my-security'); ?></h2>
					<p class="gms-service-detail__intro">
						<?php esc_html_e('Choosing Grow My Security means partnering with a team dedicated to turning expertise into visible authority and higher-conviction demand.', 'grow-my-security'); ?>
					</p>
					<div class="gms-service-benefits__list">
						<article class="gms-service-benefit">
							<h3><?php esc_html_e('Tailored Strategies', 'grow-my-security'); ?></h3>
							<p><?php esc_html_e('Custom systems designed around your goals, market position, and the trust gaps that need to close first.', 'grow-my-security'); ?>
							</p>
						</article>
						<article class="gms-service-benefit">
							<h3><?php esc_html_e('Data-Driven Insights', 'grow-my-security'); ?></h3>
							<p><?php esc_html_e('We use evidence to refine messaging, prioritize channels, and improve the quality of the pipeline you attract.', 'grow-my-security'); ?>
							</p>
						</article>
						<article class="gms-service-benefit">
							<h3><?php esc_html_e('Innovative Tools', 'grow-my-security'); ?></h3>
							<p><?php esc_html_e('Modern workflows, structured execution, and production systems that help your team move faster without sounding generic.', 'grow-my-security'); ?>
							</p>
						</article>
					</div>
				</div>
				<div class="gms-service-detail__benefits-media">
					<img src="<?php echo esc_url(gms_get_generated_asset_url('service-benefits.png', get_theme_file_uri('assets/images/home-solution-media.png'))); ?>"
						alt="" loading="lazy" decoding="async">
				</div>
			</section>
			<section class="gms-service-detail__section gms-service-detail__faq">
				<div class="gms-faq-list">
					<?php foreach (gms_get_extended_faq_items() as $index => $item): ?>
						<div class="gms-faq-item <?php echo esc_attr(0 === $index ? 'is-open' : ''); ?>">
							<button class="gms-faq-question" type="button"
								aria-expanded="<?php echo esc_attr(0 === $index ? 'true' : 'false'); ?>">
								<?php echo esc_html($item['question']); ?>
							</button>
							<div class="gms-faq-answer" <?php echo 0 === $index ? '' : ' hidden'; ?>>
								<p><?php echo esc_html($item['answer']); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
	</section>
	<?php
}

function gms_get_about_values_from_elementor(): array
{
	$page = get_page_by_path('about-us');
	if (!($page instanceof WP_Post)) return [];
	$raw = get_post_meta($page->ID, '_elementor_data', true);
	if (!is_string($raw) || '' === trim($raw)) return [];
	$data = json_decode($raw, true);
	if (!is_array($data)) return [];

	$cards = [];
	$stack = $data;
	while (!empty($stack)) {
		$node = array_shift($stack);
		if (!is_array($node)) continue;
		if (isset($node['widgetType']) && 'gms-story' === $node['widgetType'] && !empty($node['settings']['values'])) {
			foreach ($node['settings']['values'] as $card) {
				$title = is_array($card['title'] ?? null) ? ($card['title']['title'] ?? '') : ($card['title'] ?? '');
				$title = trim((string) $title);
				if ('' !== $title) {
					$cards[] = ['title' => $title, 'text' => trim((string) ($card['text'] ?? ''))];
				}
			}
			return $cards;
		}
		if (!empty($node['elements'])) {
			foreach ($node['elements'] as $child) {
				$stack[] = $child;
			}
		}
	}
	return $cards;
}

function gms_render_about_page_content(): void
{
	gms_render_internal_intro(
		[
			'eyebrow' => __('About Us', 'grow-my-security'),
			'title' => __('Turn Expertise into trust that scales', 'grow-my-security'),
			'lede' => __('We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.', 'grow-my-security'),
			'modifier' => 'about',
			'support_html' => sprintf(
				'<p>%1$s</p><a class="gms-button" href="%2$s">%3$s</a>',
				esc_html__('Grow My Security Company offers fresh and grounded leadership for security brands that need more visible trust, more consistent authority, and better commercial momentum.', 'grow-my-security'),
				esc_url('#gms-about-origin'),
				esc_html__('Meet Founder', 'grow-my-security')
			),
		]
	);
	$elementor_cards = gms_get_about_values_from_elementor();
	$cards_to_render = !empty($elementor_cards) ? $elementor_cards : gms_get_about_value_cards();
	?>
	<figure class="gms-approved-media-panel gms-approved-media-panel--wide">
		<img src="<?php echo esc_url(gms_get_generated_asset_url('about-team.png', gms_get_brand_asset_url('page-about'))); ?>"
			alt="<?php esc_attr_e('Grow My Security strategy discussion', 'grow-my-security'); ?>" loading="lazy"
			decoding="async">
	</figure>
	<section class="gms-approved-section gms-approved-section--mission">
		<div class="gms-approved-section__intro">
			<div class="gms-eyebrow"><?php esc_html_e('Our Commitment', 'grow-my-security'); ?></div>
			<h2><?php esc_html_e('Our Mission & Values', 'grow-my-security'); ?></h2>
		</div>
		<div class="gms-approved-section__content">
			<p><?php esc_html_e('We help technical brands turn visible credibility into measurable authority, stronger conversion, and more resilient growth built on trust instead of noise.', 'grow-my-security'); ?>
			</p>
			<div class="gms-approved-quote">
				<?php esc_html_e('Born in decades of lived cybersecurity, we believe trust is built in action and then accelerated by the right digital experience.', 'grow-my-security'); ?>
			</div>
			<div class="gms-approved-value-grid">
				<?php foreach ($cards_to_render as $card): ?>
					<article class="gms-approved-value-card">
						<h3><?php echo esc_html($card['title']); ?></h3>
						<p><?php echo esc_html($card['text']); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<section id="gms-about-origin" class="gms-approved-section gms-approved-section--origin">
		<div class="gms-approved-section__intro">
			<div class="gms-eyebrow"><?php esc_html_e('Our Story', 'grow-my-security'); ?></div>
			<h2><?php esc_html_e('Origin Story', 'grow-my-security'); ?></h2>
		</div>
		<div class="gms-approved-origin">
			<div class="gms-approved-origin__story">
				<img src="<?php echo esc_url(gms_get_generated_asset_url('about-origin.png', gms_get_brand_asset_url('page-about'))); ?>"
					alt="<?php esc_attr_e('Security leadership meeting', 'grow-my-security'); ?>" loading="lazy"
					decoding="async">
				<p><?php esc_html_e('After spending nearly 20 years climbing the security corporate ladder, Anthony noticed a heightened need for strategic company growth inside the security industry. Security expertise was abundant, but the ability to communicate it online with clarity, authority, and consistency was often missing.', 'grow-my-security'); ?>
				</p>
			</div>
			<div class="gms-approved-founder">
				<img src="<?php echo esc_url(gms_get_generated_asset_url('about-founder.png', gms_get_brand_asset_url('page-about'))); ?>"
					alt="<?php esc_attr_e('Anthony Rumore portrait', 'grow-my-security'); ?>" loading="lazy"
					decoding="async">
				<div class="gms-approved-founder__copy">
					<p><?php esc_html_e('Born and raised in Long Island, Anthony currently resides in Cave Creek, Arizona, where he and his wife actively work to support first responders, law enforcement, and veteran communities.', 'grow-my-security'); ?>
					</p>
					<p><?php esc_html_e('Anthony served in the United States Marine Corps and later spent more than a decade in law enforcement before moving into sales and leadership roles across the security industry. That combined background shapes the disciplined, trust-first way Grow My Security operates today.', 'grow-my-security'); ?>
					</p>
				</div>
			</div>
		</div>
	</section>
	<?php
}

function gms_get_contact_page_defaults(): array
{
	$config = gms_get_demo_config();

	return [
		'eyebrow'       => __('Contact Us', 'grow-my-security'),
		'title'         => __('Ready to build, Trust & Pipeline?', 'grow-my-security'),
		'description'   => __('Let\'s build a stronger pipeline for your security business, secure, scalable, and measurable.', 'grow-my-security'),
		'submit_text'   => __('Start the Conversation', 'grow-my-security'),
		'email'         => (string) ($config['branding']['email'] ?? ''),
		'response_note' => __('We respond to all inquiries within 1 business day', 'grow-my-security'),
		'panel_image'   => [
			'url' => get_theme_file_uri('assets/images/contact-us-panel-visual.png'),
		],
		'email_heading' => __('Prefer Email?', 'grow-my-security'),
		'email_note'    => __('We respect your inbox. No spam, ever.', 'grow-my-security'),
		'call_heading'  => __('Need To Move Fast?', 'grow-my-security'),
		'call_text'     => __('Book a call directly', 'grow-my-security'),
		'call_url'      => [
			'url' => 'https://meetings.hubspot.com/rumore/grow-my-security-company-?uuid=fa51c8d1-f823-42df-91ac-85496638ef83',
		],
	];
}

function gms_get_contact_page_media_url($media): string
{
	if (is_array($media)) {
		$url = (string) ($media['url'] ?? '');

		if ('' !== $url) {
			return function_exists('gms_normalize_public_url') ? (string) gms_normalize_public_url($url) : $url;
		}
	}

	if (is_string($media)) {
		return function_exists('gms_normalize_public_url') ? (string) gms_normalize_public_url($media) : $media;
	}

	return '';
}

function gms_render_contact_page_layout(array $settings = []): void
{
	$config   = gms_get_demo_config();
	$defaults = gms_get_contact_page_defaults();
	$settings = array_replace_recursive($defaults, $settings);

	$eyebrow       = (string) ($settings['eyebrow'] ?? '');
	$title         = (string) ($settings['title'] ?? '');
	$description   = (string) ($settings['description'] ?? '');
	$submit_text   = (string) ($settings['submit_text'] ?? '');
	$email         = trim((string) ($settings['email'] ?? ''));
	$response_note = (string) ($settings['response_note'] ?? '');
	$email_heading = (string) ($settings['email_heading'] ?? '');
	$email_note    = (string) ($settings['email_note'] ?? '');
	$call_heading  = (string) ($settings['call_heading'] ?? '');
	$call_text     = (string) ($settings['call_text'] ?? '');
	$call_url      = is_array($settings['call_url'] ?? null) ? (string) (($settings['call_url']['url'] ?? '')) : (string) ($settings['call_url'] ?? '');
	$panel_image   = gms_get_contact_page_media_url($settings['panel_image'] ?? '');

	gms_render_internal_intro(
		[
			'eyebrow'      => $eyebrow,
			'title'        => $title,
			'modifier'     => 'contact',
			'support_html' => '' !== trim($description) ? sprintf('<p>%s</p>', esc_html($description)) : '',
		]
	);
	?>
	<section class="gms-approved-contact-shell">
		<div class="gms-approved-contact-form">
			<div class="gms-approved-form-grid">
				<label>
					<span><?php esc_html_e('Full Name', 'grow-my-security'); ?></span>
					<input type="text" name="full_name" placeholder="e.g. Jane Doe" autocomplete="name">
				</label>
				<label>
					<span><?php esc_html_e('Email Address', 'grow-my-security'); ?></span>
					<input type="email" name="email" placeholder="e.g. jane@company.com" autocomplete="email">
				</label>
				<label>
					<span><?php esc_html_e('Company Name', 'grow-my-security'); ?></span>
					<input type="text" name="company_name" placeholder="e.g. Acme Corp" autocomplete="organization">
				</label>
				<label>
					<span><?php esc_html_e('Industry', 'grow-my-security'); ?></span>
					<select name="industry">
						<option value=""><?php esc_html_e('Select Industry', 'grow-my-security'); ?></option>
						<?php foreach ($config['industries'] as $industry): ?>
							<option value="<?php echo esc_attr($industry); ?>"><?php echo esc_html($industry); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<label>
				<span><?php esc_html_e('How did you hear about Grow My Security Company?', 'grow-my-security'); ?></span>
				<input type="text" name="referral_source"
					placeholder="<?php esc_attr_e('Found on Google search, Facebook, YouTube, Referral...', 'grow-my-security'); ?>">
			</label>
			<label>
				<span><?php esc_html_e('Service you\'re interested in', 'grow-my-security'); ?></span>
				<select name="service_interest">
					<option value=""><?php esc_html_e('Select Services', 'grow-my-security'); ?></option>
					<?php foreach ($config['services'] as $service): ?>
						<option value="<?php echo esc_attr($service['title']); ?>">
							<?php echo esc_html($service['title']); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>
				<span><?php esc_html_e('Message (Optional)', 'grow-my-security'); ?></span>
				<textarea name="message"
					placeholder="<?php esc_attr_e('Enter any details you\'d like to share with us...', 'grow-my-security'); ?>"></textarea>
			</label>
			<label class="gms-approved-checkbox">
				<input type="checkbox" name="privacy_acceptance" value="1">
				<span><?php esc_html_e('By submitting this form you agree to our privacy policy', 'grow-my-security'); ?></span>
			</label>
			<label class="gms-approved-checkbox gms-approved-checkbox--bot">
				<input type="checkbox" name="bot_check" value="1">
				<span><?php esc_html_e('I\'m not a robot', 'grow-my-security'); ?></span>
			</label>
			<a class="gms-button" style="text-decoration: none; text-align: center;"
				href="https://meetings.hubspot.com/rumore/grow-my-security-company-?uuid=fa51c8d1-f823-42df-91ac-85496638ef83"><?php echo esc_html($submit_text); ?></a>
		</div>
		<aside class="gms-approved-contact-aside">
			<?php if ('' !== trim($response_note)) : ?>
				<div class="gms-approved-contact-note">
					<p><?php echo esc_html($response_note); ?></p>
				</div>
			<?php endif; ?>
			<?php if ('' !== $panel_image) : ?>
				<div class="gms-approved-contact-panel" aria-hidden="true"><img src="<?php echo esc_url($panel_image); ?>" alt="" loading="lazy" decoding="async"></div>
			<?php endif; ?>
			<div class="gms-approved-contact-links">
				<div>
					<strong><?php echo esc_html($email_heading); ?></strong>
					<a
						href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
					<span><?php echo esc_html($email_note); ?></span>
				</div>
				<div>
					<strong><?php echo esc_html($call_heading); ?></strong>
					<a
						href="<?php echo esc_url($call_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($call_text); ?></a>
				</div>
			</div>
		</aside>
	</section>
	<?php
}

function gms_render_contact_page_content(): void
{
	gms_render_contact_page_layout();
}

function gms_get_faq_items_from_elementor(): array
{
	$page = get_page_by_path('faq');

	if (!($page instanceof WP_Post)) {
		return [];
	}

	$raw = get_post_meta($page->ID, '_elementor_data', true);

	if (!is_string($raw) || '' === trim($raw)) {
		return [];
	}

	$data = json_decode($raw, true);

	if (!is_array($data)) {
		return [];
	}

	$items = [];
	$stack = $data;

	while (!empty($stack)) {
		$node = array_shift($stack);

		if (!is_array($node)) {
			continue;
		}

		if (isset($node['widgetType']) && 'gms-faq' === $node['widgetType'] && !empty($node['settings']['items'])) {
			foreach ($node['settings']['items'] as $item) {
				$q = trim((string) ($item['question'] ?? ''));
				$a = trim((string) ($item['answer'] ?? ''));

				if ('' !== $q && '' !== $a) {
					$items[] = [
						'question' => $q,
						'answer'   => $a,
					];
				}
			}

			return $items;
		}

		if (!empty($node['elements'])) {
			foreach ($node['elements'] as $child) {
				$stack[] = $child;
			}
		}
	}

	return $items;
}

function gms_render_faq_page_content(): void
{
	gms_render_internal_intro(
		[
			'eyebrow' => __('FAQ', 'grow-my-security'),
			'title' => __('Read Frequently Asked Question', 'grow-my-security'),
			'modifier' => 'faq',
			'support_html' => sprintf('<p>%s</p>', esc_html__('Straight answers about how we are helping brands find their voice and connect with their audience in meaningful ways.', 'grow-my-security')),
		]
	);
	
	$elementor_items = gms_get_faq_items_from_elementor();
	$items_to_render = !empty($elementor_items) ? $elementor_items : gms_get_extended_faq_items();
	?>
	<section class="gms-approved-faq-shell">
		<div class="gms-faq-list">
			<?php foreach ($items_to_render as $index => $faq_item): ?>
				<div class="gms-faq-item <?php echo esc_attr(0 === $index ? 'is-open' : ''); ?>">
					<button class="gms-faq-question" type="button"
						aria-expanded="<?php echo esc_attr(0 === $index ? 'true' : 'false'); ?>">
						<?php echo esc_html($faq_item['question']); ?>
					</button>
					<div class="gms-faq-answer" <?php echo 0 === $index ? '' : ' hidden'; ?>>
						<p><?php echo esc_html($faq_item['answer']); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<p class="gms-approved-faq-footer">
			<?php esc_html_e('Do you have anymore questions for us?', 'grow-my-security'); ?>
			<a
				href="<?php echo esc_url(home_url('/contact-us/')); ?>"><?php esc_html_e('Contact Us', 'grow-my-security'); ?></a>
		</p>
	</section>
	<?php
	gms_render_money_cta();
}

function gms_render_resources_page_content(WP_Post $post): void
{
	$filter = sanitize_title(wp_unslash($_GET['gms_filter'] ?? ''));
	$search = sanitize_text_field(wp_unslash($_GET['gms_search'] ?? ''));
	$resources_q = gms_get_resources_query($filter, $search);
	$categories = get_categories(
		[
			'hide_empty' => true,
			'exclude' => array_values(array_filter([get_cat_ID('Press'), get_cat_ID('Podcast'), get_cat_ID('Uncategorized')])),
		]
	);

	gms_render_internal_intro(
		[
			'eyebrow' => __('Blog', 'grow-my-security'),
			'title' => __('Resources & Insights', 'grow-my-security'),
			'lede' => __('News, commentary, and insight built specifically for high-trust markets.', 'grow-my-security'),
			'modifier' => 'resources',
			'support_html' => '',
		]
	);
	?>
	<section class="gms-approved-resources-shell">
		<div class="gms-approved-resource-toolbar">
			<div class="gms-approved-filter-list">
				<a class="<?php echo esc_attr('' === $filter ? 'is-active' : ''); ?>"
					href="<?php echo esc_url(get_permalink($post)); ?>"><?php esc_html_e('All', 'grow-my-security'); ?></a>
				<?php foreach ($categories as $category): ?>
					<a class="<?php echo esc_attr($filter === $category->slug ? 'is-active' : ''); ?>"
						href="<?php echo esc_url(add_query_arg(['gms_filter' => $category->slug], get_permalink($post))); ?>"><?php echo esc_html($category->name); ?></a>
				<?php endforeach; ?>
			</div>
			<form class="gms-approved-search" method="get" action="<?php echo esc_url(get_permalink($post)); ?>">
				<?php if ('' !== $filter): ?>
					<input type="hidden" name="gms_filter" value="<?php echo esc_attr($filter); ?>">
				<?php endif; ?>
				<input type="search" name="gms_search" value="<?php echo esc_attr($search); ?>"
					placeholder="<?php esc_attr_e('Search articles...', 'grow-my-security'); ?>">
			</form>
		</div>
		<div class="gms-post-grid-widget gms-post-grid-widget--approved">
			<?php
			$display_posts = $resources_q->posts;
			if (empty($filter) && '' === $search && count($display_posts) > 0 && count($display_posts) < 6) {
				$original = $display_posts;
				while (count($display_posts) < 6) {
					foreach ($original as $repeat_post) {
						$display_posts[] = $repeat_post;
						if (count($display_posts) >= 6) {
							break 2;
						}
					}
				}
			}

			foreach ($display_posts as $display_post) {
				gms_render_post_card($display_post);
			}
			?>
		</div>
	</section>
	<?php
	gms_render_money_cta(
		[
			'eyebrow' => __('FAQ\'s', 'grow-my-security'),
			'title' => __('Ready to build trust that drives revenue?', 'grow-my-security'),
			'title_html' => '',
			'copy' => __('You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.', 'grow-my-security'),
			'copy_html' => '',
			'button' => __('Schedule a Free Consultation', 'grow-my-security'),
			'url' => home_url('/contact-us/'),
			'footnote' => __('82% of B2B buyers ignore vendors they do not trust. We help your people earn belief before the first conversation, because trust is what drives every deal.', 'grow-my-security'),
			'footnote_html' => '',
			'image_url' => get_theme_file_uri('assets/images/security-dashboard-visual.png'),
		]
	);
}

function gms_get_press_items_from_elementor(): array
{
	$page = get_page_by_path('press-media');
	if (!($page instanceof WP_Post)) return [];
	$raw = get_post_meta($page->ID, '_elementor_data', true);
	if (!is_string($raw) || '' === trim($raw)) return [];
	$data = json_decode($raw, true);
	if (!is_array($data)) return [];

	$items = [];
	$stack = $data;
	while (!empty($stack)) {
		$node = array_shift($stack);
		if (!is_array($node)) continue;
		if (isset($node['widgetType']) && 'gms-post-grid' === $node['widgetType'] && !empty($node['settings']['items'])) {
			foreach ($node['settings']['items'] as $item) {
				$title = trim((string) ($item['title'] ?? ''));
				$url = trim((string) ($item['url']['url'] ?? ''));
				$image = '';
				if (!empty($item['image']['url'])) {
					$image = $item['image']['url'];
				}
				if ('' !== $title) {
					$items[] = [
						'title' => $title,
						'url'   => $url,
						'image' => $image,
					];
				}
			}
			return $items;
		}
		if (!empty($node['elements'])) {
			foreach ($node['elements'] as $child) {
				$stack[] = $child;
			}
		}
	}
	return $items;
}

function gms_render_press_page_content(): void
{
	ob_start();
	gms_render_newsletter_form('press-media', __('Subscribe', 'grow-my-security'));
	$newsletter_markup = ob_get_clean();

	gms_render_internal_intro(
		[
			'eyebrow' => __('Press & Media', 'grow-my-security'),
			'title' => __('Press & Media', 'grow-my-security'),
			'lede' => __('Coverage, interviews, and commentary created to strengthen trust for security brands operating in high-consequence markets.', 'grow-my-security'),
			'modifier' => 'press',
			'support_html' => $newsletter_markup,
		]
	);
	
	$elementor_items = gms_get_press_items_from_elementor();
	if (!empty($elementor_items)) {
		$features = array_slice($elementor_items, 0, 3);
		$coverage = array_slice($elementor_items, 3);
	} else {
		$features = gms_get_press_feature_items();
		$coverage = gms_get_press_coverage_items();
	}
	?>
	<section class="gms-approved-press-features">
		<?php foreach ($features as $item): ?>
			<a class="gms-approved-feature-card" href="<?php echo esc_url($item['url']); ?>">
				<?php if (!empty($item['image'])): ?>
					<img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>"
						loading="lazy" decoding="async">
				<?php endif; ?>
				<span><?php echo esc_html($item['title']); ?></span>
			</a>
		<?php endforeach; ?>
	</section>
	<section class="gms-approved-coverage">
		<h2><?php esc_html_e('Media Coverage', 'grow-my-security'); ?></h2>
		<div class="gms-approved-coverage__grid">
			<?php foreach ($coverage as $item): ?>
				<a class="gms-approved-coverage-card" href="<?php echo esc_url($item['url']); ?>">
					<span class="gms-approved-coverage-card__dot" aria-hidden="true"></span>
					<strong><?php echo esc_html($item['title']); ?></strong>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<section class="gms-approved-spotlight">
		<div class="gms-approved-spotlight__media">
			<img src="<?php echo esc_url(gms_get_generated_asset_url('press-spotlight.png', gms_get_brand_asset_url('page-single-press'))); ?>"
				alt="<?php esc_attr_e('Press feature spotlight', 'grow-my-security'); ?>" loading="lazy" decoding="async">
		</div>
		<div class="gms-approved-spotlight__copy">
			<h3><?php esc_html_e('My Husband Was a First Responder on 9/11', 'grow-my-security'); ?></h3>
			<a class="gms-button"
				href="<?php echo esc_url(home_url('/press-media/')); ?>"><?php esc_html_e('Watch Interview', 'grow-my-security'); ?></a>
		</div>
	</section>
	<?php
}

function gms_get_podcast_items_from_elementor(): array
{
	$page = get_page_by_path('podcast');
	if (!($page instanceof WP_Post)) return [];
	$raw = get_post_meta($page->ID, '_elementor_data', true);
	if (!is_string($raw) || '' === trim($raw)) return [];
	$data = json_decode($raw, true);
	if (!is_array($data)) return [];

	$items = [];
	$stack = $data;
	while (!empty($stack)) {
		$node = array_shift($stack);
		if (!is_array($node)) continue;
		if (isset($node['widgetType']) && 'gms-post-grid' === $node['widgetType'] && !empty($node['settings']['items'])) {
			foreach ($node['settings']['items'] as $item) {
				$title = trim((string) ($item['title'] ?? ''));
				$url = trim((string) ($item['url']['url'] ?? ''));
				$image = '';
				if (!empty($item['image']['url'])) {
					$image = $item['image']['url'];
				}
				if ('' !== $title) {
					$items[] = [
						'title' => $title,
						'url'   => $url,
						'image' => $image,
					];
				}
			}
			return $items;
		}
		if (!empty($node['elements'])) {
			foreach ($node['elements'] as $child) {
				$stack[] = $child;
			}
		}
	}
	return $items;
}

function gms_render_podcast_page_content(): void
{
	$waveform = '<div class="gms-approved-waveform" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>';
	gms_render_internal_intro(
		[
			'eyebrow' => __('Podcast', 'grow-my-security'),
			'title' => __('Broadcasting Across Platforms', 'grow-my-security'),
			'lede' => __('We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.', 'grow-my-security'),
			'modifier' => 'podcast',
			'support_html' => $waveform,
		]
	);

	$elementor_items = gms_get_podcast_items_from_elementor();
	$items_to_render = !empty($elementor_items) ? $elementor_items : gms_get_podcast_episode_items();
	?>
	<section class="gms-approved-podcast-grid">
		<?php foreach ($items_to_render as $item): ?>
			<a class="gms-approved-episode-card" href="<?php echo esc_url($item['url'] ?? ''); ?>">
				<?php if (!empty($item['image'])): ?>
					<img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title'] ?? ''); ?>"
						loading="lazy" decoding="async">
				<?php endif; ?>
				<span><?php echo esc_html($item['title'] ?? ''); ?></span>
			</a>
		<?php endforeach; ?>
	</section>
	<div class="gms-approved-actions">
		<a class="gms-button"
			href="<?php echo esc_url(home_url('/press-media/')); ?>"><?php esc_html_e('Explore Media & Press', 'grow-my-security'); ?></a>
	</div>
	<?php
}

function gms_render_services_page_content(): void
{
	gms_render_internal_intro(
		[
			'eyebrow' => __('Services', 'grow-my-security'),
			'title' => __('Creative Services
built for impact', 'grow-my-security'),
			'lede' => __('We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.', 'grow-my-security'),
			'modifier' => 'services',
			'support_html' => sprintf('<div class="gms-approved-art-card"><img src="%s" alt="" loading="eager" decoding="async" fetchpriority="high"></div>', esc_url(get_theme_file_uri('assets/images/services-hero-art.png'))),
		]
	);
	gms_render_service_archive_cards();
	gms_render_money_cta(
		[
			'eyebrow' => __('FAQ\'s', 'grow-my-security'),
			'title' => __('Ready to build trust that drives revenue?', 'grow-my-security'),
			'title_html' => '',
			'copy' => __('You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.', 'grow-my-security'),
			'copy_html' => '',
			'button' => __('Schedule a Free Consultation', 'grow-my-security'),
			'url' => home_url('/contact-us/'),
			'footnote' => __('82% of B2B buyers ignore vendors they do not trust. We help your people earn belief before the first conversation, because trust is what drives every deal.', 'grow-my-security'),
			'footnote_html' => '',
			'image_url' => get_theme_file_uri('assets/images/security-dashboard-visual.png'),
		]
	);
}

function gms_render_industry_card_icon( string $industry_name ): string {
	$icon = gms_get_industry_icon( $industry_name );
	$red  = '#ef2014'; // Brand Red

	switch ( $icon['value'] ?? 'fas fa-shield-alt' ) {
		case 'fas fa-user-shield': // Physical Security
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 23C16.16 21.74 20 16.55 20 11V5L12 2Z" fill="white" fill-opacity="0.1"/>
				<path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 23L12 2Z" fill="white" fill-opacity="0.05"/>
				<path d="M12 7V17M7 12H17" stroke="' . $red . '" stroke-width="2" stroke-linecap="round"/>
				<path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 23C16.16 21.74 20 16.55 20 11V5L12 2Z" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
			</svg>';
		case 'fas fa-microchip': // Electronic Security
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<rect x="5" y="5" width="14" height="14" rx="2" fill="white" fill-opacity="0.1"/>
				<path d="M9 9H15V15H9V9Z" fill="' . $red . '"/>
				<path d="M2 8H5M2 12H5M2 16H5M19 8H22M19 12H22M19 16H22M8 2V5M12 2V5M16 2V5M8 19V22M12 19V22M16 19V22" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
				<circle cx="12" cy="12" r="2" fill="white" fill-opacity="0.5"/>
			</svg>';
		case 'fas fa-broadcast-tower': // Alarm & Monitoring
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<circle cx="12" cy="12" r="3" fill="' . $red . '"/>
				<path d="M12 12M12 5C8.13 5 5 8.13 5 12M19 12C19 8.13 15.87 5 12 5" stroke="white" stroke-width="1.5" stroke-linecap="round" fill="none"/>
				<path d="M12 12M12 2C6.48 2 2 6.48 2 12M22 12C22 6.48 17.52 2 12 2" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-opacity="0.4" fill="none"/>
				<path d="M12 15L12 21" stroke="' . $red . '" stroke-width="2" stroke-linecap="round"/>
				<path d="M9 21H15" stroke="' . $red . '" stroke-width="2" stroke-linecap="round"/>
			</svg>';
		case 'fas fa-user-secret': // Executive Protection
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12Z" fill="white" fill-opacity="0.8"/>
				<path d="M12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="white" fill-opacity="0.2"/>
				<path d="M12 14C10.5 14 9 14.5 9 15.5V17H15V15.5C15 14.5 13.5 14 12 14Z" fill="' . $red . '"/>
				<path d="M18 7L22 7M18 10L21 10M18 4L20 4" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
			</svg>';
		case 'fas fa-users': // Event Security
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<circle cx="8" cy="7" r="3" fill="white" fill-opacity="0.4"/>
				<circle cx="16" cy="7" r="3" fill="white" fill-opacity="0.4"/>
				<path d="M4 18C4 15.34 6.67 14 8 14C9.33 14 12 15.34 12 18V20H4V18Z" fill="white" fill-opacity="0.1"/>
				<path d="M12 18C12 15.34 14.67 14 16 14C17.33 14 20 15.34 20 18V20H12V18Z" fill="white" fill-opacity="0.1"/>
				<circle cx="12" cy="12" r="6" stroke="' . $red . '" stroke-width="1.5" fill="none" stroke-dasharray="2 2"/>
				<path d="M12 9V15M9 12H15" stroke="' . $red . '" stroke-width="2" stroke-linecap="round"/>
			</svg>';
		case 'fas fa-shopping-cart': // Loss Prevention
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 18C5.9 18 5.01 18.9 5.01 20C5.01 21.1 5.9 22 7 22C8.1 22 9 21.1 9 20C9 18.9 8.1 18 7 18ZM17 18C15.9 18 15.01 18.9 15.01 20C15.01 21.1 15.9 22 17 22C18.1 22 19 21.1 19 20C19 18.9 18.1 18 17 18ZM7.17 14.75C7.2 14.89 7.33 15 7.5 15H19V17H7C5.89 17 5 16.1 5 15C5 14.38 5.28 13.84 5.73 13.47L3.1 8H1V6H4.27L5.21 8H20C20.55 8 21 8.45 21 9C21 9.17 20.95 9.34 20.88 9.5L17.31 16.02C17.02 16.59 16.44 17 15.78 17H8.1L7.17 14.75Z" fill="white" fill-opacity="0.2"/>
				<path d="M12 3C10.34 3 9 4.34 9 6V8H15V6C15 4.34 13.66 3 12 3ZM10 8V6C10 4.9 10.9 4 12 4C13.1 4 14 4.9 14 6V8H10Z" fill="' . $red . '"/>
				<rect x="8" y="8" width="8" height="6" rx="1" fill="' . $red . '"/>
			</svg>';
		case 'fas fa-search': // Private Investigation
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<circle cx="10" cy="10" r="7" stroke="white" stroke-width="1.5" fill="white" fill-opacity="0.1"/>
				<path d="M15 15L21 21" stroke="white" stroke-width="2" stroke-linecap="round"/>
				<path d="M10 6C7.79 6 6 7.79 6 10" stroke="' . $red . '" stroke-width="1.5" stroke-linecap="round"/>
				<circle cx="10" cy="10" r="2" fill="' . $red . '"/>
				<path d="M13 10H17M10 13V17" stroke="white" stroke-width="1" stroke-opacity="0.5"/>
			</svg>';
		case 'fas fa-landmark': // Government
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 2L2 7V9H22V7L12 2Z" fill="white" fill-opacity="0.8"/>
				<path d="M4 10V18H6V10H4ZM9 10V18H11V10H9ZM14 10V18H16V10H14ZM19 10V18H21V10H19Z" fill="white" fill-opacity="0.2"/>
				<path d="M2 19V22H22V19H2Z" fill="' . $red . '"/>
				<path d="M12 11V17" stroke="' . $red . '" stroke-width="2" stroke-linecap="round"/>
			</svg>';
		case 'fas fa-video': // Camera Monitoring
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<path d="M15 8V4H1V16H15V12L22 17V3L15 8Z" fill="white" fill-opacity="0.1" stroke="white" stroke-width="1.5"/>
				<circle cx="8" cy="10" r="3" stroke="' . $red . '" stroke-width="1.5" fill="none"/>
				<circle cx="8" cy="10" r="1" fill="' . $red . '"/>
				<path d="M20 7V13" stroke="white" stroke-width="1" stroke-opacity="0.5" stroke-dasharray="2 2"/>
				<rect x="3" y="6" width="2" height="2" fill="' . $red . '"/>
			</svg>';
		case 'fas fa-shield-alt':
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Z" fill="white" fill-opacity="0.1" stroke="white" stroke-width="1.5"/>
				<path d="M12 7V17M12 7L9 10M12 7L15 10" stroke="' . $red . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>';
	}
}

function gms_find_elementor_widget_settings(array $elements, string $widget_type): array
{
	foreach ($elements as $element) {
		if ('widget' === ($element['elType'] ?? '') && $widget_type === ($element['widgetType'] ?? '')) {
			return is_array($element['settings'] ?? null) ? $element['settings'] : [];
		}

		if (!empty($element['elements']) && is_array($element['elements'])) {
			$settings = gms_find_elementor_widget_settings($element['elements'], $widget_type);

			if (!empty($settings)) {
				return $settings;
			}
		}
	}

	return [];
}

function gms_get_page_elementor_widget_settings(string $page_slug, string $widget_type): array
{
	static $cache = [];
	$cache_key = $page_slug . '::' . $widget_type;

	if (array_key_exists($cache_key, $cache)) {
		return $cache[$cache_key];
	}

	$page = get_page_by_path($page_slug);

	if (!($page instanceof WP_Post)) {
		$cache[$cache_key] = [];
		return $cache[$cache_key];
	}

	$raw_data = get_post_meta($page->ID, '_elementor_data', true);

	if (!is_string($raw_data) || '' === trim($raw_data)) {
		$cache[$cache_key] = [];
		return $cache[$cache_key];
	}

	$data = json_decode($raw_data, true);

	if (!is_array($data)) {
		$cache[$cache_key] = [];
		return $cache[$cache_key];
	}

	$cache[$cache_key] = gms_find_elementor_widget_settings($data, $widget_type);

	return $cache[$cache_key];
}

function gms_get_page_hero_slide_settings(string $page_slug): array
{
	$settings = gms_get_page_elementor_widget_settings($page_slug, 'gms-hero');
	$slides = $settings['slides'] ?? [];

	return is_array($slides[0] ?? null) ? $slides[0] : [];
}

function gms_get_page_cta_banner_settings(string $page_slug): array
{
	$settings = gms_get_page_elementor_widget_settings($page_slug, 'gms-cta-banner');

	return is_array($settings) ? $settings : [];
}
function gms_get_hero_slide_video_url(array $slide): string
{
	$video = $slide['art_video_url'] ?? '';

	if (is_array($video)) {
		return (string) ($video['url'] ?? '');
	}

	return is_string($video) ? $video : '';
}

function gms_get_video_control_markup(string $modifier = ''): string
{
	$controls_class = 'gms-approved-video-card__controls';

	if ('' !== trim($modifier)) {
		$controls_class .= ' ' . trim($modifier);
	}

	return sprintf(
		'<div class="%1$s"><button class="gms-approved-video-card__toggle gms-approved-video-card__toggle--mute" type="button" data-gms-video-mute-toggle aria-pressed="true" aria-label="%2$s"><span class="gms-approved-video-card__mute-icon" aria-hidden="true"></span><span class="gms-approved-video-card__toggle-label">%3$s</span></button><button class="gms-approved-video-card__toggle" type="button" data-gms-video-toggle aria-pressed="true" aria-label="%4$s"><span class="gms-approved-video-card__toggle-icon" aria-hidden="true"></span><span class="gms-approved-video-card__toggle-label">%5$s</span></button></div>',
		esc_attr($controls_class),
		esc_attr__('Unmute video', 'grow-my-security'),
		esc_html__('Muted', 'grow-my-security'),
		esc_attr__('Pause video', 'grow-my-security'),
		esc_html__('Pause', 'grow-my-security')
	);
}

function gms_get_industries_intro_support_html(array $hero_slide): string
{
	$image_url = (string) ($hero_slide['art_image']['url'] ?? '');
	$video_url = gms_get_hero_slide_video_url($hero_slide);
	$media_type = !empty($hero_slide['art_media_type']) ? (string) $hero_slide['art_media_type'] : ($video_url ? 'video' : 'image');

	if ('image' === $media_type && '' !== $image_url) {
		return sprintf(
			'<div class="gms-approved-video-card"><div class="gms-approved-video-card__frame"><img class="gms-approved-video-card__media" src="%s" alt=""></div></div>',
			esc_url($image_url)
		);
	}

	$video_url = '' !== $video_url ? $video_url : get_theme_file_uri('assets/images/industry-video.mp4');
	$video_type = wp_check_filetype($video_url)['type'] ?? 'video/mp4';

	return sprintf(
		'<div class="gms-approved-video-card"><div class="gms-approved-video-card__frame" data-gms-video-frame><video class="gms-approved-video-card__media" autoplay muted loop playsinline preload="metadata"><source src="%1$s" type="%2$s"></video>%3$s</div></div>',
		esc_url($video_url),
		esc_attr($video_type ?: 'video/mp4'),
		gms_get_video_control_markup()
	);
}

function gms_render_industries_banner_intro(array $hero_slide): void
{
	$eyebrow = !empty($hero_slide['label']) ? (string) $hero_slide['label'] : __('Industries', 'grow-my-security');
	$title   = !empty($hero_slide['title']) ? (string) $hero_slide['title'] : __('Security Verticals Supported', 'grow-my-security');
	$lede    = !empty($hero_slide['copy']) ? (string) $hero_slide['copy'] : __('Choose the vertical you serve and open its dedicated industry page.', 'grow-my-security');

	$background_image = trim((string) ($hero_slide['art_image']['url'] ?? ''));
	if ('' === $background_image) {
		$background_image = trim((string) ($hero_slide['image']['url'] ?? ''));
	}

	$background_image = function_exists('gms_normalize_media_url') ? (string) gms_normalize_media_url($background_image) : $background_image;
	$video_url        = function_exists('gms_normalize_media_url') ? (string) gms_normalize_media_url(gms_get_hero_slide_video_url($hero_slide)) : gms_get_hero_slide_video_url($hero_slide);
	$media_type       = !empty($hero_slide['art_media_type']) ? (string) $hero_slide['art_media_type'] : ($video_url ? 'video' : 'image');
	$has_video        = 'video' === $media_type && '' !== $video_url;
	$video_type       = wp_check_filetype($video_url)['type'] ?? 'video/mp4';
	$section_classes  = [
		'gms-approved-intro',
		'gms-approved-intro--industries',
		'gms-approved-intro--industries-banner',
	];

	if ($has_video) {
		$section_classes[] = 'gms-approved-intro--has-video';
	}
	?>
	<section class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
		<div class="gms-approved-intro__grid">
			<div class="gms-approved-intro__main">
				<?php if ('' !== trim($eyebrow)): ?>
					<div class="gms-eyebrow"><?php echo esc_html($eyebrow); ?></div>
				<?php endif; ?>
				<h1><?php echo wp_kses_post(nl2br(esc_html($title))); ?></h1>
				<?php if ('' !== trim($lede)): ?>
					<div class="gms-approved-intro__lede">
						<p><?php echo esc_html($lede); ?></p>
					</div>
				<?php endif; ?>
				<?php if ('' !== trim((string) ($hero_slide['primary_text'] ?? '')) || '' !== trim((string) ($hero_slide['secondary_text'] ?? ''))): ?>
					<div class="gms-page-hero__actions gms-page-hero__actions--banner">
						<?php gms_render_theme_button_link((array) ($hero_slide['primary_url'] ?? []), (string) ($hero_slide['primary_text'] ?? ''), 'gms-button'); ?>
						<?php gms_render_theme_button_link((array) ($hero_slide['secondary_url'] ?? []), (string) ($hero_slide['secondary_text'] ?? ''), 'gms-button-outline'); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="gms-approved-intro__banner-stage">
			<div class="gms-approved-intro__banner-media"<?php echo $has_video ? ' data-gms-video-frame' : ''; ?>>
				<?php if ($has_video): ?>
					<video class="gms-approved-intro__banner-video" autoplay muted loop playsinline preload="metadata"<?php echo '' !== $background_image ? ' poster="' . esc_url($background_image) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<source src="<?php echo esc_url($video_url); ?>" type="<?php echo esc_attr($video_type ?: 'video/mp4'); ?>">
					</video>
				<?php elseif ('' !== $background_image): ?>
					<img class="gms-approved-intro__banner-image" src="<?php echo esc_url($background_image); ?>" alt="" loading="eager" decoding="async" fetchpriority="high">
				<?php endif; ?>
				<span class="gms-approved-intro__banner-overlay" aria-hidden="true"></span>
				<span class="gms-approved-intro__banner-glow" aria-hidden="true"></span>
			</div>
			<?php if ($has_video): ?>
				<?php echo gms_get_video_control_markup('gms-approved-intro__banner-controls'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function gms_get_industries_from_elementor(): array
{
	$page = get_page_by_path('industries');

	if (!($page instanceof WP_Post)) {
		return [];
	}

	$raw = get_post_meta($page->ID, '_elementor_data', true);

	if (!is_string($raw) || '' === trim($raw)) {
		return [];
	}

	$data = json_decode($raw, true);

	if (!is_array($data)) {
		return [];
	}

	// Walk the Elementor JSON tree to find the gms-card-grid widget's cards
	$cards = [];
	$stack = $data;

	while (!empty($stack)) {
		$node = array_shift($stack);

		if (!is_array($node)) {
			continue;
		}

		if (isset($node['widgetType']) && 'gms-card-grid' === $node['widgetType'] && !empty($node['settings']['cards'])) {
			foreach ($node['settings']['cards'] as $card) {
				$title = is_array($card['title'] ?? null) ? ($card['title']['title'] ?? '') : ($card['title'] ?? '');
				$title = trim((string) $title);

				if ('' !== $title) {
					$cards[] = [
						'title'   => $title,
						'text'    => trim((string) ($card['text'] ?? '')),
					];
				}
			}

			return $cards;
		}

		if (!empty($node['elements'])) {
			foreach ($node['elements'] as $child) {
				$stack[] = $child;
			}
		}
	}

	return $cards;
}

function gms_render_industries_page_content(): void
{
	$config = gms_get_demo_config();
	$hero_slide = gms_get_page_hero_slide_settings('industries');
	$cta_settings = gms_get_page_cta_banner_settings('industries');
	$eyebrow = !empty($hero_slide['label']) ? (string) $hero_slide['label'] : __('Industries', 'grow-my-security');
	$title = !empty($hero_slide['title']) ? (string) $hero_slide['title'] : __('Security Verticals Supported', 'grow-my-security');
	$lede = !empty($hero_slide['copy']) ? (string) $hero_slide['copy'] : __('Choose the vertical you serve and open its dedicated industry page.', 'grow-my-security');
	$hero_layout = !empty($hero_slide['layout']) ? (string) $hero_slide['layout'] : 'split';
	$cta_image_url = trim((string) ($cta_settings['image']['url'] ?? ''));

	if ('banner' === $hero_layout) {
		gms_render_industries_banner_intro($hero_slide);
	} elseif ('split' === $hero_layout) {
		gms_render_internal_intro(
			[
				'eyebrow' => $eyebrow,
				'title' => $title,
				'lede' => $lede,
				'modifier' => 'industries',
				'support_html' => gms_get_industries_intro_support_html($hero_slide),
			]
		);
	} else {
		gms_render_internal_intro(
			[
				'eyebrow' => $eyebrow,
				'title' => $title,
				'lede' => $lede,
				'modifier' => 'industries',
				'support_html' => gms_get_industries_intro_support_html($hero_slide),
			]
		);
	}

	// Read card data from Elementor's saved settings when the user has edited them.
	// Falls back to the hardcoded industries list from demo-data.php.
	$elementor_cards = gms_get_industries_from_elementor();
	$use_elementor   = !empty($elementor_cards);
	?>
	<div class="gms-approved-industry-grid">
		<?php if ($use_elementor): ?>
			<?php foreach ($elementor_cards as $card): ?>
				<?php
				$clean_title  = gms_clean_industry_name((string) $card['title']);
				$industry_url = gms_get_industry_url($clean_title);
				$card_text    = trim((string) ($card['text'] ?? ''));

				if ('' === $card_text || false !== strpos($card_text, 'earn credibility')) {
					$card_text = gms_get_industry_summary($clean_title);
				}
				?>
				<article class="gms-approved-industry-card">
					<div class="gms-approved-industry-card__inner">
						<a class="gms-approved-industry-card__link" href="<?php echo esc_url($industry_url); ?>" aria-label="<?php echo esc_attr($clean_title); ?>"></a>
						<div class="gms-approved-industry-card__icon" aria-hidden="true"><?php echo gms_render_industry_card_icon($clean_title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="gms-approved-industry-card__content">
							<h3><?php echo esc_html($clean_title); ?></h3>
							<p><?php echo esc_html($card_text); ?></p>
						</div>
						<div class="gms-approved-industry-card__action">
							<a class="gms-approved-industry-card__button" href="<?php echo esc_url($industry_url); ?>"><?php esc_html_e('Learn More', 'grow-my-security'); ?><span class="gms-button__arrow" aria-hidden="true"></span></a>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		<?php else: ?>
			<?php foreach ($config['industries'] as $industry): ?>
				<?php
				$clean_title  = gms_clean_industry_name((string) $industry);
				$industry_url = gms_get_industry_url($clean_title);
				?>
				<article class="gms-approved-industry-card">
					<div class="gms-approved-industry-card__inner">
						<a class="gms-approved-industry-card__link" href="<?php echo esc_url($industry_url); ?>" aria-label="<?php echo esc_attr($clean_title); ?>"></a>
						<div class="gms-approved-industry-card__icon" aria-hidden="true"><?php echo gms_render_industry_card_icon($clean_title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="gms-approved-industry-card__content">
							<h3><?php echo esc_html($clean_title); ?></h3>
							<p><?php echo esc_html(gms_get_industry_summary($clean_title)); ?></p>
						</div>
						<div class="gms-approved-industry-card__action">
							<a class="gms-approved-industry-card__button" href="<?php echo esc_url($industry_url); ?>"><?php esc_html_e('Learn More', 'grow-my-security'); ?><span class="gms-button__arrow" aria-hidden="true"></span></a>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php
	gms_render_industries_premium_cta(
		[
			'eyebrow' => __('FAQ\'s', 'grow-my-security'),
			'title' => __('Ready to build trust that drives revenue?', 'grow-my-security'),
			'title_html' => '',
			'copy' => __('You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.', 'grow-my-security'),
			'copy_html' => '',
			'button' => __('Schedule a Free Consultation', 'grow-my-security'),
			'url' => home_url('/contact-us/'),
			'footnote' => __('82% of B2B buyers ignore vendors they do not trust. We help your people earn belief before the first conversation, because trust is what drives every deal.', 'grow-my-security'),
			'footnote_html' => '',
			'image_url' => '' !== $cta_image_url ? $cta_image_url : get_theme_file_uri('assets/images/security-dashboard-visual.png'),
		]
	);
}

function gms_get_theme_home_data_from_elementor(): array
{
	$page_id = (int) get_option('page_on_front');
	if ($page_id <= 0) {
		$page = get_page_by_path('home');
		if ($page instanceof WP_Post) {
			$page_id = $page->ID;
		}
	}
	if ($page_id <= 0) {
		return [];
	}

	$raw = get_post_meta($page_id, '_elementor_data', true);
	if (!is_string($raw) || '' === trim($raw)) {
		return [];
	}
	$data = json_decode($raw, true);
	if (!is_array($data)) {
		return [];
	}

	$home_data = [];
	$stack = $data;

	while (!empty($stack)) {
		$node = array_shift($stack);
		if (!is_array($node)) continue;

		if (isset($node['widgetType'])) {
			$widget_type = $node['widgetType'];
			$settings = $node['settings'] ?? [];

			if ('gms-hero' === $widget_type && !empty($settings['slides'])) {
				$home_data['hero_slides'] = [];
				foreach ($settings['slides'] as $slide) {
					$home_data['hero_slides'][] = [
						'label'          => $slide['label'] ?? '',
						'title'          => $slide['title'] ?? '',
						'copy'           => $slide['copy'] ?? '',
						'primary_text'   => $slide['primary_text'] ?? '',
						'primary_url'    => ltrim($slide['primary_url']['url'] ?? '', '/'),
						'secondary_text' => $slide['secondary_text'] ?? '',
						'secondary_url'  => ltrim($slide['secondary_url']['url'] ?? '', '/'),
						'image'          => $slide['image']['url'] ?? '',
					];
				}
			}

			if ('gms-story' === $widget_type && 'problem-list' === ($settings['layout'] ?? '')) {
				$intro = [];
				if (!empty($settings['description'])) $intro[] = $settings['description'];
				if (!empty($settings['supporting_text'])) $intro[] = $settings['supporting_text'];
				if (!empty($settings['copy_secondary'])) $intro[] = $settings['copy_secondary'];
				$home_data['problem_intro'] = $intro;

				$home_data['problem_items'] = [];
				if (!empty($settings['values'])) {
					foreach ($settings['values'] as $val) {
						$home_data['problem_items'][] = [
							'title' => $val['title'] ?? '',
							'copy'  => $val['description'] ?? '',
						];
					}
				}
			}

			if ('gms-story' === $widget_type && 'media-content' === ($settings['layout'] ?? '')) {
				if (!empty($settings['bullets'])) {
					$home_data['solution_items'] = array_filter(array_map('trim', explode("\n", $settings['bullets'])));
				}
				if (!empty($settings['chips'])) {
					$home_data['solution_metrics'] = array_filter(array_map('trim', explode("\n", $settings['chips'])));
				}
				$home_data['solution_media_url'] = $settings['image']['url'] ?? '';
			}

			if ('gms-process-timeline' === $widget_type && !empty($settings['items'])) {
				$home_data['guarantee_items'] = [];
				foreach ($settings['items'] as $item) {
					$home_data['guarantee_items'][] = [
						'accent' => $item['accent'] ?? '',
						'title'  => $item['title'] ?? '',
						'copy'   => $item['text'] ?? '',
						'cta'    => $item['link_text'] ?? '',
						'url'    => ltrim($item['link_url']['url'] ?? '', '/'),
						'icon'   => rtrim(str_replace('fas fa-', '', $item['icon']['value'] ?? ($item['icon'] ?? 'shield')), 's'), 
					];
				}
			}

			if ('gms-testimonials' === $widget_type && !empty($settings['items'])) {
				$home_data['testimonials'] = [];
				foreach ($settings['items'] as $item) {
					$logo = $item['logo']['url'] ?? ($item['logo'] ?? '');
					if ( function_exists( 'gms_normalize_media_url' ) ) {
						$logo = (string) gms_normalize_media_url( (string) $logo );
					}
					$home_data['testimonials'][] = [
						'quote' => $item['quote'] ?? '',
						'name'  => $item['name'] ?? '',
						'role'  => $item['role'] ?? '',
						'logo'  => $logo,
					];
				}
			}

			if ('gms-icon-grid' === $widget_type && !empty($settings['items']) && 'Who We Serve' === ($settings['eyebrow'] ?? '')) {
				$home_data['industries'] = [];
				foreach ($settings['items'] as $item) {
					$clean_title = gms_clean_industry_name($item['title'] ?? '');
					$home_data['industries'][] = [
						'title' => $item['title'] ?? '',
						'url'   => gms_get_industry_url($clean_title),
						'icon'  => rtrim(str_replace('fa-solid fa-', '', $item['icon']['value'] ?? ($item['icon'] ?? '')), 's'),
					];
				}
			}

			if ('gms-services-accordion' === $widget_type && !empty($settings['items'])) {
				$home_data['service_items'] = [];
				foreach ($settings['items'] as $item) {
					$benefits_source = (string) ($item['benefits'] ?? ($item['bullets'] ?? ($item['tags'] ?? '')));
					$benefits = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $benefits_source)));
					$image    = $item['image']['url'] ?? ($item['image'] ?? '');

					if (function_exists('gms_normalize_media_url')) {
						$image = (string) gms_normalize_media_url((string) $image);
					}

					$home_data['service_items'][] = [
						'id'       => sanitize_title($item['title'] ?? ''),
						'title'    => $item['title'] ?? '',
						'subtitle' => $item['subtitle'] ?? '',
						'summary'  => $item['summary'] ?? ($item['text'] ?? ''),
						'benefits' => $benefits,
						'image'    => $image,
					];
				}
			}

			if ('gms-faq' === $widget_type && !empty($settings['items'])) {
				$home_data['faq_items'] = [];
				foreach ($settings['items'] as $item) {
					$home_data['faq_items'][] = [
						'question' => $item['question'] ?? '',
						'answer'   => $item['answer'] ?? '',
					];
				}
			}

			if ('gms-post-grid' === $widget_type && 'journal' === ($settings['layout_style'] ?? '') && !empty($settings['items'])) {
				$home_data['journal_cards'] = [];
				foreach ($settings['items'] as $item) {
					$home_data['journal_cards'][] = [
						'category' => $item['meta'] ?? '',
						'title'    => $item['title'] ?? '',
						'image'    => $item['image']['url'] ?? '',
						'url'      => ltrim($item['url']['url'] ?? '', '/'),
					];
				}
			}

			if ('gms-contact-form' === $widget_type) {
				if (!empty($settings['services_list'])) {
					$home_data['quote_services'] = array_filter(array_map('trim', explode("\n", $settings['services_list'])));
				}
				$home_data['contact_details'] = [
					[ 'icon' => 'phone', 'label' => 'Phone', 'value' => $settings['phone'] ?? '' ],
					[ 'icon' => 'email', 'label' => 'Email', 'value' => $settings['email'] ?? '' ],
					[ 'icon' => 'office', 'label' => 'Head Office', 'value' => $settings['address'] ?? '' ],
					[ 'icon' => 'hours', 'label' => 'Working Hours', 'value' => $settings['hours'] ?? '' ],
				];
			}
		}

		if (!empty($node['elements'])) {
			foreach ($node['elements'] as $child) {
				$stack[] = $child;
			}
		}
	}

	return $home_data;
}
function gms_render_theme_controlled_page(WP_Post $post): void
{
	$context       = gms_get_public_page_context($post);
	$shell_classes = [
		'gms-page-shell',
		'gms-approved-page',
		'gms-approved-page--' . sanitize_html_class($context['type']),
	];

	// Resources page uses Elementor-driven rendering so it is editable in the editor.
	if (gms_should_use_elementor_builder_on_theme_route($post)) {
		$shell_classes[] = 'gms-approved-page--elementor-live';
		echo '<div class="' . esc_attr(implode(' ', $shell_classes)) . '">';
		gms_render_submission_notice();
		echo '<div class="gms-page-content gms-page-content--elementor gms-page-content--theme-builder">';

		$previous_post   = $GLOBALS['post'] ?? null;
		$GLOBALS['post'] = $post;
		setup_postdata($post);
		if ( ! function_exists( 'gms_output_elementor_builder_markup' ) || ! gms_output_elementor_builder_markup( $post ) ) {
			the_content();
		}
		wp_reset_postdata();

		if ($previous_post instanceof WP_Post) {
			$GLOBALS['post'] = $previous_post;
			setup_postdata($previous_post);
		}

		echo '</div></div>';
		return;
	}

	echo '<div class="' . esc_attr(implode(' ', $shell_classes)) . '">';
	gms_render_submission_notice();
	echo '<div class="gms-container gms-approved-stack">';

	switch ($context['type']) {
		case 'about':
			gms_render_about_page_content();
			break;
		case 'contact':
			gms_render_contact_page_content();
			break;
		case 'faq':
			gms_render_faq_page_content();
			break;
		case 'resources':
			gms_render_resources_page_content($post);
			break;
		case 'press':
			gms_render_press_page_content();
			break;
		case 'podcast':
			gms_render_podcast_page_content();
			break;
		case 'services':
			gms_render_services_page_content();
			break;
		case 'industries':
			gms_render_industries_page_content();
			break;
		case 'service-detail':
			if (is_array($context['service'])) {
				gms_render_service_detail_content($context['service']);
			}
			break;
	}

	echo '</div></div>';
}

function gms_handle_newsletter_form_submission(): void
{
	$redirect = wp_get_referer() ?: home_url('/resources-insights/');

	if (!isset($_POST['gms_newsletter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gms_newsletter_nonce'])), 'gms_newsletter_form')) {
		wp_safe_redirect(add_query_arg('gms_subscribe', 'error', $redirect));
		exit;
	}

	$email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
	$consent = !empty($_POST['consent']);
	$source = sanitize_text_field(wp_unslash($_POST['source'] ?? 'newsletter'));

	if (!is_email($email) || !$consent) {
		wp_safe_redirect(add_query_arg('gms_subscribe', 'error', $redirect));
		exit;
	}

	$config = gms_get_demo_config();
	$recipient = sanitize_email($config['branding']['email']) ?: get_option('admin_email');
	$subject = sprintf(__('Newsletter request from %s', 'grow-my-security'), $email);
	$body = implode(
		PHP_EOL,
		[
			'Email: ' . $email,
			'Source: ' . $source,
			'Requested at: ' . current_time('mysql'),
		]
	);
	$sent = wp_mail(
		$recipient,
		$subject,
		$body,
		[
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $email,
		]
	);

	wp_safe_redirect(add_query_arg('gms_subscribe', $sent ? 'success' : 'error', $redirect));
	exit;
}
add_action('admin_post_nopriv_gms_newsletter_form', 'gms_handle_newsletter_form_submission');
add_action('admin_post_gms_newsletter_form', 'gms_handle_newsletter_form_submission');




