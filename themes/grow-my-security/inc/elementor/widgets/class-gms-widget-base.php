<?php
/**
 * Base Elementor widget helpers.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class GMS_Widget_Base extends Widget_Base {
	public function get_categories() {
		return [ 'gms-elements' ];
	}

	protected function has_valid_link( array $link ): bool {
		$url = trim( (string) ( $link['url'] ?? '' ) );

		return '' !== $url && '#' !== $url;
	}

	protected function render_link( string $attribute_key, array $link, string $text, string $class_name = '', array $attributes = [] ): bool {
		$text = trim( $text );

		if ( '' === $text || ! $this->has_valid_link( $link ) ) {
			return false;
		}

		if ( '' !== $class_name ) {
			$this->add_render_attribute( $attribute_key, 'class', $class_name );
		}

		$this->add_link_attributes( $attribute_key, $link );

		foreach ( $attributes as $name => $value ) {
			if ( '' === $value || null === $value ) {
				continue;
			}

			$this->add_render_attribute( $attribute_key, $name, $value );
		}

		printf(
			'<a %1$s>%2$s</a>',
			$this->get_render_attribute_string( $attribute_key ),
			esc_html( $text )
		);

		return true;
	}

	protected function add_section_heading_controls( $defaults = [] ) {
		$this->add_control(
			'eyebrow',
			[
				'label'       => __( 'Eyebrow', 'grow-my-security' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $defaults['eyebrow'] ?? '',
				'label_block' => true,
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Title', 'grow-my-security' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => $defaults['title'] ?? '',
				'label_block' => true,
			]
		);

		$this->add_control(
			'description',
			[
				'label'       => __( 'Description', 'grow-my-security' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => $defaults['description'] ?? '',
				'label_block' => true,
			]
		);
	}

	protected function add_box_style_controls( $section_id, $selector ) {
		$this->start_controls_section(
			$section_id,
			[
				'label' => __( 'Box Style', 'grow-my-security' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			$section_id . '_background',
			[
				'label'     => __( 'Background', 'grow-my-security' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$section_id . '_text_color',
			[
				'label'     => __( 'Text Color', 'grow-my-security' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector . ', ' . $selector . ' h1, ' . $selector . ' h2, ' . $selector . ' h3, ' . $selector . ' h4, ' . $selector . ' p, ' . $selector . ' li' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			$section_id . '_padding',
			[
				'label'      => __( 'Padding', 'grow-my-security' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => $section_id . '_heading_typography',
				'label'    => __( 'Heading Typography', 'grow-my-security' ),
				'selector' => $selector . ' h1, ' . $selector . ' h2, ' . $selector . ' h3, ' . $selector . ' h4',
			]
		);

		$this->end_controls_section();
	}

	protected function add_widget_style_controls( $section_id, $selector ) {
		$this->start_controls_section(
			$section_id,
			[
				'label' => __( 'Section Style', 'grow-my-security' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => $section_id . '_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => $selector,
			]
		);

		$this->add_control(
			$section_id . '_text_color',
			[
				'label'     => __( 'Text Color', 'grow-my-security' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector . ', ' . $selector . ' h1, ' . $selector . ' h2, ' . $selector . ' h3, ' . $selector . ' h4, ' . $selector . ' h5, ' . $selector . ' h6, ' . $selector . ' p, ' . $selector . ' li, ' . $selector . ' a, ' . $selector . ' span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			$section_id . '_padding',
			[
				'label'      => __( 'Padding', 'grow-my-security' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			$section_id . '_border_radius',
			[
				'label'      => __( 'Border Radius', 'grow-my-security' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render_media_image( array $image, array $args = [] ): void {
		$url = trim( (string) ( $image['url'] ?? '' ) );

		if ( function_exists( '\gms_normalize_media_url' ) ) {
			$url = trim( (string) \gms_normalize_media_url( $url ) );
		}

		if ( '' === $url ) {
			return;
		}

		$loading = 'eager' === ( $args['loading'] ?? 'lazy' ) ? 'eager' : 'lazy';
		$attributes = [
			'class'         => trim( (string) ( $args['class'] ?? '' ) ),
			'alt'           => array_key_exists( 'alt', $args ) ? (string) $args['alt'] : (string) ( $image['alt'] ?? '' ),
			'decoding'      => (string) ( $args['decoding'] ?? 'async' ),
			'loading'       => $loading,
			'fetchpriority' => (string) ( $args['fetchpriority'] ?? ( 'eager' === $loading ? 'high' : 'low' ) ),
		];
		$attributes = array_filter(
			$attributes,
			static function ( $value, $key ) {
				if ( null === $value ) {
					return false;
				}

				return 'alt' === $key || '' !== $value;
			},
			ARRAY_FILTER_USE_BOTH
		);
		$attachment_id = absint( $image['id'] ?? 0 );
		$size          = $args['size'] ?? 'full';

		if ( $attachment_id ) {
			echo wp_kses_post( wp_get_attachment_image( $attachment_id, $size, false, $attributes ) );
			return;
		}

		$attribute_string = '';
		foreach ( $attributes as $name => $value ) {
			$attribute_string .= sprintf( ' %1$s="%2$s"', esc_attr( $name ), esc_attr( $value ) );
		}

		printf( '<img src="%1$s"%2$s>', esc_url( $url ), $attribute_string );
	}

	protected function render_section_heading( $settings ) {
		if ( empty( $settings['eyebrow'] ) && empty( $settings['title'] ) && empty( $settings['description'] ) ) {
			return;
		}

		echo '<div class="gms-section-heading">';

		if ( ! empty( $settings['eyebrow'] ) ) {
			echo '<div class="gms-eyebrow">' . esc_html( $settings['eyebrow'] ) . '</div>';
		}

		if ( ! empty( $settings['title'] ) ) {
			echo '<h2>' . esc_html( $settings['title'] ) . '</h2>';
		}

		if ( ! empty( $settings['description'] ) ) {
			echo '<p>' . esc_html( $settings['description'] ) . '</p>';
		}

		echo '</div>';
	}
}
