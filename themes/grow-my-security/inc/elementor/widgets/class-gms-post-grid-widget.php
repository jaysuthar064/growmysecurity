<?php
/**
 * Post grid widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Grid_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-post-grid';
	}

	public function get_title() {
		return __( 'GMS Post Grid', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	protected function register_controls() {
		$config = \gms_get_demo_config();
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'grow-my-security' ),
			]
		);
		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Blogs',
				'title'       => 'Resources & Insights',
				'description' => 'Thoughtful articles, commentary, and insight built specifically for high-trust markets.',
			]
		);
		$this->add_control(
			'layout_style',
			[
				'label'   => __( 'Layout Style', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => [
					'standard'     => __( 'Standard Grid', 'grow-my-security' ),
					'journal'      => __( 'Homepage Journal', 'grow-my-security' ),
					'case_studies' => __( 'Homepage Case Studies', 'grow-my-security' ),
				],
			]
		);
		$this->add_control( 'button_text', [ 'label' => __( 'Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$this->add_control( 'button_url', [ 'label' => __( 'Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$this->add_control( 'card_button_text', [ 'label' => __( 'Card CTA Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Read article', 'grow-my-security' ) ] );

		$repeater = new Repeater();
		$repeater->add_control( 'meta', [ 'label' => __( 'Meta', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'label_block' => true ] );
		$repeater->add_control( 'excerpt', [ 'label' => __( 'Excerpt', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$repeater->add_control( 'image', [ 'label' => __( 'Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA ] );
		$repeater->add_control( 'url', [ 'label' => __( 'URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$repeater->add_control( 'metric_value', [ 'label' => __( 'Metric Value', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$repeater->add_control( 'metric_label', [ 'label' => __( 'Metric Label', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$defaults = array_map(
			static function ( $post, $index ) {
				$meta = [ 'Website Design', 'AI Trend', 'AI Trend' ];

				return [
					'meta'         => $post['meta'] ?? ( $meta[ $index ] ?? 'Blog' ),
					'title'        => $post['title'],
					'excerpt'      => $post['excerpt'],
					'image'        => [ 'url' => $post['image'] ],
					'url'          => [ 'url' => home_url( '/' . $post['slug'] . '/' ) ],
					'metric_value' => '',
					'metric_label' => '',
				];
			},
			$config['blog_posts'],
			array_keys( $config['blog_posts'] )
		);
		$this->add_control(
			'items',
			[
				'label'       => __( 'Posts', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $defaults,
				'title_field' => '{{{ title }}}',
			]
		);
		$this->end_controls_section();

		$this->add_widget_style_controls( 'post_grid_section_style', '{{WRAPPER}} .gms-widget' );
		$this->add_box_style_controls( 'post_box', '{{WRAPPER}} .gms-post-card' );
	}

	private function get_layout_style( array $settings ): string {
		$layout = (string) ( $settings['layout_style'] ?? '' );

		if ( in_array( $layout, [ 'standard', 'journal', 'case_studies' ], true ) ) {
			return $layout;
		}

		$title = strtolower( trim( (string) ( $settings['title'] ?? '' ) ) );

		if ( false !== strpos( $title, 'proven results for security brands' ) ) {
			return 'case_studies';
		}

		if ( false !== strpos( $title, 'updated journal' ) ) {
			return 'journal';
		}

		return 'standard';
	}

	private function render_home_journal( array $settings ): void {
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--journal">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
					<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--blogs" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
					<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
					<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				</div>
				<div class="gms-homepage-journal__grid">
					<?php foreach ( $settings['items'] as $item ) : ?>
						<?php $url = $item['url']['url'] ?? ''; ?>
						<article class="gms-homepage-journal__card">
							<a href="<?php echo esc_url( $url ?: '#' ); ?>">
								<?php if ( ! empty( $item['image']['url'] ) ) : ?>
									<?php $this->render_media_image( $item['image'], [ 'alt' => (string) ( $item['title'] ?? '' ), 'size' => 'large' ] ); ?>
								<?php endif; ?>
								<div class="gms-homepage-journal__meta"><?php echo esc_html( $item['meta'] ?? '' ); ?></div>
								<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
				<?php if ( $this->has_valid_link( $settings['button_url'] ?? [] ) && ! empty( $settings['button_text'] ) ) : ?>
					<div class="gms-homepage-journal__actions">
						<?php $this->add_render_attribute( 'post-grid-home-button-' . $this->get_id(), 'class', 'gms-homepage-button gms-homepage-button--primary gms-homepage-button--fixed' ); ?>
						<?php $this->add_link_attributes( 'post-grid-home-button-' . $this->get_id(), $settings['button_url'] ?? [] ); ?>
						<a <?php echo $this->get_render_attribute_string( 'post-grid-home-button-' . $this->get_id() ); ?>><span><?php echo esc_html( $settings['button_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	private function render_home_case_studies( array $settings ): void {
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--case-studies">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-section-heading gms-homepage-section-heading--center gms-homepage-section-heading--wide">
					<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--solution" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
					<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
					<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
				</div>
				<div class="gms-cs-grid">
					<?php foreach ( $settings['items'] as $index => $item ) : ?>
						<?php $item_url = $item['url']['url'] ?? ''; ?>
						<article class="gms-cs-card">
							<?php if ( $this->has_valid_link( $item['url'] ?? [] ) ) : ?>
								<?php $this->add_render_attribute( 'post-case-link-' . $this->get_id() . '-' . $index, 'class', 'gms-cs-card-overlay-link' ); ?>
								<?php $this->add_link_attributes( 'post-case-link-' . $this->get_id() . '-' . $index, $item['url'] ); ?>
								<a <?php echo $this->get_render_attribute_string( 'post-case-link-' . $this->get_id() . '-' . $index ); ?> aria-label="<?php echo esc_attr( $item['title'] ?? '' ); ?>"></a>
							<?php endif; ?>
							<div class="gms-cs-card__image">
								<?php if ( ! empty( $item['image']['url'] ) ) : ?>
									<?php $this->render_media_image( $item['image'], [ 'size' => 'large', 'alt' => (string) ( $item['title'] ?? '' ) ] ); ?>
								<?php else : ?>
									<div class="gms-cs-card__placeholder" aria-hidden="true" style="background:#222;width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#444;min-height:200px;">
										<svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
									</div>
								<?php endif; ?>
								<div class="gms-cs-card__metric-float">
									<span class="gms-cs-card__metric-value"><?php echo esc_html( $item['metric_value'] ?? '' ); ?></span>
									<span class="gms-cs-card__metric-label"><?php echo esc_html( $item['metric_label'] ?? '' ); ?></span>
								</div>
							</div>
							<div class="gms-cs-card__content">
								<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
								<p><?php echo esc_html( $item['excerpt'] ?? '' ); ?></p>
								<?php if ( $item_url ) : ?>
									<a href="<?php echo esc_url( $item_url ); ?>" class="gms-cs-card__link"><?php echo esc_html( $settings['card_button_text'] ?? __( 'View Case Study', 'grow-my-security' ) ); ?></a>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
				<?php if ( $this->has_valid_link( $settings['button_url'] ?? [] ) && ! empty( $settings['button_text'] ) ) : ?>
					<div class="gms-homepage-journal__actions">
						<?php $this->add_render_attribute( 'post-grid-case-button-' . $this->get_id(), 'class', 'gms-homepage-button gms-homepage-button--primary gms-homepage-button--fixed' ); ?>
						<?php $this->add_link_attributes( 'post-grid-case-button-' . $this->get_id(), $settings['button_url'] ?? [] ); ?>
						<a <?php echo $this->get_render_attribute_string( 'post-grid-case-button-' . $this->get_id() ); ?>><span><?php echo esc_html( $settings['button_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}


	private function render_standard_grid( array $settings, string $card_button_text ): void {
		?>
		<section class="gms-widget gms-posts-section">
			<?php $this->render_section_heading( $settings ); ?>
			<div class="gms-post-grid-widget gms-post-grid-widget--approved">
				<?php foreach ( $settings['items'] as $index => $item ) : ?>
					<?php $has_item_link = $this->has_valid_link( $item['url'] ?? [] ); ?>
					<article class="gms-post-card">
						<?php if ( $has_item_link ) : ?>
							<?php $this->add_render_attribute( 'post-card-media-' . $this->get_id() . '-' . $index, 'class', 'gms-post-card__media' ); ?>
							<?php $this->add_link_attributes( 'post-card-media-' . $this->get_id() . '-' . $index, $item['url'] ); ?>
							<a <?php echo $this->get_render_attribute_string( 'post-card-media-' . $this->get_id() . '-' . $index ); ?>>
								<?php if ( ! empty( $item['image']['url'] ) ) : ?>
									<?php $this->render_media_image( $item['image'], [ 'size' => 'gms-card' ] ); ?>
								<?php endif; ?>
							</a>
						<?php else : ?>
							<div class="gms-post-card__media">
								<?php if ( ! empty( $item['image']['url'] ) ) : ?>
									<?php $this->render_media_image( $item['image'], [ 'size' => 'gms-card' ] ); ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<div class="gms-post-card__body">
							<?php if ( ! empty( $item['meta'] ) ) : ?><div class="gms-post-card__meta"><?php echo esc_html( $item['meta'] ); ?></div><?php endif; ?>
							<h3 class="gms-post-card__title">
								<?php if ( $has_item_link ) : ?>
									<?php $this->render_link( 'post-card-title-' . $this->get_id() . '-' . $index, $item['url'] ?? [], $item['title'] ?? '', 'gms-post-card__title-link' ); ?>
								<?php else : ?>
									<?php echo esc_html( $item['title'] ?? '' ); ?>
								<?php endif; ?>
							</h3>
							<p><?php echo esc_html( $item['excerpt'] ?? '' ); ?></p>
							<?php if ( $has_item_link && '' !== trim( $card_button_text ) ) : ?>
								<div class="gms-post-card__actions">
									<?php $this->render_link( 'post-card-cta-' . $this->get_id() . '-' . $index, $item['url'] ?? [], $card_button_text, 'gms-post-card__cta' ); ?>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
			<?php if ( $this->has_valid_link( $settings['button_url'] ?? [] ) && ! empty( $settings['button_text'] ) ) : ?>
				<div class="gms-posts-section__actions">
					<?php $this->render_link( 'post-grid-button-' . $this->get_id(), $settings['button_url'] ?? [], $settings['button_text'] ?? '', 'gms-button' ); ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}

	private function render_standard_grid_items_only( array $items, string $card_button_text ): void {
		?>
		<?php foreach ( $items as $index => $item ) : ?>
			<?php $has_item_link = $this->has_valid_link( $item['url'] ?? [] ); ?>
			<article class="gms-post-card">
				<?php if ( $has_item_link ) : ?>
					<?php $this->add_render_attribute( 'post-card-media-inline-' . $this->get_id() . '-' . $index, 'class', 'gms-post-card__media' ); ?>
					<?php $this->add_link_attributes( 'post-card-media-inline-' . $this->get_id() . '-' . $index, $item['url'] ); ?>
					<a <?php echo $this->get_render_attribute_string( 'post-card-media-inline-' . $this->get_id() . '-' . $index ); ?>>
						<?php if ( ! empty( $item['image']['url'] ) ) : ?>
							<?php $this->render_media_image( $item['image'], [ 'size' => 'gms-card' ] ); ?>
						<?php endif; ?>
					</a>
				<?php else : ?>
					<div class="gms-post-card__media">
						<?php if ( ! empty( $item['image']['url'] ) ) : ?>
							<?php $this->render_media_image( $item['image'], [ 'size' => 'gms-card' ] ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="gms-post-card__body">
					<?php if ( ! empty( $item['meta'] ) ) : ?><div class="gms-post-card__meta"><?php echo esc_html( $item['meta'] ); ?></div><?php endif; ?>
					<h3 class="gms-post-card__title">
						<?php if ( $has_item_link ) : ?>
							<?php $this->render_link( 'post-card-title-inline-' . $this->get_id() . '-' . $index, $item['url'] ?? [], $item['title'] ?? '', 'gms-post-card__title-link' ); ?>
						<?php else : ?>
							<?php echo esc_html( $item['title'] ?? '' ); ?>
						<?php endif; ?>
					</h3>
					<p><?php echo esc_html( $item['excerpt'] ?? '' ); ?></p>
					<?php if ( $has_item_link && '' !== trim( $card_button_text ) ) : ?>
						<div class="gms-post-card__actions">
							<?php $this->render_link( 'post-card-cta-inline-' . $this->get_id() . '-' . $index, $item['url'] ?? [], $card_button_text, 'gms-post-card__cta' ); ?>
						</div>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Render the full resources page layout.
	 *
	 * This produces the EXACT same HTML as gms_render_resources_page_content()
	 * so the Elementor editor preview matches the live site perfectly.
	 * Heading text comes from widget settings = editable in Elementor.
	 */
	private function render_resources_layout( array $settings ): void {
		$eyebrow     = $settings['eyebrow'] ?? __( 'Blog', 'grow-my-security' );
		$title       = $settings['title'] ?? __( 'Resources & Insights', 'grow-my-security' );
		$description = $settings['description'] ?? __( 'News, commentary, and insight built specifically for high-trust markets.', 'grow-my-security' );

		// Query dynamic blog posts.
		$filter = '';
		$search = '';
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$filter = sanitize_title( wp_unslash( $_GET['gms_filter'] ?? '' ) );
			$search = sanitize_text_field( wp_unslash( $_GET['gms_search'] ?? '' ) );
		}

		$page_post = get_post();
		$permalink = $page_post instanceof \WP_Post ? get_permalink( $page_post ) : home_url( '/resources-insights/' );

		$categories = get_categories( [
			'hide_empty' => true,
			'exclude'    => array_values( array_filter( [ get_cat_ID( 'Press' ), get_cat_ID( 'Podcast' ), get_cat_ID( 'Uncategorized' ) ] ) ),
		] );

		$resources_q = function_exists( 'gms_get_resources_query' )
			? gms_get_resources_query( $filter, $search )
			: new \WP_Query( [
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 9,
			] );
		$manual_items = is_array( $settings['items'] ?? null ) ? $settings['items'] : [];
		$use_manual_items = '' === $filter && '' === $search && ! empty( $manual_items );

		// Render intro section (same as gms_render_internal_intro).
		?>
		<section class="gms-approved-intro gms-approved-intro--resources">
			<div class="gms-approved-intro__grid">
				<div class="gms-approved-intro__main">
					<?php if ( '' !== trim( $eyebrow ) ) : ?>
						<div class="gms-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
					<?php endif; ?>
					<h1><?php echo esc_html( $title ); ?></h1>
					<?php if ( '' !== trim( $description ) ) : ?>
						<div class="gms-approved-intro__lede">
							<p><?php echo esc_html( $description ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="gms-approved-resources-shell">
			<div class="gms-approved-resource-toolbar">
				<div class="gms-approved-filter-list">
					<a class="<?php echo esc_attr( '' === $filter ? 'is-active' : '' ); ?>"
						href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'All', 'grow-my-security' ); ?></a>
					<?php foreach ( $categories as $category ) : ?>
						<a class="<?php echo esc_attr( $filter === $category->slug ? 'is-active' : '' ); ?>"
							href="<?php echo esc_url( add_query_arg( [ 'gms_filter' => $category->slug ], $permalink ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
					<?php endforeach; ?>
				</div>
				<form class="gms-approved-search" method="get" action="<?php echo esc_url( $permalink ); ?>">
					<?php if ( '' !== $filter ) : ?>
						<input type="hidden" name="gms_filter" value="<?php echo esc_attr( $filter ); ?>">
					<?php endif; ?>
					<input type="search" name="gms_search" value="<?php echo esc_attr( $search ); ?>"
						placeholder="<?php esc_attr_e( 'Search articles...', 'grow-my-security' ); ?>">
				</form>
			</div>
			<div class="gms-post-grid-widget gms-post-grid-widget--approved">
				<?php if ( $use_manual_items ) : ?>
					<?php $this->render_standard_grid_items_only( $manual_items, (string) ( $settings['card_button_text'] ?? __( 'Read article', 'grow-my-security' ) ) ); ?>
				<?php else : ?>
					<?php
					$display_posts = $resources_q->posts;
					if ( empty( $filter ) && '' === $search && count( $display_posts ) > 0 && count( $display_posts ) < 6 ) {
						$original = $display_posts;
						while ( count( $display_posts ) < 6 ) {
							foreach ( $original as $repeat_post ) {
								$display_posts[] = $repeat_post;
								if ( count( $display_posts ) >= 6 ) {
									break 2;
								}
							}
						}
					}

					foreach ( $display_posts as $display_post ) {
						if ( function_exists( 'gms_render_post_card' ) ) {
							gms_render_post_card( $display_post );
						}
					}
					?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	protected function render() {
		$settings         = $this->get_settings();
		$card_button_text = $settings['card_button_text'] ?? __( 'Read article', 'grow-my-security' );
		$layout_style     = $this->get_layout_style( $settings );

		if ( 'journal' === $layout_style ) {
			$this->render_home_journal( $settings );
			return;
		}

		if ( 'case_studies' === $layout_style ) {
			$this->render_home_case_studies( $settings );
			return;
		}

		// Detect resources page context and render the full resources layout.
		$page_slug = '';
		$current_post = get_post();
		if ( $current_post instanceof \WP_Post ) {
			$page_slug = $current_post->post_name;
		}

		if ( 'resources-insights' === $page_slug && 'standard' === $layout_style ) {
			$this->render_resources_layout( $settings );
			return;
		}

		$this->render_standard_grid( $settings, $card_button_text );
	}
}
