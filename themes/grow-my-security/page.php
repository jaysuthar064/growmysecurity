<?php
/**
 * Generic page template.
 *
 * @package GrowMySecurity
 */

get_header();

$is_elementor_page = function_exists( 'gms_post_has_elementor_content' ) ? gms_post_has_elementor_content( get_the_ID() ) : (bool) get_post_meta( get_the_ID(), '_elementor_edit_mode', true );
$contact_status    = isset( $_GET['gms_contact'] ) ? sanitize_key( wp_unslash( $_GET['gms_contact'] ) ) : '';
?>
	<?php if ( is_page( [ 'privacy-policy', 'terms-conditions' ] ) ) : ?>
		<div class="gms-legal-progress" id="legal-progress"></div>
	<?php endif; ?>

<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>
	<?php $current_page = get_post(); ?>
	<?php if ( ! $current_page instanceof WP_Post ) { continue; } ?>

	<?php if ( function_exists( 'gms_is_theme_controlled_public_route' ) && gms_is_theme_controlled_public_route( $current_page ) ) : ?>
		<?php gms_render_theme_controlled_page( $current_page ); ?>
	<?php else : ?>
		<?php
		$is_legal_page      = in_array( $current_page->post_name, [ 'privacy-policy', 'terms-conditions' ], true );
		$show_page_intro    = function_exists( 'gms_should_render_page_hero' ) ? gms_should_render_page_hero( $current_page, $is_elementor_page ) : ! $is_elementor_page;
		$page_shell_classes = [ 'gms-page-shell' ];
		$page_shell_classes[] = $is_elementor_page ? 'gms-page-shell--standard' : 'gms-approved-page gms-approved-page--standard';
		if ( $show_page_intro ) { $page_shell_classes[] = 'gms-page-shell--has-intro'; }
		if ( $is_legal_page ) { $page_shell_classes[] = 'gms-legal-page'; }
		?>
		<div class="<?php echo esc_attr( implode( ' ', $page_shell_classes ) ); ?>">
			<?php if ( $is_legal_page ) : ?>
				<header class="gms-legal-hero">
					<div class="gms-container">
						<h1><?php the_title(); ?></h1>
						<p><?php echo esc_html( 'privacy-policy' === $current_page->post_name ? __( 'Your privacy and trust are important to us.', 'grow-my-security' ) : __( 'Transparency and clear expectations for our cybersecurity marketing services.', 'grow-my-security' ) ); ?></p>
					</div>
				</header>
			<?php endif; ?>

			<?php if ( 'success' === $contact_status || 'error' === $contact_status ) : ?>
				<div class="gms-container gms-approved-stack">
					<div class="gms-notice <?php echo 'success' === $contact_status ? 'gms-notice--success' : 'gms-notice--error'; ?>">
						<?php echo esc_html( 'success' === $contact_status ? __( 'Thanks. Your request has been sent.', 'grow-my-security' ) : __( 'Something went wrong while sending your request. Please try again.', 'grow-my-security' ) ); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $is_elementor_page ) : ?>
				<?php
				// Skip the legacy page hero when the Elementor content already renders the page intro.
				$skip_elementor_hero = in_array( $current_page->post_name, [ 'resources-insights', 'contact-us', 'faq', 'faqs' ], true );
				?>
				<?php if ( $show_page_intro && ! $is_legal_page && ! $skip_elementor_hero ) : ?>
					<div class="gms-container gms-page-stack">
						<?php gms_render_page_hero( gms_get_page_hero_data( $current_page ) ); ?>
					</div>
				<?php endif; ?>
				<div class="gms-page-content gms-page-content--elementor">
					<div class="<?php echo $is_legal_page ? 'gms-legal-container gms-legal-content' : ''; ?>">
						<?php the_content(); ?>
					</div>
				</div>
			<?php else : ?>
				<?php
				$excerpt = has_excerpt( $current_page ) ? wp_strip_all_tags( get_the_excerpt( $current_page ) ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $current_page ) ), 26, '...' );
				ob_start();
				?>
				<div class="gms-approved-search-panel">
					<p class="gms-approved-toolbar-note"><?php echo esc_html( $excerpt ); ?></p>
					<div class="gms-approved-meta-strip">
						<span><?php echo esc_html( get_the_modified_date( 'M j, Y', $current_page ) ); ?></span>
						<span><?php esc_html_e( 'Standard Page', 'grow-my-security' ); ?></span>
					</div>
				</div>
				<?php
				$support_html = ob_get_clean();
				?>
				<div class="gms-container <?php echo $is_legal_page ? 'gms-legal-container' : 'gms-approved-stack'; ?>">
					<?php if ( $show_page_intro && ! $is_legal_page ) : ?>
						<?php if ( function_exists( 'gms_render_internal_intro' ) ) : ?>
							<?php
							gms_render_internal_intro(
								[
									'eyebrow'      => __( 'Page', 'grow-my-security' ),
									'title'        => get_the_title( $current_page ),
									'lede'         => $excerpt,
									'modifier'     => 'standard',
									'support_html' => $support_html,
								]
							);
							?>
						<?php else : ?>
							<?php gms_render_page_hero( gms_get_page_hero_data( $current_page ) ); ?>
						<?php endif; ?>
					<?php endif; ?>

					<article <?php post_class( $is_legal_page ? 'gms-legal-content' : 'gms-approved-standard-panel gms-rich-text' ); ?>>
						<?php the_content(); ?>
					</article>

					<?php if ( function_exists( 'gms_render_money_cta' ) && ! $is_legal_page ) : ?>
						<?php gms_render_money_cta(); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php endwhile; ?>
<?php get_footer(); ?>
