<?php
/**
 * Elementor integration bootstrap.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gms_elementor_theme_support() {
	add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'gms_elementor_theme_support' );

function gms_register_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'gms-elements',
		[
			'title' => __( 'Grow My Security', 'grow-my-security' ),
			'icon'  => 'fa fa-plug',
		]
	);
}
add_action( 'elementor/elements/categories_registered', 'gms_register_elementor_category' );

function gms_register_theme_locations( $manager ) {
	if ( method_exists( $manager, 'register_all_core_location' ) ) {
		$manager->register_all_core_location();
	}
}
add_action( 'elementor/theme/register_locations', 'gms_register_theme_locations' );

function gms_load_elementor_widget_files() {
	static $loaded = false;

	if ( $loaded ) {
		return;
	}

	$loaded = true;

	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-widget-base.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-hero-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-page-hero-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-card-grid-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-service-grid-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-story-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-service-detail-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-industry-detail-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-process-timeline-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-icon-grid-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-services-accordion-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-testimonials-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-faq-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-post-grid-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-cta-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-contact-form-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-case-studies-listing-widget.php';
	require_once get_template_directory() . '/inc/elementor/widgets/class-gms-case-study-single-widget.php';
}

function gms_register_elementor_widgets( $widgets_manager ) {
	gms_load_elementor_widget_files();

	$widgets_manager->register( new \GMS\Elementor\Widgets\Hero_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Page_Hero_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Card_Grid_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Service_Grid_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Story_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Service_Detail_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Industry_Detail_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Process_Timeline_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Icon_Grid_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Services_Accordion_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Testimonials_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Faq_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Post_Grid_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Cta_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Contact_Form_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Case_Studies_Listing_Widget() );
	$widgets_manager->register( new \GMS\Elementor\Widgets\Case_Study_Single_Widget() );
}
add_action( 'elementor/widgets/register', 'gms_register_elementor_widgets' );
