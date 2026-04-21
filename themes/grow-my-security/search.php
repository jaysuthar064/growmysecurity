<?php
/**
 * Search results template.
 *
 * @package GrowMySecurity
 */

get_header();

global $wp_query;

$results_count        = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
$editorial_categories = get_categories(
	[
		'hide_empty' => true,
		'exclude'    => array_values( array_filter( [ get_cat_ID( 'Uncategorized' ) ] ) ),
	]
);

ob_start();
?>
<div class="gms-approved-search-panel">
	<p class="gms-approved-toolbar-note"><?php echo esc_html( 0 === $results_count ? __( 'We could not find a direct match, but you can refine your query or explore the main editorial categories below.', 'grow-my-security' ) : sprintf( _n( 'We found %d relevant result across the site.', 'We found %d relevant results across the site.', $results_count, 'grow-my-security' ), $results_count ) ); ?></p>
	<?php echo get_search_form( false ); ?>
</div>
<?php
$support_html = ob_get_clean();
?>
<div class="gms-page-shell gms-approved-page gms-approved-page--search">
	<div class="gms-container gms-approved-stack">
		<?php if ( function_exists( 'gms_render_internal_intro' ) ) : ?>
			<?php
			gms_render_internal_intro(
				[
					'eyebrow'      => __( 'Search Results', 'grow-my-security' ),
					'title'        => sprintf( __( 'Results for "%s"', 'grow-my-security' ), get_search_query() ),
					'lede'         => __( 'Search across editorial resources, press coverage, podcast features, and site pages from the approved Grow My Security system.', 'grow-my-security' ),
					'modifier'     => 'search',
					'support_html' => $support_html,
				]
			);
			?>
		<?php else : ?>
			<?php $hero_args = function_exists( 'gms_get_search_hero_data' ) ? gms_get_search_hero_data() : []; ?>
			<?php $hero_args['extra'] = get_search_form( false ); ?>
			<?php gms_render_page_hero( $hero_args ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $editorial_categories ) ) : ?>
			<div class="gms-approved-resource-toolbar">
				<div class="gms-approved-filter-list">
					<a class="is-active" href="<?php echo esc_url( home_url( '/resources-insights/' ) ); ?>"><?php esc_html_e( 'Browse All', 'grow-my-security' ); ?></a>
					<?php foreach ( $editorial_categories as $editorial_category ) : ?>
						<a href="<?php echo esc_url( get_category_link( $editorial_category ) ); ?>"><?php echo esc_html( $editorial_category->name ); ?></a>
					<?php endforeach; ?>
				</div>
				<div class="gms-approved-meta-strip">
					<span><?php echo esc_html( sprintf( _n( '%d result', '%d results', $results_count, 'grow-my-security' ), $results_count ) ); ?></span>
					<span><?php esc_html_e( 'Search the full site', 'grow-my-security' ); ?></span>
				</div>
			</div>
		<?php endif; ?>

		<section class="gms-approved-editorial-section">
			<?php if ( have_posts() ) : ?>
				<div class="gms-post-grid-widget gms-post-grid-widget--approved">
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
						<?php gms_render_post_card( get_post() ); ?>
					<?php endwhile; ?>
				</div>
				<div class="gms-approved-pagination">
					<?php the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => __( 'Previous', 'grow-my-security' ), 'next_text' => __( 'Next', 'grow-my-security' ) ] ); ?>
				</div>
			<?php else : ?>
				<div class="gms-empty-state">
					<h2><?php esc_html_e( 'No exact matches found.', 'grow-my-security' ); ?></h2>
					<p><?php esc_html_e( 'Try a broader keyword, search for a service category, or jump into the sections below.', 'grow-my-security' ); ?></p>
					<div class="gms-empty-state__actions">
						<a class="gms-button" href="<?php echo esc_url( home_url( '/resources-insights/' ) ); ?>"><?php esc_html_e( 'Browse Resources', 'grow-my-security' ); ?></a>
						<a class="gms-button-outline" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Explore Services', 'grow-my-security' ); ?></a>
					</div>
				</div>
			<?php endif; ?>
		</section>

		<?php if ( function_exists( 'gms_render_money_cta' ) ) : ?>
			<?php gms_render_money_cta(); ?>
		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
