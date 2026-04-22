<?php
/**
 * Premium service inner-page widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Service_Detail_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-service-detail';
	}

	public function get_title() {
		return __( 'GMS Service Detail', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-single-post';
	}

	public function get_style_depends() {
		return [ 'grow-my-security-service-detail', 'grow-my-security-service-inner-premium' ];
	}

	private function get_icon_options(): array {
		return [
			'shield'     => __( 'Shield', 'grow-my-security' ),
			'megaphone'  => __( 'Megaphone', 'grow-my-security' ),
			'target'     => __( 'Target', 'grow-my-security' ),
			'chart'      => __( 'Chart', 'grow-my-security' ),
			'brain'      => __( 'Brain', 'grow-my-security' ),
			'spark'      => __( 'Spark', 'grow-my-security' ),
			'layers'     => __( 'Layers', 'grow-my-security' ),
			'users'      => __( 'Users', 'grow-my-security' ),
			'compass'    => __( 'Compass', 'grow-my-security' ),
			'funnel'     => __( 'Funnel', 'grow-my-security' ),
			'search'     => __( 'Search', 'grow-my-security' ),
			'globe'      => __( 'Globe', 'grow-my-security' ),
			'code'       => __( 'Code', 'grow-my-security' ),
			'guard'      => __( 'Guard', 'grow-my-security' ),
			'broadcast'  => __( 'Broadcast', 'grow-my-security' ),
			'chip'       => __( 'Chip', 'grow-my-security' ),
			'landmark'   => __( 'Landmark', 'grow-my-security' ),
			'star'       => __( 'Star', 'grow-my-security' ),
			'video'      => __( 'Video', 'grow-my-security' ),
			'grid'       => __( 'Grid', 'grow-my-security' ),
		];
	}

	protected function register_controls() {
		$config    = \gms_get_demo_config();
		$post_name = \get_post_field( 'post_name', \get_the_ID() );
		$service   = null;

		foreach ( (array) ( $config['services'] ?? [] ) as $item ) {
			if ( ( $item['slug'] ?? '' ) === $post_name ) {
				$service = $item;
				break;
			}
		}

		if ( ! $service ) {
			$service = $config['services'][0] ?? [];
		}

		$defaults = \gms_get_service_detail_widget_settings( $service, $config, \get_template_directory_uri(), \home_url( '/' ) );

		$this->start_controls_section( 'section_hero', [ 'label' => __( 'Hero', 'grow-my-security' ) ] );
		$this->add_control( 'hero_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['hero_eyebrow'] ?? '' ] );
		$this->add_control( 'hero_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['hero_title'] ?? '' ] );
		$this->add_control( 'hero_subtext', [ 'label' => __( 'Subtext', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['hero_subtext'] ?? '' ] );
		$this->add_control( 'hero_badges', [ 'label' => __( 'Hero Highlights', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['hero_badges'] ?? '', 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$this->add_control( 'hero_image', [ 'label' => __( 'Hero Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA, 'default' => $defaults['hero_image'] ?? [] ] );
		$this->add_control( 'hero_primary_text', [ 'label' => __( 'Primary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['hero_primary_text'] ?? '' ] );
		$this->add_control( 'hero_primary_url', [ 'label' => __( 'Primary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['hero_primary_url'] ?? [] ] );
		$this->add_control( 'hero_secondary_text', [ 'label' => __( 'Secondary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['hero_secondary_text'] ?? '' ] );
		$this->add_control( 'hero_secondary_url', [ 'label' => __( 'Secondary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['hero_secondary_url'] ?? [] ] );
		$this->add_control( 'contact_phone', [ 'label' => __( 'Contact Phone', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['contact_phone'] ?? '' ] );
		$this->end_controls_section();

		$this->start_controls_section( 'section_about', [ 'label' => __( 'About', 'grow-my-security' ) ] );
		$this->add_control( 'about_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['about_eyebrow'] ?? '' ] );
		$this->add_control( 'about_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['about_title'] ?? '' ] );
		$this->add_control( 'about_text', [ 'label' => __( 'Paragraphs', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['about_text'] ?? '', 'description' => __( 'Separate paragraphs with a blank line.', 'grow-my-security' ) ] );
		$this->add_control( 'about_points', [ 'label' => __( 'Highlights', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['about_points'] ?? '', 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$this->add_control( 'about_image', [ 'label' => __( 'About Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA, 'default' => $defaults['about_image'] ?? [] ] );
		$this->end_controls_section();

		$icon_options = $this->get_icon_options();
		$feature_repeater = new Repeater();
		$feature_repeater->add_control( 'icon', [ 'label' => __( 'Icon', 'grow-my-security' ), 'type' => Controls_Manager::SELECT, 'options' => $icon_options, 'default' => 'shield' ] );
		$feature_repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$feature_repeater->add_control( 'text', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );

		$this->start_controls_section( 'section_features', [ 'label' => __( 'Features', 'grow-my-security' ) ] );
		$this->add_control( 'features_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['features_eyebrow'] ?? '' ] );
		$this->add_control( 'features_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['features_title'] ?? '' ] );
		$this->add_control( 'features_text', [ 'label' => __( 'Intro', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['features_text'] ?? '' ] );
		$this->add_control( 'features', [ 'label' => __( 'Feature Cards', 'grow-my-security' ), 'type' => Controls_Manager::REPEATER, 'fields' => $feature_repeater->get_controls(), 'default' => $defaults['features'] ?? [], 'title_field' => '{{{ title }}}' ] );
		$this->end_controls_section();

		$industry_repeater = new Repeater();
		$industry_repeater->add_control( 'icon', [ 'label' => __( 'Icon', 'grow-my-security' ), 'type' => Controls_Manager::SELECT, 'options' => $icon_options, 'default' => 'shield' ] );
		$industry_repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$industry_repeater->add_control( 'text', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );

		$this->start_controls_section( 'section_industries', [ 'label' => __( 'Industries', 'grow-my-security' ) ] );
		$this->add_control( 'industries_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['industries_eyebrow'] ?? '' ] );
		$this->add_control( 'industries_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['industries_title'] ?? '' ] );
		$this->add_control( 'industries_text', [ 'label' => __( 'Intro', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['industries_text'] ?? '' ] );
		$this->add_control( 'industries', [ 'label' => __( 'Industry Cards', 'grow-my-security' ), 'type' => Controls_Manager::REPEATER, 'fields' => $industry_repeater->get_controls(), 'default' => $defaults['industries'] ?? [], 'title_field' => '{{{ title }}}' ] );
		$this->end_controls_section();

		$why_repeater = new Repeater();
		$why_repeater->add_control( 'title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$why_repeater->add_control( 'text', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );

		$this->start_controls_section( 'section_why', [ 'label' => __( 'Why Choose Us', 'grow-my-security' ) ] );
		$this->add_control( 'why_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['why_eyebrow'] ?? '' ] );
		$this->add_control( 'why_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['why_title'] ?? '' ] );
		$this->add_control( 'why_text', [ 'label' => __( 'Intro', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['why_text'] ?? '' ] );
		$this->add_control( 'why_items', [ 'label' => __( 'Value Cards', 'grow-my-security' ), 'type' => Controls_Manager::REPEATER, 'fields' => $why_repeater->get_controls(), 'default' => $defaults['why_items'] ?? [], 'title_field' => '{{{ title }}}' ] );
		$this->end_controls_section();

		$process_repeater = new Repeater();
		$process_repeater->add_control( 'title', [ 'label' => __( 'Step Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$process_repeater->add_control( 'text', [ 'label' => __( 'Step Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );

		$this->start_controls_section( 'section_process', [ 'label' => __( 'Process', 'grow-my-security' ) ] );
		$this->add_control( 'process_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['process_eyebrow'] ?? '' ] );
		$this->add_control( 'process_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['process_title'] ?? '' ] );
		$this->add_control( 'process_text', [ 'label' => __( 'Intro', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['process_text'] ?? '' ] );
		$this->add_control( 'process_items', [ 'label' => __( 'Steps', 'grow-my-security' ), 'type' => Controls_Manager::REPEATER, 'fields' => $process_repeater->get_controls(), 'default' => $defaults['process_items'] ?? [], 'title_field' => '{{{ title }}}' ] );
		$this->end_controls_section();

		$this->start_controls_section( 'section_cta', [ 'label' => __( 'CTA', 'grow-my-security' ) ] );
		$this->add_control( 'cta_eyebrow', [ 'label' => __( 'Eyebrow', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['cta_eyebrow'] ?? '' ] );
		$this->add_control( 'cta_title', [ 'label' => __( 'Title', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['cta_title'] ?? '' ] );
		$this->add_control( 'cta_text', [ 'label' => __( 'Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['cta_text'] ?? '' ] );
		$this->add_control( 'cta_primary_text', [ 'label' => __( 'Primary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['cta_primary_text'] ?? '' ] );
		$this->add_control( 'cta_primary_url', [ 'label' => __( 'Primary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['cta_primary_url'] ?? [] ] );
		$this->add_control( 'cta_secondary_text', [ 'label' => __( 'Secondary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['cta_secondary_text'] ?? '' ] );
		$this->add_control( 'cta_secondary_url', [ 'label' => __( 'Secondary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $defaults['cta_secondary_url'] ?? [] ] );
		$this->end_controls_section();

		$this->add_widget_style_controls( 'service_detail_section_style', '{{WRAPPER}} .gms-widget' );
	}

	private function render_icon_markup( string $icon ): string {
		switch ( $icon ) {
			case 'megaphone':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5V13.5H6L12.5 18V6L6 10.5H3ZM14 8.23V15.77C15.76 15.15 17 13.49 17 12C17 10.51 15.76 8.85 14 8.23Z" fill="currentColor"/></svg>';
			case 'target':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10h-2a8 8 0 1 1-8-8V2Zm7 1-2.59 2.59A7.96 7.96 0 0 1 20 12h2a9.95 9.95 0 0 0-2.29-6.41L22 3h-3Zm-7 4a5 5 0 1 0 5 5h-2a3 3 0 1 1-3-3V7Z" fill="currentColor"/></svg>';
			case 'chart':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19H20V21H4V19ZM6 10H9V17H6V10ZM10.5 6H13.5V17H10.5V6ZM15 12H18V17H15V12Z" fill="currentColor"/></svg>';
			case 'brain':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 2h6a3 3 0 0 1 3 3v2a3 3 0 0 1 2 2.83V12a3 3 0 0 1-2 2.82V17a3 3 0 0 1-3 3h-1v2h-2v-2h-2v2H8v-2H7a3 3 0 0 1-3-3v-2.18A3 3 0 0 1 2 12V9.83A3 3 0 0 1 4 7V5a3 3 0 0 1 3-3h2Z" fill="currentColor"/></svg>';
			case 'spark':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.4 5.6L20 10l-5.6 2.4L12 18l-2.4-5.6L4 10l5.6-2.4L12 2Zm7 13 1 2.3L22 18l-2 0.7L19 21l-1-2.3L16 18l2-0.7L19 15ZM5 15l1 2.3L8 18l-2 0.7L5 21l-1-2.3L2 18l2-0.7L5 15Z" fill="currentColor"/></svg>';
			case 'layers':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 10 5-10 5L2 7l10-5Zm-8.2 9L12 15.1 20.2 11 22 11.9l-10 5-10-5 1.8-.9Zm0 4L12 19.1 20.2 15 22 15.9l-10 5-10-5 1.8-.9Z" fill="currentColor"/></svg>';
			case 'users':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-2.83-6.83A4 4 0 0 0 16 11Zm-8 0A4 4 0 1 0 8 3a4 4 0 0 0 0 8Zm8 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4ZM8 13c-2.67 0-8 1.34-8 4v2h6v-2c0-1.38.69-2.61 1.75-3.33L8 13Z" fill="currentColor"/></svg>';
			case 'compass':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm3.9 6.1-2.1 6-6 2.1 2.1-6 6-2.1Z" fill="currentColor"/></svg>';
			case 'funnel':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h18l-7 8v6l-4 2v-8L3 4Z" fill="currentColor"/></svg>';
			case 'search':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2a8 8 0 1 0 5.29 14.29L21 22l1-1-5.71-5.71A8 8 0 0 0 10 2Z" fill="currentColor"/></svg>';
			case 'globe':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm6.93 9h-2.98a15.6 15.6 0 0 0-1.1-5.23A8.04 8.04 0 0 1 18.93 11ZM12 4.07c.82 1.18 1.72 3.54 1.95 6.93h-3.9C10.28 7.61 11.18 5.25 12 4.07Z" fill="currentColor"/></svg>';
			case 'code':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8.59 16.59-4.58-4.59 4.58-4.59L10 8.83 6.83 12 10 15.17l-1.41 1.42ZM15.41 16.59 14 15.17 17.17 12 14 8.83l1.41-1.42L20 12l-4.59 4.59Z" fill="currentColor"/></svg>';
			case 'guard':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Z" fill="currentColor"/></svg>';
			case 'broadcast':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 0 0-9 9h2a7 7 0 0 1 14 0h2a9 9 0 0 0-9-9Zm0 4a5 5 0 0 0-5 5h2a3 3 0 1 1 6 0h2a5 5 0 0 0-5-5Zm-1 6h2v8h-2v-8Z" fill="currentColor"/></svg>';
			case 'chip':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h10v10H7V7Zm2 2v6h6V9H9ZM9 1h2v4H9V1Zm4 0h2v4h-2V1ZM1 9h4v2H1V9Zm0 4h4v2H1v-2Zm18-4h4v2h-4V9Zm0 4h4v2h-4v-2ZM9 19h2v4H9v-4Zm4 0h2v4h-2v-4Z" fill="currentColor"/></svg>';
			case 'landmark':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 2 8v2h20V8L12 3ZM4 12h2v6H4v-6Zm4 0h2v6H8v-6Zm4 0h2v6h-2v-6Zm4 0h2v6h-2v-6ZM2 20h20v2H2v-2Z" fill="currentColor"/></svg>';
			case 'star':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.9 6 6.6.96-4.75 4.63 1.12 6.56L12 16.9l-5.87 3.25 1.12-6.56L2.5 8.96 9.1 8 12 2Z" fill="currentColor"/></svg>';
			case 'video':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h14a2 2 0 0 1 2 2v1.5L23 7v10l-4-2.5V16a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" fill="currentColor"/></svg>';
			case 'grid':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8v8H3V3Zm10 0h8v8h-8V3ZM3 13h8v8H3v-8Zm10 0h8v8h-8v-8Z" fill="currentColor"/></svg>';
			case 'shield':
			default:
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Z" fill="currentColor"/></svg>';
		}
	}

	private function render_paragraphs( string $text ): void {
		foreach ( preg_split( '/\r\n\r\n|\n\n|\r\r/', trim( $text ) ) as $paragraph ) {
			$paragraph = trim( $paragraph );
			if ( '' === $paragraph ) {
				continue;
			}
			echo '<p>' . esc_html( $paragraph ) . '</p>';
		}
	}

	private function render_line_items( string $text, string $wrapper_class, string $item_class ): void {
		$lines = preg_split( '/\r\n|\r|\n/', trim( $text ) );
		$lines = array_values( array_filter( array_map( 'trim', $lines ) ) );

		if ( empty( $lines ) ) {
			return;
		}

		echo '<div class="' . esc_attr( $wrapper_class ) . '">';
		foreach ( $lines as $line ) {
			echo '<span class="' . esc_attr( $item_class ) . '">' . esc_html( $line ) . '</span>';
		}
		echo '</div>';
	}

	private function render_heading( string $eyebrow, string $title, string $text, string $modifier = '' ): void {
		$classes = 'gms-service-premium__heading';
		if ( '' !== $modifier ) {
			$classes .= ' ' . $modifier;
		}
		echo '<div class="' . esc_attr( $classes ) . '">';
		if ( '' !== trim( $eyebrow ) ) {
			echo '<span class="gms-service-premium__eyebrow">' . esc_html( $eyebrow ) . '</span>';
		}
		if ( '' !== trim( $title ) ) {
			echo '<h2>' . esc_html( $title ) . '</h2>';
		}
		if ( '' !== trim( $text ) ) {
			echo '<p>' . esc_html( $text ) . '</p>';
		}
		echo '</div>';
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$features   = is_array( $settings['features'] ?? null ) ? $settings['features'] : [];
		$industries = is_array( $settings['industries'] ?? null ) ? $settings['industries'] : [];
		$why_items  = is_array( $settings['why_items'] ?? null ) ? $settings['why_items'] : [];
		$process    = is_array( $settings['process_items'] ?? null ) ? $settings['process_items'] : [];
		?>
		<section class="gms-widget gms-service-page gms-service-detail-page gms-service-premium">
			<section class="gms-service-premium__hero">
				<div class="gms-service-premium__shell">
					<div class="gms-service-premium__hero-panel">
						<div class="gms-service-premium__hero-grid">
							<div class="gms-service-premium__hero-copy">
								<?php if ( ! empty( $settings['hero_eyebrow'] ) ) : ?><span class="gms-service-premium__eyebrow"><?php echo esc_html( $settings['hero_eyebrow'] ); ?></span><?php endif; ?>
								<?php if ( ! empty( $settings['hero_title'] ) ) : ?><h1><?php echo esc_html( $settings['hero_title'] ); ?></h1><?php endif; ?>
								<?php if ( ! empty( $settings['hero_subtext'] ) ) : ?><p class="gms-service-premium__hero-text"><?php echo esc_html( $settings['hero_subtext'] ); ?></p><?php endif; ?>
								<?php $this->render_line_items( (string) ( $settings['hero_badges'] ?? '' ), 'gms-service-premium__badges', 'gms-service-premium__badge' ); ?>
								<div class="gms-service-premium__actions">
									<?php $this->render_link( 'service-hero-primary-' . $this->get_id(), $settings['hero_primary_url'] ?? [], $settings['hero_primary_text'] ?? '', 'gms-button gms-service-premium__button' ); ?>
									<?php $this->render_link( 'service-hero-secondary-' . $this->get_id(), $settings['hero_secondary_url'] ?? [], $settings['hero_secondary_text'] ?? '', 'gms-button-outline gms-service-premium__button gms-service-premium__button--ghost' ); ?>
								</div>
								<?php if ( ! empty( $settings['contact_phone'] ) ) : ?>
									<div class="gms-service-premium__meta">
										<span class="gms-service-premium__meta-label"><?php esc_html_e( 'Direct line', 'grow-my-security' ); ?></span>
										<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) $settings['contact_phone'] ) ); ?>"><?php echo esc_html( $settings['contact_phone'] ); ?></a>
									</div>
								<?php endif; ?>
							</div>
							<div class="gms-service-premium__hero-media">
								<div class="gms-service-premium__media-frame">
									<?php $this->render_media_image( is_array( $settings['hero_image'] ?? null ) ? $settings['hero_image'] : [], [ 'size' => 'large', 'loading' => 'eager', 'fetchpriority' => 'high' ] ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section class="gms-service-premium__section">
				<div class="gms-service-premium__shell">
					<div class="gms-service-premium__split">
						<div class="gms-service-premium__visual-card">
							<?php $this->render_media_image( is_array( $settings['about_image'] ?? null ) ? $settings['about_image'] : [], [ 'size' => 'large' ] ); ?>
						</div>
						<div class="gms-service-premium__content-card">
							<?php $this->render_heading( (string) ( $settings['about_eyebrow'] ?? '' ), (string) ( $settings['about_title'] ?? '' ), '', 'gms-service-premium__heading--left' ); ?>
							<div class="gms-service-premium__prose"><?php $this->render_paragraphs( (string) ( $settings['about_text'] ?? '' ) ); ?></div>
							<?php $this->render_line_items( (string) ( $settings['about_points'] ?? '' ), 'gms-service-premium__highlights', 'gms-service-premium__highlight' ); ?>
						</div>
					</div>
				</div>
			</section>

			<section class="gms-service-premium__section gms-service-premium__section--muted">
				<div class="gms-service-premium__shell">
					<?php $this->render_heading( (string) ( $settings['features_eyebrow'] ?? '' ), (string) ( $settings['features_title'] ?? '' ), (string) ( $settings['features_text'] ?? '' ) ); ?>
					<div class="gms-service-premium__grid">
						<?php foreach ( $features as $feature ) : ?>
							<article class="gms-service-premium__glass-card">
								<div class="gms-service-premium__icon" aria-hidden="true"><?php echo $this->render_icon_markup( (string) ( $feature['icon'] ?? 'shield' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<h3><?php echo esc_html( $feature['title'] ?? '' ); ?></h3>
								<p><?php echo esc_html( $feature['text'] ?? '' ); ?></p>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<?php if ( ! empty( $industries ) ) : ?>
				<section class="gms-service-premium__section">
					<div class="gms-service-premium__shell">
						<?php $this->render_heading( (string) ( $settings['industries_eyebrow'] ?? '' ), (string) ( $settings['industries_title'] ?? '' ), (string) ( $settings['industries_text'] ?? '' ) ); ?>
						<div class="gms-service-premium__grid gms-service-premium__grid--compact">
							<?php foreach ( $industries as $industry ) : ?>
								<article class="gms-service-premium__glass-card gms-service-premium__glass-card--compact">
									<div class="gms-service-premium__icon" aria-hidden="true"><?php echo $this->render_icon_markup( (string) ( $industry['icon'] ?? 'shield' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
									<h3><?php echo esc_html( $industry['title'] ?? '' ); ?></h3>
									<p><?php echo esc_html( $industry['text'] ?? '' ); ?></p>
								</article>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<section class="gms-service-premium__section gms-service-premium__section--muted">
				<div class="gms-service-premium__shell">
					<?php $this->render_heading( (string) ( $settings['why_eyebrow'] ?? '' ), (string) ( $settings['why_title'] ?? '' ), (string) ( $settings['why_text'] ?? '' ) ); ?>
					<div class="gms-service-premium__grid gms-service-premium__grid--three">
						<?php foreach ( $why_items as $item ) : ?>
							<article class="gms-service-premium__glass-card gms-service-premium__glass-card--value">
								<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
								<p><?php echo esc_html( $item['text'] ?? '' ); ?></p>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section id="process" class="gms-service-premium__section">
				<div class="gms-service-premium__shell">
					<?php $this->render_heading( (string) ( $settings['process_eyebrow'] ?? '' ), (string) ( $settings['process_title'] ?? '' ), (string) ( $settings['process_text'] ?? '' ) ); ?>
					<div class="gms-service-premium__steps">
						<?php foreach ( $process as $index => $item ) : ?>
							<article class="gms-service-premium__step-card">
								<div class="gms-service-premium__step-index"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></div>
								<div class="gms-service-premium__step-copy">
									<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
									<p><?php echo esc_html( $item['text'] ?? '' ); ?></p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="gms-service-premium__section gms-service-premium__section--cta">
				<div class="gms-service-premium__shell">
					<div class="gms-service-premium__cta-card">
						<?php if ( ! empty( $settings['cta_eyebrow'] ) ) : ?><span class="gms-service-premium__eyebrow"><?php echo esc_html( $settings['cta_eyebrow'] ); ?></span><?php endif; ?>
						<?php if ( ! empty( $settings['cta_title'] ) ) : ?><h2><?php echo esc_html( $settings['cta_title'] ); ?></h2><?php endif; ?>
						<?php if ( ! empty( $settings['cta_text'] ) ) : ?><p><?php echo esc_html( $settings['cta_text'] ); ?></p><?php endif; ?>
						<div class="gms-service-premium__actions gms-service-premium__actions--center">
							<?php $this->render_link( 'service-cta-primary-' . $this->get_id(), $settings['cta_primary_url'] ?? [], $settings['cta_primary_text'] ?? '', 'gms-button gms-service-premium__button' ); ?>
						</div>
					</div>
				</div>
			</section>
		</section>
		<?php
	}
}
