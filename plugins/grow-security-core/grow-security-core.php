<?php
/**
 * Plugin Name: Grow Security Core
 * Description: Registers editable content types and metadata for the Grow My Security website.
 * Version: 1.0.0
 * Author: OpenAI Codex
 * Text Domain: grow-security-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gsc_register_post_types() {
	register_post_type(
		'gms_service',
		[
			'labels' => [
				'name'          => __( 'Services', 'grow-security-core' ),
				'singular_name' => __( 'Service', 'grow-security-core' ),
				'add_new_item'  => __( 'Add New Service', 'grow-security-core' ),
				'edit_item'     => __( 'Edit Service', 'grow-security-core' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-admin-tools',
			'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
			'rewrite'      => [
				'slug'       => 'services',
				'with_front' => false,
			],
			'has_archive'  => false,
		]
	);

	register_post_type(
		'gms_testimonial',
		[
			'labels' => [
				'name'          => __( 'Testimonials', 'grow-security-core' ),
				'singular_name' => __( 'Testimonial', 'grow-security-core' ),
				'add_new_item'  => __( 'Add New Testimonial', 'grow-security-core' ),
				'edit_item'     => __( 'Edit Testimonial', 'grow-security-core' ),
			],
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-format-quote',
			'supports'           => [ 'title', 'editor', 'excerpt', 'page-attributes' ],
		]
	);

	register_post_type(
		'gms_faq',
		[
			'labels' => [
				'name'          => __( 'FAQs', 'grow-security-core' ),
				'singular_name' => __( 'FAQ', 'grow-security-core' ),
				'add_new_item'  => __( 'Add New FAQ', 'grow-security-core' ),
				'edit_item'     => __( 'Edit FAQ', 'grow-security-core' ),
			],
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-editor-help',
			'supports'           => [ 'title', 'editor', 'page-attributes' ],
		]
	);

	register_post_type(
		'gms_industry',
		[
			'labels' => [
				'name'          => __( 'Industries', 'grow-security-core' ),
				'singular_name' => __( 'Industry', 'grow-security-core' ),
				'add_new_item'  => __( 'Add New Industry', 'grow-security-core' ),
				'edit_item'     => __( 'Edit Industry', 'grow-security-core' ),
			],
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-building',
			'supports'           => [ 'title', 'editor', 'excerpt', 'page-attributes' ],
		]
	);

	register_post_type(
		'gms_press',
		[
			'labels' => [
				'name'          => __( 'Press', 'grow-security-core' ),
				'singular_name' => __( 'Press Item', 'grow-security-core' ),
				'add_new_item'  => __( 'Add New Press Item', 'grow-security-core' ),
				'edit_item'     => __( 'Edit Press Item', 'grow-security-core' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-megaphone',
			'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
			'rewrite'      => [
				'slug'       => 'press-media',
				'with_front' => false,
			],
			'has_archive'  => false,
		]
	);

	register_post_type(
		'gms_podcast',
		[
			'labels' => [
				'name'          => __( 'Podcast Episodes', 'grow-security-core' ),
				'singular_name' => __( 'Podcast Episode', 'grow-security-core' ),
				'add_new_item'  => __( 'Add New Podcast Episode', 'grow-security-core' ),
				'edit_item'     => __( 'Edit Podcast Episode', 'grow-security-core' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-microphone',
			'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
			'rewrite'      => [
				'slug'       => 'podcast',
				'with_front' => false,
			],
			'has_archive'  => false,
		]
	);

	register_post_type(
		'gms_case_study',
		[
			'labels' => [
				'name'          => __( 'Case Studies', 'grow-security-core' ),
				'singular_name' => __( 'Case Study', 'grow-security-core' ),
				'add_new_item'  => __( 'Add New Case Study', 'grow-security-core' ),
				'edit_item'     => __( 'Edit Case Study', 'grow-security-core' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-chart-bar',
			'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
			'rewrite'      => [
				'slug'       => 'case-studies',
				'with_front' => false,
			],
			'has_archive'  => false,
			'hierarchical' => false,
		]
	);
}
add_action( 'init', 'gsc_register_post_types', 10 );
add_action( 'init', 'gsc_seed_sample_case_studies', 20 );

function gsc_maybe_flush_rewrite_rules() {
	$rewrite_version = 'gsc-rewrite-rules-v1';

	if ( get_option( 'gsc_rewrite_rules_version' ) === $rewrite_version ) {
		return;
	}

	flush_rewrite_rules();
	update_option( 'gsc_rewrite_rules_version', $rewrite_version, false );
}
add_action( 'init', 'gsc_maybe_flush_rewrite_rules', 30 );

function gsc_activate_plugin() {
	gsc_register_post_types();
	flush_rewrite_rules();
	gsc_register_meta_boxes();
	gsc_seed_sample_case_studies(); // Seed data on activation/update
	update_option( 'gsc_rewrite_rules_version', 'gsc-rewrite-rules-v1', false );
}
register_activation_hook( __FILE__, 'gsc_activate_plugin' );

function gsc_register_case_study_meta() {
	$meta_keys = [ 
		'gms_cs_metric_value', 'gms_cs_metric_label', 'gms_cs_short_desc', 'gms_cs_badge', 
		'gms_cs_challenge', 'gms_cs_strategy', 'gms_cs_execution', 'gms_cs_visual_url',
		'gms_cs_image_url', 'gms_cs_result_1_val', 'gms_cs_result_1_lab', 
		'gms_cs_result_2_val', 'gms_cs_result_2_lab', 'gms_cs_result_3_val', 'gms_cs_result_3_lab' 
	];

	foreach ( $meta_keys as $key ) {
		register_post_meta( 'gms_case_study', $key, [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
		] );
	}
}
add_action( 'init', 'gsc_register_case_study_meta' );

/**
 * [gms_cs_meta key="field_id"]
 * Shortcode to pull case study meta dynamically into Elementor or Gutenberg.
 */
function gsc_case_study_meta_shortcode( $atts ) {
    $a = shortcode_atts( [
        'key' => '',
    ], $atts );

    if ( empty( $a['key'] ) ) {
        return '';
    }

    // Prepend prefix if not present for convenience
    $meta_key = ( strpos( $a['key'], 'gms_cs_' ) === false ) ? 'gms_cs_' . $a['key'] : $a['key'];
    
    $value = get_post_meta( get_the_ID(), $meta_key, true );
    
    // Return formatted text/HTML if needed, or just the raw value.
    return ! empty( $value ) ? wp_kses_post( $value ) : '';
}
add_shortcode( 'gms_cs_meta', 'gsc_case_study_meta_shortcode' );

/**
 * [gms_cs_label text="05 // Label"]
 * Shortcode to render the stylized Intelligence Report labels.
 */
function gsc_case_study_label_shortcode( $atts ) {
    $a = shortcode_atts( [
        'text' => '00 // Section',
    ], $atts );
    return '<div class="gms-cs-report-label gms-revealed">' . esc_html( $a['text'] ) . '</div>';
}
add_shortcode( 'gms_cs_label', 'gsc_case_study_label_shortcode' );

/**
 * [gms_cs_row] ... content ... [/gms_cs_row]
 * Shortcode to wrap content in the premium 2-column report grid.
 */
function gsc_case_study_row_shortcode( $atts, $content = null ) {
    return '<div class="gms-cs-report-grid gms-revealed">' . do_shortcode( $content ) . '</div>';
}
add_shortcode( 'gms_cs_row', 'gsc_case_study_row_shortcode' );

/**
 * Enqueue Single Case Study Interactivity
 */
function gsc_enqueue_case_study_assets() {
    if ( is_singular( 'gms_case_study' ) ) {
        wp_enqueue_script( 'gms-cs-interact', get_theme_file_uri( '/assets/js/case-studies-interact.js' ), [ 'jquery' ], '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'gsc_enqueue_case_study_assets' );

/**
 * Automatically enable Elementor for Case Studies CPT.
 */
function gsc_enable_elementor_for_case_studies() {
	$cpt_support = get_option( 'elementor_cpt_support' );
	if ( ! $cpt_support ) {
		$cpt_support = [ 'post', 'page', 'gms_case_study' ];
	} elseif ( is_array( $cpt_support ) && ! in_array( 'gms_case_study', $cpt_support, true ) ) {
		$cpt_support[] = 'gms_case_study';
	} else {
		return;
	}

	update_option( 'elementor_cpt_support', array_values( array_unique( $cpt_support ) ) );
}
add_action( 'after_setup_theme', 'gsc_enable_elementor_for_case_studies' );

/**
 * Seed sample case studies if they don't exist.
 */
function gsc_seed_sample_case_studies() {
	// Only run this ONCE to avoid "stopping the web" or slowing down the server.
	if ( get_option( 'gsc_case_studies_seeded_v11' ) ) {
		return;
	}

	$samples = [
		[
			'title'       => 'Bank-Grade Ransomware Defense',
			'description' => 'How we saved a mid-sized financial institution from a $2.4M tactical ransomware breach using proactive threat hunting.',
			'metric_val'  => '$2.4M',
			'metric_lab'  => 'Saved Loss',
			'image'       => get_theme_file_uri( 'assets/images/case-studies/cs-ransomware.png' ),
			'badge'       => 'CRITICAL BREACH PREVENTED',
		],
		[
			'title'       => 'Global Zero-Trust Rollout',
			'description' => 'Implementing identity-first security architecture for 50,000+ endpoints across 14 international regions.',
			'metric_val'  => '50k+',
			'metric_lab'  => 'Secured Nodes',
			'image'       => get_theme_file_uri( 'assets/images/case-studies/cs-zero-trust.png' ),
			'badge'       => 'GLOBAL DEPLOYMENT',
		],
		[
			'title'       => '45% Faster SOC Response',
			'description' => 'Optimizing SecOps workflows for a national MSSP, reducing Mean Time to Remediate (MTTR) by nearly half.',
			'metric_val'  => '45%',
			'metric_lab'  => 'Faster Response',
			'image'       => get_theme_file_uri( 'assets/images/case-studies/cs-soc.png' ),
			'badge'       => 'SOC OPTIMIZATION',
		],
		[
			'title'       => 'Cloud Posture Optimization',
			'description' => 'Reducing Azure/AWS misconfigurations by 92% for a high-growth FinTech startup scaling internationally.',
			'metric_val'  => '92%',
			'metric_lab'  => 'Misconfig Reduction',
			'image'       => get_theme_file_uri( 'assets/images/case-studies/cs-cloud.png' ),
			'badge'       => 'CLOUD COMPLIANCE',
		],
		[
			'title'       => 'Zero Phishing Incidents',
			'description' => 'Deploying simulation-led awareness training for 5,000 employees, achieving 12 months of zero successful breaches.',
			'metric_val'  => '0%',
			'metric_lab'  => 'Breach Rate',
			'image'       => get_theme_file_uri( 'assets/images/case-studies/cs-phishing.png' ),
			'badge'       => 'HUMAN DEFENSE LAYER',
		],
		[
			'title'       => 'HIPAA Compliance Guard',
			'description' => 'Securing 2M+ patient records for a major health network through automated logging and access control.',
			'metric_val'  => '2M+',
			'metric_lab'  => 'Records Secured',
			'image'       => get_theme_file_uri( 'assets/images/case-studies/cs-health.png' ),
			'badge'       => 'HEALTHCARE SECURITY',
		],
	];

	// Cleanup old dummy data if it exists
	$old_titles = [ '3.2x Lead Growth', '50% Lower CPL', 'Authority Positioning' ];
	foreach ( $old_titles as $old_title ) {
		$old_posts = get_posts( [
			'title'       => $old_title,
			'post_type'   => 'gms_case_study',
			'post_status' => 'any',
			'numberposts' => 1,
		] );
		if ( ! empty( $old_posts ) ) {
			wp_delete_post( $old_posts[0]->ID, true );
		}
	}

	foreach ( $samples as $sample ) {
		$existing_posts = get_posts( [
			'title'       => $sample['title'],
			'post_type'   => 'gms_case_study',
			'post_status' => 'any',
			'numberposts' => 1,
		] );
		
		$check_post = ! empty( $existing_posts ) ? $existing_posts[0] : null;
		
		if ( $check_post ) {
			// FORCE UPDATE the meta fields even if post exists (v11 "Intelligence Hub" content update)
			update_post_meta( $check_post->ID, 'gms_cs_metric_value', $sample['metric_val'] );
			update_post_meta( $check_post->ID, 'gms_cs_metric_label', $sample['metric_lab'] );
			update_post_meta( $check_post->ID, 'gms_cs_short_desc', $sample['description'] );
			update_post_meta( $check_post->ID, 'gms_cs_badge', $sample['badge'] );
			update_post_meta( $check_post->ID, 'gms_cs_image_url', $sample['image'] );
			update_post_meta( $check_post->ID, 'gms_cs_challenge', 'The organization faced a critical infrastructure vulnerability that threatened 40% of their operational uptime. Legacy security stacks were unable to detect the sophisticated lateral movement of the target threat vector.' );
			update_post_meta( $check_post->ID, 'gms_cs_strategy', 'Our team implemented a Zero-Trust segmentation protocol coupled with AI-driven behavioral analytics. We focused on reducing the blast radius and establishing a 24/7 proactive monitoring hub.' );
			update_post_meta( $check_post->ID, 'gms_cs_execution', 'Phase 1: Deep packet inspection and audit. Phase 2: Implementation of automated containment triggers. Phase 3: Post-incident hardening and staff training.' );
			update_post_meta( $check_post->ID, 'gms_cs_result_1_val', '+3.2x' );
			update_post_meta( $check_post->ID, 'gms_cs_result_1_lab', 'Lead Growth' );
			update_post_meta( $check_post->ID, 'gms_cs_result_2_val', '50%' );
			update_post_meta( $check_post->ID, 'gms_cs_result_2_lab', 'Lower Cost' );
			update_post_meta( $check_post->ID, 'gms_cs_result_3_val', '2x' );
			update_post_meta( $check_post->ID, 'gms_cs_result_3_lab', 'Engagement' );
			update_post_meta( $check_post->ID, 'gms_cs_visual_url', get_theme_file_uri( 'assets/images/case-studies/cs-revenue.png' ) );
			continue;
		}

		$post_id = wp_insert_post( [
			'post_title'   => $sample['title'],
			'post_content' => $sample['description'],
			'post_status'  => 'publish',
			'post_type'    => 'gms_case_study',
		] );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, 'gms_cs_metric_value', $sample['metric_val'] );
			update_post_meta( $post_id, 'gms_cs_metric_label', $sample['metric_lab'] );
			update_post_meta( $post_id, 'gms_cs_short_desc', $sample['description'] );
			update_post_meta( $post_id, 'gms_cs_badge', $sample['badge'] );
			update_post_meta( $post_id, 'gms_cs_image_url', $sample['image'] );

			// Detailed section placeholders for modern relevance
			update_post_meta( $post_id, 'gms_cs_challenge', 'The organization faced a sophisticated threat vector targeting legacy infrastructure and high-value data silos.' );
			update_post_meta( $post_id, 'gms_cs_strategy', 'We deployed a layered defense strategy combining AI-driven behavior monitoring and network segmentation.' );
			update_post_meta( $post_id, 'gms_cs_execution', 'Phase 1: Vulnerability assessment. Phase 2: System-wide integration. Phase 3: Continuous testing and refinement.' );
		}
	}

	// Mark as seeded so this never runs again.
	update_option( 'gsc_case_studies_seeded_v11', true );
}
function gsc_deactivate_plugin() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'gsc_deactivate_plugin' );

function gsc_register_meta_boxes() {
	add_meta_box( 'gsc_service_details', __( 'Service Details', 'grow-security-core' ), 'gsc_render_service_meta_box', 'gms_service', 'normal', 'default' );
	add_meta_box( 'gsc_testimonial_details', __( 'Testimonial Details', 'grow-security-core' ), 'gsc_render_testimonial_meta_box', 'gms_testimonial', 'normal', 'default' );
	add_meta_box( 'gsc_industry_details', __( 'Industry Details', 'grow-security-core' ), 'gsc_render_industry_meta_box', 'gms_industry', 'normal', 'default' );
	add_meta_box( 'gsc_case_study_details', __( 'Case Study Highlights', 'grow-security-core' ), 'gsc_render_case_study_meta_box', 'gms_case_study', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'gsc_register_meta_boxes' );

function gsc_meta_text_input( $name, $label, $value, $description = '' ) {
	?>
	<p>
		<label for="<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br>
		<input class="widefat" type="text" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php if ( $description ) : ?>
			<span class="description"><?php echo esc_html( $description ); ?></span>
		<?php endif; ?>
	</p>
	<?php
}

function gsc_meta_textarea( $name, $label, $value, $description = '' ) {
	?>
	<p>
		<label for="<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br>
		<textarea class="widefat" rows="6" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<?php if ( $description ) : ?>
			<span class="description"><?php echo esc_html( $description ); ?></span>
		<?php endif; ?>
	</p>
	<?php
}

function gsc_render_service_meta_box( $post ) {
	wp_nonce_field( 'gsc_save_meta', 'gsc_meta_nonce' );
	gsc_meta_text_input( 'gms_service_nav_title', __( 'Navigation Title', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_service_nav_title', true ), __( 'Optional shorter label for menus and accordions.', 'grow-security-core' ) );
	gsc_meta_text_input( 'gms_service_icon', __( 'Icon Label', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_service_icon', true ), __( 'Short code shown in service cards, for example SEO or AEO.', 'grow-security-core' ) );
	gsc_meta_textarea( 'gms_service_bullets', __( 'Bullets', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_service_bullets', true ), __( 'One bullet per line.', 'grow-security-core' ) );
}

function gsc_render_testimonial_meta_box( $post ) {
	wp_nonce_field( 'gsc_save_meta', 'gsc_meta_nonce' );
	gsc_meta_text_input( 'gms_testimonial_role', __( 'Role', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_testimonial_role', true ) );
	gsc_meta_text_input( 'gms_testimonial_company', __( 'Company', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_testimonial_company', true ) );
}

function gsc_render_industry_meta_box( $post ) {
	wp_nonce_field( 'gsc_save_meta', 'gsc_meta_nonce' );
	gsc_meta_text_input( 'gms_industry_icon', __( 'Icon Key', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_industry_icon', true ), __( 'Use one of: guard, bolt, alarm, user, team, retail, investigation, building, camera, shield.', 'grow-security-core' ) );
	gsc_meta_textarea( 'gms_industry_bullets', __( 'Bullets', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_industry_bullets', true ), __( 'Optional one bullet per line for card layouts.', 'grow-security-core' ) );
}

function gsc_render_case_study_meta_box( $post ) {
	wp_nonce_field( 'gsc_save_meta', 'gsc_meta_nonce' );
	
	echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;"><div>';
	gsc_meta_text_input( 'gms_cs_metric_value', __( 'Key Metric (Value)', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_cs_metric_value', true ), __( 'e.g., +3.2x', 'grow-security-core' ) );
	echo '</div><div>';
	gsc_meta_text_input( 'gms_cs_metric_label', __( 'Key Metric (Label)', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_cs_metric_label', true ), __( 'e.g., leads', 'grow-security-core' ) );
	echo '</div></div>';

	gsc_meta_textarea( 'gms_cs_short_desc', __( 'Short Description', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_cs_short_desc', true ), __( 'Shown on the homepage Case Study cards.', 'grow-security-core' ) );
	
	echo '<hr style="margin: 20px 0;">';
	echo '<h3>' . esc_html__( 'Detailed Sections (Editable via Dynamic Tags)', 'grow-security-core' ) . '</h3>';

	gsc_meta_textarea( 'gms_cs_challenge', __( 'The Challenge', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_cs_challenge', true ) );
	gsc_meta_textarea( 'gms_cs_strategy', __( 'Our Strategy', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_cs_strategy', true ) );
	gsc_meta_textarea( 'gms_cs_execution', __( 'The Execution', 'grow-security-core' ), (string) get_post_meta( $post->ID, 'gms_cs_execution', true ) );
}

function gsc_save_meta_boxes( $post_id ) {
	if ( ! isset( $_POST['gsc_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gsc_meta_nonce'] ) ), 'gsc_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = [
		'gms_service_nav_title'  => 'sanitize_text_field',
		'gms_service_icon'       => 'sanitize_text_field',
		'gms_service_bullets'    => 'sanitize_textarea_field',
		'gms_testimonial_role'   => 'sanitize_text_field',
		'gms_testimonial_company'=> 'sanitize_text_field',
		'gms_industry_icon'      => 'sanitize_text_field',
		'gms_industry_bullets'   => 'sanitize_textarea_field',
		'gms_cs_metric_value'    => 'sanitize_text_field',
		'gms_cs_metric_label'    => 'sanitize_text_field',
		'gms_cs_short_desc'      => 'sanitize_textarea_field',
		'gms_cs_challenge'       => 'sanitize_textarea_field',
		'gms_cs_strategy'        => 'sanitize_textarea_field',
		'gms_cs_execution'       => 'sanitize_textarea_field',
	];

	foreach ( $fields as $field => $sanitize_callback ) {
		if ( ! isset( $_POST[ $field ] ) ) {
			continue;
		}

		$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );

		if ( '' === $value ) {
			delete_post_meta( $post_id, $field );
			continue;
		}

		update_post_meta( $post_id, $field, $value );
	}
}
add_action( 'save_post', 'gsc_save_meta_boxes' );

function gsc_custom_enter_title( $title, $post ) {
	if ( 'gms_faq' === $post->post_type ) {
		return __( 'Enter question', 'grow-security-core' );
	}

	if ( 'gms_testimonial' === $post->post_type ) {
		return __( 'Enter client name', 'grow-security-core' );
	}

	return $title;
}
add_filter( 'enter_title_here', 'gsc_custom_enter_title', 10, 2 );
