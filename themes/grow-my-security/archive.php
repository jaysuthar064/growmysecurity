<?php
/**
 * Archive template.
 *
 * @package GrowMySecurity
 */

get_header();

global $wp_query;

$results_count        = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
$archive_title        = wp_strip_all_tags( get_the_archive_title() );
$archive_description  = trim( wp_strip_all_tags( get_the_archive_description() ) );
$editorial_categories = get_categories(
	[
		'hide_empty' => true,
		'exclude'    => array_values( array_filter( [ get_cat_ID( 'Uncategorized' ) ] ) ),
	]
);
$eyebrow              = __( 'Archive', 'grow-my-security' );

if ( is_category() ) {
	$eyebrow = __( 'Category', 'grow-my-security' );
} elseif ( is_tag() ) {
	$eyebrow = __( 'Tag', 'grow-my-security' );
} elseif ( is_author() ) {
	$eyebrow = __( 'Author', 'grow-my-security' );
} elseif ( is_date() ) {
	$eyebrow = __( 'Date Archive', 'grow-my-security' );
}

if ( '' === $archive_description ) {
	$archive_description = __( 'Editorial coverage and practical insight for security teams building visibility, credibility, and higher-conviction demand.', 'grow-my-security' );
}

ob_start();
?>
<div class="gms-approved-search-panel">
	<p class="gms-approved-toolbar-note"><?php echo esc_html( sprintf( _n( '%d published story in this archive.', '%d published stories in this archive.', $results_count, 'grow-my-security' ), $results_count ) ); ?></p>
	<?php echo get_search_form( false ); ?>
</div>
<?php
$support_html = ob_get_clean();
?>
<div class="gms-page-shell gms-approved-page gms-approved-page--archive">
	<div class="gms-container gms-approved-stack">
		<?php if ( function_exists( 'gms_render_internal_intro' ) ) : ?>
			<?php
			gms_render_internal_intro(
				[
					'eyebrow'      => $eyebrow,
					'title'        => $archive_title,
					'lede'         => $archive_description,
					'modifier'     => 'archive',
					'support_html' => $support_html,
				]
			);
			?>
		<?php else : ?>
			<?php gms_render_page_hero( gms_get_archive_hero_data() ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $editorial_categories ) ) : ?>
			<div class="gms-approved-resource-toolbar">
				<div class="gms-approved-filter-list">
					<a class="<?php echo esc_attr( is_category() ? '' : 'is-active' ); ?>" href="<?php echo esc_url( home_url( '/resources-insights/' ) ); ?>"><?php esc_html_e( 'All', 'grow-my-security' ); ?></a>
					<?php foreach ( $editorial_categories as $editorial_category ) : ?>
						<a class="<?php echo esc_attr( is_category( $editorial_category->term_id ) ? 'is-active' : '' ); ?>" href="<?php echo esc_url( get_category_link( $editorial_category ) ); ?>"><?php echo esc_html( $editorial_category->name ); ?></a>
					<?php endforeach; ?>
				</div>
				<div class="gms-approved-meta-strip">
					<span><?php echo esc_html( sprintf( _n( '%d story', '%d stories', $results_count, 'grow-my-security' ), $results_count ) ); ?></span>
					<span><?php echo esc_html( is_category() || is_tag() ? __( 'Filtered archive', 'grow-my-security' ) : __( 'Editorial archive', 'grow-my-security' ) ); ?></span>
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
					<h2><?php esc_html_e( 'No archive entries are available yet.', 'grow-my-security' ); ?></h2>
					<p><?php esc_html_e( 'Browse our services or contact the team if you are looking for something specific.', 'grow-my-security' ); ?></p>
					<div class="gms-empty-state__actions">
						<a class="gms-button" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Explore Services', 'grow-my-security' ); ?></a>
						<a class="gms-button-outline" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'grow-my-security' ); ?></a>
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
