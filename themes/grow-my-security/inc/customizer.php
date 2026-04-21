<?php
/**
 * Theme Customizer settings.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gms_get_theme_style_defaults(): array {
	$config = gms_get_demo_config();
	$tokens = $config['tokens'] ?? [];

	return [
		'gms_accent_color'            => $tokens['accent'] ?? '#ef2014',
		'gms_background_color'        => $tokens['background'] ?? '#130b0a',
		'gms_background_alt_color'    => $tokens['background_2'] ?? '#241816',
		'gms_surface_color'           => $tokens['surface'] ?? '#2a1110',
		'gms_surface_alt_color'       => $tokens['surface_soft'] ?? '#332c2b',
		'gms_text_color'              => $tokens['text'] ?? '#f5f1ee',
		'gms_text_muted_color'        => $tokens['text_muted'] ?? '#c2b8b2',
		'gms_heading_font'            => $tokens['heading_font'] ?? 'Manrope',
		'gms_body_font'               => $tokens['body_font'] ?? 'Inter',
		'gms_base_font_size'          => 15.5,
		'gms_content_width'           => 1288,
		'gms_content_gutter'          => 32,
		'gms_section_gap'             => 104,
		'gms_button_radius'           => 8,
		'gms_header_background_color' => '#1a0b0a',
		'gms_footer_background_color' => '#090505',
		'gms_site_background_image'   => '',
	];
}

function gms_get_font_choices(): array {
	return [
		'Inter'     => __( 'Inter', 'grow-my-security' ),
		'Manrope'   => __( 'Manrope', 'grow-my-security' ),
		'System UI' => __( 'System Sans', 'grow-my-security' ),
		'Georgia'   => __( 'Georgia Serif', 'grow-my-security' ),
	];
}

function gms_normalize_font_choice( string $value ): string {
	$map = [
		'inter'     => 'Inter',
		'manrope'   => 'Manrope',
		'system ui' => 'System UI',
		'system-ui' => 'System UI',
		'system'    => 'System UI',
		'georgia'   => 'Georgia',
	];

	$value = trim( $value );

	if ( isset( $map[ strtolower( $value ) ] ) ) {
		return $map[ strtolower( $value ) ];
	}

	return $value;
}

function gms_sanitize_font_choice( $value ): string {
	$value   = gms_normalize_font_choice( (string) $value );
	$choices = array_keys( gms_get_font_choices() );

	return in_array( $value, $choices, true ) ? $value : 'Inter';
}

function gms_get_font_stack( string $value, string $fallback = 'Inter' ): string {
	switch ( gms_sanitize_font_choice( $value ?: $fallback ) ) {
		case 'Manrope':
			return '"Manrope", sans-serif';
		case 'System UI':
			return 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
		case 'Georgia':
			return 'Georgia, "Times New Roman", serif';
		case 'Inter':
		default:
			return '"Inter", sans-serif';
	}
}

function gms_sanitize_positive_int( $value ): int {
	return max( 1, absint( $value ) );
}

function gms_sanitize_positive_float( $value ): float {
	$value = is_numeric( $value ) ? (float) $value : 0;

	return $value > 0 ? $value : 1;
}

function gms_add_customizer_color_setting( \WP_Customize_Manager $wp_customize, string $setting_id, string $label, string $section, string $default ): void {
	$wp_customize->add_setting(
		$setting_id,
		[
			'default'           => $default,
			'sanitize_callback' => 'sanitize_hex_color',
		]
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			$setting_id,
			[
				'label'   => $label,
				'section' => $section,
			]
		)
	);
}

function gms_add_customizer_number_setting( \WP_Customize_Manager $wp_customize, string $setting_id, string $label, string $section, $default, $min, $max, $step, string $sanitize_callback ): void {
	$wp_customize->add_setting(
		$setting_id,
		[
			'default'           => $default,
			'sanitize_callback' => $sanitize_callback,
		]
	);

	$wp_customize->add_control(
		$setting_id,
		[
			'label'       => $label,
			'section'     => $section,
			'type'        => 'number',
			'input_attrs' => [
				'min'  => $min,
				'max'  => $max,
				'step' => $step,
			],
		]
	);
}

function gms_add_customizer_select_setting( \WP_Customize_Manager $wp_customize, string $setting_id, string $label, string $section, string $default, array $choices ): void {
	$wp_customize->add_setting(
		$setting_id,
		[
			'default'           => $default,
			'sanitize_callback' => 'gms_sanitize_font_choice',
		]
	);

	$wp_customize->add_control(
		$setting_id,
		[
			'label'   => $label,
			'section' => $section,
			'type'    => 'select',
			'choices' => $choices,
		]
	);
}

function gms_register_customizer( \WP_Customize_Manager $wp_customize ): void {
	$defaults = gms_get_theme_style_defaults();
	$fonts    = gms_get_font_choices();

	$wp_customize->add_panel(
		'gms_site_controls',
		[
			'title'       => __( 'Grow My Security Controls', 'grow-my-security' ),
			'description' => __( 'Global design controls for branding, typography, layout, and theme backgrounds.', 'grow-my-security' ),
			'priority'    => 160,
		]
	);

	$wp_customize->add_section(
		'gms_style_colors',
		[
			'title'    => __( 'Brand Colors', 'grow-my-security' ),
			'panel'    => 'gms_site_controls',
			'priority' => 10,
		]
	);

	gms_add_customizer_color_setting( $wp_customize, 'gms_accent_color', __( 'Accent Color', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_accent_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_background_color', __( 'Primary Background', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_background_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_background_alt_color', __( 'Secondary Background', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_background_alt_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_surface_color', __( 'Surface Color', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_surface_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_surface_alt_color', __( 'Soft Surface Color', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_surface_alt_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_text_color', __( 'Primary Text Color', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_text_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_text_muted_color', __( 'Muted Text Color', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_text_muted_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_header_background_color', __( 'Header Background', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_header_background_color'] );
	gms_add_customizer_color_setting( $wp_customize, 'gms_footer_background_color', __( 'Footer Background', 'grow-my-security' ), 'gms_style_colors', $defaults['gms_footer_background_color'] );

	$wp_customize->add_section(
		'gms_style_typography',
		[
			'title'    => __( 'Typography', 'grow-my-security' ),
			'panel'    => 'gms_site_controls',
			'priority' => 20,
		]
	);

	gms_add_customizer_select_setting( $wp_customize, 'gms_heading_font', __( 'Heading Font', 'grow-my-security' ), 'gms_style_typography', $defaults['gms_heading_font'], $fonts );
	gms_add_customizer_select_setting( $wp_customize, 'gms_body_font', __( 'Body Font', 'grow-my-security' ), 'gms_style_typography', $defaults['gms_body_font'], $fonts );
	gms_add_customizer_number_setting( $wp_customize, 'gms_base_font_size', __( 'Base Font Size (px)', 'grow-my-security' ), 'gms_style_typography', $defaults['gms_base_font_size'], 12, 24, 0.1, 'gms_sanitize_positive_float' );

	$wp_customize->add_section(
		'gms_style_layout',
		[
			'title'    => __( 'Layout', 'grow-my-security' ),
			'panel'    => 'gms_site_controls',
			'priority' => 30,
		]
	);

	gms_add_customizer_number_setting( $wp_customize, 'gms_content_width', __( 'Content Width (px)', 'grow-my-security' ), 'gms_style_layout', $defaults['gms_content_width'], 960, 1680, 1, 'gms_sanitize_positive_int' );
	gms_add_customizer_number_setting( $wp_customize, 'gms_content_gutter', __( 'Content Gutter (px)', 'grow-my-security' ), 'gms_style_layout', $defaults['gms_content_gutter'], 12, 80, 1, 'gms_sanitize_positive_int' );
	gms_add_customizer_number_setting( $wp_customize, 'gms_section_gap', __( 'Section Gap (px)', 'grow-my-security' ), 'gms_style_layout', $defaults['gms_section_gap'], 32, 180, 1, 'gms_sanitize_positive_int' );
	gms_add_customizer_number_setting( $wp_customize, 'gms_button_radius', __( 'Button Radius (px)', 'grow-my-security' ), 'gms_style_layout', $defaults['gms_button_radius'], 0, 60, 1, 'gms_sanitize_positive_int' );

	$wp_customize->add_section(
		'gms_style_media',
		[
			'title'       => __( 'Theme Backgrounds', 'grow-my-security' ),
			'description' => __( 'Section-specific background images can be changed directly inside Elementor widget style controls.', 'grow-my-security' ),
			'panel'       => 'gms_site_controls',
			'priority'    => 40,
		]
	);

	$wp_customize->add_setting(
		'gms_site_background_image',
		[
			'default'           => $defaults['gms_site_background_image'],
			'sanitize_callback' => 'esc_url_raw',
		]
	);

	$wp_customize->add_control(
		new \WP_Customize_Image_Control(
			$wp_customize,
			'gms_site_background_image',
			[
				'label'   => __( 'Site Background Image', 'grow-my-security' ),
				'section' => 'gms_style_media',
			]
		)
	);
}
add_action( 'customize_register', 'gms_register_customizer' );
