<?php
/**
 * Single template.
 *
 * @package GrowMySecurity
 */

get_header();

$is_elementor_single = function_exists( 'gms_post_has_elementor_content' ) ? gms_post_has_elementor_content( get_the_ID() ) : (bool) get_post_meta( get_the_ID(), '_elementor_edit_mode', true );
?>
<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>
	<?php $current_post = get_post(); ?>
	<?php if ( ! $current_post instanceof WP_Post ) { continue; } ?>

	<?php if ( $is_elementor_single ) : ?>
		<div class="gms-page-shell gms-page-shell--single">
			<div class="gms-page-content gms-page-content--elementor">
				<?php the_content(); ?>
			</div>
		</div>
	<?php else : ?>
		<?php
		$categories        = get_the_category( $current_post->ID );
		$primary_category  = ! empty( $categories ) ? $categories[0] : null;
		$read_time         = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $current_post ) ) ) / 220 ) );
		$hero_image        = function_exists( 'gms_get_post_card_image_url' ) ? gms_get_post_card_image_url( $current_post ) : '';
		$related_posts     = function_exists( 'gms_get_single_related_posts' ) ? gms_get_single_related_posts( $current_post->ID ) : [];
		$is_press_context  = has_category( 'press', $current_post );
		$is_podcast        = has_category( 'podcast', $current_post );
		$eyebrow           = $is_press_context ? __( 'Press Release', 'grow-my-security' ) : ( $is_podcast ? __( 'Podcast Feature', 'grow-my-security' ) : ( $primary_category->name ?? __( 'Insight', 'grow-my-security' ) ) );
		$excerpt           = has_excerpt( $current_post ) ? wp_strip_all_tags( get_the_excerpt( $current_post ) ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $current_post ) ), 28, '...' );
		$share_url         = urlencode( get_permalink( $current_post ) );
		$share_title       = urlencode( get_the_title( $current_post ) );
		$tag_names         = wp_get_post_tags( $current_post->ID, [ 'fields' => 'names' ] );
		$category_link     = $primary_category ? get_category_link( $primary_category ) : home_url( '/resources-insights/' );
		$category_label    = $primary_category ? $primary_category->name : __( 'Resources', 'grow-my-security' );
		$is_resource_blog  = ! $is_press_context && ! $is_podcast;

		if ( $is_resource_blog ) :
			$blueprint = function_exists( 'gms_get_resource_blog_blueprint' ) ? gms_get_resource_blog_blueprint( $current_post ) : [];
			$article_heading = (string) ( $blueprint['article_heading'] ?? __( 'Article', 'grow-my-security' ) );
			ob_start();
			?>
			<div class="gms-approved-search-panel">
				<p class="gms-approved-toolbar-note"><?php echo esc_html( $excerpt ); ?></p>
				<div class="gms-approved-meta-strip">
					<span><?php echo esc_html( get_the_date( 'M j, Y', $current_post ) ); ?></span>
					<span><?php echo esc_html( sprintf( _n( '%d min read', '%d min read', $read_time, 'grow-my-security' ), $read_time ) ); ?></span>
					<?php if ( $primary_category ) : ?>
						<span><?php echo esc_html( $primary_category->name ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<?php
			$support_html = ob_get_clean();
			?>
			<div class="gms-page-shell gms-approved-page gms-approved-page--single gms-approved-page--seo-blog">
				<div class="gms-container gms-approved-stack">
					<?php if ( function_exists( 'gms_render_internal_intro' ) ) : ?>
						<?php
						gms_render_internal_intro(
							[
								'eyebrow'      => $eyebrow,
								'title'        => get_the_title( $current_post ),
								'modifier'     => 'single',
								'support_html' => $support_html,
							]
						);
						?>
					<?php endif; ?>

					<?php if ( '' !== $hero_image ) : ?>
						<figure class="gms-seo-blog-hero gms-approved-article__hero">
							<img src="<?php echo esc_url( $hero_image ); ?>" alt="<?php echo esc_attr( get_the_title( $current_post ) ); ?>" decoding="async" loading="eager" fetchpriority="high">
						</figure>
					<?php endif; ?>

					<section class="gms-editorial-post-shell gms-seo-blog-shell">
						<div class="gms-seo-blog-grid">
							<article <?php post_class( 'gms-editorial-post-article gms-seo-blog-article' ); ?>>
								<div class="gms-editorial-post-article__meta">
									<span><?php echo esc_html( $category_label ); ?></span>
									<span><?php echo esc_html( get_the_date( 'M j, Y', $current_post ) ); ?></span>
									<span><?php echo esc_html( sprintf( _n( '%d min read', '%d min read', $read_time, 'grow-my-security' ), $read_time ) ); ?></span>
								</div>
								<h2><?php echo esc_html( $article_heading ); ?></h2>
								<div class="gms-seo-blog-content gms-rich-text">
									<?php if ( '' !== trim( (string) get_post_field( 'post_content', $current_post ) ) ) : ?>
										<?php the_content(); ?>
									<?php else : ?>
										<?php echo wp_kses_post( wpautop( $excerpt ) ); ?>
									<?php endif; ?>
									<?php wp_link_pages(); ?>
								</div>
							</article>

							<?php if ( function_exists( 'gms_get_resource_blog_sidebar_html' ) ) : ?>
								<?php echo wp_kses_post( gms_get_resource_blog_sidebar_html( $current_post, $blueprint ) ); ?>
							<?php endif; ?>
						</div>
					</section>

					<?php if ( ! empty( $related_posts ) ) : ?>
						<section class="gms-approved-editorial-section gms-related-posts gms-editorial-post-related">
							<div class="gms-related-posts__header">
								<div>
									<div class="gms-eyebrow"><?php esc_html_e( 'More Insights', 'grow-my-security' ); ?></div>
									<h2><?php esc_html_e( 'Keep exploring the journal.', 'grow-my-security' ); ?></h2>
								</div>
								<a class="gms-button-outline" href="<?php echo esc_url( home_url( '/resources-insights/' ) ); ?>"><?php esc_html_e( 'View All Posts', 'grow-my-security' ); ?></a>
							</div>
							<div class="gms-post-grid-widget gms-post-grid-widget--approved">
								<?php foreach ( $related_posts as $related_post ) : ?>
									<?php gms_render_post_card( $related_post ); ?>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<?php if ( function_exists( 'gms_render_money_cta' ) ) : ?>
						<?php
						gms_render_money_cta(
							[
								'title'  => __( 'Ready to build trust that drives revenue?', 'grow-my-security' ),
								'copy'   => __( 'You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.', 'grow-my-security' ),
								'button' => __( 'Schedule a Free Consultation', 'grow-my-security' ),
								'url'    => home_url( '/contact-us/' ),
							]
						);
						?>
					<?php endif; ?>
				</div>
			</div>
			<?php
			continue;
		endif;

		ob_start();
		?>
		<div class="gms-approved-search-panel">
			<p class="gms-approved-toolbar-note"><?php echo esc_html( $excerpt ); ?></p>
			<div class="gms-approved-meta-strip">
				<span><?php echo esc_html( get_the_date( 'M j, Y', $current_post ) ); ?></span>
				<span><?php echo esc_html( sprintf( _n( '%d min read', '%d min read', $read_time, 'grow-my-security' ), $read_time ) ); ?></span>
				<?php if ( $primary_category ) : ?>
					<span><?php echo esc_html( $primary_category->name ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$support_html = ob_get_clean();
		?>
		<div class="gms-page-shell gms-approved-page gms-approved-page--single">
			<div class="gms-container gms-approved-stack">
				<?php if ( function_exists( 'gms_render_internal_intro' ) ) : ?>
					<?php
					gms_render_internal_intro(
						[
							'eyebrow'      => $eyebrow,
							'title'        => get_the_title( $current_post ),
							'modifier'     => 'single',
							'support_html' => $support_html,
						]
					);
					?>
				<?php else : ?>
					<?php gms_render_page_hero( gms_get_single_post_hero_data( $current_post ) ); ?>
				<?php endif; ?>

				<article <?php post_class( 'gms-approved-article' ); ?>>
					<?php if ( '' !== $hero_image ) : ?>
						<div class="gms-approved-article__hero<?php echo esc_attr( ( $is_press_context || $is_podcast ) ? ' gms-approved-article__hero--press' : '' ); ?>">
							<img src="<?php echo esc_url( $hero_image ); ?>" alt="<?php echo esc_attr( get_the_title( $current_post ) ); ?>" decoding="async" loading="eager" fetchpriority="high">
						</div>
					<?php endif; ?>

					<div class="gms-approved-article__body">
						<div class="gms-approved-article__content gms-rich-text">
							<?php the_content(); ?>
							<?php wp_link_pages(); ?>
						</div>

						<aside class="gms-approved-article__aside">
							<div class="gms-approved-side-card">
								<h3><?php esc_html_e( 'Article Details', 'grow-my-security' ); ?></h3>
								<div class="gms-approved-meta-list">
									<div class="gms-approved-meta-item">
										<span><?php esc_html_e( 'Published', 'grow-my-security' ); ?></span>
										<strong><?php echo esc_html( get_the_date( 'F j, Y', $current_post ) ); ?></strong>
									</div>
									<div class="gms-approved-meta-item">
										<span><?php esc_html_e( 'Reading Time', 'grow-my-security' ); ?></span>
										<strong><?php echo esc_html( sprintf( _n( '%d minute', '%d minutes', $read_time, 'grow-my-security' ), $read_time ) ); ?></strong>
									</div>
									<div class="gms-approved-meta-item">
										<span><?php esc_html_e( 'Filed Under', 'grow-my-security' ); ?></span>
										<a href="<?php echo esc_url( $category_link ); ?>"><?php echo esc_html( $category_label ); ?></a>
									</div>
								</div>
							</div>

							<div class="gms-approved-side-card">
								<h3><?php esc_html_e( 'Share This', 'grow-my-security' ); ?></h3>
								<div class="gms-approved-share">
									<a href="mailto:?subject=<?php echo esc_attr( $share_title ); ?>&body=<?php echo esc_attr( $share_url ); ?>"><?php esc_html_e( 'Email', 'grow-my-security' ); ?></a>
									<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $share_url ); ?>" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'LinkedIn', 'grow-my-security' ); ?></a>
									<a href="https://twitter.com/intent/tweet?url=<?php echo esc_attr( $share_url ); ?>&text=<?php echo esc_attr( $share_title ); ?>" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'X', 'grow-my-security' ); ?></a>
								</div>
								<?php if ( ! empty( $tag_names ) ) : ?>
									<div class="gms-approved-meta-strip gms-approved-meta-strip--compact">
										<?php foreach ( array_slice( $tag_names, 0, 4 ) as $tag_name ) : ?>
											<span><?php echo esc_html( $tag_name ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>

							<div class="gms-approved-side-card gms-approved-side-card--accent">
								<h3><?php esc_html_e( 'Need a Trust-Led Growth Partner?', 'grow-my-security' ); ?></h3>
								<p><?php esc_html_e( 'We help security brands turn technical expertise into visible authority, stronger credibility, and qualified demand.', 'grow-my-security' ); ?></p>
								<a class="gms-button" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Talk to the Team', 'grow-my-security' ); ?></a>
							</div>
						</aside>
					</div>
				</article>

				<?php if ( ! empty( $related_posts ) ) : ?>
					<section class="gms-approved-editorial-section gms-related-posts">
						<div class="gms-related-posts__header">
							<div>
								<div class="gms-eyebrow"><?php echo esc_html( $is_press_context ? __( 'More Press', 'grow-my-security' ) : __( 'More Insights', 'grow-my-security' ) ); ?></div>
								<h2><?php echo esc_html( $is_press_context ? __( 'Continue exploring recent coverage.', 'grow-my-security' ) : __( 'Keep exploring the journal.', 'grow-my-security' ) ); ?></h2>
							</div>
							<a class="gms-button-outline" href="<?php echo esc_url( home_url( '/resources-insights/' ) ); ?>"><?php esc_html_e( 'View All Posts', 'grow-my-security' ); ?></a>
						</div>
						<div class="gms-post-grid-widget gms-post-grid-widget--approved">
							<?php foreach ( $related_posts as $related_post ) : ?>
								<?php gms_render_post_card( $related_post ); ?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( function_exists( 'gms_render_money_cta' ) ) : ?>
					<?php
					gms_render_money_cta(
						[
							'title'  => $is_press_context ? __( 'Ready to shape a stronger security story?', 'grow-my-security' ) : __( 'Ready to build trust that drives revenue?', 'grow-my-security' ),
							'copy'   => __( 'You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.', 'grow-my-security' ),
							'button' => __( 'Schedule a Free Consultation', 'grow-my-security' ),
							'url'    => home_url( '/contact-us/' ),
						]
					);
					?>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
<?php endwhile; ?>
<?php get_footer(); ?>
