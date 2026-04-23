<?php
/**
 * Elementor sync helpers for theme-controlled pages.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gms_get_service_elementor_icon( string $slug ): array {
	$map = [
		'advertising-solutions'       => 'fas fa-bullhorn',
		'ai-solutions'                => 'fas fa-brain',
		'fractional-cmo-services'     => 'fas fa-chart-line',
		'digital-marketing-solutions' => 'fas fa-chart-pie',
		'leads-generation-services'   => 'fas fa-crosshairs',
		'seo-solutions'               => 'fas fa-search',
		'aeo'                         => 'fas fa-comments',
		'geo'                         => 'fas fa-globe',
		'web-development'             => 'fas fa-code',
		'content-marketing'           => 'fas fa-file-alt',
		'marketing-strategies'        => 'fas fa-compass',
		'social-media-marketing'      => 'fas fa-share-alt',
		'public-relations'            => 'fas fa-handshake',
		'website-audit'               => 'fas fa-clipboard-list',
		'brand-authority-development' => 'fas fa-award',
		'website-hosting-maintenance' => 'fas fa-server',
		'gbp-management'              => 'fas fa-map-marker-alt',
		'sales-funnel-development'    => 'fas fa-filter',
		'crm-integration-optimization' => 'fas fa-project-diagram',
		'sales-coaching'              => 'fas fa-user-tie',
		'growth-consultation-security-company' => 'fas fa-rocket',
	];

	return [
		'value'   => $map[ $slug ] ?? 'fas fa-shield-alt',
		'library' => 'fa-solid',
	];
}

function gms_get_service_elementor_grid_cards( array $services ): array {
	$cards = [];

	foreach ( $services as $service ) {
		$slug = (string) ( $service['slug'] ?? '' );

		if ( '' === $slug ) {
			continue;
		}

		$cards[] = [
			'meta'        => function_exists( 'gms_get_service_card_tag' ) ? gms_get_service_card_tag( $slug ) : '',
			'icon_type'   => gms_get_service_elementor_icon( $slug ),
			'title'       => $service['title'] ?? '',
			'text'        => $service['description'] ?? '',
			'bullets'     => implode( "\n", (array) ( $service['bullets'] ?? [] ) ),
			'button_text' => __( 'Learn More', 'grow-my-security' ),
			'button_url'  => [ 'url' => gms_service_link( $slug, home_url( '/' ) ) ],
		];
	}

	return $cards;
}

function gms_sync_service_grid_widget_cards( array &$elements, array $services ): bool {
	$updated = false;

	foreach ( $elements as &$element ) {
		if ( ! is_array( $element ) ) {
			continue;
		}

		if ( 'widget' === ( $element['elType'] ?? '' ) && 'gms-service-grid' === ( $element['widgetType'] ?? '' ) ) {
			$settings  = is_array( $element['settings'] ?? null ) ? $element['settings'] : [];
			$data_type = (string) ( $settings['data_type'] ?? 'services' );

			if ( 'industries' !== $data_type ) {
				$cards = gms_get_service_elementor_grid_cards( $services );

				if ( ( $settings['cards'] ?? [] ) !== $cards ) {
					$settings['cards'] = $cards;
					$element['settings'] = $settings;
					$updated = true;
				}
			}
		}

		if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
			$updated = gms_sync_service_grid_widget_cards( $element['elements'], $services ) || $updated;
		}
	}

	return $updated;
}

function gms_normalize_service_detail_widget_urls( array &$elements ): bool {
	$updated = false;
	$theme_path = (string) wp_parse_url( get_template_directory_uri(), PHP_URL_PATH );
	$theme_path = rtrim( $theme_path, '/' );

	foreach ( $elements as &$element ) {
		if ( ! is_array( $element ) ) {
			continue;
		}

		if ( 'widget' === ( $element['elType'] ?? '' ) && 'gms-service-detail' === ( $element['widgetType'] ?? '' ) ) {
			$settings = is_array( $element['settings'] ?? null ) ? $element['settings'] : [];

			$hero_primary = (string) ( $settings['hero_primary_url']['url'] ?? '' );
			$cta_primary  = (string) ( $settings['cta_primary_url']['url'] ?? '' );
			$cta_secondary = (string) ( $settings['cta_secondary_url']['url'] ?? '' );
			$contact_path = gms_internal_link( '/contact-us/', home_url( '/' ) );

			if ( $hero_primary !== $contact_path ) {
				$settings['hero_primary_url']['url'] = $contact_path;
				$updated = true;
			}

			if ( $cta_primary !== $contact_path ) {
				$settings['cta_primary_url']['url'] = $contact_path;
				$updated = true;
			}

			if ( $cta_secondary !== $contact_path ) {
				$settings['cta_secondary_url']['url'] = $contact_path;
				$updated = true;
			}

			foreach ( [ 'hero_image', 'about_image' ] as $image_key ) {
				$image_url = (string) ( $settings[ $image_key ]['url'] ?? '' );

				if ( '' === $image_url ) {
					continue;
				}

				$image_path = (string) wp_parse_url( $image_url, PHP_URL_PATH );

				if ( '' !== $image_path && false !== strpos( $image_path, '/wp-content/themes/' ) && $image_url !== $image_path ) {
					$settings[ $image_key ]['url'] = $image_path;
					$updated = true;
				} elseif ( '' !== $theme_path && 0 === strpos( $image_url, '/assets/' ) ) {
					$settings[ $image_key ]['url'] = $theme_path . $image_url;
					$updated = true;
				} elseif ( '' !== $theme_path && 0 === strpos( $image_url, 'assets/' ) ) {
					$settings[ $image_key ]['url'] = $theme_path . '/' . ltrim( $image_url, '/' );
					$updated = true;
				}
			}

			$element['settings'] = $settings;
		}

		if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
			$updated = gms_normalize_service_detail_widget_urls( $element['elements'] ) || $updated;
		}
	}

	return $updated;
}

function gms_sync_service_catalog_elementor_content(): void {
	if ( function_exists( 'gms_should_skip_runtime_elementor_sync' ) && gms_should_skip_runtime_elementor_sync() ) {
		return;
	}

	$sync_version = '2026-04-17-service-catalog-v5';

	if ( get_option( 'gms_service_catalog_elementor_sync_version' ) === $sync_version ) {
		return;
	}

	$config  = gms_get_demo_config();
	$updated = false;

	$services_page = get_page_by_path( 'services' );

	if ( $services_page instanceof WP_Post ) {
		$current_json = get_post_meta( $services_page->ID, '_elementor_data', true );
		$current_json = is_string( $current_json ) ? trim( $current_json ) : '';
		$elements     = '' !== $current_json ? json_decode( $current_json, true ) : null;

		if ( is_array( $elements ) ) {
			if ( gms_sync_service_grid_widget_cards( $elements, (array) ( $config['services'] ?? [] ) ) ) {
				$new_json = wp_json_encode( $elements );

				if ( is_string( $new_json ) ) {
					update_post_meta( $services_page->ID, '_elementor_data', wp_slash( $new_json ) );

					if ( 'builder' !== get_post_meta( $services_page->ID, '_elementor_edit_mode', true ) ) {
						update_post_meta( $services_page->ID, '_elementor_edit_mode', 'builder' );
					}

					clean_post_cache( $services_page->ID );
					$updated = true;
				}
			}
		} elseif ( '' === $current_json ) {
			$templates = gms_get_public_page_elementor_templates();

			if ( isset( $templates['services'] ) ) {
				$updated = gms_sync_elementor_template_to_page( $services_page, $templates['services'] ) || $updated;
			}
		}
	}

	foreach ( (array) ( $config['services'] ?? [] ) as $service ) {
		$slug = (string) ( $service['slug'] ?? '' );

		if ( '' === $slug ) {
			continue;
		}

		$page = get_page_by_path( 'services/' . $slug, OBJECT, 'page' );

		if ( ! ( $page instanceof WP_Post ) ) {
			continue;
		}

		$current_json = get_post_meta( $page->ID, '_elementor_data', true );
		$current_json = is_string( $current_json ) ? trim( $current_json ) : '';

		if ( '' !== $current_json ) {
			$elements = json_decode( $current_json, true );

			if ( is_array( $elements ) && gms_normalize_service_detail_widget_urls( $elements ) ) {
				$new_json = wp_json_encode( $elements );

				if ( is_string( $new_json ) ) {
					update_post_meta( $page->ID, '_elementor_data', wp_slash( $new_json ) );

					if ( 'builder' !== get_post_meta( $page->ID, '_elementor_edit_mode', true ) ) {
						update_post_meta( $page->ID, '_elementor_edit_mode', 'builder' );
					}

					clean_post_cache( $page->ID );
					$updated = true;
				}
			}

			continue;
		}

		$template = gms_get_service_template( $service, $config, get_template_directory_uri(), home_url( '/' ) );
		$updated  = gms_sync_elementor_template_to_page( $page, $template ) || $updated;
	}

	if ( $updated && class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'gms_service_catalog_elementor_sync_version', $sync_version, false );
}
add_action( 'init', 'gms_sync_service_catalog_elementor_content', 26 );

function gms_get_resource_blog_posts_for_sync( int $limit = -1 ): array {
	$args = [
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit > 0 ? $limit : -1,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'category__not_in'    => array_values(
			array_filter(
				[
					get_cat_ID( 'Press' ),
					get_cat_ID( 'Podcast' ),
					get_cat_ID( 'Uncategorized' ),
				]
			)
		),
	];

	return get_posts( $args );
}

function gms_get_press_media_grid_items( array $items ): array {
	$output = [];

	foreach ( $items as $item ) {
		$slug = trim( (string) ( $item['slug'] ?? '' ) );

		if ( '' === $slug ) {
			continue;
		}

		$output[] = [
			'meta'         => (string) ( $item['meta'] ?? __( 'Press Feature', 'grow-my-security' ) ),
			'title'        => (string) ( $item['title'] ?? '' ),
			'excerpt'      => (string) ( $item['excerpt'] ?? '' ),
			'image'        => [ 'url' => gms_get_elementor_safe_media_url( (string) ( $item['image'] ?? '' ) ) ],
			'url'          => [ 'url' => gms_get_elementor_safe_internal_url( home_url( '/' . $slug . '/' ) ) ],
			'metric_value' => '',
			'metric_label' => '',
		];
	}

	return $output;
}

function gms_get_elementor_safe_internal_url( string $url ): string {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );

	return '' !== $path ? $path : $url;
}

function gms_get_elementor_safe_media_url( string $url ): string {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );

	if ( '' !== $path && ( 0 === strpos( $path, '/wp-content/' ) || 0 === strpos( $path, '/assets/' ) ) ) {
		return $path;
	}

	return $url;
}

function gms_get_resource_post_grid_items_from_posts( array $posts ): array {
	$items = [];

	foreach ( $posts as $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			continue;
		}

		$image_url = function_exists( 'gms_get_post_card_image_url' ) ? gms_get_post_card_image_url( $post ) : '';
		$image_url = gms_get_elementor_safe_media_url( (string) $image_url );
		$excerpt   = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 24, '...' );

		$permalink = get_permalink( $post );
		$url       = is_string( $permalink ) ? gms_get_elementor_safe_internal_url( $permalink ) : home_url( '/' );

		$items[] = [
			'post_id'      => (string) $post->ID,
			'meta'         => function_exists( 'gms_get_post_card_meta_label' ) ? gms_get_post_card_meta_label( $post ) : __( 'Blog', 'grow-my-security' ),
			'title'        => get_the_title( $post ),
			'excerpt'      => wp_strip_all_tags( (string) $excerpt ),
			'image'        => [ 'url' => (string) $image_url ],
			'url'          => [ 'url' => $url ],
			'metric_value' => '',
			'metric_label' => '',
		];
	}

	return $items;
}

function gms_get_resource_post_feature_media_url( WP_Post $post ): string {
	$override = function_exists( 'gms_get_theme_controlled_post_image_url' ) ? (string) gms_get_theme_controlled_post_image_url( $post ) : '';

	if ( '' !== $override ) {
		return gms_get_elementor_safe_media_url( $override );
	}

	$image_url = function_exists( 'gms_get_post_card_image_url' ) ? (string) gms_get_post_card_image_url( $post ) : '';

	return gms_get_elementor_safe_media_url( $image_url );
}

function gms_get_resource_post_paragraphs( WP_Post $post, int $max = 4 ): array {
	$raw = trim( (string) $post->post_content );

	if ( '' === $raw && has_excerpt( $post ) ) {
		$raw = trim( (string) get_the_excerpt( $post ) );
	}

	$parts = preg_split( '/\r\n\r\n|\n\n|\r\r/', wp_strip_all_tags( $raw ) );
	$parts = array_values(
		array_filter(
			array_map(
				static function ( $part ): string {
					return trim( preg_replace( '/\s+/', ' ', (string) $part ) );
				},
				(array) $parts
			)
		)
	);

	if ( empty( $parts ) ) {
		$parts[] = wp_strip_all_tags( (string) get_the_excerpt( $post ) );
	}

	return array_slice( $parts, 0, $max );
}

function gms_get_resource_blog_blueprint( WP_Post $post ): array {
	$category   = function_exists( 'gms_get_post_card_meta_label' ) ? gms_get_post_card_meta_label( $post ) : __( 'Insight', 'grow-my-security' );
	$excerpt    = wp_strip_all_tags( (string) ( get_the_excerpt( $post ) ?: __( 'A trust-building perspective for security brands moving from technical capability to visible authority.', 'grow-my-security' ) ) );
	$paragraphs = gms_get_resource_post_paragraphs( $post, 4 );
	$intro      = $paragraphs[0] ?? $excerpt;
	$detail     = $paragraphs[1] ?? $excerpt;
	$conclusion = $paragraphs[2] ?? __( 'When the message is clear and the proof is visible, buyers move with less hesitation and more confidence.', 'grow-my-security' );

	$defaults = [
		'hero_eyebrow'          => $category,
		'hero_description'      => $excerpt,
		'snapshot_title'        => get_the_title( $post ),
		'snapshot_description'  => $intro,
		'snapshot_supporting'   => $detail,
		'snapshot_highlight'    => __( 'High-trust buyers do not need louder marketing. They need clearer proof, cleaner structure, and a lower-friction next step.', 'grow-my-security' ),
		'snapshot_values'       => [
			[
				'title' => __( 'Clarity Wins', 'grow-my-security' ),
				'text'  => __( 'The right structure removes uncertainty before a buyer ever reaches out.', 'grow-my-security' ),
			],
			[
				'title' => __( 'Proof Converts', 'grow-my-security' ),
				'text'  => __( 'Strong evidence, sharp framing, and trust cues improve decision confidence.', 'grow-my-security' ),
			],
			[
				'title' => __( 'Momentum Matters', 'grow-my-security' ),
				'text'  => __( 'Every section should move the reader toward the next meaningful action.', 'grow-my-security' ),
			],
		],
		'article_heading'       => __( 'What Security Teams Should Take From This', 'grow-my-security' ),
		'article_sections'      => [
			[
				'heading' => __( 'Why this matters now', 'grow-my-security' ),
				'body'    => $intro,
			],
			[
				'heading' => __( 'Where most teams lose traction', 'grow-my-security' ),
				'body'    => $detail,
			],
			[
				'heading' => __( 'How to turn the insight into action', 'grow-my-security' ),
				'body'    => $conclusion,
			],
		],
		'process_title'         => __( 'How to Apply This Insight', 'grow-my-security' ),
		'process_description'   => __( 'A simple trust-first workflow turns strategy ideas into a repeatable growth system.', 'grow-my-security' ),
		'process_items'         => [
			[
				'accent' => __( 'Audit ', 'grow-my-security' ),
				'title'  => __( 'the first impression', 'grow-my-security' ),
				'text'   => __( 'Check the first screen, proof signals, and message hierarchy against what cautious buyers need to see first.', 'grow-my-security' ),
				'icon'   => 'shield',
			],
			[
				'accent' => __( 'Tighten ', 'grow-my-security' ),
				'title'  => __( 'the conversion path', 'grow-my-security' ),
				'text'   => __( 'Reduce ambiguity, remove dead space, and make the next step obvious for serious prospects.', 'grow-my-security' ),
				'icon'   => 'target',
			],
			[
				'accent' => __( 'Measure ', 'grow-my-security' ),
				'title'  => __( 'buyer response', 'grow-my-security' ),
				'text'   => __( 'Track qualified conversions, engagement depth, and proof consumption to refine the page over time.', 'grow-my-security' ),
				'icon'   => 'link',
			],
		],
		'cta_title'             => __( 'Need Help Turning Insight Into Pipeline?', 'grow-my-security' ),
		'cta_description'       => __( 'We help security brands turn messaging, proof, and UX into a system that earns trust before the sales call.', 'grow-my-security' ),
	];

	$map = [
		'why-ux-ui-design-can-make-or-break-your-website' => [
			'hero_eyebrow'         => __( 'Website Experience', 'grow-my-security' ),
			'hero_description'     => __( 'Security buyers read design as a trust signal. Better UX and UI remove doubt, support proof, and improve conversion before the first conversation.', 'grow-my-security' ),
			'snapshot_title'       => __( 'Why UX and UI quietly decide whether credibility sticks', 'grow-my-security' ),
			'snapshot_description' => __( 'When the structure is confusing or the proof is buried, technical expertise never gets the chance to land. Strong UX is what lets authority be felt quickly.', 'grow-my-security' ),
			'snapshot_supporting'  => __( 'Security websites need more than polish. They need deliberate information hierarchy, fast trust cues, and a path that respects cautious decision-makers.', 'grow-my-security' ),
			'snapshot_highlight'   => __( 'Credibility is not a design trend. It is the system that helps a serious buyer feel safe enough to keep reading.', 'grow-my-security' ),
			'snapshot_values'      => [
				[
					'title' => __( 'Trust Signals', 'grow-my-security' ),
					'text'  => __( 'Buyers decide quickly whether the site feels credible, current, and capable.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Proof Architecture', 'grow-my-security' ),
					'text'  => __( 'Case studies, authority markers, and outcomes must be visible before interest drops.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Friction Removal', 'grow-my-security' ),
					'text'  => __( 'Navigation, layout, and CTA placement should reduce hesitation instead of creating it.', 'grow-my-security' ),
				],
			],
			'article_sections'     => [
				[
					'heading' => __( 'Security buyers judge the experience before the offer', 'grow-my-security' ),
					'body'    => __( 'If the page feels scattered, dated, or unclear, prospects assume the same about the business behind it. UX and UI are often the first proof points a buyer receives.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'Authority needs structure, not just aesthetics', 'grow-my-security' ),
					'body'    => __( 'A well-designed site helps buyers find what matters: who you serve, what outcomes you create, and why your process is credible. Good design is what lets proof work harder.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'The strongest pages make the next step feel natural', 'grow-my-security' ),
					'body'    => __( 'Clear paths, strategic calls to action, and visible reassurance reduce friction. That is what turns interest into inquiry for complex security services.', 'grow-my-security' ),
				],
			],
			'process_items'        => [
				[
					'accent' => __( 'Clarify ', 'grow-my-security' ),
					'title'  => __( 'the first screen', 'grow-my-security' ),
					'text'   => __( 'Lead with the core promise, target buyer, and strongest proof instead of generic headlines.', 'grow-my-security' ),
					'icon'   => 'shield',
				],
				[
					'accent' => __( 'Align ', 'grow-my-security' ),
					'title'  => __( 'layout with buyer questions', 'grow-my-security' ),
					'text'   => __( 'Structure every section around trust, capability, differentiation, and the next step.', 'grow-my-security' ),
					'icon'   => 'users',
				],
				[
					'accent' => __( 'Tighten ', 'grow-my-security' ),
					'title'  => __( 'the decision path', 'grow-my-security' ),
					'text'   => __( 'Use proof and CTA placement to guide serious buyers without forcing them to hunt for reassurance.', 'grow-my-security' ),
					'icon'   => 'target',
				],
			],
		],
		'five-must-have-features-for-a-modern-business-website' => [
			'hero_eyebrow'         => __( 'Website Essentials', 'grow-my-security' ),
			'hero_description'     => __( 'Modern websites for security companies need more than a clean look. They need the right conversion, proof, and trust systems built into the experience.', 'grow-my-security' ),
			'snapshot_title'       => __( 'The site features that turn visibility into qualified conversations', 'grow-my-security' ),
			'snapshot_description' => __( 'A modern business website should make your offer easier to understand, your proof easier to trust, and your next step easier to take.', 'grow-my-security' ),
			'snapshot_supporting'  => __( 'For security brands, the difference is usually not traffic alone. It is whether the site helps technical buyers move from curiosity to confidence.', 'grow-my-security' ),
			'snapshot_highlight'   => __( 'The best feature set is the one that makes trust visible and action easy.', 'grow-my-security' ),
			'snapshot_values'      => [
				[
					'title' => __( 'Clear Positioning', 'grow-my-security' ),
					'text'  => __( 'Your site should immediately explain who you help, what you solve, and why it matters.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Visible Proof', 'grow-my-security' ),
					'text'  => __( 'Case studies, testimonials, and capability markers need to appear before skepticism grows.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Conversion Paths', 'grow-my-security' ),
					'text'  => __( 'Serious prospects need low-friction ways to move from reading to reaching out.', 'grow-my-security' ),
				],
			],
			'article_sections'     => [
				[
					'heading' => __( 'Feature one: message clarity', 'grow-my-security' ),
					'body'    => __( 'If the headline is vague or the service framing is generic, the site loses momentum immediately. Modern sites earn attention by being specific and useful fast.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'Feature two: proof that supports the promise', 'grow-my-security' ),
					'body'    => __( 'Outcomes, social proof, and recognizable trust markers should support every major claim. Buyers in security markets want validation before they want a demo.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'Feature three through five: speed, responsiveness, and action', 'grow-my-security' ),
					'body'    => __( 'Fast load times, strong mobile behavior, and clear CTAs make the rest of the site usable. Those fundamentals are what keep opportunity from leaking out of the funnel.', 'grow-my-security' ),
				],
			],
			'process_items'        => [
				[
					'accent' => __( 'Define ', 'grow-my-security' ),
					'title'  => __( 'the must-have sections', 'grow-my-security' ),
					'text'   => __( 'Map the exact information, proof, and conversion points a high-intent buyer needs.', 'grow-my-security' ),
					'icon'   => 'shield',
				],
				[
					'accent' => __( 'Prioritize ', 'grow-my-security' ),
					'title'  => __( 'speed and usability', 'grow-my-security' ),
					'text'   => __( 'Make sure the site performs cleanly on mobile, loads quickly, and keeps the decision path obvious.', 'grow-my-security' ),
					'icon'   => 'target',
				],
				[
					'accent' => __( 'Measure ', 'grow-my-security' ),
					'title'  => __( 'what the page is doing', 'grow-my-security' ),
					'text'   => __( 'Use conversion data and behavior analytics to see which features are supporting pipeline and which need refinement.', 'grow-my-security' ),
					'icon'   => 'link',
				],
			],
		],
		'ai-in-content-creation-friend-or-foe-in-2026' => [
			'hero_eyebrow'         => __( 'AI Content Strategy', 'grow-my-security' ),
			'hero_description'     => __( 'AI can accelerate content production, but it becomes dangerous when it replaces expertise, nuance, and the claims discipline that security brands require.', 'grow-my-security' ),
			'snapshot_title'       => __( 'How to use AI in content without losing authority', 'grow-my-security' ),
			'snapshot_description' => __( 'The winning model is not all-human or all-AI. It is a supervised workflow where experts keep control of accuracy, voice, and strategic direction.', 'grow-my-security' ),
			'snapshot_supporting'  => __( 'Security audiences are quick to spot generic language. If the content feels thin, repetitive, or over-polished, trust drops before the brand gets credit.', 'grow-my-security' ),
			'snapshot_highlight'   => __( 'AI should increase speed and structure. Human expertise should still own nuance, claims, and strategic judgment.', 'grow-my-security' ),
			'snapshot_values'      => [
				[
					'title' => __( 'Research Speed', 'grow-my-security' ),
					'text'  => __( 'AI can accelerate outlining, synthesis, and first-pass drafting when the brief is strong.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Human Control', 'grow-my-security' ),
					'text'  => __( 'Experts need to own final messaging, proof standards, and factual precision.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Brand Accuracy', 'grow-my-security' ),
					'text'  => __( 'Authority grows when content sounds like your team, not like a generic system prompt.', 'grow-my-security' ),
				],
			],
			'article_sections'     => [
				[
					'heading' => __( 'AI helps most when the strategy is already clear', 'grow-my-security' ),
					'body'    => __( 'If the positioning, audience, and editorial standards are undefined, AI will only produce faster confusion. The tool is useful once the direction is already strong.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'Unsupervised output creates trust risk', 'grow-my-security' ),
					'body'    => __( 'Security buyers notice when the content is repetitive, thin, or overly broad. That is why expert review and claim verification are non-negotiable.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'The best workflow combines speed with editorial discipline', 'grow-my-security' ),
					'body'    => __( 'Use AI for structure and iteration, then let real subject-matter expertise shape the final argument, examples, and proof. That is what preserves authority while increasing output.', 'grow-my-security' ),
				],
			],
			'process_items'        => [
				[
					'accent' => __( 'Brief ', 'grow-my-security' ),
					'title'  => __( 'the system correctly', 'grow-my-security' ),
					'text'   => __( 'Start with audience, claim boundaries, proof sources, and the exact outcome the content needs to drive.', 'grow-my-security' ),
					'icon'   => 'shield',
				],
				[
					'accent' => __( 'Draft ', 'grow-my-security' ),
					'title'  => __( 'with controlled prompts', 'grow-my-security' ),
					'text'   => __( 'Use AI to produce structure and options, not final authority statements without review.', 'grow-my-security' ),
					'icon'   => 'users',
				],
				[
					'accent' => __( 'Review ', 'grow-my-security' ),
					'title'  => __( 'for nuance and proof', 'grow-my-security' ),
					'text'   => __( 'Let an expert finalize the argument, tighten the language, and remove any claims that cannot be defended.', 'grow-my-security' ),
					'icon'   => 'target',
				],
			],
		],
	];

	return array_replace_recursive( $defaults, $map[ $post->post_name ] ?? [] );
}

function gms_get_resource_blog_article_html( WP_Post $post, array $blueprint ): string {
	$category  = function_exists( 'gms_get_post_card_meta_label' ) ? gms_get_post_card_meta_label( $post ) : __( 'Insight', 'grow-my-security' );
	$read_time = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post ) ) ) / 220 ) );
	$sections  = (array) ( $blueprint['article_sections'] ?? [] );

	ob_start();
	?>
	<div class="gms-editorial-post-article">
		<div class="gms-editorial-post-article__meta">
			<span><?php echo esc_html( $category ); ?></span>
			<span><?php echo esc_html( get_the_date( 'M j, Y', $post ) ); ?></span>
			<span><?php echo esc_html( sprintf( _n( '%d min read', '%d min read', $read_time, 'grow-my-security' ), $read_time ) ); ?></span>
		</div>
		<h2><?php echo esc_html( (string) ( $blueprint['article_heading'] ?? __( 'Article', 'grow-my-security' ) ) ); ?></h2>
		<?php foreach ( $sections as $section ) : ?>
			<?php $heading = trim( (string) ( $section['heading'] ?? '' ) ); ?>
			<?php $body = trim( (string) ( $section['body'] ?? '' ) ); ?>
			<?php if ( '' === $heading && '' === $body ) { continue; } ?>
			<section class="gms-editorial-post-article__section">
				<?php if ( '' !== $heading ) : ?>
					<h3><?php echo esc_html( $heading ); ?></h3>
				<?php endif; ?>
				<?php if ( '' !== $body ) : ?>
					<?php echo wp_kses_post( wpautop( esc_html( $body ) ) ); ?>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
	<?php

	return trim( (string) ob_get_clean() );
}

function gms_get_resource_blog_sidebar_html( WP_Post $post, array $blueprint ): string {
	$category  = function_exists( 'gms_get_post_card_meta_label' ) ? gms_get_post_card_meta_label( $post ) : __( 'Insight', 'grow-my-security' );
	$read_time = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post ) ) ) / 220 ) );
	$bullets   = array_map(
		static function ( array $item ): string {
			return (string) ( $item['title'] ?? '' );
		},
		array_slice( (array) ( $blueprint['snapshot_values'] ?? [] ), 0, 3 )
	);

	ob_start();
	?>
	<div class="gms-editorial-post-sidebar">
		<div class="gms-editorial-post-sidebar__panel">
			<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'Category', 'grow-my-security' ); ?></span>
			<strong><?php echo esc_html( $category ); ?></strong>
		</div>
		<div class="gms-editorial-post-sidebar__panel">
			<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'Published', 'grow-my-security' ); ?></span>
			<strong><?php echo esc_html( get_the_date( 'F j, Y', $post ) ); ?></strong>
		</div>
		<div class="gms-editorial-post-sidebar__panel">
			<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'Reading Time', 'grow-my-security' ); ?></span>
			<strong><?php echo esc_html( sprintf( _n( '%d minute', '%d minutes', $read_time, 'grow-my-security' ), $read_time ) ); ?></strong>
		</div>
		<?php if ( ! empty( $bullets ) ) : ?>
			<div class="gms-editorial-post-sidebar__panel gms-editorial-post-sidebar__panel--list">
				<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'Key Themes', 'grow-my-security' ); ?></span>
				<ul>
					<?php foreach ( $bullets as $bullet ) : ?>
						<?php if ( '' === trim( $bullet ) ) { continue; } ?>
						<li><?php echo esc_html( $bullet ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<div class="gms-editorial-post-sidebar__callout">
			<h3><?php esc_html_e( 'Need help applying this insight?', 'grow-my-security' ); ?></h3>
			<p><?php echo esc_html( (string) ( $blueprint['cta_description'] ?? __( 'We help security brands turn strategy into visible authority and qualified demand.', 'grow-my-security' ) ) ); ?></p>
			<div class="gms-editorial-post-sidebar__actions">
				<a class="gms-button" href="<?php echo esc_url( gms_internal_link( '/contact-us/', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Book a Strategy Call', 'grow-my-security' ); ?></a>
				<a class="gms-button-outline" href="<?php echo esc_url( gms_internal_link( '/resources-insights/', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Back to Resources', 'grow-my-security' ); ?></a>
			</div>
		</div>
	</div>
	<?php

	return trim( (string) ob_get_clean() );
}

function gms_get_resource_blog_related_items( int $post_id, int $limit = 3 ): array {
	$resource_posts = array_values(
		array_filter(
			gms_get_resource_blog_posts_for_sync(),
			static function ( $post ) use ( $post_id ): bool {
				return $post instanceof WP_Post && (int) $post->ID !== $post_id;
			}
		)
	);

	return gms_get_resource_post_grid_items_from_posts( array_slice( $resource_posts, 0, $limit ) );
}

function gms_get_press_media_posts_for_sync(): array {
	$config = gms_get_demo_config();
	$posts  = [];

	foreach ( (array) ( $config['press_items'] ?? [] ) as $item ) {
		$slug = trim( (string) ( $item['slug'] ?? '' ) );

		if ( '' === $slug ) {
			continue;
		}

		$post = get_page_by_path( $slug, OBJECT, 'post' );

		if ( $post instanceof WP_Post ) {
			$posts[] = $post;
		}
	}

	return $posts;
}

function gms_get_press_post_blueprint( WP_Post $post ): array {
	$excerpt    = wp_strip_all_tags( (string) ( get_the_excerpt( $post ) ?: __( 'A visibility-focused press feature for security companies building trust, authority, and better demand quality.', 'grow-my-security' ) ) );
	$defaults   = [
		'hero_eyebrow'         => __( 'Press Coverage', 'grow-my-security' ),
		'hero_description'     => $excerpt,
		'snapshot_title'       => get_the_title( $post ),
		'snapshot_description' => __( 'Strong media visibility is not about vanity. It gives security buyers another proof point that the company understands the market, speaks clearly, and belongs in serious conversations.', 'grow-my-security' ),
		'snapshot_supporting'  => __( 'The best coverage reinforces positioning, creates familiarity, and makes the next sales conversation easier because the brand already feels more credible.', 'grow-my-security' ),
		'snapshot_highlight'   => __( 'Third-party visibility works best when the message is clear enough to support trust, not just attention.', 'grow-my-security' ),
		'snapshot_values'      => [
			[
				'title' => __( 'Authority Signal', 'grow-my-security' ),
				'text'  => __( 'Coverage gives buyers independent evidence that the brand is active, relevant, and credible.', 'grow-my-security' ),
			],
			[
				'title' => __( 'Positioning Reinforcement', 'grow-my-security' ),
				'text'  => __( 'The right feature sharpens how the market understands the company and what it stands for.', 'grow-my-security' ),
			],
			[
				'title' => __( 'Commercial Trust', 'grow-my-security' ),
				'text'  => __( 'Clear proof and familiar visibility lower hesitation before the first serious inquiry.', 'grow-my-security' ),
			],
		],
		'article_heading'      => __( 'What This Coverage Reinforces', 'grow-my-security' ),
		'article_sections'     => [
			[
				'heading' => __( 'Why this feature matters', 'grow-my-security' ),
				'body'    => __( 'Security buyers tend to evaluate credibility before they evaluate detail. Press visibility helps them see the business as established, current, and worth a closer look.', 'grow-my-security' ),
			],
			[
				'heading' => __( 'How media coverage supports demand quality', 'grow-my-security' ),
				'body'    => __( 'When a company appears in the right conversations, it creates familiarity and reduces doubt. That usually improves the quality of inbound conversations more than volume alone.', 'grow-my-security' ),
			],
			[
				'heading' => __( 'What to do after the coverage goes live', 'grow-my-security' ),
				'body'    => __( 'The strongest teams reuse the feature across sales pages, outreach, and trust sections on the site so the visibility compounds instead of disappearing after launch.', 'grow-my-security' ),
			],
		],
		'process_title'        => __( 'How to Turn Coverage Into Pipeline', 'grow-my-security' ),
		'process_description'  => __( 'A good feature becomes more valuable when it is packaged into the trust system around the site, the sales process, and the brand story.', 'grow-my-security' ),
		'process_items'        => [
			[
				'accent' => __( 'Clarify ', 'grow-my-security' ),
				'title'  => __( 'the commercial angle', 'grow-my-security' ),
				'text'   => __( 'Make sure the audience understands what the coverage says about the business, not just where it appeared.', 'grow-my-security' ),
				'icon'   => 'shield',
			],
			[
				'accent' => __( 'Distribute ', 'grow-my-security' ),
				'title'  => __( 'the proof everywhere', 'grow-my-security' ),
				'text'   => __( 'Feature the coverage on the website, in proposals, and in outreach so trust compounds across the buyer journey.', 'grow-my-security' ),
				'icon'   => 'users',
			],
			[
				'accent' => __( 'Connect ', 'grow-my-security' ),
				'title'  => __( 'the visibility to action', 'grow-my-security' ),
				'text'   => __( 'Pair the credibility boost with a clear next step so interested prospects know exactly how to engage.', 'grow-my-security' ),
				'icon'   => 'target',
			],
		],
		'cta_title'            => __( 'Want More Visibility That Actually Supports Sales?', 'grow-my-security' ),
		'cta_description'      => __( 'We help security companies turn positioning, authority, and media visibility into stronger demand quality and more confident buyers.', 'grow-my-security' ),
	];
	$map = [
		'coaching-for-the-security-industry' => [
			'hero_description'     => __( 'A closer look at how coaching-led guidance helps security companies sharpen positioning, lead with authority, and communicate growth more clearly.', 'grow-my-security' ),
			'snapshot_title'       => __( 'Why coaching changes the way a security company is perceived', 'grow-my-security' ),
			'snapshot_description' => __( 'Strong companies often have the expertise already. What they need is a clearer way to package it so buyers, partners, and the market can understand the value faster.', 'grow-my-security' ),
			'snapshot_supporting'  => __( 'Coaching gives leadership teams a sharper commercial story, better message discipline, and a more credible way to show why the business deserves attention.', 'grow-my-security' ),
			'snapshot_highlight'   => __( 'The coaching angle matters because buyers trust companies that sound focused, consistent, and commercially clear.', 'grow-my-security' ),
			'snapshot_values'      => [
				[
					'title' => __( 'Message Clarity', 'grow-my-security' ),
					'text'  => __( 'Coaching helps leadership translate expertise into language the market can understand quickly.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Leadership Positioning', 'grow-my-security' ),
					'text'  => __( 'A clearer executive narrative makes the company feel more focused and more credible.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Trust Acceleration', 'grow-my-security' ),
					'text'  => __( 'When the story is coherent, buyers move faster because they understand what makes the business reliable.', 'grow-my-security' ),
				],
			],
			'article_sections'     => [
				[
					'heading' => __( 'Coaching creates strategic clarity', 'grow-my-security' ),
					'body'    => __( 'Many security companies are operationally strong but commercially under-positioned. Coaching helps leadership connect expertise, delivery, and growth into a message buyers can trust.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'The market responds to confidence and consistency', 'grow-my-security' ),
					'body'    => __( 'A stronger leadership narrative improves how the company is introduced, how it is remembered, and how seriously it is taken across sales and partnerships.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'That clarity should shape the full brand system', 'grow-my-security' ),
					'body'    => __( 'Once the positioning is sharper, it should show up across the website, sales material, proposals, and every trust signal the brand puts into the market.', 'grow-my-security' ),
				],
			],
		],
		'how-to-scale-security-marketing' => [
			'hero_description'     => __( 'A practical look at scaling security marketing without losing trust, precision, or the commercial discipline serious buyers expect.', 'grow-my-security' ),
			'snapshot_title'       => __( 'How to scale visibility without looking generic', 'grow-my-security' ),
			'snapshot_description' => __( 'Growth in security markets usually breaks when messaging becomes broad, proof gets diluted, or the brand starts sounding like everyone else. Scale has to preserve credibility.', 'grow-my-security' ),
			'snapshot_supporting'  => __( 'The right marketing system expands reach while keeping positioning tight, proof visible, and the buyer path structured around trust.', 'grow-my-security' ),
			'snapshot_highlight'   => __( 'Scale works when the company gets more visible without becoming less credible.', 'grow-my-security' ),
			'snapshot_values'      => [
				[
					'title' => __( 'Focused Expansion', 'grow-my-security' ),
					'text'  => __( 'Growth should increase reach without making the message vague or diluted.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Proof at Scale', 'grow-my-security' ),
					'text'  => __( 'Authority markers, case evidence, and trust signals need to scale with the campaigns.', 'grow-my-security' ),
				],
				[
					'title' => __( 'Buyer Readiness', 'grow-my-security' ),
					'text'  => __( 'The goal is better conversations, not just more traffic or more impressions.', 'grow-my-security' ),
				],
			],
			'article_sections'     => [
				[
					'heading' => __( 'Scaling too early creates noise', 'grow-my-security' ),
					'body'    => __( 'When teams push distribution before they have strong positioning, the result is usually more activity but weaker trust. Visibility only helps if the message is already doing its job.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'The strongest systems scale proof with the campaigns', 'grow-my-security' ),
					'body'    => __( 'As reach increases, the site, offers, and trust sections need to reinforce the same story. Otherwise new attention arrives without enough confidence to convert.', 'grow-my-security' ),
				],
				[
					'heading' => __( 'Sustainable scale comes from repeatable structure', 'grow-my-security' ),
					'body'    => __( 'A strong growth system connects targeting, message clarity, proof, and conversion design so the company can expand without compromising quality.', 'grow-my-security' ),
				],
			],
		],
	];

	return array_replace_recursive( $defaults, $map[ $post->post_name ] ?? [] );
}

function gms_get_press_post_sidebar_html( WP_Post $post, array $blueprint ): string {
	$read_time = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post ) ) ) / 220 ) );
	$bullets   = array_map(
		static function ( array $item ): string {
			return (string) ( $item['title'] ?? '' );
		},
		array_slice( (array) ( $blueprint['snapshot_values'] ?? [] ), 0, 3 )
	);

	ob_start();
	?>
	<div class="gms-editorial-post-sidebar">
		<div class="gms-editorial-post-sidebar__panel">
			<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'Coverage Type', 'grow-my-security' ); ?></span>
			<strong><?php esc_html_e( 'Press Feature', 'grow-my-security' ); ?></strong>
		</div>
		<div class="gms-editorial-post-sidebar__panel">
			<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'Published', 'grow-my-security' ); ?></span>
			<strong><?php echo esc_html( get_the_date( 'F j, Y', $post ) ); ?></strong>
		</div>
		<div class="gms-editorial-post-sidebar__panel">
			<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'Reading Time', 'grow-my-security' ); ?></span>
			<strong><?php echo esc_html( sprintf( _n( '%d minute', '%d minutes', $read_time, 'grow-my-security' ), $read_time ) ); ?></strong>
		</div>
		<?php if ( ! empty( $bullets ) ) : ?>
			<div class="gms-editorial-post-sidebar__panel gms-editorial-post-sidebar__panel--list">
				<span class="gms-editorial-post-sidebar__label"><?php esc_html_e( 'What This Supports', 'grow-my-security' ); ?></span>
				<ul>
					<?php foreach ( $bullets as $bullet ) : ?>
						<?php if ( '' === trim( $bullet ) ) { continue; } ?>
						<li><?php echo esc_html( $bullet ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<div class="gms-editorial-post-sidebar__callout">
			<h3><?php esc_html_e( 'Need more visibility like this?', 'grow-my-security' ); ?></h3>
			<p><?php echo esc_html( (string) ( $blueprint['cta_description'] ?? __( 'We help security companies turn positioning and visibility into stronger demand quality.', 'grow-my-security' ) ) ); ?></p>
			<div class="gms-editorial-post-sidebar__actions">
				<a class="gms-button" href="<?php echo esc_url( gms_internal_link( '/contact-us/', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Book a Strategy Call', 'grow-my-security' ); ?></a>
				<a class="gms-button-outline" href="<?php echo esc_url( gms_internal_link( '/press-media/', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Back to Media', 'grow-my-security' ); ?></a>
			</div>
		</div>
	</div>
	<?php

	return trim( (string) ob_get_clean() );
}

function gms_get_press_post_related_items( int $post_id, int $limit = 3 ): array {
	$posts = array_values(
		array_filter(
			gms_get_press_media_posts_for_sync(),
			static function ( $post ) use ( $post_id ): bool {
				return $post instanceof WP_Post && (int) $post->ID !== $post_id;
			}
		)
	);

	return gms_get_resource_post_grid_items_from_posts( array_slice( $posts, 0, $limit ) );
}

function gms_get_press_post_elementor_template( WP_Post $post ): array {
	$blueprint   = gms_get_press_post_blueprint( $post );
	$hero_image  = gms_get_resource_post_feature_media_url( $post );
	$related     = gms_get_press_post_related_items( (int) $post->ID, 3 );
	$contact_url = gms_internal_link( '/contact-us/', home_url( '/' ) );
	$media_url   = gms_internal_link( '/press-media/', home_url( '/' ) );
	$services_url = gms_internal_link( '/services/', home_url( '/' ) );
	$cta_image   = gms_theme_asset_link( 'assets/images/security-dashboard-visual.png', get_template_directory_uri() );
	$story_bullets = implode(
		"\n",
		array_map(
			static function ( array $item ): string {
				return (string) ( $item['text'] ?? '' );
			},
			array_slice( (array) ( $blueprint['snapshot_values'] ?? [] ), 0, 3 )
		)
	);
	$story_chips = implode(
		"\n",
		array_map(
			static function ( array $item ): string {
				return (string) ( $item['title'] ?? '' );
			},
			array_slice( (array) ( $blueprint['snapshot_values'] ?? [] ), 0, 3 )
		)
	);

	return gms_page_template(
		(string) $post->post_title,
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-page-hero', [
				'variant'        => 'detail',
				'alignment'      => 'left',
				'eyebrow'        => (string) ( $blueprint['hero_eyebrow'] ?? __( 'Press Coverage', 'grow-my-security' ) ),
				'title'          => (string) $post->post_title,
				'description'    => (string) ( $blueprint['hero_description'] ?? '' ),
				'art_image'      => [ 'url' => (string) $hero_image ],
				'primary_text'   => __( 'Book a Strategy Call', 'grow-my-security' ),
				'primary_url'    => [ 'url' => $contact_url ],
				'secondary_text' => __( 'Back to Media', 'grow-my-security' ),
				'secondary_url'  => [ 'url' => $media_url ],
			], 'press-post-hero-' . $post->ID ) ], 'press-post-hero-col-' . $post->ID ) ], 'press-post-hero-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-hero gms-editorial-post-hero--press' ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'layout'          => 'media-content',
				'eyebrow'         => (string) ( $blueprint['hero_eyebrow'] ?? __( 'Press Coverage', 'grow-my-security' ) ),
				'title'           => (string) ( $blueprint['snapshot_title'] ?? (string) $post->post_title ),
				'description'     => (string) ( $blueprint['snapshot_description'] ?? '' ),
				'image'           => [ 'url' => (string) $hero_image ],
				'supporting_text' => (string) ( $blueprint['snapshot_supporting'] ?? '' ),
				'copy_secondary'  => (string) ( $blueprint['article_sections'][0]['body'] ?? '' ),
				'highlight_text'  => (string) ( $blueprint['snapshot_highlight'] ?? '' ),
				'bullets'         => $story_bullets,
				'chips'           => $story_chips,
				'button_text'     => __( 'Explore Services', 'grow-my-security' ),
				'button_url'      => [ 'url' => $services_url ],
			], 'press-post-story-' . $post->ID ) ], 'press-post-story-col-' . $post->ID ) ], 'press-post-story-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-story gms-editorial-post-story--press' ] ),
			gms_section_node(
				[
					gms_column_node(
						[
							gms_widget_node(
								'text-editor',
								[
									'editor' => gms_get_resource_blog_article_html( $post, $blueprint ),
								],
								'press-post-article-copy-' . $post->ID
							),
						],
						'press-post-article-copy-col-' . $post->ID,
						64
					),
					gms_column_node(
						[
							gms_widget_node(
								'text-editor',
								[
									'editor' => gms_get_press_post_sidebar_html( $post, $blueprint ),
								],
								'press-post-article-sidebar-' . $post->ID
							),
						],
						'press-post-article-sidebar-col-' . $post->ID,
						36
					),
				],
				'press-post-article-shell-' . $post->ID,
				[
					'css_classes' => 'gms-editorial-post-shell gms-editorial-post-shell--press',
				]
			),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-process-timeline', [
				'eyebrow'     => __( 'How to Use It', 'grow-my-security' ),
				'title'       => (string) ( $blueprint['process_title'] ?? __( 'How to Turn Coverage Into Pipeline', 'grow-my-security' ) ),
				'description' => (string) ( $blueprint['process_description'] ?? '' ),
				'items'       => (array) ( $blueprint['process_items'] ?? [] ),
			], 'press-post-process-' . $post->ID ) ], 'press-post-process-col-' . $post->ID ) ], 'press-post-process-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-process gms-editorial-post-process--press' ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'          => __( 'Related Coverage', 'grow-my-security' ),
				'title'            => __( 'Keep Exploring Media Coverage', 'grow-my-security' ),
				'description'      => __( 'More visibility, commentary, and trust-building perspective for security companies that want stronger authority in the market.', 'grow-my-security' ),
				'items'            => $related,
				'card_button_text' => __( 'Read feature', 'grow-my-security' ),
				'button_text'      => __( 'View All Media', 'grow-my-security' ),
				'button_url'       => [ 'url' => $media_url ],
			], 'press-post-related-' . $post->ID ) ], 'press-post-related-col-' . $post->ID ) ], 'press-post-related-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-related gms-editorial-post-related--press' ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => __( 'Press & Media', 'grow-my-security' ),
				'title'       => (string) ( $blueprint['cta_title'] ?? __( 'Want More Visibility That Actually Supports Sales?', 'grow-my-security' ) ),
				'description' => (string) ( $blueprint['cta_description'] ?? '' ),
				'button_text' => __( 'Start the Conversation', 'grow-my-security' ),
				'button_url'  => [ 'url' => $contact_url ],
				'image'       => [ 'url' => $cta_image ],
			], 'press-post-cta-' . $post->ID ) ], 'press-post-cta-col-' . $post->ID ) ], 'press-post-cta-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-cta gms-editorial-post-cta--press' ] ),
		]
	);
}

function gms_get_resource_blog_elementor_template( WP_Post $post ): array {
	$blueprint  = gms_get_resource_blog_blueprint( $post );
	$hero_image = gms_get_resource_post_feature_media_url( $post );
	$related    = gms_get_resource_blog_related_items( (int) $post->ID, 3 );
	$contact_url = gms_internal_link( '/contact-us/', home_url( '/' ) );
	$resources_url = gms_internal_link( '/resources-insights/', home_url( '/' ) );
	$services_url = gms_internal_link( '/services/', home_url( '/' ) );
	$cta_image = gms_theme_asset_link( 'assets/images/security-dashboard-visual.png', get_template_directory_uri() );
	$story_bullets = implode(
		"\n",
		array_map(
			static function ( array $item ): string {
				return (string) ( $item['text'] ?? '' );
			},
			array_slice( (array) ( $blueprint['snapshot_values'] ?? [] ), 0, 3 )
		)
	);
	$story_chips = implode(
		"\n",
		array_map(
			static function ( array $item ): string {
				return (string) ( $item['title'] ?? '' );
			},
			array_slice( (array) ( $blueprint['snapshot_values'] ?? [] ), 0, 3 )
		)
	);

	return gms_page_template(
		(string) $post->post_title,
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-page-hero', [
				'variant'        => 'detail',
				'alignment'      => 'left',
				'eyebrow'        => (string) ( $blueprint['hero_eyebrow'] ?? __( 'Insight', 'grow-my-security' ) ),
				'title'          => (string) $post->post_title,
				'description'    => (string) ( $blueprint['hero_description'] ?? '' ),
				'art_image'      => [ 'url' => (string) $hero_image ],
				'primary_text'   => __( 'Book a Strategy Call', 'grow-my-security' ),
				'primary_url'    => [ 'url' => $contact_url ],
				'secondary_text' => __( 'Browse Resources', 'grow-my-security' ),
				'secondary_url'  => [ 'url' => $resources_url ],
			], 'resource-post-hero-' . $post->ID ) ], 'resource-post-hero-col-' . $post->ID ) ], 'resource-post-hero-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-hero' ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'layout'          => 'media-content',
				'eyebrow'         => (string) ( $blueprint['hero_eyebrow'] ?? __( 'Insight', 'grow-my-security' ) ),
				'title'           => (string) ( $blueprint['snapshot_title'] ?? (string) $post->post_title ),
				'description'     => (string) ( $blueprint['snapshot_description'] ?? '' ),
				'image'           => [ 'url' => (string) $hero_image ],
				'supporting_text' => (string) ( $blueprint['snapshot_supporting'] ?? '' ),
				'copy_secondary'  => (string) ( $blueprint['article_sections'][0]['body'] ?? '' ),
				'highlight_text'  => (string) ( $blueprint['snapshot_highlight'] ?? '' ),
				'bullets'         => $story_bullets,
				'chips'           => $story_chips,
				'button_text'     => __( 'Explore Services', 'grow-my-security' ),
				'button_url'      => [ 'url' => $services_url ],
			], 'resource-post-story-' . $post->ID ) ], 'resource-post-story-col-' . $post->ID ) ], 'resource-post-story-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-story' ] ),
			gms_section_node(
				[
					gms_column_node(
						[
							gms_widget_node(
								'text-editor',
								[
									'editor' => gms_get_resource_blog_article_html( $post, $blueprint ),
								],
								'resource-post-article-copy-' . $post->ID
							),
						],
						'resource-post-article-copy-col-' . $post->ID,
						64
					),
					gms_column_node(
						[
							gms_widget_node(
								'text-editor',
								[
									'editor' => gms_get_resource_blog_sidebar_html( $post, $blueprint ),
								],
								'resource-post-article-sidebar-' . $post->ID
							),
						],
						'resource-post-article-sidebar-col-' . $post->ID,
						36
					),
				],
				'resource-post-article-shell-' . $post->ID,
				[
					'css_classes' => 'gms-editorial-post-shell',
				]
			),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-process-timeline', [
				'eyebrow'     => __( 'Application', 'grow-my-security' ),
				'title'       => (string) ( $blueprint['process_title'] ?? __( 'How to Apply This Insight', 'grow-my-security' ) ),
				'description' => (string) ( $blueprint['process_description'] ?? '' ),
				'items'       => (array) ( $blueprint['process_items'] ?? [] ),
			], 'resource-post-process-' . $post->ID ) ], 'resource-post-process-col-' . $post->ID ) ], 'resource-post-process-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-process' ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'eyebrow'          => __( 'Related Articles', 'grow-my-security' ),
				'title'            => __( 'Keep Exploring the Journal', 'grow-my-security' ),
				'description'      => __( 'More trust-building insight for security brands that want better positioning, better UX, and better demand quality.', 'grow-my-security' ),
				'items'            => $related,
				'card_button_text' => __( 'Read article', 'grow-my-security' ),
				'button_text'      => __( 'View All Resources', 'grow-my-security' ),
				'button_url'       => [ 'url' => $resources_url ],
			], 'resource-post-related-' . $post->ID ) ], 'resource-post-related-col-' . $post->ID ) ], 'resource-post-related-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-related' ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => __( 'Still Have Questions?', 'grow-my-security' ),
				'title'       => (string) ( $blueprint['cta_title'] ?? __( 'Need Help Turning Insight Into Pipeline?', 'grow-my-security' ) ),
				'description' => (string) ( $blueprint['cta_description'] ?? '' ),
				'button_text' => __( 'Get in Touch', 'grow-my-security' ),
				'button_url'  => [ 'url' => $contact_url ],
				'image'       => [ 'url' => $cta_image ],
			], 'resource-post-cta-' . $post->ID ) ], 'resource-post-cta-col-' . $post->ID ) ], 'resource-post-cta-sec-' . $post->ID, [ 'css_classes' => 'gms-editorial-post-cta' ] ),
		]
	);
}

function gms_sync_resource_blog_posts_elementor_content(): void {
	if ( function_exists( 'gms_should_skip_runtime_elementor_sync' ) && gms_should_skip_runtime_elementor_sync() ) {
		return;
	}

	$sync_version = '2026-04-18-resource-blog-posts-v4';
	$updated      = false;

	foreach ( gms_get_resource_blog_posts_for_sync() as $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			continue;
		}

		if ( get_post_meta( $post->ID, '_gms_resource_blog_sync_version', true ) === $sync_version && function_exists( 'gms_post_has_elementor_content' ) && gms_post_has_elementor_content( (int) $post->ID ) ) {
			continue;
		}

		$template = gms_get_resource_blog_elementor_template( $post );

		if ( gms_sync_elementor_template_to_page( $post, $template ) ) {
			$updated = true;
		}

		update_post_meta( $post->ID, '_gms_resource_blog_sync_version', $sync_version );
	}

	if ( $updated && class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}
add_action( 'admin_init', 'gms_sync_resource_blog_posts_elementor_content', 27 );

function gms_sync_press_media_posts_elementor_content(): void {
	$sync_version = '2026-04-18-press-media-posts-v1';
	$updated      = false;

	foreach ( gms_get_press_media_posts_for_sync() as $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			continue;
		}

		if ( get_post_meta( $post->ID, '_gms_press_media_post_sync_version', true ) === $sync_version && function_exists( 'gms_post_has_elementor_content' ) && gms_post_has_elementor_content( (int) $post->ID ) ) {
			continue;
		}

		$template = gms_get_press_post_elementor_template( $post );

		if ( gms_sync_elementor_template_to_page( $post, $template ) ) {
			$updated = true;
		}

		update_post_meta( $post->ID, '_gms_press_media_post_sync_version', $sync_version );
	}

	if ( $updated && class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}
add_action( 'init', 'gms_sync_press_media_posts_elementor_content', 28 );

function gms_sync_resources_grid_widget_items(): void {
	if ( function_exists( 'gms_should_skip_runtime_elementor_sync' ) && gms_should_skip_runtime_elementor_sync() ) {
		return;
	}

	$page = get_page_by_path( 'resources-insights' );

	if ( ! ( $page instanceof WP_Post ) ) {
		return;
	}

	$raw_data = get_post_meta( $page->ID, '_elementor_data', true );

	if ( ! is_string( $raw_data ) || '' === trim( $raw_data ) ) {
		return;
	}

	$data = json_decode( $raw_data, true );

	if ( ! is_array( $data ) ) {
		return;
	}

	$sync_items   = gms_get_resource_post_grid_items_from_posts( gms_get_resource_blog_posts_for_sync() );
	$sync_version = '2026-04-18-resources-grid-cards-v3';
	$force_sync   = get_option( 'gms_resources_grid_cards_sync_version' ) !== $sync_version;
	$updated      = false;

	$walker = static function ( array &$nodes ) use ( &$walker, $sync_items, $force_sync, &$updated ): void {
		foreach ( $nodes as &$node ) {
			if (
				isset( $node['widgetType'], $node['settings'] ) &&
				'gms-post-grid' === $node['widgetType']
			) {
				$existing_items = (array) ( $node['settings']['items'] ?? [] );

				if ( $force_sync || count( $existing_items ) < count( $sync_items ) ) {
					$node['settings']['items'] = $sync_items;

					if ( empty( $node['settings']['card_button_text'] ) ) {
						$node['settings']['card_button_text'] = __( 'Read article', 'grow-my-security' );
					}

					$updated = true;
				}
			}

			if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
				$walker( $node['elements'] );
			}
		}
	};

	$walker( $data );

	if ( ! $updated ) {
		update_option( 'gms_resources_grid_cards_sync_version', $sync_version, false );
		return;
	}

	$new_json = wp_json_encode( $data );

	if ( ! is_string( $new_json ) ) {
		return;
	}

	update_post_meta( $page->ID, '_elementor_data', wp_slash( $new_json ) );
	clean_post_cache( $page->ID );

	if ( class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'gms_resources_grid_cards_sync_version', $sync_version, false );
}
add_action( 'admin_init', 'gms_sync_resources_grid_widget_items', 28 );

function gms_get_industry_elementor_grid_cards(): array {
	$cards = [];

	foreach ( gms_get_industry_page_map() as $label => $data ) {
		$clean_title = gms_clean_industry_name( $label );

		$cards[] = [
			'meta'        => '',
			'icon'        => gms_get_industry_icon( $clean_title ),
			'title'       => $clean_title,
			'text'        => $data['summary'] ?? '',
			'bullets'     => '',
			'button_text' => __( 'Learn More', 'grow-my-security' ),
			'button_url'  => [ 'url' => gms_get_industry_url( $clean_title ) ],
		];
	}

	return $cards;
}

function gms_get_home_hero_elementor_slides(): array {
	return [
		[
			'layout'         => 'centered',
			'label'          => 'Security Marketing Agency',
			'title'          => 'Turn Your Brand Into Your Most Powerful Strategic Asset',
			'copy'           => 'We help cybersecurity and regulated-industry companies generate qualified pipeline through compliant, data-backed marketing systems without compromising trust, privacy, or security.',
			'image'          => [ 'url' => gms_asset( 'assets/images/slide1-bg.png' ) ],
			'art_media_type' => 'image',
			'art_image'      => [ 'url' => '' ],
			'art_video_url'  => [ 'url' => '' ],
			'primary_text'   => 'Request a Free Consultation',
			'primary_url'    => [ 'url' => home_url( '/contact-us/' ) ],
			'secondary_text' => 'See How Growth Works',
			'secondary_url'  => [ 'url' => '#problem' ],
			'feature_points' => '',
			'scroll_label'   => '',
		],
		[
			'layout'         => 'centered',
			'label'          => 'Authority Positioning',
			'title'          => 'Be the Security Brand Buyers Trust Before They Ever Contact You',
			'copy'           => 'Establish authority, build credibility, and stay top-of-mind with security-conscious buyers through strategic, compliant marketing.',
			'image'          => [ 'url' => gms_asset( 'assets/images/slide2-bg.png' ) ],
			'art_media_type' => 'image',
			'art_image'      => [ 'url' => '' ],
			'art_video_url'  => [ 'url' => '' ],
			'primary_text'   => 'Get Started',
			'primary_url'    => [ 'url' => home_url( '/contact-us/' ) ],
			'secondary_text' => 'Learn More',
			'secondary_url'  => [ 'url' => home_url( '/services/' ) ],
			'feature_points' => '',
			'scroll_label'   => '',
		],
		[
			'layout'         => 'centered',
			'label'          => 'Data-Backed Pipeline',
			'title'          => 'Build Qualified Pipeline With Compliant, Data-Backed Marketing',
			'copy'           => 'Drive consistent, high-quality leads while maintaining strict compliance and protecting your brand reputation.',
			'image'          => [ 'url' => gms_asset( 'assets/images/slide3-bg.png' ) ],
			'art_media_type' => 'image',
			'art_image'      => [ 'url' => '' ],
			'art_video_url'  => [ 'url' => '' ],
			'primary_text'   => 'Get Your Free Audit',
			'primary_url'    => [ 'url' => home_url( '/website-audit/' ) ],
			'secondary_text' => 'View Services',
			'secondary_url'  => [ 'url' => home_url( '/services/' ) ],
			'feature_points' => '',
			'scroll_label'   => '',
		],
	];
}

function gms_get_case_study_elementor_grid_cards(): array {
	if ( ! function_exists( 'get_posts' ) ) {
		return [];
	}

	// Optimization: Only query if we're actually on the Case Studies listing page or in sync mode
	$posts = get_posts( [
		'post_type'      => 'gms_case_study',
		'posts_per_page' => 12, // Limit to 12 for the initial grid to save memory/space
		'post_status'    => 'publish',
	] );

	if ( empty( $posts ) ) {
		return [];
	}

	$cards = [];
	foreach ( $posts as $p ) {
		$image_url = (string) get_the_post_thumbnail_url( $p->ID, 'large' );
		if ( ! $image_url ) {
			$image_url = (string) get_post_meta( $p->ID, 'gms_cs_image_url', true );
		}

		if ( function_exists( 'gms_normalize_case_study_asset_url' ) ) {
			$image_url = gms_normalize_case_study_asset_url( $image_url );
		}

		$cards[] = [
			'title'   => (string) $p->post_title,
			'excerpt' => (string) get_post_meta( $p->ID, 'gms_cs_short_desc', true ),
			'image'   => [ 'url' => $image_url ],
			'badge'   => (string) get_post_meta( $p->ID, 'gms_cs_badge', true ),
			'val'     => (string) get_post_meta( $p->ID, 'gms_cs_metric_value', true ),
			'lab'     => (string) get_post_meta( $p->ID, 'gms_cs_metric_label', true ),
			'link'    => [ 'url' => (string) get_permalink( $p->ID ) ?: '#' ],
		];
	}

	return $cards;
}

function gms_get_home_testimonial_items(): array {
	return [
		[
			'quote' => "Anthony's integrity, transparency, and consistency have helped to maintain our property patrols running seamlessly and efficiently at various locations throughout the valley. He is available when needed, informative in response to all our requests.",
			'name'  => 'Kayra Z.',
			'role'  => 'Satisfied Customer',
		],
		[
			'quote' => 'I wanted to take a moment to commend you on your outstanding job in the Fractional CMO role for our security company. Your expertise in marketing strategy and your ability to understand our unique needs has been invaluable to our business.',
			'name'  => 'Mike D.',
			'role'  => 'Satisfied Customer',
		],
		[
			'quote' => 'I have known Anthony for several years in his capacity with another agency, as a Vice-President, and have nothing to say but positive things about him and his ethics, attention to detail, and looking out for the bottom line of his clients.',
			'name'  => 'Bill B.',
			'role'  => 'Satisfied Customer',
		],
		[
			'quote' => 'Anthony has done a great job for us in managing our security needs and sometimes on short notice. As part of a 5 billion a year company, it is nice to know that Anthony has our back. Thank you for your ongoing professional support.',
			'name'  => 'Donnie W.',
			'role'  => 'Satisfied Customer',
		],
		[
			'quote' => 'Grow My Security Company has been undoubtedly a leader in digital marketing strategy and I am extremely proud to have this outstanding company collaborating with my business. Anthony has helped me drive my company forward.',
			'name'  => 'Nelson V.',
			'role'  => 'Satisfied Customer',
		],
		[
			'quote' => 'I would like to recommend Anthony J. Rumore for his expertise in Security Consulting. I have had the pleasure of working with Anthony since 2015 when we contracted with him for our night and weekend patrols in Anthem Parkside.',
			'name'  => 'Mary Beth Z.',
			'role'  => 'Satisfied Customer',
		],
	];
}

function gms_get_home_services_accordion_items(): array {
	return [
		[
			'title'    => 'Strategic Marketing',
			'subtitle' => 'SEO, AEO & GEO Mastery',
			'summary'  => 'Dominate the answer engines and traditional search results where your buyers are already asking critical questions.',
			'benefits' => "AEO for Voice and AI search visibility\nGEO to become the source of truth for LLMs\nAuthority-based SEO for long-term equity",
			'image'    => [ 'url' => gms_asset( 'assets/images/service-strategic-marketing.png' ) ],
		],
		[
			'title'    => 'Precision Lead Gen',
			'subtitle' => 'High-Intent Pathways',
			'summary'  => 'We do not just find leads; we engineer systems that attract decision-makers ready to sign contracts.',
			'benefits' => "ABM for high-value commercial targets\nMulti-channel attribution tracking\nHigh-converting landing pages",
			'image'    => [ 'url' => gms_asset( 'assets/images/service-precision-leads.png' ) ],
		],
		[
			'title'    => 'Fractional CMO',
			'subtitle' => 'Executive-Level Strategy',
			'summary'  => 'Gain executive-level marketing leadership without the 300k plus overhead. We guide your mission-critical growth.',
			'benefits' => "Quarterly strategic roadmaps\nBudget optimization and waste reduction\nTeam mentorship and management",
			'image'    => [ 'url' => gms_asset( 'assets/images/service-fractional-cmo.png' ) ],
		],
		[
			'title'    => 'Authority Web Dev',
			'subtitle' => 'High-Performance Engines',
			'summary'  => 'Your website is your number one sales rep. We build secure, military-grade platforms that convert traffic into trust.',
			'benefits' => "Industry-specific UX for security\nMilitary-grade security hardening\nIntegrated conversion tracking",
			'image'    => [ 'url' => gms_asset( 'assets/images/service-authority-webdev.png' ) ],
		],
		[
			'title'    => 'AI Growth Solutions',
			'subtitle' => 'Future-Proof Systems',
			'summary'  => 'Leverage AI models trained on industry insights to automate workflows and gain a competitive edge.',
			'benefits' => "Automated human-like outreach\nPredictive market analytics\nCustom AI content engines",
			'image'    => [ 'url' => gms_asset( 'assets/images/service-ai-growth.png' ) ],
		],
	];
}

function gms_get_home_case_study_elementor_cards(): array {
	$fallback_cards  = [
		[
			'title'        => 'Lead Generation',
			'excerpt'      => 'Driving consistent, high-quality leads through targeted campaigns and optimized funnels.',
			'image'        => [ 'url' => gms_asset( 'assets/images/service-precision-leads.png' ) ],
			'url'          => [ 'url' => home_url( '/case-study-lead-generation/' ) ],
			'metric_value' => '3X',
			'metric_label' => 'INCREASE IN QUALIFIED LEADS',
		],
		[
			'title'        => 'SEO Services',
			'excerpt'      => 'Improving search visibility and organic traffic through strategic optimization.',
			'image'        => [ 'url' => gms_asset( 'assets/images/service-strategic-marketing.png' ) ],
			'url'          => [ 'url' => home_url( '/case-study-seo/' ) ],
			'metric_value' => '200%',
			'metric_label' => 'GROWTH IN ORGANIC TRAFFIC',
		],
		[
			'title'        => 'Web Development',
			'excerpt'      => 'Building fast, secure, and conversion-focused websites for scalable growth.',
			'image'        => [ 'url' => gms_asset( 'assets/images/service-authority-webdev.png' ) ],
			'url'          => [ 'url' => home_url( '/case-study-web-development/' ) ],
			'metric_value' => '40%',
			'metric_label' => 'INCREASE IN CONVERSION RATE',
		],
	];
	$query = get_posts(
		[
			'post_type'      => 'gms_case_study',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		]
	);

	if ( empty( $query ) ) {
		return $fallback_cards;
	}

	$cards = [];

	foreach ( $query as $index => $post ) {
		$image_url = function_exists( 'gms_get_case_study_image_url' ) ? gms_get_case_study_image_url( $post, 'large' ) : '';

		$cards[] = [
			'title'        => get_the_title( $post ),
			'excerpt'      => (string) get_post_meta( $post->ID, 'gms_cs_short_desc', true ),
			'image'        => [ 'url' => $image_url ?: ( $fallback_cards[ $index ]['image']['url'] ?? '' ) ],
			'url'          => [ 'url' => get_permalink( $post ) ?: home_url( '/case-studies/' ) ],
			'metric_value' => (string) get_post_meta( $post->ID, 'gms_cs_metric_value', true ) ?: ( $fallback_cards[ $index ]['metric_value'] ?? '' ),
			'metric_label' => (string) get_post_meta( $post->ID, 'gms_cs_metric_label', true ) ?: ( $fallback_cards[ $index ]['metric_label'] ?? '' ),
		];
	}

	return $cards;
}

function gms_sync_homepage_case_study_widget_items(): void {
	if ( function_exists( 'gms_should_skip_runtime_elementor_sync' ) && gms_should_skip_runtime_elementor_sync() ) {
		return;
	}

	$front_page = gms_get_front_page_for_elementor_sync();

	if ( ! ( $front_page instanceof WP_Post ) ) {
		return;
	}

	$raw_data = get_post_meta( $front_page->ID, '_elementor_data', true );

	if ( ! is_string( $raw_data ) || '' === trim( $raw_data ) ) {
		return;
	}

	$data = json_decode( $raw_data, true );

	if ( ! is_array( $data ) ) {
		return;
	}

	$cards   = gms_get_home_case_study_elementor_cards();
	$updated = false;

	$walker = static function ( array &$nodes ) use ( &$walker, $cards, &$updated ): void {
		foreach ( $nodes as &$node ) {
			if (
				isset( $node['widgetType'], $node['settings'] ) &&
				'gms-post-grid' === $node['widgetType'] &&
				'case_studies' === ( $node['settings']['layout_style'] ?? '' )
			) {
				$node['settings']['items']            = $cards;
				$node['settings']['card_button_text'] = 'View Case Study →';
				$updated                              = true;
			}

			if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
				$walker( $node['elements'] );
			}
		}
	};

	$walker( $data );

	if ( ! $updated ) {
		return;
	}

	$new_json = wp_json_encode( $data );

	if ( ! is_string( $new_json ) || trim( $new_json ) === trim( $raw_data ) ) {
		return;
	}

	update_post_meta( $front_page->ID, '_elementor_data', wp_slash( $new_json ) );
	clean_post_cache( $front_page->ID );

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}
add_action( 'admin_init', 'gms_sync_homepage_case_study_widget_items', 25 );

function gms_replace_stale_case_study_image_paths( string $json ): string {
	$replacements = [
		'assets/images/cs-cloud.png'    => 'assets/images/case-studies/cs-cloud.png',
		'assets/images/cs-phishing.png' => 'assets/images/case-studies/cs-phishing.png',
		'assets/images/cs-health.png'   => 'assets/images/case-studies/cs-health.png',
	];

	return str_replace( array_keys( $replacements ), array_values( $replacements ), $json );
}

function gms_get_home_sync_section( array $widgets, string $seed, array $settings = [] ): array {
	$base_settings = [
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
	];

	return gms_section_node(
		[
			gms_column_node( $widgets, $seed . '-col' ),
		],
		$seed . '-sec',
		array_merge( $base_settings, $settings )
	);
}

function gms_get_home_elementor_sync_template(): array {
	$config    = gms_get_demo_config();
	$faq_items = array_values( (array) ( $config['faqs'] ?? [] ) );

	$faq_items[] = [
		'question' => 'How long does it take to see traction?',
		'answer'   => 'The first wins usually come from clearer positioning and sharper buyer-facing pages, while compounding SEO and authority gains build over the following months.',
	];

	return gms_page_template(
		'Home',
		[
			gms_get_home_sync_section( [ gms_widget_node( 'gms-hero', [
				'slides' => gms_get_home_hero_elementor_slides(),
			], 'home-hero-sync' ) ], 'home-hero-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-story', [
				'layout'          => 'problem-list',
				'eyebrow'         => 'The Problem',
				'title'           => 'Security Brands Struggle Because Their Expertise Is Not Visible or Trusted Online.',
				'description'     => 'In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They do not respond to loud marketing, generic promises, or copy-paste campaigns. They look for credibility, authority, and proof long before they ever reach out.',
				'supporting_text' => 'As a result, potential clients visit, hesitate, and leave. The value is not communicated in a way buyers believe.',
				'copy_secondary'  => 'This is the gap we focus on closing.',
				'image'           => [ 'url' => '' ],
				'values'          => gms_story_values(
					[
						[ 'Invisible Profile', 'Without a clear digital footprint, high-value clients cannot find you. Your agency exists in the shadows of the internet.' ],
						[ 'Trust Deficit', 'Security is bought on trust. If your brand looks generic, clients assume your security protocols are too.' ],
						[ 'False Perception', 'Generic visuals create a shell-company aesthetic. Real authority requires distinct, verified, and credible branding.' ],
					]
				),
			], 'home-problem-sync' ) ], 'home-problem-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-story', [
				'layout'          => 'media-content',
				'eyebrow'         => 'The Solution',
				'title'           => 'Smarter Marketing, Trust-led Positioning Built Specifically For the Security Industries',
				'description'     => 'We help security companies build visible trust through systems designed around how security buyers actually think and buy. From positioning and messaging to content, search visibility, and lead pathways, every element works together to answer one question:',
				'highlight_text'  => 'Can I trust this company with something critical?',
				'supporting_text' => 'Instead of disconnected campaigns, we build a structured growth engine that makes your brand a credible authority in your niche.',
				'copy_secondary'  => 'This is not marketing for clicks. This is marketing that builds confidence, reduces doubt, and shortens decision cycles because in security, trust is the real conversion factor.',
				'bullets'         => "Establishes your brand as a credible authority in your niche\nCommunicates expertise without overselling or exaggeration\nAligns marketing with real security buyer intent\nConverts visibility into qualified, high-trust inquiries",
				'chips'           => "3.2X Increase in Lead Generation\n100% User Retention in Key Campaigns\n50% Reduction in Wasted Ad Spend",
				'button_text'     => 'Schedule a Free Consultation',
				'button_url'      => [ 'url' => home_url( '/contact-us/' ) ],
				'image'           => [ 'url' => gms_asset( 'assets/images/home-solution-v2.png' ) ],
				'values'          => [],
			], 'home-solution-sync' ) ], 'home-solution-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-process-timeline', [
				'eyebrow'     => 'Our Guarantee',
				'title'       => 'How We Help Security Brands Win Trust & Growth',
				'description' => 'We work as a strategic growth partner for security-focused brands, helping them move from being technically capable to being clearly trusted in the market. Every engagement is designed around long-term authority, not short-term spikes.',
				'items'       => [
					[
						'accent'    => 'Clear Positioning',
						'title'     => 'in a Crowded Market',
						'text'      => 'We help you articulate what you do, who you are for, and why you are different without jargon or overclaiming. Your buyers should understand your value in seconds.',
						'link_text' => 'Learn More',
						'link_url'  => [ 'url' => home_url( '/positioning/' ) ],
						'icon'      => 'shield',
					],
					[
						'accent'    => 'Trust-First Brand',
						'title'     => 'Presence',
						'text'      => 'From messaging and content to design and digital touchpoints, we ensure your brand looks, sounds, and feels credible across every channel where buyers research you.',
						'link_text' => 'See How This Works',
						'link_url'  => [ 'url' => home_url( '/presence/' ) ],
						'icon'      => 'users',
					],
					[
						'accent'    => 'Buyer-Aligned Demand',
						'title'     => 'Generation',
						'text'      => 'We focus on attracting decision-makers who are already searching for solutions like yours, guiding them with the right information at each stage of their journey.',
						'link_text' => 'Explore More',
						'link_url'  => [ 'url' => home_url( '/demand/' ) ],
						'icon'      => 'target',
					],
					[
						'accent'    => 'Consistency Over',
						'title'     => 'Campaigns',
						'text'      => 'Instead of disconnected tactics, we build a repeatable system that compounds visibility, authority, and inbound demand over time.',
						'link_text' => 'Get Started',
						'link_url'  => [ 'url' => home_url( '/contact-us/' ) ],
						'icon'      => 'link',
					],
				],
			], 'home-process-sync' ) ], 'home-process-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-testimonials', [
				'layout'          => 'featured',
				'background_word' => 'Testimonials',
				'items'           => gms_get_home_testimonial_items(),
			], 'home-testimonials-sync' ) ], 'home-testimonials-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-post-grid', [
				'layout_style'     => 'case_studies',
				'eyebrow'          => 'Case Studies',
				'title'            => 'Proven Results for Security Brands',
				'description'      => 'Real outcomes from trust-led marketing strategies',
				'card_button_text' => 'View Case Study →',
				'button_text'      => '',
				'button_url'       => [ 'url' => home_url( '/case-studies/' ) ],
				'items'            => gms_get_home_case_study_elementor_cards(),
			], 'home-case-studies-sync' ) ], 'home-case-studies-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-icon-grid', [
				'eyebrow'     => 'Who We Serve',
				'title'       => 'Security Verticals Supported',
				'description' => 'If your buyers are technical and your product is complex, you are in the right place.',
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
				'button_url'  => [ 'url' => home_url( '/contact-us/' ) ],
				'footer_text' => 'Do not see your industry? Contact us to see if we can help you.',
			], 'home-industries-sync' ) ], 'home-industries-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-services-accordion', [
				'eyebrow'     => 'Our Services',
				'title'       => 'Building Security Solutions with Intelligent Services',
				'description' => 'If your buyers are technical and your product is complex, you are in the right place.',
				'image'       => [ 'url' => gms_asset( 'assets/images/home-services-media.png' ) ],
				'button_text' => 'View All Services',
				'button_url'  => [ 'url' => home_url( '/services/' ) ],
				'items'       => gms_get_home_services_accordion_items(),
			], 'home-services-sync' ) ], 'home-services-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-faq', [
				'layout'                => 'split',
				'eyebrow'               => "FAQ's",
				'title'                 => 'Frequently Asked Questions',
				'description'           => 'In the security industry, trust is everything. Buyers are cautious, risk-aware, and slow to commit. They do not respond to loud marketing, generic promises, or copy-paste campaigns.',
				'primary_button_text'   => 'Schedule a Free Consultation',
				'primary_button_url'    => [ 'url' => home_url( '/contact-us/' ) ],
				'secondary_button_text' => 'Check All FAQ',
				'secondary_button_url'  => [ 'url' => home_url( '/faq/' ) ],
				'footer_text'           => 'Do you have anymore questions for us?',
				'footer_link_text'      => 'Contact Us',
				'footer_link_url'       => [ 'url' => home_url( '/contact-us/' ) ],
				'items'                 => $faq_items,
			], 'home-faq-sync' ) ], 'home-faq-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-post-grid', [
				'layout_style' => 'journal',
				'eyebrow'     => 'Blogs',
				'title'       => 'Updated Journal',
				'description' => 'Strategists dedicated to creating stunning, functional websites that align with your unique business goals.',
				'items'       => [
					[
						'meta'         => 'Website Design',
						'title'        => 'Why UX/UI Design Can Make or Break Your Website.',
						'excerpt'      => 'Why the top of the funnel is not broken anymore and how better trust design improves conversion before the first call.',
						'image'        => [ 'url' => gms_asset( 'assets/images/home-journal-1.png' ) ],
						'url'          => [ 'url' => home_url( '/why-ux-ui-design-can-make-or-break-your-website/' ) ],
						'metric_value' => '',
						'metric_label' => '',
					],
					[
						'meta'         => 'AI Trend',
						'title'        => '5 Must-Have Features for a Modern Business Website.',
						'excerpt'      => 'The technical and credibility layers every modern security brand website needs to convert high-intent buyers.',
						'image'        => [ 'url' => gms_asset( 'assets/images/home-journal-2.png' ) ],
						'url'          => [ 'url' => home_url( '/five-must-have-features-for-a-modern-business-website/' ) ],
						'metric_value' => '',
						'metric_label' => '',
					],
					[
						'meta'         => 'AI Trend',
						'title'        => 'AI in Content Creation: Friend or Foe? in 2026',
						'excerpt'      => 'A practical view of AI-assisted content systems for regulated industries that cannot afford generic messaging.',
						'image'        => [ 'url' => gms_asset( 'assets/images/home-journal-3.png' ) ],
						'url'          => [ 'url' => home_url( '/ai-in-content-creation-friend-or-foe-in-2026/' ) ],
						'metric_value' => '',
						'metric_label' => '',
					],
				],
				'button_text' => 'View All Services',
				'button_url'  => [ 'url' => home_url( '/resources-insights/' ) ],
			], 'home-posts-sync' ) ], 'home-posts-sync' ),
			gms_get_home_sync_section( [ gms_widget_node( 'gms-contact-form', [
				'eyebrow'       => 'Contact Us',
				'title'         => 'Get your free quote',
				'description'   => 'Supercharge your online presence with a tailored digital marketing strategy. Fill out the form today to get a personalized consultation and quote within 24-48 hours - no hidden fees, no obligations.',
				'email'         => 'info@growmysecuritycompany.com',
				'phone'         => '(623) 282-1778',
				'address'       => 'Chicago, IL, United States',
				'hours'         => 'Monday-Friday: 09:00AM - 06:00PM',
				'services_list' => "Branding Services\nSearch Engine Optimisation\nFractional CMO Services\nSocial Media Marketing\nWebsite Design\nWebsite Development\nAdvertising Services\nAI Solutions",
				'submit_text'   => 'Get My Free Quote',
				'footer_chips'  => "24-48 hour response\nNo spam, ever\nLocal experts",
			], 'home-contact-sync' ) ], 'home-contact-sync' ),
		]
	);
}

function gms_get_press_media_elementor_template( array $config ): array {
	$contact_url   = gms_get_elementor_safe_internal_url( home_url( '/contact-us/' ) );
	$resources_url = gms_get_elementor_safe_internal_url( home_url( '/resources-insights/' ) );
	$story_image   = gms_get_elementor_safe_media_url( gms_asset( 'assets/images/image-4.png' ) );
	$cta_image     = gms_get_elementor_safe_media_url( gms_asset( 'assets/images/image-3.png' ) );

	return gms_page_template(
		'Press & Media',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-page-hero', [
				'variant'        => 'faq',
				'alignment'      => 'center',
				'eyebrow'        => 'Press & Media',
				'title'          => 'Media Coverage That Builds Authority',
				'description'    => 'Interviews, commentary, and strategic perspective built to help security brands look credible in high-trust markets.',
				'primary_text'   => 'Request a Conversation',
				'primary_url'    => [ 'url' => $contact_url ],
				'secondary_text' => 'Explore Resources',
				'secondary_url'  => [ 'url' => $resources_url ],
			], 'press-media-hero-sync' ) ], 'press-media-hero-sync-col' ) ], 'press-media-hero-sync-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'layout'          => 'split-cards',
				'eyebrow'         => 'Why Media Matters',
				'title'           => 'The right coverage helps cautious buyers trust faster',
				'description'     => 'In complex security markets, outside visibility does more than create awareness. It gives buyers a reason to believe the brand is established, relevant, and worth a serious look.',
				'image'           => [ 'url' => $story_image ],
				'supporting_text' => "Strong press and commentary create a credibility layer your website cannot build alone.\n\nWhen your insights appear in the right places, buyers read your brand as more established before they ever book a call.",
				'highlight_text'  => 'The goal is not noise. The goal is earned visibility that reinforces positioning, authority, and trust.',
				'button_text'     => 'Discuss a Media Opportunity',
				'button_url'      => [ 'url' => $contact_url ],
				'values'          => gms_story_values(
					[
						[ 'Third-Party Credibility', 'External visibility helps security buyers see the brand as trusted beyond its own channels.' ],
						[ 'Sharper Positioning', 'Coverage works best when the message is focused, differentiated, and aligned to the right buyer concerns.' ],
						[ 'Commercial Trust', 'Better authority signals reduce hesitation and make serious prospects more ready to engage.' ],
					]
				),
			], 'press-media-story-sync' ) ], 'press-media-story-sync-col' ) ], 'press-media-story-sync-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'layout_style'     => 'standard',
				'eyebrow'          => 'Recent Coverage',
				'title'            => 'Press Features & Commentary',
				'description'      => 'A focused view of the interviews, coaching perspectives, and growth commentary shaping how security brands build authority.',
				'card_button_text' => 'Read feature',
				'button_text'      => 'Contact Our Team',
				'button_url'       => [ 'url' => $contact_url ],
				'items'            => gms_get_press_media_grid_items( (array) ( $config['press_items'] ?? [] ) ),
			], 'press-media-grid-sync' ) ], 'press-media-grid-sync-col' ) ], 'press-media-grid-sync-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => 'Press Inquiries',
				'title'       => 'Need a quote, guest, or interview angle?',
				'description' => 'We support podcasts, editorials, interviews, and expert commentary for security-company growth, authority, and positioning.',
				'button_text' => 'Start the Conversation',
				'button_url'  => [ 'url' => $contact_url ],
				'trust_text'  => 'We keep outreach clear, responsive, and aligned to the way serious security buyers evaluate expertise.',
				'image'       => [ 'url' => $cta_image ],
			], 'press-media-cta-sync' ) ], 'press-media-cta-sync-col' ) ], 'press-media-cta-sync-sec' ),
		]
	);
}

function gms_get_podcast_page_grid_items( array $items ): array {
	$output = [];

	foreach ( $items as $item ) {
		$slug      = (string) ( $item['slug'] ?? '' );
		$image_url = gms_get_elementor_safe_media_url( (string) ( $item['image'] ?? '' ) );

		if ( function_exists( 'gms_get_generated_asset_url' ) && '' !== $slug ) {
			$image_url = gms_get_elementor_safe_media_url(
				(string) gms_get_generated_asset_url(
					'building-trust-in-security-marketing' === $slug ? 'podcast-episode-1.png' : 'podcast-episode-2.png',
					$image_url
				)
			);
		}

		$output[] = [
			'meta'    => (string) ( $item['meta'] ?? 'Podcast Episode' ),
			'title'   => (string) ( $item['title'] ?? '' ),
			'excerpt' => (string) ( $item['excerpt'] ?? '' ),
			'image'   => [ 'url' => $image_url ],
			'url'     => [ 'url' => gms_get_elementor_safe_internal_url( gms_internal_link( '/' . trim( $slug, '/' ) . '/', home_url( '/' ) ) ) ],
		];
	}

	return $output;
}

function gms_get_podcast_page_elementor_template( array $config ): array {
	$contact_url   = gms_get_elementor_safe_internal_url( home_url( '/contact-us/' ) );
	$resources_url = gms_get_elementor_safe_internal_url( home_url( '/resources-insights/' ) );
	$media_url     = gms_get_elementor_safe_internal_url( home_url( '/press-media/' ) );
	$story_image   = function_exists( 'gms_get_generated_asset_url' )
		? (string) gms_get_generated_asset_url( 'podcast-episode-2.png', gms_asset( 'assets/images/resources.png' ) )
		: gms_asset( 'assets/images/resources.png' );
	$cta_image     = gms_get_elementor_safe_media_url( gms_asset( 'assets/images/security-dashboard-visual.png' ) );
	$story_image   = gms_get_elementor_safe_media_url( $story_image );

	return gms_page_template(
		'Podcast',
		[
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-page-hero', [
				'variant'        => 'faq',
				'alignment'      => 'center',
				'eyebrow'        => 'Podcast',
				'title'          => 'Authority-Building Conversations For Security Brands',
				'description'    => 'Podcast appearances, guest conversations, and interview-ready perspectives designed to sharpen positioning and make trust easier to earn.',
				'primary_text'   => 'Book a Strategy Call',
				'primary_url'    => [ 'url' => $contact_url ],
				'secondary_text' => 'Explore Media',
				'secondary_url'  => [ 'url' => $media_url ],
			], 'podcast-hero-sync' ) ], 'podcast-hero-sync-col' ) ], 'podcast-hero-sync-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
				'layout'          => 'split-cards',
				'eyebrow'         => 'Why Podcast Visibility Matters',
				'title'           => 'Long-form conversations help buyers trust the thinking behind the brand',
				'description'     => 'In security markets, trust often grows when prospects hear how a company thinks, not just what it sells. Podcast conversations create room for nuance, clarity, and real authority.',
				'image'           => [ 'url' => $story_image ],
				'supporting_text' => "The strongest episodes do more than create awareness. They reveal strategic thinking, calm confidence, and category depth in a way short-form content rarely can.\n\nThat gives serious buyers another proof point before they ever fill out a form.",
				'highlight_text'  => 'The goal is not exposure alone. It is credibility that compounds across every other channel.',
				'button_text'     => 'View Resources',
				'button_url'      => [ 'url' => $resources_url ],
				'values'          => gms_story_values(
					[
						[ 'Deeper Trust', 'Long-form audio lets prospects hear conviction, clarity, and experience without the noise of a hard sell.' ],
						[ 'Clearer Positioning', 'Well-framed conversations help security brands explain what makes them different in a more memorable way.' ],
						[ 'Reusable Authority', 'One strong episode can feed website content, social proof, sales enablement, and future outreach.' ],
					]
				),
			], 'podcast-story-sync' ) ], 'podcast-story-sync-col' ) ], 'podcast-story-sync-sec', [ 'css_classes' => 'gms-podcast-story-sync' ] ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
				'layout_style'     => 'standard',
				'eyebrow'          => 'Featured Episodes',
				'title'            => 'Podcast Conversations & Guest Appearances',
				'description'      => 'A focused collection of interviews and appearances that help security brands communicate trust, positioning, and commercial clarity.',
				'card_button_text' => 'Listen to Episode',
				'button_text'      => 'Contact Our Team',
				'button_url'       => [ 'url' => $contact_url ],
				'items'            => gms_get_podcast_page_grid_items( (array) ( $config['podcasts'] ?? [] ) ),
			], 'podcast-grid-sync' ) ], 'podcast-grid-sync-col' ) ], 'podcast-grid-sync-sec' ),
			gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
				'eyebrow'     => 'Podcast Requests',
				'title'       => 'Need a guest who understands security-company growth?',
				'description' => 'We support podcast hosts, interviews, roundtables, and collaborative conversations focused on authority, trust, and pipeline growth in security markets.',
				'button_text' => 'Start the Conversation',
				'button_url'  => [ 'url' => $contact_url ],
				'trust_text'  => 'Clear positioning, thoughtful commentary, and practical insight for serious audiences and serious buyers.',
				'image'       => [ 'url' => $cta_image ],
			], 'podcast-cta-sync' ) ], 'podcast-cta-sync-col' ) ], 'podcast-cta-sync-sec' ),
		]
	);
}

function gms_get_public_page_elementor_templates(): array {
	$config = gms_get_demo_config();

	return [
		'home' => gms_get_home_elementor_sync_template(),
		'about-us' => gms_page_template(
			'About Us',
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
					'eyebrow'     => 'About Us',
					'title'       => 'Turn expertise into trust that scales',
					'description' => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
					'image'       => [ 'url' => gms_asset( 'assets/images/image-2.png' ) ],
					'values'      => gms_story_values(
						[
							[ 'Truth Over Tactics', 'Security buyers respond to clear authority, not hype.' ],
							[ 'Empathy in Action', 'We build for cautious, risk-aware audiences.' ],
							[ 'Active Listening', 'Deep discovery produces stronger positioning.' ],
						]
					),
				], 'about-mission-sync' ) ], 'about-mission-sync-col' ) ], 'about-mission-sync-sec' ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-story', [
					'eyebrow'     => 'Our Story',
					'title'       => 'Origin Story',
					'description' => 'After nearly 20 years climbing the security corporate ladder, Anthony noticed a heightened need for strategic company growth within the security industry.',
					'image'       => [ 'url' => gms_asset( 'assets/images/image-3.png' ) ],
					'values'      => gms_story_values(
						[
							[ 'Security Industry Depth', 'Built from leadership, operations, sales, and growth experience.' ],
							[ 'Military Discipline', 'A no-nonsense approach to execution and follow-through.' ],
							[ 'Authority Through Visibility', 'Visible trust is the bridge between expertise and growth.' ],
						]
					),
				], 'about-origin-sync' ) ], 'about-origin-sync-col' ) ], 'about-origin-sync-sec' ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
					'eyebrow'     => 'Contact Us',
					'title'       => 'Ready to build trust that drives revenue?',
					'description' => 'Let us turn your credibility into visible authority.',
					'button_text' => 'Schedule a Free Consultation',
					'button_url'  => [ 'url' => home_url( '/contact-us/' ) ],
					'image'       => [ 'url' => gms_asset( 'assets/images/security-dashboard-visual.png' ) ],
				], 'about-cta-sync' ) ], 'about-cta-sync-col' ) ], 'about-cta-sync-sec' ),
			]
		),
		'services' => gms_page_template(
			'Services',
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-page-hero', [
					'variant'          => 'services',
					'eyebrow'          => 'Services',
					'title'            => "Creative Services\nbuilt for impact",
					'description'      => 'We help technical teams earn credibility and belief, turning what they know into visible, measurable trust that drives real opportunity.',
					'art_image'        => gms_get_service_archive_hero_media(),
					'background_image' => [ 'url' => '' ],
					'primary_text'     => '',
					'primary_url'      => [ 'url' => home_url( '/contact-us/' ) ],
					'secondary_text'   => '',
					'secondary_url'    => [ 'url' => home_url( '/about-us/' ) ],
				], 'services-hero-sync' ) ], 'services-hero-sync-col' ) ], 'services-hero-sync-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '16', 'left' => '0', 'isLinked' => false ] ] ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-service-grid', [
					'data_type'    => 'services',
					'show_heading' => '',
					'cards'        => gms_get_service_elementor_grid_cards( (array) ( $config['services'] ?? [] ) ),
				], 'services-grid-sync' ) ], 'services-grid-sync-col' ) ], 'services-grid-sync-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '24', 'right' => '0', 'bottom' => '48', 'left' => '0', 'isLinked' => false ] ] ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
					'eyebrow'     => "FAQ's",
					'title'       => 'Ready to build trust that drives revenue?',
					'description' => 'You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.',
					'button_text' => 'Schedule a Free Consultation',
					'button_url'  => [ 'url' => home_url( '/contact-us/' ) ],
					'image'       => [ 'url' => gms_asset( 'assets/images/home-services-media.png' ) ],
				], 'services-cta-sync' ) ], 'services-cta-sync-col' ) ], 'services-cta-sync-sec' ),
			]
		),
		'industries' => gms_page_template(
			'Industries',
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-hero', [
					'slides' => [
						[
							'layout'         => 'banner',
							'content_align'  => 'center',
							'label'          => 'Industries',
							'title'          => 'Security Verticals Supported',
							'copy'           => 'Choose the vertical you serve and open its dedicated industry page.',
							'image'          => [ 'url' => '' ],
							'art_media_type' => 'video',
							'art_image'      => [ 'url' => gms_asset( 'assets/images/industry-hero-lock.png' ) ],
							'art_video_url'  => [ 'url' => gms_asset( 'assets/images/industry-video.mp4' ) ],
							'primary_text'   => 'Get Quote',
							'primary_url'    => [ 'url' => home_url( '/contact-us/' ) ],
							'secondary_text' => '',
							'secondary_url'  => [ 'url' => home_url( '/about-us/' ) ],
						],
					],
				], 'industries-hero-sync' ) ], 'industries-hero-sync-col' ) ], 'industries-hero-sync-sec', [ 'padding' => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '32', 'left' => '0', 'isLinked' => false ] ] ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-card-grid', [
					'eyebrow'     => 'Industries',
					'title'       => 'Security Verticals Supported',
					'description' => 'Choose the sector you serve and open its dedicated industry page.',
					'cards'       => gms_get_industry_elementor_grid_cards(),
				], 'industries-grid-sync' ) ], 'industries-grid-sync-col' ) ], 'industries-grid-sync-sec' ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
					'eyebrow'     => "FAQ's",
					'title'       => 'Ready to build trust that drives revenue?',
					'description' => 'You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.',
					'button_text' => 'Schedule a Free Consultation',
					'button_url'  => [ 'url' => home_url( '/contact-us/' ) ],
					'image'       => [ 'url' => gms_asset( 'assets/images/security-dashboard-visual.png' ) ],
				], 'industries-cta-sync' ) ], 'industries-cta-sync-col' ) ], 'industries-cta-sync-sec' ),
			]
		),
		'resources-insights' => gms_page_template(
			'Resources & Insights',
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-post-grid', [
					'eyebrow'     => 'Blog',
					'title'       => 'Resources & Insights',
					'description' => 'News, commentary, and insight built to help security companies find measurable trust online.',
					'items'       => gms_post_items( (array) ( $config['blog_posts'] ?? [] ) ),
				], 'resources-grid-sync' ) ], 'resources-grid-sync-col' ) ], 'resources-grid-sync-sec' ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
					'eyebrow'     => 'Ready',
					'title'       => 'Ready to build trust that drives revenue?',
					'description' => 'The right strategy compounds visibility and demand.',
					'button_text' => 'Schedule a Free Consultation',
					'button_url'  => [ 'url' => home_url( '/contact-us/' ) ],
					'image'       => [ 'url' => gms_asset( 'assets/images/security-dashboard-visual.png' ) ],
				], 'resources-cta-sync' ) ], 'resources-cta-sync-col' ) ], 'resources-cta-sync-sec' ),
			]
		),
		'faqs' => gms_page_template(
			'FAQ',
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-page-hero', [
					'variant'          => 'faq',
					'alignment'        => 'center',
					'title'            => 'Frequently Asked Questions',
					'description'      => 'Straight answers about how we are helping security brands find their voice and build defensible trust.',
					'primary_text'     => 'Book a Consultation',
					'primary_url'      => [ 'url' => home_url( '/contact-us/' ) ],
				], 'faq-hero-sync' ) ], 'faq-hero-sync-col' ) ], 'faq-hero-sync-sec' ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-faq', [
					'layout'      => 'stacked',
					'eyebrow'     => '',
					'title'       => '',
					'description' => '',
					'items'       => array_map( function( $item ) {
						if ( empty( $item['_id'] ) ) {
							$item['_id'] = substr( md5( wp_json_encode( $item ) ), 0, 8 );
						}
						return $item;
					}, function_exists( 'gms_get_extended_faq_items' ) ? gms_get_extended_faq_items() : (array) ( $config['faqs'] ?? [] ) ),
				], 'faq-list-sync' ) ], 'faq-list-sync-col' ) ], 'faq-list-sync-sec' ),
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-cta-banner', [
					'eyebrow'     => "FAQ's",
					'title'       => 'Ready to build trust that drives revenue?',
					'description' => 'You bring the expertise, we turn it into visibility, credibility, and pipeline momentum.',
					'button_text' => 'Schedule a Free Consultation',
					'button_url'  => [ 'url' => home_url( '/contact-us/' ) ],
					'image'       => [ 'url' => gms_asset( 'assets/images/security-dashboard-visual.png' ) ],
				], 'faq-cta-sync' ) ], 'faq-cta-sync-col' ) ], 'faq-cta-sync-sec' ),
			]
		),
		'contact-us' => gms_page_template(
			'Contact Us',
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-contact-form', [
					'eyebrow'     => 'Contact Us',
					'title'       => 'Ready to build, Trust & Pipeline?',
					'description' => 'Let\'s build a stronger pipeline for your security business, secure, scalable, and measurable.',
					'email'       => $config['branding']['email'] ?? 'info@growmysecuritycompany.com',
					'phone'       => $config['branding']['phone'] ?? '+1 (623) 282-1778',
					'address'     => $config['branding']['address'] ?? 'Chicago, IL, United States',
					'hours'       => 'Monday-Friday, 09:00AM - 06:00PM',
					'submit_text' => 'Start the Conversation',
					'response_note' => 'We respond to all inquiries within 1 business day',
					'panel_image' => [ 'url' => gms_asset( 'assets/images/contact-us-panel-visual.png' ) ],
					'email_heading' => 'Prefer Email?',
					'email_note' => 'We respect your inbox. No spam, ever.',
					'call_heading' => 'Need To Move Fast?',
					'call_text' => 'Book a call directly',
					'call_url' => [ 'url' => 'https://meetings.hubspot.com/rumore/grow-my-security-company-?uuid=fa51c8d1-f823-42df-91ac-85496638ef83' ],
				], 'contact-page-sync' ) ], 'contact-page-sync-col' ) ], 'contact-page-sync-sec' ),
			]
		),
		'press-media' => gms_get_press_media_elementor_template( $config ),
		'podcast' => gms_get_podcast_page_elementor_template( $config ),
		'case-studies' => gms_page_template(
			'Case Studies',
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-case-studies-listing', [
					'hero_eyebrow'  => 'Intelligence Reports',
					'hero_title'    => 'Case Studies',
					'hero_subtitle' => 'Real results from cybersecurity marketing strategies',
					'source'        => 'manual',
					'cards'         => gms_get_case_study_elementor_grid_cards(),
					'cta_title'     => 'Ready to build trust that drives revenue?',
					'cta_btn_text'  => 'Schedule a Free Consultation',
					'cta_btn_url'   => [ 'url' => home_url( '/contact-us/' ) ],
					'badge_3_label' => 'Efficiency Gain',
				], 'case-studies-listing-sync' ) ], 'case-studies-listing-sync-col' ) ], 'case-studies-listing-sync-sec' ),
			]
		),
	];
}

function gms_sync_elementor_template_to_page( WP_Post $page, array $template ): bool {
	$content       = $template['content'] ?? [];
	$page_settings = $template['page_settings'] ?? [];
	$new_json      = wp_json_encode( $content );
	$updated       = false;

	if ( ! is_string( $new_json ) ) {
		return false;
	}

	$current_json = get_post_meta( $page->ID, '_elementor_data', true );
	$current_json = is_string( $current_json ) ? trim( $current_json ) : '';

	if ( trim( $new_json ) !== $current_json ) {
		update_post_meta( $page->ID, '_elementor_data', wp_slash( $new_json ) );
		$updated = true;
	}

	$current_settings = get_post_meta( $page->ID, '_elementor_page_settings', true );
	if ( $current_settings !== $page_settings ) {
		update_post_meta( $page->ID, '_elementor_page_settings', $page_settings );
		$updated = true;
	}

	if ( 'builder' !== get_post_meta( $page->ID, '_elementor_edit_mode', true ) ) {
		update_post_meta( $page->ID, '_elementor_edit_mode', 'builder' );
		$updated = true;
	}

	if ( $updated ) {
		clean_post_cache( $page->ID );
	}

	return $updated;
}

function gms_get_front_page_for_elementor_sync(): ?WP_Post {
	$front_page_id = (int) get_option( 'page_on_front' );

	if ( $front_page_id > 0 ) {
		$page = get_post( $front_page_id );
		if ( $page instanceof WP_Post ) {
			return $page;
		}
	}

	$page = get_page_by_path( 'home' );

	return $page instanceof WP_Post ? $page : null;
}

function gms_sync_public_pages_elementor_templates(): void {
	if ( function_exists( 'gms_should_skip_runtime_elementor_sync' ) && gms_should_skip_runtime_elementor_sync() ) {
		return;
	}

	$sync_version = '2026-04-17-public-pages-elementor-v26';

	if ( get_option( 'gms_public_pages_elementor_sync_version' ) === $sync_version ) {
		return;
	}

	$templates = gms_get_public_page_elementor_templates();
	$config    = gms_get_demo_config();
	$updated   = false;

	foreach ( $templates as $page_key => $template ) {
		$page = 'home' === $page_key ? gms_get_front_page_for_elementor_sync() : get_page_by_path( $page_key );

		if ( ! ( $page instanceof WP_Post ) ) {
			continue;
		}

		$updated = gms_sync_elementor_template_to_page( $page, $template ) || $updated;
	}

	foreach ( (array) ( $config['services'] ?? [] ) as $service ) {
		$slug = (string) ( $service['slug'] ?? '' );

		if ( '' === $slug ) {
			continue;
		}

		$page = get_page_by_path( 'services/' . $slug, OBJECT, 'page' );

		if ( ! ( $page instanceof WP_Post ) ) {
			continue;
		}

		$template = gms_get_service_template( $service, $config, get_template_directory_uri(), home_url( '/' ) );
		$updated  = gms_sync_elementor_template_to_page( $page, $template ) || $updated;
	}

	foreach ( gms_get_industry_page_map() as $title => $industry ) {
		$slug = (string) ( $industry['slug'] ?? '' );

		if ( '' === $slug ) {
			continue;
		}

		$page = get_page_by_path( 'industries/' . $slug, OBJECT, 'page' );

		if ( ! ( $page instanceof WP_Post ) ) {
			continue;
		}

		$template = gms_get_industry_template( (string) $title, $slug, get_template_directory_uri(), home_url( '/' ) );
		$updated  = gms_sync_elementor_template_to_page( $page, $template ) || $updated;
	}

	/* ── Sync individual case study posts ── */
	$cs_posts = get_posts( [
		'post_type'      => 'gms_case_study',
		'posts_per_page' => 100,
		'post_status'    => 'publish',
		'fields'         => 'all',
	] );

	foreach ( $cs_posts as $cs_post ) {
		if ( ! ( $cs_post instanceof WP_Post ) ) {
			continue;
		}

		// Pull current meta to make it editable in Elementor immediately
		$challenge  = (string) get_post_meta( $cs_post->ID, 'gms_cs_challenge', true );
		$strategy   = (string) get_post_meta( $cs_post->ID, 'gms_cs_strategy', true );
		$execution  = (string) get_post_meta( $cs_post->ID, 'gms_cs_execution', true );
		$visual_url = (string) get_post_meta( $cs_post->ID, 'gms_cs_visual_url', true );
		$short_desc = (string) get_post_meta( $cs_post->ID, 'gms_cs_short_desc', true );
		$hero_image = function_exists( 'gms_get_case_study_image_url' ) ? gms_get_case_study_image_url( $cs_post, 'full' ) : '';

		if ( '' === $visual_url ) {
			$visual_url = $hero_image;
		}

		$results = [];
		for ( $i = 1; $i <= 3; $i++ ) {
			$val = (string) get_post_meta( $cs_post->ID, "gms_cs_result_{$i}_val", true );
			$lab = (string) get_post_meta( $cs_post->ID, "gms_cs_result_{$i}_lab", true );
			if ( '' !== $val || '' !== $lab ) {
				$results[] = [ 'val' => $val, 'lab' => $lab ];
			}
		}

		$cs_template = gms_page_template(
			$cs_post->post_title,
			[
				gms_section_node( [ gms_column_node( [ gms_widget_node( 'gms-case-study-single', [
					'hero_title'    => (string) $cs_post->post_title,
					'hero_subtitle' => (string) $short_desc,
					'hero_bg_image' => [ 'url' => (string) $hero_image ],
					'challenge'     => (string) $challenge,
					'strategy'      => (string) $strategy,
					'execution'     => (string) $execution,
					'results'       => $results,
					'visual_image'  => [ 'url' => (string) $visual_url ],
					'cta_title'     => 'Ready to achieve similar results?',
					'cta_text'      => 'Our tailored cyber-marketing strategies drive conversion through technical authority.',
					'cta_btn_text'  => 'Schedule a Free Consultation',
					'cta_btn_url'   => [ 'url' => (string) home_url( '/contact-us/' ) ],
				], 'cs-single-sync-' . $cs_post->ID ) ], 'cs-single-sync-col-' . $cs_post->ID ) ], 'cs-single-sync-sec-' . $cs_post->ID ),
			]
		);

		$updated = gms_sync_elementor_template_to_page( $cs_post, $cs_template ) || $updated;
	}

	if ( $updated && class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'gms_public_pages_elementor_sync_version', $sync_version );
}
add_action( 'admin_init', 'gms_sync_public_pages_elementor_templates', 24 );

function gms_sync_press_media_elementor_template(): void {
	$sync_version = '2026-04-18-press-media-template-v1';

	if ( get_option( 'gms_press_media_elementor_sync_version' ) === $sync_version ) {
		return;
	}

	$page = get_page_by_path( 'press-media' );

	if ( ! ( $page instanceof WP_Post ) ) {
		update_option( 'gms_press_media_elementor_sync_version', $sync_version, false );
		return;
	}

	$template = gms_get_press_media_elementor_template( gms_get_demo_config() );
	$updated  = gms_sync_elementor_template_to_page( $page, $template );

	if ( $updated && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'gms_press_media_elementor_sync_version', $sync_version, false );
}
add_action( 'init', 'gms_sync_press_media_elementor_template', 27 );

function gms_sync_podcast_elementor_template(): void {
	$sync_version = '2026-04-18-podcast-template-v1';

	if ( get_option( 'gms_podcast_elementor_sync_version' ) === $sync_version ) {
		return;
	}

	$page = get_page_by_path( 'podcast' );

	if ( ! ( $page instanceof WP_Post ) ) {
		update_option( 'gms_podcast_elementor_sync_version', $sync_version, false );
		return;
	}

	$template = gms_get_podcast_page_elementor_template( gms_get_demo_config() );
	$updated  = gms_sync_elementor_template_to_page( $page, $template );

	if ( $updated && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'gms_podcast_elementor_sync_version', $sync_version, false );
}
add_action( 'init', 'gms_sync_podcast_elementor_template', 28 );

function gms_migrate_stale_case_study_image_paths_in_elementor_data(): void {
	if ( function_exists( 'gms_should_skip_runtime_elementor_sync' ) && gms_should_skip_runtime_elementor_sync() ) {
		return;
	}

	$migration_version = '2026-04-10-case-study-image-paths-v1';

	if ( get_option( 'gms_case_study_image_path_migration_version' ) === $migration_version ) {
		return;
	}

	global $wpdb;

	$post_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s",
			'_elementor_data'
		)
	);

	$updated = false;

	foreach ( array_map( 'intval', (array) $post_ids ) as $post_id ) {
		if ( $post_id <= 0 ) {
			continue;
		}

		$json = get_post_meta( $post_id, '_elementor_data', true );

		if ( ! is_string( $json ) || '' === $json ) {
			continue;
		}

		$new_json = gms_replace_stale_case_study_image_paths( $json );

		if ( $new_json === $json ) {
			continue;
		}

		update_post_meta( $post_id, '_elementor_data', wp_slash( $new_json ) );
		clean_post_cache( $post_id );
		$updated = true;
	}

	if ( $updated && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'gms_case_study_image_path_migration_version', $migration_version );
}
add_action( 'init', 'gms_migrate_stale_case_study_image_paths_in_elementor_data', 23 );
