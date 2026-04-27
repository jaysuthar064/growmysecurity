<?php
/**
 * Grow My Security theme bootstrap.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/demo-data.php';
require_once get_template_directory() . '/inc/service-detail-data.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/elementor.php';
require_once get_template_directory() . '/inc/public-pages.php';
require_once get_template_directory() . '/inc/industry-data.php';
require_once get_template_directory() . '/inc/elementor-sync.php';

function gms_should_skip_runtime_elementor_sync(): bool {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}

	if ( wp_doing_ajax() ) {
		return true;
	}

	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
		return true;
	}

	if ( isset( $_GET['action'] ) && 'elementor' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
		return true;
	}

	if ( isset( $_GET['elementor-preview'] ) ) {
		return true;
	}

	return ! is_admin();
}

function gms_should_skip_runtime_maintenance_tasks(): bool {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}

	if ( wp_doing_ajax() ) {
		return true;
	}

	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
		return true;
	}

	if ( isset( $_GET['action'] ) && 'elementor' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
		return true;
	}

	if ( isset( $_GET['elementor-preview'] ) ) {
		return true;
	}

	return ! is_admin();
}

function gms_get_request_origin() {
	static $origin = null;

	if ( null !== $origin ) {
		return $origin;
	}

	$cli_runtime     = ( PHP_SAPI === 'cli' ) || ( defined( 'WP_CLI' ) && WP_CLI );
	$forwarded_proto = (string) ( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' );
	$https_enabled   = ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'];
	$scheme          = ( $https_enabled || false !== stripos( $forwarded_proto, 'https' ) ) ? 'https' : 'http';
	$host            = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_X_ORIGINAL_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
	$host            = preg_replace( '/[^A-Za-z0-9\.\-:]/', '', (string) $host );

	if ( '' === $host && ! empty( $_SERVER['SERVER_NAME'] ) ) {
		$host = preg_replace( '/[^A-Za-z0-9\.\-:]/', '', (string) $_SERVER['SERVER_NAME'] );

		if ( '' !== $host && ! preg_match( '/:\d+$/', $host ) && ! empty( $_SERVER['SERVER_PORT'] ) ) {
			$server_port = (int) $_SERVER['SERVER_PORT'];

			if ( $server_port > 0 && ! in_array( $server_port, [ 80, 443 ], true ) ) {
				$host .= ':' . $server_port;
			}
		}
	}

	if ( '' === $host ) {
		$host = 'https' === $scheme ? 'tonetically-nonmetric-kirstie.ngrok-free.dev' : ( $cli_runtime ? 'localhost' : 'localhost:8881' );
	}

	$origin = $scheme . '://' . $host;

	return $origin;
}

function gms_is_local_origin( $origin = null ) {
	$origin = is_string( $origin ) ? $origin : gms_get_request_origin();

	if ( '' === $origin ) {
		return true;
	}

	return 1 === preg_match( '#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?$#i', $origin );
}

function gms_normalize_public_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}

	$origin = gms_get_request_origin();

	$url = preg_replace( '#^//(?:localhost|127\.0\.0\.1)(?::\d+)?#i', preg_replace( '#^https?:#', '', $origin ), $url );

	return preg_replace( '#https?://(?:localhost|127\.0\.0\.1)(?::\d+)?#i', $origin, $url );
}

function gms_filter_public_url( $url ) {
	return gms_normalize_public_url( $url );
}

function gms_normalize_case_study_asset_url( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return $value;
	}

	$value = str_replace(
		[
			'assets/images/cs-cloud.png',
			'assets/images/cs-phishing.png',
			'assets/images/cs-health.png',
		],
		[
			'assets/images/case-studies/cs-cloud.png',
			'assets/images/case-studies/cs-phishing.png',
			'assets/images/case-studies/cs-health.png',
		],
		$value
	);

	return gms_normalize_public_url( $value );
}

add_filter(
	'wp_get_attachment_url',
	static function ( $url ) {
		return gms_normalize_case_study_asset_url( $url );
	},
	20
);

function gms_filter_public_upload_dir( $uploads ) {
	if ( ! is_array( $uploads ) || empty( $uploads['baseurl'] ) ) {
		return $uploads;
	}

	$uploads['baseurl'] = gms_normalize_public_url( $uploads['baseurl'] );

	if ( ! empty( $uploads['url'] ) ) {
		$uploads['url'] = gms_normalize_public_url( $uploads['url'] );
	}

	return $uploads;
}

function gms_start_public_origin_buffer() {
	$origin = gms_get_request_origin();

	if ( gms_is_local_origin( $origin ) || is_admin() || wp_doing_ajax() ) {
		return;
	}

	ob_start(
		static function ( $buffer ) use ( $origin ) {
			if ( ! is_string( $buffer ) || '' === $buffer ) {
				return $buffer;
			}

			$escaped_origin = str_replace( '/', '\\/', $origin );
			$origin_host    = preg_replace( '#^https?:#', '', $origin );
			$buffer         = str_replace( [ '//localhost:8881', '//localhost' ], $origin_host, $buffer );

			return str_replace(
				[
					'http://localhost:8881',
					'https://localhost:8881',
					'http:\\/\\/localhost:8881',
					'https:\\/\\/localhost:8881',
				],
				[
					$origin,
					$origin,
					$escaped_origin,
					$escaped_origin,
				],
				$buffer
			);
		}
	);
}
add_action( 'template_redirect', 'gms_start_public_origin_buffer', 0 );

add_filter( 'option_home', 'gms_filter_public_url' );
add_filter( 'option_siteurl', 'gms_filter_public_url' );
add_filter( 'home_url', 'gms_filter_public_url' );
add_filter( 'site_url', 'gms_filter_public_url' );
add_filter( 'content_url', 'gms_filter_public_url' );
add_filter( 'plugins_url', 'gms_filter_public_url' );
add_filter( 'includes_url', 'gms_filter_public_url' );
add_filter( 'theme_file_uri', 'gms_filter_public_url' );
add_filter( 'stylesheet_directory_uri', 'gms_filter_public_url' );
add_filter( 'template_directory_uri', 'gms_filter_public_url' );
add_filter( 'script_loader_src', 'gms_filter_public_url' );
add_filter( 'style_loader_src', 'gms_filter_public_url' );
add_filter( 'post_link', 'gms_filter_public_url' );
add_filter( 'page_link', 'gms_filter_public_url' );
add_filter( 'post_type_link', 'gms_filter_public_url' );
add_filter( 'term_link', 'gms_filter_public_url' );
add_filter( 'attachment_link', 'gms_filter_public_url' );
add_filter( 'upload_dir', 'gms_filter_public_upload_dir' );

function gms_theme_version() {
	$theme = wp_get_theme( 'grow-my-security' );

	return $theme->get( 'Version' ) ?: '1.0.0';
}

function gms_asset_version( $relative_path ) {
	static $versions = [];

	$relative_path = ltrim( (string) $relative_path, '/\\' );

	if ( isset( $versions[ $relative_path ] ) ) {
		return $versions[ $relative_path ];
	}

	if ( '' !== $relative_path ) {
		$absolute_path = get_theme_file_path( $relative_path );

		if ( is_string( $absolute_path ) && '' !== $absolute_path && file_exists( $absolute_path ) ) {
			$versions[ $relative_path ] = (string) filemtime( $absolute_path );

			return $versions[ $relative_path ];
		}
	}

	$versions[ $relative_path ] = gms_theme_version();

	return $versions[ $relative_path ];
}


function gms_upload_asset_url( $path = '' ) {
	$path = trim( (string) $path, "/\\" );

	if ( '' === $path ) {
		return content_url( 'uploads/gms-assets' );
	}

	$segments = array_map(
		'rawurlencode',
		preg_split( '#[\\\\/]#', $path )
	);

	return trailingslashit( content_url( 'uploads/gms-assets' ) ) . implode( '/', $segments );
}
function gms_get_upload_asset_index(): array {
	static $asset_index = null;

	if ( null !== $asset_index ) {
		return $asset_index;
	}

	$asset_index = [];
	$directory   = trailingslashit( WP_CONTENT_DIR ) . 'uploads/gms-assets';

	if ( ! is_dir( $directory ) ) {
		return $asset_index;
	}

	foreach ( glob( $directory . '/*' ) ?: [] as $asset_path ) {
		if ( ! is_file( $asset_path ) ) {
			continue;
		}

		$filename                               = wp_basename( $asset_path );
		$asset_index[ strtolower( $filename ) ] = $filename;
	}

	return $asset_index;
}

function gms_get_brand_asset_map(): array {
	return [
		'logo'                => 'logo.png',
		'home-hero-1'         => 'Slider #1.png',
		'home-hero-2'         => 'Slider #2.png',
		'home-hero-3'         => 'Slider #3.png',
		'page-about'          => 'About Us.png',
		'page-contact'        => 'Contact Us.png',
		'page-faq'            => 'FAQ.png',
		'page-resources'      => 'Blogs.png',
		'page-press'          => 'Press & Media.png',
		'page-podcast'        => 'Podcast.png',
		'page-industries'     => 'Industries.png',
		'page-services'       => 'Services.png',
		'page-service-detail' => 'Single Service.png',
		'page-single-post'    => 'Single Blog.png',
		'page-single-press'   => 'Single Press.png',
		'service-archive-art' => 'Services-1.png',
		'service-detail-art'  => 'Services-1.png',
		'industry-art'        => 'Industry.png',
		'resource-card'       => 'Resources.png',
	];
}

function gms_find_upload_asset_filename( string $filename ): string {
	$asset_index = gms_get_upload_asset_index();

	return $asset_index[ strtolower( $filename ) ] ?? '';
}

function gms_get_brand_asset_url( string $key_or_filename, string $fallback = '' ): string {
	$key_or_filename = trim( $key_or_filename );

	if ( '' === $key_or_filename ) {
		return $fallback;
	}

	$map             = gms_get_brand_asset_map();
	$target_filename = $map[ $key_or_filename ] ?? $key_or_filename;
	$resolved_name   = gms_find_upload_asset_filename( $target_filename );

	if ( '' !== $resolved_name ) {
		return gms_upload_asset_url( $resolved_name );
	}

	if ( '' !== $fallback ) {
		return $fallback;
	}

	if ( false !== strpos( $target_filename, '/' ) || false !== strpos( $target_filename, '\\' ) ) {
		return get_theme_file_uri( ltrim( $target_filename, '/\\' ) );
	}

	return get_theme_file_uri( 'assets/images/' . $target_filename );
}

function gms_get_service_archive_hero_media(): array {
	$preferred_filename = 'service-archive-hero-v2.png';
	$resolved_name      = gms_find_upload_asset_filename( $preferred_filename );

	if ( '' !== $resolved_name ) {
		$url = gms_upload_asset_url( $resolved_name );

		return [
			'id'  => function_exists( 'attachment_url_to_postid' ) ? absint( attachment_url_to_postid( $url ) ) : 0,
			'url' => $url,
		];
	}

	return [
		'url' => get_theme_file_uri( 'assets/images/services-hero-v4-final.png' ),
	];

}

function gms_normalize_media_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );

	if ( ! is_string( $path ) || '' === $path ) {
		return $url;
	}

	$basename = wp_basename( $path );
	$aliases  = [
		'logo.png'               => 'logo',
		'hero-slide-1.png'       => 'home-hero-1',
		'hero-slide-2.png'       => 'home-hero-2',
		'hero-slide-3.png'       => 'home-hero-3',
		'cs-cloud.png'           => 'assets/images/case-studies/cs-cloud.png',
		'cs-health.png'          => 'assets/images/case-studies/cs-health.png',
		'cs-phishing.png'        => 'assets/images/case-studies/cs-phishing.png',
		'cs-ransomware.png'      => 'assets/images/case-studies/cs-ransomware.png',
		'cs-revenue.png'         => 'assets/images/case-studies/cs-revenue.png',
		'cs-soc.png'             => 'assets/images/case-studies/cs-soc.png',
		'cs-zero-trust.png'      => 'assets/images/case-studies/cs-zero-trust.png',
		'resources.png'          => 'resource-card',
		'industry.png'           => 'industry-art',
		'industry-hero-lock.png' => 'industry-art',
		'services-icon.png'      => 'service-detail-art',
		'service-overview.png'   => 'service-detail-art',
	];

	$basename_key = strtolower( $basename );

	// Only rewrite known branded placeholders. Arbitrary uploaded media, especially
	// videos selected in Elementor, should keep their original URLs.
	if ( ! isset( $aliases[ $basename_key ] ) ) {
		return $url;
	}

	$asset_key = $aliases[ $basename_key ];
	$asset_url = gms_get_brand_asset_url( $asset_key );

	return '' !== $asset_url ? $asset_url : $url;
}

function gms_get_page_url_by_path( string $path ): string {
	$page = get_page_by_path( trim( $path, '/' ) );

	if ( ! $page instanceof WP_Post ) {
		return '';
	}

	return get_permalink( $page );
}

function gms_get_footer_company_links(): array {
	$links = [
		[
			'label' => __( 'About', 'grow-my-security' ),
			'url'   => gms_get_page_url_by_path( 'about-us' ),
		],
		[
			'label' => __( 'Contact Us', 'grow-my-security' ),
			'url'   => gms_get_page_url_by_path( 'contact-us' ),
		],
		[
			'label' => __( 'Resources', 'grow-my-security' ),
			'url'   => gms_get_page_url_by_path( 'resources-insights' ),
		],
		[
			'label' => __( 'FAQ', 'grow-my-security' ),
			'url'   => gms_get_page_url_by_path( 'faq' ),
		],
	];

	$privacy_policy_url = get_privacy_policy_url();

	if ( $privacy_policy_url ) {
		$links[] = [
			'label' => __( 'Privacy Policy', 'grow-my-security' ),
			'url'   => $privacy_policy_url,
		];
	}

	return array_values(
		array_filter(
			$links,
			static function ( array $link ): bool {
				return ! empty( $link['url'] );
			}
		)
	);
}

function gms_post_has_elementor_content( int $post_id ): bool {
	$edit_mode = get_post_meta( $post_id, '_elementor_edit_mode', true );

	if ( empty( $edit_mode ) ) {
		return false;
	}

	$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

	if ( is_array( $elementor_data ) ) {
		return ! empty( $elementor_data );
	}

	if ( is_string( $elementor_data ) ) {
		$elementor_data = trim( $elementor_data );

		return '' !== $elementor_data && '[]' !== $elementor_data;
	}

	return false;
}

function gms_get_elementor_preview_post_id(): int {
	if ( isset( $_GET['elementor-preview'] ) ) {
		return absint( wp_unslash( $_GET['elementor-preview'] ) );
	}

	return 0;
}

function gms_resolve_elementor_fallback_post( $post = null ): ?WP_Post {
	$post = $post instanceof WP_Post ? $post : get_post( $post );

	if ( $post instanceof WP_Post ) {
		return $post;
	}

	if ( function_exists( 'gms_is_elementor_preview_request' ) && gms_is_elementor_preview_request() ) {
		$preview_post_id = gms_get_elementor_preview_post_id();

		if ( $preview_post_id > 0 ) {
			$post = get_post( $preview_post_id );

			if ( $post instanceof WP_Post ) {
				return $post;
			}
		}
	}

	$queried_post_id = (int) get_queried_object_id();

	if ( $queried_post_id > 0 ) {
		$post = get_post( $queried_post_id );

		if ( $post instanceof WP_Post ) {
			return $post;
		}
	}

	return null;
}

function gms_is_homepage_elementor_context( $post = null ): bool {
	if ( is_front_page() ) {
		return true;
	}

	if ( ! function_exists( 'gms_is_elementor_preview_request' ) || ! gms_is_elementor_preview_request() ) {
		return false;
	}

	$page_on_front = (int) get_option( 'page_on_front' );

	if ( $page_on_front <= 0 ) {
		return false;
	}

	$post = gms_resolve_elementor_fallback_post( $post );

	return $post instanceof WP_Post && (int) $post->ID === $page_on_front;
}

function gms_should_render_elementor_content_fallback( $post = null ): bool {
	if ( is_admin() || wp_doing_ajax() ) {
		return false;
	}

	if ( ! function_exists( 'gms_is_elementor_preview_request' ) || ! gms_is_elementor_preview_request() ) {
		return false;
	}

	return gms_resolve_elementor_fallback_post( $post ) instanceof WP_Post;
}

function gms_render_elementor_content_fallback( $post = null ): bool {
	if ( ! gms_should_render_elementor_content_fallback( $post ) ) {
		return false;
	}

	$post = gms_resolve_elementor_fallback_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$is_home_preview = gms_is_homepage_elementor_context( $post );
	$previous_post   = $GLOBALS['post'] ?? null;
	$GLOBALS['post'] = $post;
	setup_postdata( $post );
	?>
	<?php if ( $is_home_preview ) : ?>
		<div class="gms-homepage gms-homepage--elementor-editor">
			<?php if ( ! gms_output_elementor_builder_markup( $post ) ) : ?>
				<?php the_content(); ?>
			<?php endif; ?>
			<?php if ( function_exists( 'gms_render_homepage_footer' ) ) : ?>
				<?php gms_render_homepage_footer(); ?>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<div class="gms-page-shell gms-page-shell--elementor-editor">
			<div class="gms-page-content gms-page-content--elementor">
				<?php if ( ! gms_output_elementor_builder_markup( $post ) ) : ?>
					<?php the_content(); ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php

	wp_reset_postdata();

	if ( $previous_post instanceof WP_Post ) {
		$GLOBALS['post'] = $previous_post;
		setup_postdata( $previous_post );
	} else {
		unset( $GLOBALS['post'] );
	}

	return true;
}

function gms_render_elementor_editor_bridge( $post = null ): bool {
	if ( ! gms_should_render_elementor_content_fallback( $post ) ) {
		return false;
	}

	$post = gms_resolve_elementor_fallback_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$previous_post   = $GLOBALS['post'] ?? null;
	$GLOBALS['post'] = $post;
	setup_postdata( $post );
	?>
	<div class="gms-elementor-editor-bridge" style="display:none !important;" aria-hidden="true">
		<?php if ( ! gms_output_elementor_builder_markup( $post ) ) : ?>
			<?php the_content(); ?>
		<?php endif; ?>
	</div>
	<?php
	wp_reset_postdata();

	if ( $previous_post instanceof WP_Post ) {
		$GLOBALS['post'] = $previous_post;
		setup_postdata( $previous_post );
	}

	return true;
}

function gms_get_dom_node_inner_html( DOMNode $node ): string {
	$html = '';

	foreach ( $node->childNodes as $child_node ) {
		$html .= $node->ownerDocument->saveHTML( $child_node );
	}

	return $html;
}

function gms_strip_elementor_layout_wrappers( string $html ): string {
	$html = trim( $html );

	if ( '' === $html || ! class_exists( 'DOMDocument' ) ) {
		return $html;
	}

	$previous_errors = libxml_use_internal_errors( true );
	$dom             = new DOMDocument( '1.0', 'UTF-8' );
	$document_html   = '<?xml encoding="UTF-8">' . '<!DOCTYPE html><html><body><div id="gms-elementor-root">' . $html . '</div></body></html>';

	$loaded = $dom->loadHTML( $document_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

	if ( ! $loaded ) {
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );

		return $html;
	}

	$xpath      = new DOMXPath( $dom );
	$containers = $xpath->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' elementor-widget-container ')]" );

	if ( ! $containers instanceof DOMNodeList || 0 === $containers->length ) {
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );

		return $html;
	}

	$stripped_html = '';

	foreach ( $containers as $container ) {
		$container_html = trim( gms_get_dom_node_inner_html( $container ) );

		if ( '' !== $container_html ) {
			$stripped_html .= $container_html;
		}
	}

	libxml_clear_errors();
	libxml_use_internal_errors( $previous_errors );

	return '' !== trim( $stripped_html ) ? $stripped_html : $html;
}

function gms_get_elementor_builder_markup( int $post_id ): string {
	if ( $post_id <= 0 || ! class_exists( '\Elementor\Plugin' ) ) {
		return '';
	}

	$plugin = \Elementor\Plugin::$instance ?? null;

	if ( ! $plugin || empty( $plugin->frontend ) || ! method_exists( $plugin->frontend, 'get_builder_content_for_display' ) ) {
		return '';
	}

	$markup = $plugin->frontend->get_builder_content_for_display( $post_id, true );

	return is_string( $markup ) ? trim( $markup ) : '';
}

function gms_get_elementor_builder_markup_cache_key( int $post_id ): string {
	$raw_data      = get_post_meta( $post_id, '_elementor_data', true );
	$page_settings = get_post_meta( $post_id, '_elementor_page_settings', true );
	$cache_source  = wp_json_encode(
		[
			'post_id'         => $post_id,
			'post_modified'   => get_post_field( 'post_modified_gmt', $post_id ),
			'elementor_data'  => $raw_data,
			'page_settings'   => $page_settings,
			'theme_version'   => gms_theme_version(),
			'preview_context' => function_exists( 'gms_is_elementor_preview_request' ) && gms_is_elementor_preview_request(),
		]
	);

	return 'gms_el_markup_' . $post_id . '_' . substr( md5( (string) $cache_source ), 0, 12 );
}

function gms_get_cached_elementor_builder_markup( int $post_id ): string {
	if ( $post_id <= 0 ) {
		return '';
	}

	$cache_key = gms_get_elementor_builder_markup_cache_key( $post_id );
	$markup    = get_transient( $cache_key );

	if ( is_string( $markup ) && '' !== $markup ) {
		return $markup;
	}

	$markup = gms_get_elementor_builder_markup( $post_id );

	if ( '' !== $markup ) {
		set_transient( $cache_key, $markup, 10 * MINUTE_IN_SECONDS );
	}

	return $markup;
}

function gms_output_elementor_builder_markup( WP_Post $post ): bool {
	if ( function_exists( 'gms_is_elementor_preview_request' ) && gms_is_elementor_preview_request() ) {
		return false;
	}

	$markup = gms_get_cached_elementor_builder_markup( (int) $post->ID );

	if ( '' === $markup ) {
		return false;
	}

	echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	return true;
}

function gms_render_elementor_widget_content_only( $post = null ): bool {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$markup = gms_get_cached_elementor_builder_markup( (int) $post->ID );

	if ( '' === $markup ) {
		return false;
	}

	echo gms_strip_elementor_layout_wrappers( $markup ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	return true;
}

function gms_force_fresh_front_page_template( string $template ): string {
	if ( ! is_front_page() ) {
		return $template;
	}

	$fresh_template = get_theme_file_path( 'front-page-render.php' );

	if ( is_string( $fresh_template ) && '' !== $fresh_template && file_exists( $fresh_template ) ) {
		return $fresh_template;
	}

	return $template;
}
add_filter( 'frontpage_template', 'gms_force_fresh_front_page_template', 99 );

function gms_elementor_page_has_hero( int $post_id ): bool {
	$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

	if ( ! is_string( $elementor_data ) || '' === $elementor_data ) {
		return false;
	}

	return false !== strpos( $elementor_data, 'gms-page-hero' ) || false !== strpos( $elementor_data, 'gms-hero' );
}

function gms_should_render_page_hero( WP_Post $post, bool $is_elementor_page ): bool {
	if ( is_front_page() ) {
		return false;
	}

	if ( $is_elementor_page && gms_elementor_page_has_hero( $post->ID ) ) {
		return false;
	}

	return true;
}

function gms_get_page_hero_copy_map(): array {
	return [
		'about-us' => [
			'eyebrow'     => __( 'About Us', 'grow-my-security' ),
			'description' => __( 'Grow My Security was built for buyers who need proof before they trust a marketing partner. Everything we ship is designed to make expertise visible, credible, and commercially useful.', 'grow-my-security' ),
			'image'       => 'page-about',
			'primary'     => [ 'label' => __( 'Explore Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
			'secondary'   => [ 'label' => __( 'Book a Strategy Call', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
			'chips'       => [ __( 'Veteran-led', 'grow-my-security' ), __( 'Security-first', 'grow-my-security' ), __( 'Trust systems', 'grow-my-security' ) ],
		],
		'contact-us' => [
			'eyebrow'     => __( 'Contact Us', 'grow-my-security' ),
			'description' => __( 'Talk through the growth challenge, trust gap, or visibility problem you need solved and we will map the next highest-leverage move with you.', 'grow-my-security' ),
			'image'       => 'page-contact',
			'primary'     => [ 'label' => __( 'View Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
			'secondary'   => [ 'label' => __( 'See Resources', 'grow-my-security' ), 'url' => home_url( '/resources-insights/' ) ],
			'chips'       => [ __( 'Chicago, IL', 'grow-my-security' ), __( 'Discovery call', 'grow-my-security' ), __( 'Demand strategy', 'grow-my-security' ) ],
		],
		'resources-insights' => [
			'eyebrow'     => __( 'Resources', 'grow-my-security' ),
			'description' => __( 'Editorial guidance, category insight, and tactical thinking for security companies building authority in a market where trust is earned slowly and lost quickly.', 'grow-my-security' ),
			'image'       => 'page-resources',
			'primary'     => [ 'label' => __( 'Book a Strategy Call', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
			'secondary'   => [ 'label' => __( 'Browse Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
			'chips'       => [ __( 'Thought leadership', 'grow-my-security' ), __( 'Demand generation', 'grow-my-security' ), __( 'Brand trust', 'grow-my-security' ) ],
		],
		'press-media' => [
			'eyebrow'     => __( 'Press & Media', 'grow-my-security' ),
			'description' => __( 'Coverage, interviews, and commentary that support a more visible and more trusted market presence for security brands navigating high-stakes buying journeys.', 'grow-my-security' ),
			'image'       => 'page-press',
			'primary'     => [ 'label' => __( 'Talk to the Team', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
			'secondary'   => [ 'label' => __( 'Read Insights', 'grow-my-security' ), 'url' => home_url( '/resources-insights/' ) ],
			'chips'       => [ __( 'Interviews', 'grow-my-security' ), __( 'Commentary', 'grow-my-security' ), __( 'Industry signals', 'grow-my-security' ) ],
		],
		'podcast' => [
			'eyebrow'     => __( 'Podcast', 'grow-my-security' ),
			'description' => __( 'Conversations for founders, operators, and growth leaders shaping how trust is built, communicated, and converted inside the security industry.', 'grow-my-security' ),
			'image'       => 'page-podcast',
			'primary'     => [ 'label' => __( 'Start a Conversation', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
			'secondary'   => [ 'label' => __( 'See Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
			'chips'       => [ __( 'Founder stories', 'grow-my-security' ), __( 'Growth operators', 'grow-my-security' ), __( 'Security buyers', 'grow-my-security' ) ],
		],
		'faq' => [
			'eyebrow'     => __( 'FAQ', 'grow-my-security' ),
			'description' => __( 'Answers to the questions security teams ask before committing to a growth partner, from strategic fit and service scope to how we balance credibility with performance.', 'grow-my-security' ),
			'image'       => 'page-faq',
			'primary'     => [ 'label' => __( 'Contact Us', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
			'secondary'   => [ 'label' => __( 'Explore Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
			'chips'       => [ __( 'Scope clarity', 'grow-my-security' ), __( 'Process visibility', 'grow-my-security' ), __( 'Buyer trust', 'grow-my-security' ) ],
		],
	];
}

function gms_get_page_hero_data( WP_Post $post ): array {
	$page_copy_map = gms_get_page_hero_copy_map();
	$page_copy     = $page_copy_map[ $post->post_name ] ?? [];
	$excerpt       = has_excerpt( $post ) ? wp_strip_all_tags( get_the_excerpt( $post ) ) : '';
	$description   = $page_copy['description'] ?? $excerpt;

	if ( '' === $description ) {
		$description = __( 'Premium cybersecurity marketing systems designed to unify visibility, authority, and conversion across every public touchpoint.', 'grow-my-security' );
	}

	return [
		'eyebrow'   => $page_copy['eyebrow'] ?? __( 'Grow My Security', 'grow-my-security' ),
		'title'     => get_the_title( $post ),
		'copy'      => $description,
		'image_url' => gms_get_brand_asset_url( $page_copy['image'] ?? 'logo' ),
		'primary'   => $page_copy['primary'] ?? [ 'label' => __( 'Contact Us', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
		'secondary' => $page_copy['secondary'] ?? [ 'label' => __( 'Our Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
		'chips'     => $page_copy['chips'] ?? [ __( 'Cybersecurity', 'grow-my-security' ), __( 'Trust-led growth', 'grow-my-security' ) ],
		'classes'   => [ 'gms-page-hero--context', 'gms-page-hero--page' ],
	];
}

function gms_get_contextual_archive_image_key(): string {
	if ( is_category( 'press' ) ) {
		return 'page-press';
	}

	if ( is_category( 'podcast' ) ) {
		return 'page-podcast';
	}

	return 'page-resources';
}

function gms_get_search_hero_data(): array {
	global $wp_query;

	$results_count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
	$description   = 0 === $results_count
		? __( 'We could not find a direct match, but you can refine your query or jump into the most useful site sections below.', 'grow-my-security' )
		: sprintf(
			_n( 'We found %d relevant result across the site, including articles, resources, and strategic pages.', 'We found %d relevant results across the site, including articles, resources, and strategic pages.', $results_count, 'grow-my-security' ),
			$results_count
		);

	return [
		'eyebrow'   => __( 'Search Results', 'grow-my-security' ),
		'title'     => sprintf( __( 'Results for "%s"', 'grow-my-security' ), get_search_query() ),
		'copy'      => $description,
		'image_url' => gms_get_brand_asset_url( 'page-resources' ),
		'primary'   => [ 'label' => __( 'Explore Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
		'secondary' => [ 'label' => __( 'Contact Us', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
		'chips'     => [ sprintf( _n( '%d result', '%d results', $results_count, 'grow-my-security' ), $results_count ), __( 'Search the full site', 'grow-my-security' ) ],
		'classes'   => [ 'gms-page-hero--context', 'gms-page-hero--archive' ],
	];
}

function gms_get_archive_hero_data(): array {
	global $wp_query;

	$results_count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
	$description   = trim( wp_strip_all_tags( get_the_archive_description() ) );

	if ( '' === $description ) {
		$description = __( 'Editorial coverage and practical insight for security teams building visibility, credibility, and higher-conviction demand.', 'grow-my-security' );
	}

	return [
		'eyebrow'   => __( 'Archive', 'grow-my-security' ),
		'title'     => wp_strip_all_tags( get_the_archive_title() ),
		'copy'      => $description,
		'image_url' => gms_get_brand_asset_url( gms_get_contextual_archive_image_key() ),
		'primary'   => [ 'label' => __( 'Book a Strategy Call', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
		'secondary' => [ 'label' => __( 'View Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
		'chips'     => [ sprintf( _n( '%d story', '%d stories', $results_count, 'grow-my-security' ), $results_count ), __( 'Fresh insight', 'grow-my-security' ) ],
		'classes'   => [ 'gms-page-hero--context', 'gms-page-hero--archive' ],
	];
}

function gms_get_single_post_hero_data( WP_Post $post ): array {
	$categories = get_the_category( $post->ID );
	$read_time  = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post ) ) ) / 220 ) );
	$image_key  = has_category( 'press', $post ) || has_category( 'podcast', $post ) ? 'page-single-press' : 'page-single-post';
	$chips      = [
		get_the_date( 'M j, Y', $post ),
		sprintf( _n( '%d min read', '%d min read', $read_time, 'grow-my-security' ), $read_time ),
	];

	if ( ! empty( $categories ) ) {
		array_unshift( $chips, $categories[0]->name );
	}

	return [
		'eyebrow'   => __( 'Insight', 'grow-my-security' ),
		'title'     => get_the_title( $post ),
		'copy'      => wp_strip_all_tags( get_the_excerpt( $post ) ?: __( 'A trust-building perspective for security brands navigating high-consideration growth decisions.', 'grow-my-security' ) ),
		'image_url' => gms_get_brand_asset_url( $image_key ),
		'primary'   => [ 'label' => __( 'Explore More Insights', 'grow-my-security' ), 'url' => home_url( '/resources-insights/' ) ],
		'secondary' => [ 'label' => __( 'Talk to the Team', 'grow-my-security' ), 'url' => home_url( '/contact-us/' ) ],
		'chips'     => $chips,
		'classes'   => [ 'gms-page-hero--context', 'gms-page-hero--single' ],
	];
}

function gms_get_404_hero_data(): array {
	return [
		'eyebrow'   => __( 'Page Not Found', 'grow-my-security' ),
		'title'     => __( 'The page you requested is off the grid.', 'grow-my-security' ),
		'copy'      => __( 'The route may have changed, the content may have been moved, or the link may be outdated. Use the search below or jump to the site sections people visit most often.', 'grow-my-security' ),
		'image_url' => gms_get_brand_asset_url( 'logo' ),
		'primary'   => [ 'label' => __( 'Go Home', 'grow-my-security' ), 'url' => home_url( '/' ) ],
		'secondary' => [ 'label' => __( 'Explore Services', 'grow-my-security' ), 'url' => home_url( '/services/' ) ],
		'chips'     => [ __( 'Search the site', 'grow-my-security' ), __( 'Helpful links below', 'grow-my-security' ) ],
		'classes'   => [ 'gms-page-hero--context', 'gms-page-hero--not-found' ],
	];
}

function gms_render_page_hero( array $args ): void {
	$args = wp_parse_args(
		$args,
		[
			'eyebrow'   => '',
			'title'     => '',
			'copy'      => '',
			'image_url' => '',
			'primary'   => [],
			'secondary' => [],
			'chips'     => [],
			'classes'   => [],
			'extra'     => '',
		]
	);

	if ( '' === trim( (string) $args['title'] ) ) {
		return;
	}

	$classes = array_filter(
		array_merge(
			[ 'gms-page-hero', 'gms-page-hero--context' ],
			(array) $args['classes']
		)
	);
	?>
	<section class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>">
		<div class="gms-page-hero__copy">
			<?php if ( '' !== trim( (string) $args['eyebrow'] ) ) : ?>
				<div class="gms-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></div>
			<?php endif; ?>

			<h1><?php echo esc_html( $args['title'] ); ?></h1>

			<?php if ( '' !== trim( (string) $args['copy'] ) ) : ?>
				<p><?php echo esc_html( $args['copy'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $args['chips'] ) ) : ?>
				<div class="gms-chip-list" aria-label="<?php esc_attr_e( 'Highlights', 'grow-my-security' ); ?>">
					<?php foreach ( $args['chips'] as $chip ) : ?>
						<?php if ( '' === trim( (string) $chip ) ) { continue; } ?>
						<span><?php echo esc_html( $chip ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $args['primary']['url'] ) || ! empty( $args['secondary']['url'] ) ) : ?>
				<div class="gms-page-hero__actions">
					<?php if ( ! empty( $args['primary']['url'] ) && ! empty( $args['primary']['label'] ) ) : ?>
						<a class="gms-button" href="<?php echo esc_url( $args['primary']['url'] ); ?>"><?php echo esc_html( $args['primary']['label'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $args['secondary']['url'] ) && ! empty( $args['secondary']['label'] ) ) : ?>
						<a class="gms-button-outline" href="<?php echo esc_url( $args['secondary']['url'] ); ?>"><?php echo esc_html( $args['secondary']['label'] ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( '' !== trim( (string) $args['extra'] ) ) : ?>
				<div class="gms-page-hero__extra">
					<?php echo $args['extra']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( '' !== trim( (string) $args['image_url'] ) ) : ?>
			<div class="gms-page-hero__art gms-page-hero__art--context">
				<img src="<?php echo esc_url( $args['image_url'] ); ?>" alt="" decoding="async" loading="eager" fetchpriority="high">
			</div>
		<?php endif; ?>
	</section>
	<?php
}

function gms_get_post_card_meta_label( WP_Post $post ): string {
	if ( 'post' === $post->post_type ) {
		$categories = get_the_category( $post->ID );

		if ( ! empty( $categories ) ) {
			return $categories[0]->name;
		}
	}

	$post_type_object = get_post_type_object( $post->post_type );

	return $post_type_object->labels->singular_name ?? __( 'Resource', 'grow-my-security' );
}

function gms_get_post_card_cta_label( WP_Post $post ): string {
	return 'page' === $post->post_type ? __( 'View page', 'grow-my-security' ) : __( 'Read article', 'grow-my-security' );
}

function gms_get_post_card_image_url( WP_Post $post ): string {
	if ( function_exists( 'gms_get_theme_controlled_post_image_url' ) ) {
		$override = gms_get_theme_controlled_post_image_url( $post );

		if ( '' !== $override ) {
			return (string) gms_normalize_case_study_asset_url( $override );
		}
	}

	if ( has_post_thumbnail( $post ) ) {
		return (string) gms_normalize_case_study_asset_url( (string) get_the_post_thumbnail_url( $post, 'gms-card' ) );
	}

	if ( 'page' === $post->post_type ) {
		return (string) gms_normalize_case_study_asset_url( gms_get_page_hero_data( $post )['image_url'] ?? gms_get_brand_asset_url( 'resource-card' ) );
	}

	if ( has_category( 'press', $post ) || has_category( 'podcast', $post ) ) {
		return (string) gms_normalize_case_study_asset_url( gms_get_brand_asset_url( 'page-single-press' ) );
	}

	return (string) gms_normalize_case_study_asset_url( gms_get_brand_asset_url( 'resource-card', get_theme_file_uri( 'assets/images/image-2.png' ) ) );
}

function gms_get_case_study_image_url( $post, string $size = 'large' ): string {
	$post = get_post( $post );

	if ( ! ( $post instanceof WP_Post ) ) {
		return '';
	}

	if ( has_post_thumbnail( $post ) ) {
		return (string) gms_normalize_case_study_asset_url( (string) get_the_post_thumbnail_url( $post, $size ) );
	}

	foreach ( [ 'gms_cs_image_url', 'gms_cs_visual_url' ] as $meta_key ) {
		$image_url = (string) get_post_meta( $post->ID, $meta_key, true );

		if ( '' !== $image_url ) {
			return (string) gms_normalize_case_study_asset_url( $image_url );
		}
	}

	return '';
}

function gms_filter_case_study_permalink( string $post_link, WP_Post $post, bool $leavename, bool $sample ): string {
	if ( 'gms_case_study' !== $post->post_type ) {
		return $post_link;
	}

	$slug = $leavename ? '%postname%' : $post->post_name;

	if ( '' === $slug || 0 !== strpos( $slug, 'case-study-' ) ) {
		return $post_link;
	}

	return home_url( user_trailingslashit( $slug ) );
}
add_filter( 'post_type_link', 'gms_filter_case_study_permalink', 10, 4 );

function gms_register_case_study_root_rewrites(): void {
	add_rewrite_rule(
		'^case-study-([^/]+)/?$',
		'index.php?post_type=gms_case_study&name=case-study-$matches[1]',
		'top'
	);
}
add_action( 'init', 'gms_register_case_study_root_rewrites', 20 );

function gms_render_post_card( WP_Post $post ): void {
	$image_url     = gms_get_post_card_image_url( $post );
	$classes       = implode( ' ', get_post_class( 'gms-post-card', $post ) );
	$image_loading = function_exists( 'gms_is_editorial_resources_context' ) && gms_is_editorial_resources_context() ? 'eager' : 'lazy';
	?>
	<article class="<?php echo esc_attr( $classes ); ?>">
		<a class="gms-post-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php if ( '' !== $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title( $post ) ); ?>" decoding="async" loading="<?php echo esc_attr( $image_loading ); ?>">
			<?php elseif ( has_post_thumbnail( $post ) ) : ?>
				<?php echo get_the_post_thumbnail( $post, 'gms-card', [ 'alt' => get_the_title( $post ), 'loading' => $image_loading ] ); ?>
			<?php endif; ?>
		</a>
		<div class="gms-post-card__body">
			<div class="gms-post-card__meta"><?php echo esc_html( gms_get_post_card_meta_label( $post ) ); ?></div>
			<h2 class="gms-post-card__title">
				<a class="gms-post-card__title-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</h2>
			<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ?: wp_strip_all_tags( $post->post_content ) ), 24, '...' ) ); ?></p>
			<div class="gms-post-card__actions">
				<a class="gms-post-card__cta" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( gms_get_post_card_cta_label( $post ) ); ?></a>
			</div>
		</div>
	</article>
	<?php
}

function gms_get_single_related_posts( int $post_id, int $limit = 3 ): array {
	$categories = wp_get_post_categories( $post_id );
	$query_args = [
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'post__not_in'        => [ $post_id ],
		'posts_per_page'      => $limit,
		'ignore_sticky_posts' => true,
	];

	if ( ! empty( $categories ) ) {
		$query_args['category__in'] = $categories;
	}

	$query = new WP_Query( $query_args );

	if ( ! empty( $query->posts ) ) {
		return $query->posts;
	}

	$fallback_query = new WP_Query(
		[
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__not_in'        => [ $post_id ],
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
		]
	);

	return $fallback_query->posts;
}
function gms_theme_setup() {
	load_theme_textdomain( 'grow-my-security', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', [ 'height' => 75, 'width' => 250, 'flex-height' => true, 'flex-width' => true ] );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );

	register_nav_menus(
		[
			'primary' => __( 'Primary Menu', 'grow-my-security' ),
			'footer'  => __( 'Footer Menu', 'grow-my-security' ),
		]
	);

	add_image_size( 'gms-card', 760, 520, true );
	add_editor_style(
		[
			'assets/css/fonts.css',
			'assets/css/editor.css',
		]
	);
}
add_action( 'after_setup_theme', 'gms_theme_setup' );

function gms_register_testimonial_post_type() {
	register_post_type(
		'gms_testimonial',
		[
			'labels' => [
				'name'               => __( 'Testimonials', 'grow-my-security' ),
				'singular_name'      => __( 'Testimonial', 'grow-my-security' ),
				'add_new_item'       => __( 'Add New Testimonial', 'grow-my-security' ),
				'edit_item'          => __( 'Edit Testimonial', 'grow-my-security' ),
				'new_item'           => __( 'New Testimonial', 'grow-my-security' ),
				'view_item'          => __( 'View Testimonial', 'grow-my-security' ),
				'search_items'       => __( 'Search Testimonials', 'grow-my-security' ),
				'not_found'          => __( 'No testimonials found.', 'grow-my-security' ),
				'not_found_in_trash' => __( 'No testimonials found in Trash.', 'grow-my-security' ),
			],
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-format-quote',
			'menu_position'       => 25,
			'supports'            => [ 'title', 'editor', 'excerpt', 'page-attributes' ],
			'has_archive'         => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
		]
	);
}
add_action( 'init', 'gms_register_testimonial_post_type' );

function gms_get_homepage_testimonials(): array {
	$posts = get_posts(
		[
			'post_type'      => 'gms_testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => [
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			],
			'order'          => 'ASC',
		]
	);

	$testimonials = [];

	foreach ( $posts as $post ) {
		$quote = trim( wp_strip_all_tags( $post->post_content ) );
		$name  = trim( get_the_title( $post ) );
		$role  = trim( wp_strip_all_tags( $post->post_excerpt ) );

		if ( '' === $quote || '' === $name ) {
			continue;
		}

		$testimonials[] = [
			'quote' => $quote,
			'name'  => $name,
			'role'  => $role,
		];
	}

	if ( ! empty( $testimonials ) ) {
		return $testimonials;
	}

	return [
		[
			'quote' => 'Anthony Rumore is exactly the kind of man you want to do business with. He is patient, professional and extremely knowledgeable. The most amazing quality, above all else, he is trustworthy and honest!!! In an era where it is very easy to choose the wrong person to work with. Anthony is dependable, reliable, you can count on what he says he will accomplish for you!!' . "\n\n" . 'Who you work with truly matters!! ANTHONY RUMORE is who you want to work with!!',
			'name'  => 'Valerie LaBianca',
			'role'  => 'Co-founder (Vista Tech)',
		],
	];
}

function gms_enqueue_assets() {
	wp_enqueue_style(
		'grow-my-security-fonts',
		get_theme_file_uri( 'assets/css/fonts.css' ),
		[],
		gms_asset_version( 'assets/css/fonts.css' )
	);

	wp_enqueue_style(
		'grow-my-security-style',
		get_stylesheet_uri(),
		[ 'grow-my-security-fonts' ],
		gms_asset_version( 'style.css' )
	);
	wp_add_inline_style( 'grow-my-security-style', gms_get_dynamic_theme_css() );

	if ( ! is_front_page() ) {
		wp_enqueue_script(
			'grow-my-security-script',
			get_template_directory_uri() . '/assets/js/theme.js',
			[],
			gms_asset_version( 'assets/js/theme.js' ),
			true
		);
		wp_script_add_data( 'grow-my-security-script', 'strategy', 'defer' );
	}
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_assets' );

function gms_enqueue_layout_overrides() {
	if ( is_front_page() ) {
		return;
	}

	wp_enqueue_style(
		'grow-my-security-layout-overrides',
		get_theme_file_uri( 'assets/css/layout-overrides.css' ),
		[ 'grow-my-security-style' ],
		gms_asset_version( 'assets/css/layout-overrides.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_layout_overrides', 999 );

function gms_enqueue_front_page_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	if ( function_exists( 'gms_is_elementor_preview_request' ) && gms_is_elementor_preview_request() ) {
		return;
	}

	wp_enqueue_style(
		'grow-my-security-swiper',
		'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
		[],
		'11.0.0'
	);

	wp_enqueue_style(
		'grow-my-security-front-page-font',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'grow-my-security-front-page',
		get_theme_file_uri( 'assets/css/home-custom.css' ),
		[ 'grow-my-security-style', 'grow-my-security-front-page-font', 'grow-my-security-swiper' ],
		time()
	);

	wp_enqueue_script(
		'grow-my-security-swiper',
		'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
		[],
		'11.0.0',
		true
	);

	wp_enqueue_script(
		'grow-my-security-home-custom',
		get_theme_file_uri( 'assets/js/home-custom.js' ),
		[ 'grow-my-security-swiper' ],
		gms_asset_version( 'assets/js/home-custom.js' ),
		true
	);
	wp_script_add_data( 'grow-my-security-home-custom', 'strategy', 'defer' );

	wp_enqueue_script(
		'grow-my-security-services-tabs',
		get_theme_file_uri( 'assets/js/gms-services-tabs.js' ),
		[],
		gms_asset_version( 'assets/js/gms-services-tabs.js' ),
		true
	);
	wp_script_add_data( 'grow-my-security-services-tabs', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_front_page_assets', 1000 );
function gms_enqueue_elementor_preview_assets() {
	if ( ! function_exists( 'gms_is_elementor_preview_request' ) || ! gms_is_elementor_preview_request() ) {
		return;
	}

	if ( ! function_exists( 'gms_is_homepage_elementor_context' ) || ! gms_is_homepage_elementor_context() ) {
		return;
	}

	wp_enqueue_style(
		'grow-my-security-front-page',
		get_theme_file_uri( 'assets/css/home-custom.css' ),
		[ 'grow-my-security-style' ],
		gms_asset_version( 'assets/css/home-custom.css' )
	);

	wp_enqueue_style(
		'grow-my-security-case-studies-preview',
		get_theme_file_uri( 'assets/css/case-studies.css' ),
		[ 'grow-my-security-style', 'grow-my-security-front-page' ],
		gms_asset_version( 'assets/css/case-studies.css' )
	);

	wp_enqueue_style(
		'grow-my-security-elementor-home-preview',
		get_theme_file_uri( 'assets/css/elementor-home-preview.css' ),
		[ 'grow-my-security-front-page', 'grow-my-security-case-studies-preview' ],
		gms_asset_version( 'assets/css/elementor-home-preview.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_elementor_preview_assets', 1003 );

function gms_enqueue_site_page_assets() {
	if ( is_front_page() ) {
		return;
	}

	wp_enqueue_style(
		'grow-my-security-site-pages',
		get_theme_file_uri( 'assets/css/site-pages.css' ),
		[ 'grow-my-security-layout-overrides' ],
		gms_asset_version( 'assets/css/site-pages.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_site_page_assets', 1001 );

function gms_enqueue_exact_clone_assets() {
	if ( is_front_page() ) {
		return;
	}

	wp_enqueue_style(
		'grow-my-security-exact-clone',
		get_theme_file_uri( 'assets/css/exact-clone.css' ),
		[ 'grow-my-security-site-pages' ],
		gms_asset_version( 'assets/css/exact-clone.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_exact_clone_assets', 1002 );

function gms_hex_to_rgba( $color, float $alpha ): string {
	$color = sanitize_hex_color( $color );

	if ( ! $color ) {
		return 'rgba(239, 32, 20, ' . $alpha . ')';
	}

	$red   = hexdec( substr( $color, 1, 2 ) );
	$green = hexdec( substr( $color, 3, 2 ) );
	$blue  = hexdec( substr( $color, 5, 2 ) );

	return sprintf( 'rgba(%d, %d, %d, %.2f)', $red, $green, $blue, $alpha );
}

function gms_get_dynamic_theme_css() {
	$defaults               = gms_get_theme_style_defaults();
	$accent                 = sanitize_hex_color( get_theme_mod( 'gms_accent_color', $defaults['gms_accent_color'] ) ) ?: $defaults['gms_accent_color'];
	$background             = sanitize_hex_color( get_theme_mod( 'gms_background_color', $defaults['gms_background_color'] ) ) ?: $defaults['gms_background_color'];
	$background_alt         = sanitize_hex_color( get_theme_mod( 'gms_background_alt_color', $defaults['gms_background_alt_color'] ) ) ?: $defaults['gms_background_alt_color'];
	$surface                = sanitize_hex_color( get_theme_mod( 'gms_surface_color', $defaults['gms_surface_color'] ) ) ?: $defaults['gms_surface_color'];
	$surface_alt            = sanitize_hex_color( get_theme_mod( 'gms_surface_alt_color', $defaults['gms_surface_alt_color'] ) ) ?: $defaults['gms_surface_alt_color'];
	$text                   = sanitize_hex_color( get_theme_mod( 'gms_text_color', $defaults['gms_text_color'] ) ) ?: $defaults['gms_text_color'];
	$text_muted             = sanitize_hex_color( get_theme_mod( 'gms_text_muted_color', $defaults['gms_text_muted_color'] ) ) ?: $defaults['gms_text_muted_color'];
	$heading                = gms_get_font_stack( (string) get_theme_mod( 'gms_heading_font', $defaults['gms_heading_font'] ), $defaults['gms_heading_font'] );
	$body                   = gms_get_font_stack( (string) get_theme_mod( 'gms_body_font', $defaults['gms_body_font'] ), $defaults['gms_body_font'] );
	$base_font_size         = gms_sanitize_positive_float( get_theme_mod( 'gms_base_font_size', $defaults['gms_base_font_size'] ) );
	$content_width          = gms_sanitize_positive_int( get_theme_mod( 'gms_content_width', $defaults['gms_content_width'] ) );
	$content_gutter         = gms_sanitize_positive_int( get_theme_mod( 'gms_content_gutter', $defaults['gms_content_gutter'] ) );
	$section_gap            = gms_sanitize_positive_int( get_theme_mod( 'gms_section_gap', $defaults['gms_section_gap'] ) );
	$button_radius          = gms_sanitize_positive_int( get_theme_mod( 'gms_button_radius', $defaults['gms_button_radius'] ) );
	$header_background      = sanitize_hex_color( get_theme_mod( 'gms_header_background_color', $defaults['gms_header_background_color'] ) ) ?: $defaults['gms_header_background_color'];
	$footer_background      = sanitize_hex_color( get_theme_mod( 'gms_footer_background_color', $defaults['gms_footer_background_color'] ) ) ?: $defaults['gms_footer_background_color'];
	$site_background_image  = esc_url_raw( (string) get_theme_mod( 'gms_site_background_image', $defaults['gms_site_background_image'] ) );
	$site_background_css    = $site_background_image ? 'url("' . $site_background_image . '")' : 'none';
	$accent_glow            = gms_hex_to_rgba( $accent, 0.38 );

	return sprintf(
		':root{--gms-accent:%1$s;--gms-accent-soft:%1$s;--gms-accent-glow:%2$s;--gms-bg:%3$s;--gms-bg-2:%4$s;--gms-surface:%5$s;--gms-surface-2:%6$s;--gms-text:%7$s;--gms-text-muted:%8$s;--gms-text-subtle:%8$s;--gms-font-heading:%9$s;--gms-font-body:%10$s;--gms-base-font-size:%11$spx;--gms-content-width:%12$spx;--gms-content-gutter:%13$spx;--gms-section-gap:%14$spx;--gms-button-radius:%15$spx;--gms-header-bg:%16$s;--gms-footer-bg:%17$s;--gms-site-bg-image:%18$s;--e-global-typography-primary-font-family:%9$s;--e-global-typography-primary-font-weight:700;--e-global-typography-secondary-font-family:%9$s;--e-global-typography-secondary-font-weight:600;--e-global-typography-text-font-family:%10$s;--e-global-typography-text-font-weight:400;--e-global-typography-accent-font-family:%10$s;--e-global-typography-accent-font-weight:600;}',
		$accent,
		$accent_glow,
		$background,
		$background_alt,
		$surface,
		$surface_alt,
		$text,
		$text_muted,
		$heading,
		$body,
		$base_font_size,
		$content_width,
		$content_gutter,
		$section_gap,
		$button_radius,
		$header_background,
		$footer_background,
		$site_background_css
	);
}

function gms_disable_unused_frontend_assets() {
	if ( is_admin() ) {
		return;
	}

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_footer', 'wp_print_speculation_rules' );
}
add_action( 'init', 'gms_disable_unused_frontend_assets' );

add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

function gms_excerpt_more( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'gms_excerpt_more' );

function gms_render_fallback_primary_menu() {
	$config = gms_get_demo_config();

	echo '<ul class="gms-nav-list">';
	foreach ( $config['pages'] as $page ) {
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( home_url( '/' . $page['slug'] . '/' ) ),
			esc_html( $page['title'] )
		);
	}
	echo '</ul>';
}

function gms_get_logo_markup( $context = 'default' ) {
	$attributes = [
		'class'         => 'gms-logo-image',
		'alt'           => get_bloginfo( 'name' ),
		'decoding'      => 'async',
		'fetchpriority' => 'footer' === $context ? 'low' : '',
		'loading'       => 'header' === $context ? 'eager' : 'lazy',
	];
	$fetchpriority  = '' !== $attributes['fetchpriority'] ? ' fetchpriority="' . esc_attr( $attributes['fetchpriority'] ) . '"' : '';

	return sprintf(
		'<img class="gms-logo-image" src="%1$s" alt="%2$s" decoding="async" loading="%3$s"%4$s>',
		esc_url( gms_get_brand_asset_url( 'logo' ) ),
		esc_attr( get_bloginfo( 'name' ) ),
		esc_attr( $attributes['loading'] ),
		$fetchpriority
	);
}

function gms_get_footer_social_links(): array {
	return [
		[
			'label' => __( 'Instagram', 'grow-my-security' ),
			'slug'  => 'instagram',
			'url'   => 'https://www.instagram.com/grow_my_security_company',
			'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.75 3h8.5A4.75 4.75 0 0 1 21 7.75v8.5A4.75 4.75 0 0 1 16.25 21h-8.5A4.75 4.75 0 0 1 3 16.25v-8.5A4.75 4.75 0 0 1 7.75 3Zm0 1.8A2.95 2.95 0 0 0 4.8 7.75v8.5a2.95 2.95 0 0 0 2.95 2.95h8.5a2.95 2.95 0 0 0 2.95-2.95v-8.5a2.95 2.95 0 0 0-2.95-2.95h-8.5Zm8.95 1.35a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 7.6A4.4 4.4 0 1 1 7.6 12 4.4 4.4 0 0 1 12 7.6Zm0 1.8A2.6 2.6 0 1 0 14.6 12 2.6 2.6 0 0 0 12 9.4Z" fill="currentColor"/></svg>',
		],
		[
			'label' => __( 'LinkedIn', 'grow-my-security' ),
			'slug'  => 'linkedin',
			'url'   => 'https://www.linkedin.com/in/anthonyrumore/',
			'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 8.7A1.55 1.55 0 1 1 6.59 5.6a1.55 1.55 0 0 1 .01 3.1ZM5.3 9.9h2.6v8.8H5.3V9.9Zm4.24 0h2.49v1.2h.04c.35-.65 1.2-1.34 2.47-1.34 2.64 0 3.13 1.74 3.13 4v4.94h-2.6v-4.38c0-1.04-.02-2.39-1.46-2.39-1.46 0-1.69 1.14-1.69 2.31v4.46h-2.6V9.9Z" fill="currentColor"/></svg>',
		],
		[
			'label' => __( 'X', 'grow-my-security' ),
			'slug'  => 'x',
			'url'   => 'https://x.com/GrowMySecCo',
			'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.7 5h2.84l2.94 4.2L16.05 5H19l-5.06 5.79L19.6 19h-2.84l-3.19-4.56L9.59 19H6.64l5.22-5.98L6.7 5Zm3.37 1.8H9.61l6.02 8.4h.46l-6.02-8.4Z" fill="currentColor"/></svg>',
		],
		[
			'label' => __( 'YouTube', 'grow-my-security' ),
			'slug'  => 'youtube',
			'url'   => 'https://www.youtube.com/channel/UChLRamRn4bSSkHSiB6odxUQ',
			'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8.2a2.8 2.8 0 0 0-1.97-1.98C17.28 5.75 12 5.75 12 5.75s-5.28 0-7.03.47A2.8 2.8 0 0 0 3 8.2 29.2 29.2 0 0 0 2.53 12c0 1.29.16 2.57.47 3.8a2.8 2.8 0 0 0 1.97 1.98c1.75.47 7.03.47 7.03.47s5.28 0 7.03-.47A2.8 2.8 0 0 0 21 15.8c.31-1.23.47-2.51.47-3.8 0-1.29-.16-2.57-.47-3.8ZM10.2 14.72V9.28L14.8 12l-4.6 2.72Z" fill="currentColor"/></svg>',
		],
		[
			'label' => __( 'TikTok', 'grow-my-security' ),
			'slug'  => 'tiktok',
			'url'   => 'https://www.tiktok.com/@grow_my_security_company',
			'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.89-.6-4.13-1.47-.13-.09-.26-.18-.38-.28v6.49c-.01 1.89-.55 3.84-1.84 5.23-1.43 1.56-3.66 2.38-5.74 2.22-2.31-.17-4.52-1.57-5.59-3.61-1.12-2.1-1.01-4.83.21-6.84 1.07-1.78 3.01-2.98 5.09-3.21v4.3c-1.04.14-2.15.79-2.61 1.74-.47.96-.39 2.21.2 3.12.58.89 1.64 1.41 2.68 1.34 1.05-.08 2.05-.72 2.45-1.7.15-.36.22-.75.22-1.14V.22a.493.493 0 0 1 .49-.49z" fill="currentColor"/></svg>',
		],
		[
			'label' => __( 'Facebook', 'grow-my-security' ),
			'slug'  => 'facebook',
			'url'   => 'https://www.facebook.com/GrowMySecurityCompany',
			'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7h2.35l.35-2.74H13.5V9.5c0-.79.22-1.33 1.36-1.33h1.45V5.72c-.25-.03-1.11-.1-2.12-.1-2.1 0-3.54 1.28-3.54 3.63v2.01H8.29V14h2.36v7h2.85Z" fill="currentColor"/></svg>',
		],
	];
}

function gms_render_homepage_footer(): void {
	$home_url       = home_url( '/' );
	$config         = gms_get_demo_config();
	$footer_groups  = function_exists( 'gms_get_footer_groups' ) ? gms_get_footer_groups() : [];
	$footer_socials = gms_get_footer_social_links();
	?>
	<footer id="footer" class="gms-homepage-footer">
		<div class="gms-homepage-shell">
			<div class="gms-homepage-footer__grid" style="display: grid !important; grid-template-columns: 340px 120px 259px 222px 103px !important; gap: 60px !important; justify-content: space-between !important;">
				<div class="gms-homepage-footer__brand" style="display: flex !important; flex-direction: column !important; gap: 24px !important; justify-content: flex-start !important; align-items: flex-start !important; max-width: 400px !important;">
					<a class="gms-homepage-brandmark gms-homepage-brandmark--footer" href="<?php echo esc_url( $home_url ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<?php echo gms_get_logo_markup( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<p style="margin: 0 !important;"><?php esc_html_e( 'Grow My Security Company is a full-service digital marketing agency helping security businesses grow with SEO, PPC, website development, and performance marketing.', 'grow-my-security' ); ?></p>
					<div class="gms-footer-contact">
						<a href="mailto:<?php echo esc_attr( $config['branding']['email'] ); ?>"><?php echo esc_html( $config['branding']['email'] ); ?></a>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $config['branding']['phone'] ) ); ?>"><?php echo esc_html( $config['branding']['phone'] ); ?></a>
					</div>
					<div class="gms-badge-group">
						<div class="gms-badge"><?php esc_html_e( 'Veteran-led', 'grow-my-security' ); ?></div>
						<div class="gms-badge"><?php esc_html_e( 'Service-Disabled Veteran', 'grow-my-security' ); ?></div>
					</div>
					<div class="gms-homepage-footer__socials" aria-label="<?php esc_attr_e( 'Social media links', 'grow-my-security' ); ?>">
						<?php foreach ( $footer_socials as $footer_social ) : ?>
							<a class="gms-homepage-footer__social gms-homepage-footer__social--<?php echo esc_attr( $footer_social['slug'] ); ?>" href="<?php echo esc_url( $footer_social['url'] ); ?>" target="_blank" rel="noreferrer noopener" aria-label="<?php echo esc_attr( $footer_social['label'] ); ?>">
								<?php echo $footer_social['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php foreach ( $footer_groups as $group_key => $footer_group ) : ?>
					<?php
					$panel_id     = 'gms-homepage-footer-panel-' . sanitize_html_class( $group_key );
					$is_default   = 'services' === $group_key;
					$column_class = 'gms-homepage-footer__column gms-homepage-footer__column--' . sanitize_html_class( $group_key ) . ( $is_default ? ' is-open' : '' );
					?>
					<section class="<?php echo esc_attr( $column_class ); ?>">
						<button class="gms-homepage-footer__toggle" type="button" aria-expanded="<?php echo $is_default ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-footer-toggle>
							<span><?php echo esc_html( $footer_group['title'] ); ?></span>
							<span class="gms-homepage-footer__chevron" aria-hidden="true"></span>
						</button>
						<div id="<?php echo esc_attr( $panel_id ); ?>" class="gms-homepage-footer__panel"<?php echo $is_default ? '' : ' hidden'; ?> data-footer-panel>
							<ul>
								<?php foreach ( $footer_group['links'] as $footer_link ) : ?>
									<li><a href="<?php echo esc_url( $footer_link['url'] ); ?>"><?php echo esc_html( $footer_link['label'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
							<?php if ( ! empty( $footer_group['subtitle'] ) && ! empty( $footer_group['sub_links'] ) ) : ?>
								<div class="gms-homepage-footer__subgroup">
									<h4><?php echo esc_html( $footer_group['subtitle'] ); ?></h4>
									<ul>
										<?php foreach ( $footer_group['sub_links'] as $footer_link ) : ?>
											<li><a href="<?php echo esc_url( $footer_link['url'] ); ?>"><?php echo esc_html( $footer_link['label'] ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
			<div class="gms-homepage-footer__bottom">
				<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $config['branding']['company'] ); ?>. <?php esc_html_e( 'All rights reserved.', 'grow-my-security' ); ?></p>
			</div>
		</div>
	</footer>
	<?php
}

if ( ! function_exists( 'gms_get_smtp_settings' ) ) {
	function gms_get_smtp_settings(): array {
		// Temporary fallback values for local Studio/testing runtimes that do not
		// load this workspace's wp-config.php credentials.
		$fallback_settings = [
			'enabled'    => true,
			'host'       => 'smtp.gmail.com',
			'port'       => 587,
			'auth'       => true,
			'username'   => 'jayantisuthar094@gmail.com',
			'password'   => 'wgjqgqppofxgrljz',
			'encryption' => 'tls',
			'from_email' => 'jayantisuthar094@gmail.com',
			'from_name'  => 'Grow My Security Company',
		];

		$encryption = defined( 'GMS_SMTP_ENCRYPTION' ) ? strtolower( trim( (string) GMS_SMTP_ENCRYPTION ) ) : 'tls';

		if ( ! in_array( $encryption, [ 'ssl', 'tls', '' ], true ) ) {
			$encryption = 'tls';
		}

		$from_email = defined( 'GMS_SMTP_FROM_EMAIL' ) ? sanitize_email( (string) GMS_SMTP_FROM_EMAIL ) : sanitize_email( $fallback_settings['from_email'] );
		$username   = defined( 'GMS_SMTP_USERNAME' ) ? trim( (string) GMS_SMTP_USERNAME ) : $fallback_settings['username'];

		if ( '' === $from_email && is_email( $username ) ) {
			$from_email = sanitize_email( $username );
		}

		return [
			'enabled'    => defined( 'GMS_SMTP_ENABLED' ) ? (bool) GMS_SMTP_ENABLED : $fallback_settings['enabled'],
			'host'       => defined( 'GMS_SMTP_HOST' ) ? trim( (string) GMS_SMTP_HOST ) : $fallback_settings['host'],
			'port'       => defined( 'GMS_SMTP_PORT' ) ? max( 1, (int) GMS_SMTP_PORT ) : $fallback_settings['port'],
			'auth'       => defined( 'GMS_SMTP_AUTH' ) ? (bool) GMS_SMTP_AUTH : $fallback_settings['auth'],
			'username'   => $username,
			'password'   => defined( 'GMS_SMTP_PASSWORD' ) ? (string) GMS_SMTP_PASSWORD : $fallback_settings['password'],
			'encryption' => $encryption,
			'from_email' => $from_email,
			'from_name'  => defined( 'GMS_SMTP_FROM_NAME' ) ? trim( (string) GMS_SMTP_FROM_NAME ) : $fallback_settings['from_name'],
		];
	}
}

if ( ! function_exists( 'gms_is_smtp_ready' ) ) {
	function gms_is_smtp_ready(): bool {
		$settings = gms_get_smtp_settings();

		if ( ! $settings['enabled'] || '' === $settings['host'] ) {
			return false;
		}

		if ( ! $settings['auth'] ) {
			return true;
		}

		return '' !== $settings['username'] && '' !== $settings['password'];
	}
}

if ( ! function_exists( 'gms_write_mail_debug_log' ) ) {
	function gms_write_mail_debug_log( array $entry ): void {
		$entry['time'] = gmdate( 'c' );
		$line          = wp_json_encode( $entry, JSON_UNESCAPED_SLASHES );

		if ( ! is_string( $line ) || '' === $line ) {
			return;
		}

		$log_dir = trailingslashit( ABSPATH ) . 'tmp';

		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}

		$log_path = trailingslashit( $log_dir ) . 'mail-debug.log';
		file_put_contents( $log_path, $line . PHP_EOL, FILE_APPEND | LOCK_EX );

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( '[GMS mail] ' . $line );
		}
	}
}

if ( ! function_exists( 'gms_store_contact_mail_state' ) ) {
	function gms_store_contact_mail_state( array $state ): void {
		$state['time'] = gmdate( 'c' );
		update_option( 'gms_last_contact_mail_state', $state, false );
	}
}

if ( ! function_exists( 'gms_store_last_mail_event' ) ) {
	function gms_store_last_mail_event( array $event ): void {
		$event['time'] = gmdate( 'c' );
		update_option( 'gms_last_wp_mail_event', $event, false );
	}
}

if ( ! function_exists( 'gms_is_local_runtime' ) ) {
	function gms_is_local_runtime(): bool {
		$environment_type = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : '';

		if ( 'local' === $environment_type ) {
			return true;
		}

		$hosts = [
			(string) wp_parse_url( home_url(), PHP_URL_HOST ),
			isset( $_SERVER['HTTP_HOST'] ) ? (string) wp_unslash( $_SERVER['HTTP_HOST'] ) : '',
			isset( $_SERVER['SERVER_NAME'] ) ? (string) $_SERVER['SERVER_NAME'] : '',
		];

		foreach ( $hosts as $host ) {
			$host = strtolower( trim( $host ) );

			if ( '' === $host ) {
				continue;
			}

			if ( in_array( $host, [ 'localhost', '127.0.0.1', '::1' ], true ) ) {
				return true;
			}

			if ( str_starts_with( $host, 'localhost:' ) || str_starts_with( $host, '127.0.0.1:' ) || str_starts_with( $host, '[::1]:' ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'gms_probe_smtp_socket' ) ) {
	function gms_probe_smtp_socket( string $host, int $port, int $timeout = 5 ): array {
		$result = [
			'host'        => $host,
			'port'        => $port,
			'connected'   => false,
			'error_code'  => 0,
			'error'       => '',
		];

		if ( '' === $host || $port < 1 ) {
			$result['error'] = 'Missing SMTP host or port.';
			return $result;
		}

		$error_code = 0;
		$error      = '';
		$socket     = @fsockopen( $host, $port, $error_code, $error, $timeout );

		if ( is_resource( $socket ) ) {
			$result['connected'] = true;
			fclose( $socket );
		} else {
			$result['error_code'] = (int) $error_code;
			$result['error']      = is_string( $error ) ? $error : '';
		}

		return $result;
	}
}

if ( ! function_exists( 'gms_configure_phpmailer' ) ) {
	function gms_configure_phpmailer( $phpmailer ): void {
		$settings = gms_get_smtp_settings();

		if ( ! $settings['enabled'] || '' === $settings['host'] ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host       = $settings['host'];
		$phpmailer->Port       = $settings['port'];
		$phpmailer->SMTPAuth   = $settings['auth'];
		$phpmailer->Username   = $settings['username'];
		$phpmailer->Password   = $settings['password'];
		$phpmailer->SMTPSecure = $settings['encryption'];
		$phpmailer->Timeout    = 20;

		if ( gms_is_local_runtime() ) {
			$phpmailer->SMTPOptions = [
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				],
			];
		}

		if ( '' !== $settings['from_email'] ) {
			$phpmailer->setFrom( $settings['from_email'], $settings['from_name'], false );
		}
	}
}
add_action( 'phpmailer_init', 'gms_configure_phpmailer' );

if ( ! function_exists( 'gms_filter_wp_mail_from' ) ) {
	function gms_filter_wp_mail_from( string $default_email ): string {
		$settings = gms_get_smtp_settings();

		return '' !== $settings['from_email'] ? $settings['from_email'] : $default_email;
	}
}
add_filter( 'wp_mail_from', 'gms_filter_wp_mail_from' );

if ( ! function_exists( 'gms_filter_wp_mail_from_name' ) ) {
	function gms_filter_wp_mail_from_name( string $default_name ): string {
		$settings = gms_get_smtp_settings();

		return '' !== $settings['from_name'] ? $settings['from_name'] : $default_name;
	}
}
add_filter( 'wp_mail_from_name', 'gms_filter_wp_mail_from_name' );

if ( ! function_exists( 'gms_handle_wp_mail_failed' ) ) {
	function gms_handle_wp_mail_failed( WP_Error $error ): void {
		$data = $error->get_error_data();
		$event = [
			'event'   => 'failed',
			'message' => $error->get_error_message(),
			'code'    => $error->get_error_code(),
			'mailer'  => gms_is_smtp_ready() ? 'smtp' : 'default',
			'to'      => is_array( $data['to'] ?? null ) ? $data['to'] : [],
			'subject' => is_string( $data['subject'] ?? null ) ? $data['subject'] : '',
		];

		gms_store_last_mail_event( $event );
		gms_write_mail_debug_log( $event );
	}
}
add_action( 'wp_mail_failed', 'gms_handle_wp_mail_failed' );

if ( ! function_exists( 'gms_handle_wp_mail_succeeded' ) ) {
	function gms_handle_wp_mail_succeeded( array $mail_data ): void {
		$event = [
			'event'   => 'succeeded',
			'mailer'  => gms_is_smtp_ready() ? 'smtp' : 'default',
			'to'      => is_array( $mail_data['to'] ?? null ) ? $mail_data['to'] : [],
			'subject' => is_string( $mail_data['subject'] ?? null ) ? $mail_data['subject'] : '',
		];

		gms_store_last_mail_event( $event );
		gms_write_mail_debug_log( $event );
	}
}
add_action( 'wp_mail_succeeded', 'gms_handle_wp_mail_succeeded' );

if ( ! function_exists( 'gms_get_contact_form_recipient' ) ) {
	function gms_get_contact_form_recipient(): string {
		$test_recipient = defined( 'GMS_CONTACT_TEST_RECIPIENT' ) ? sanitize_email( (string) GMS_CONTACT_TEST_RECIPIENT ) : 'tony.as.dr.doom300@gmail.com';

		if ( is_email( $test_recipient ) ) {
			return $test_recipient;
		}

		$config    = gms_get_demo_config();
		$recipient = sanitize_email( $config['branding']['email'] ?? '' );

		if ( is_email( $recipient ) ) {
			return $recipient;
		}

		return sanitize_email( get_option( 'admin_email' ) );
	}
}

if ( ! function_exists( 'gms_handle_local_mail_probe' ) ) {
	function gms_handle_local_mail_probe(): void {
		if ( ! isset( $_GET['gms_mail_probe'] ) ) {
			return;
		}

		$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
		$allowed_ips = [ '127.0.0.1', '::1' ];

		if ( ! in_array( $remote_addr, $allowed_ips, true ) ) {
			status_header( 403 );
			wp_die( 'Forbidden' );
		}

		$settings = gms_get_smtp_settings();
		$probe_to = sanitize_email( isset( $_GET['to'] ) ? wp_unslash( $_GET['to'] ) : gms_get_contact_form_recipient() );
		$subject  = 'GMS Studio mail probe';
		$body     = 'Local Studio mail probe on ' . gmdate( 'c' );
		delete_option( 'gms_last_wp_mail_event' );
		$sent     = wp_mail( $probe_to, $subject, $body, [ 'Content-Type: text/plain; charset=UTF-8' ] );

		wp_send_json(
			[
				'remote_addr' => $remote_addr,
				'sent'        => (bool) $sent,
				'probe_to'    => $probe_to,
				'smtp_ready'  => gms_is_smtp_ready(),
				'smtp'        => [
					'enabled'    => (bool) $settings['enabled'],
					'host'       => (string) $settings['host'],
					'port'       => (int) $settings['port'],
					'auth'       => (bool) $settings['auth'],
					'username'   => (string) $settings['username'],
					'encryption' => (string) $settings['encryption'],
					'from_email' => (string) $settings['from_email'],
					'from_name'  => (string) $settings['from_name'],
				],
				'recipient'   => gms_get_contact_form_recipient(),
				'socket'      => gms_probe_smtp_socket( (string) $settings['host'], (int) $settings['port'] ),
				'last_event'  => get_option( 'gms_last_wp_mail_event', null ),
			]
		);
	}
}
add_action( 'init', 'gms_handle_local_mail_probe', 1 );

if ( ! function_exists( 'gms_handle_contact_form_submission' ) ) {
	function gms_handle_contact_form_submission() {
		$redirect = wp_get_referer() ?: home_url( '/contact-us/' );

		if ( ! isset( $_POST['gms_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gms_contact_nonce'] ) ), 'gms_contact_form' ) ) {
			gms_store_contact_mail_state(
				[
					'event'  => 'contact_invalid_nonce',
					'mailer' => gms_is_smtp_ready() ? 'smtp' : 'default',
				]
			);
			gms_write_mail_debug_log(
				[
					'event'  => 'contact_invalid_nonce',
					'mailer' => gms_is_smtp_ready() ? 'smtp' : 'default',
				]
			);
			wp_safe_redirect( add_query_arg( 'gms_contact', 'error', $redirect ) );
			exit;
		}

		$full_name          = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
		$email              = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$phone              = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$company_name       = sanitize_text_field( wp_unslash( $_POST['company_name'] ?? '' ) );
		$industry           = sanitize_text_field( wp_unslash( $_POST['industry'] ?? '' ) );
		$referral_source    = sanitize_text_field( wp_unslash( $_POST['referral_source'] ?? '' ) );
		$service_interest   = sanitize_text_field( wp_unslash( $_POST['service_interest'] ?? '' ) );
		$privacy_acceptance = ! empty( $_POST['privacy_acceptance'] );
		$bot_check          = ! empty( $_POST['bot_check'] );
		$message            = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

		if ( '' === $full_name || ! is_email( $email ) || ! $privacy_acceptance || ! $bot_check ) {
			gms_store_contact_mail_state(
				[
					'event'               => 'contact_invalid_submission',
					'mailer'              => gms_is_smtp_ready() ? 'smtp' : 'default',
					'has_full_name'       => '' !== $full_name,
					'valid_email'         => is_email( $email ),
					'privacy_accepted'    => $privacy_acceptance,
					'bot_check_confirmed' => $bot_check,
				]
			);
			gms_write_mail_debug_log(
				[
					'event'              => 'contact_invalid_submission',
					'mailer'             => gms_is_smtp_ready() ? 'smtp' : 'default',
					'has_full_name'      => '' !== $full_name,
					'valid_email'        => is_email( $email ),
					'privacy_accepted'   => $privacy_acceptance,
					'bot_check_confirmed'=> $bot_check,
				]
			);
			wp_safe_redirect( add_query_arg( 'gms_contact', 'error', $redirect ) );
			exit;
		}

		$recipient  = gms_get_contact_form_recipient();
		$subject    = sprintf( __( 'New website inquiry from %s', 'grow-my-security' ), $full_name );
		$body_lines = [
			'Name: ' . $full_name,
			'Email: ' . $email,
			'Phone: ' . ( $phone ?: 'Not provided' ),
			'Company: ' . ( $company_name ?: 'Not provided' ),
			'Industry: ' . ( $industry ?: 'Not provided' ),
			'Referral Source: ' . ( $referral_source ?: 'Not provided' ),
			'Service Interest: ' . ( $service_interest ?: 'Not provided' ),
			'',
			'Message:',
			$message ?: 'No message provided.',
		];
		$headers    = [
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $full_name . ' <' . $email . '>',
		];

		gms_store_contact_mail_state(
			[
				'event'       => 'contact_attempt',
				'mailer'      => gms_is_smtp_ready() ? 'smtp' : 'default',
				'recipient'   => $recipient,
				'full_name'   => $full_name,
				'email'       => $email,
				'has_phone'   => '' !== $phone,
				'has_company' => '' !== $company_name,
			]
		);

		gms_write_mail_debug_log(
			[
				'event'      => 'contact_attempt',
				'mailer'     => gms_is_smtp_ready() ? 'smtp' : 'default',
				'recipient'  => $recipient,
				'full_name'  => $full_name,
				'email'      => $email,
				'has_phone'  => '' !== $phone,
				'has_company'=> '' !== $company_name,
			]
		);

		$sent = wp_mail( $recipient, $subject, implode( PHP_EOL, $body_lines ), $headers );

		gms_store_contact_mail_state(
			[
				'event'       => $sent ? 'contact_sent' : 'contact_send_failed',
				'mailer'      => gms_is_smtp_ready() ? 'smtp' : 'default',
				'recipient'   => $recipient,
				'full_name'   => $full_name,
				'email'       => $email,
			]
		);

		wp_safe_redirect( add_query_arg( 'gms_contact', $sent ? 'success' : 'error', $redirect ) );
		exit;
	}
}
add_action( 'admin_post_nopriv_gms_contact_form', 'gms_handle_contact_form_submission' );
add_action( 'admin_post_gms_contact_form', 'gms_handle_contact_form_submission' );

/* ═══════════════════════════════════════════════════════════════
   Website Audit — Lead Generation System
   ═══════════════════════════════════════════════════════════════ */

/**
 * Create the audit leads table on theme switch / activation.
 */
function gms_create_audit_leads_table() {
	static $did_run = false;

	if ( $did_run ) {
		return;
	}

	if ( ! doing_action( 'after_switch_theme' ) && function_exists( 'gms_should_skip_runtime_maintenance_tasks' ) && gms_should_skip_runtime_maintenance_tasks() ) {
		return;
	}

	global $wpdb;

	$table   = $wpdb->prefix . 'gms_audit_leads';
	$charset = $wpdb->get_charset_collate();

	$table_exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

	if ( $table_exists ) {
		$did_run = true;
		update_option( 'gms_audit_leads_table_ready', '1', false );

		return;
	}

	$sql = "CREATE TABLE IF NOT EXISTS $table (
		id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		name        VARCHAR(255) NOT NULL DEFAULT '',
		email       VARCHAR(255) NOT NULL DEFAULT '',
		company     VARCHAR(255) NOT NULL DEFAULT '',
		website_url TEXT         NOT NULL,
		audit_scores TEXT        DEFAULT NULL,
		ip_address  VARCHAR(45)  NOT NULL DEFAULT '',
		created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY idx_email (email(191)),
		KEY idx_created (created_at)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	$did_run = true;
	update_option( 'gms_audit_leads_table_ready', '1', false );
}
add_action( 'after_switch_theme', 'gms_create_audit_leads_table' );

function gms_maybe_create_audit_leads_table() {
	if ( '1' === get_option( 'gms_audit_leads_table_ready' ) ) {
		return;
	}

	gms_create_audit_leads_table();
}
add_action( 'init', 'gms_maybe_create_audit_leads_table', 5 );

/**
 * Auto-create the Website Audit page if it doesn't exist.
 */
function gms_ensure_audit_page_exists() {
	if ( function_exists( 'gms_should_skip_runtime_maintenance_tasks' ) && gms_should_skip_runtime_maintenance_tasks() ) {
		return;
	}

	$page = get_page_by_path( 'website-audit' );

	if ( $page instanceof WP_Post ) {
		return;
	}

	$page_id = wp_insert_post(
		[
			'post_title'   => 'Website Audit',
			'post_name'    => 'website-audit',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => 1,
		]
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-website-audit.php' );
	}
}
add_action( 'init', 'gms_ensure_audit_page_exists', 20 );

/**
 * Auto-create the Case Studies page if it doesn't exist.
 */
function gms_ensure_case_studies_page_exists() {
	if ( function_exists( 'gms_should_skip_runtime_maintenance_tasks' ) && gms_should_skip_runtime_maintenance_tasks() ) {
		return;
	}

	$page = get_page_by_path( 'case-studies' );

	if ( $page instanceof WP_Post ) {
		return;
	}

	$page_id = wp_insert_post(
		[
			'post_title'   => 'Case Studies',
			'post_name'    => 'case-studies',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => 1,
		]
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-case-studies.php' );
	}
}
add_action( 'init', 'gms_ensure_case_studies_page_exists', 20 );

function gms_register_service_detail_rewrite_rules() {
	add_rewrite_rule(
		'^services/([^/]+)/?$',
		'index.php?pagename=services/$matches[1]',
		'top'
	);
}
add_action( 'init', 'gms_register_service_detail_rewrite_rules', 19 );

function gms_map_service_detail_request( $query_vars ) {
	if ( is_admin() || ! is_array( $query_vars ) ) {
		return $query_vars;
	}

	$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' );
	$request_path = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
	$request_path = trim( $request_path, '/' );

	if ( ! preg_match( '#^services/([^/]+)/?$#', $request_path, $matches ) ) {
		return $query_vars;
	}

	$service_path = 'services/' . sanitize_title( $matches[1] );
	$page         = get_page_by_path( $service_path, OBJECT, 'page' );

	if ( ! ( $page instanceof WP_Post ) ) {
		return $query_vars;
	}

	unset( $query_vars['name'], $query_vars['error'], $query_vars['attachment'] );
	$query_vars['pagename'] = $service_path;

	return $query_vars;
}
add_filter( 'request', 'gms_map_service_detail_request', 1 );

function gms_maybe_flush_service_detail_rewrite_rules() {
	$rewrite_version = 'gms-service-detail-routes-v1';

	if ( get_option( 'gms_service_detail_rewrite_version' ) === $rewrite_version ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'gms_service_detail_rewrite_version', $rewrite_version, false );
}
add_action( 'init', 'gms_maybe_flush_service_detail_rewrite_rules', 35 );

function gms_ensure_service_detail_pages_exist() {
	if ( function_exists( 'gms_should_skip_runtime_maintenance_tasks' ) && gms_should_skip_runtime_maintenance_tasks() ) {
		return;
	}

	$parent_page = get_page_by_path( 'services' );

	if ( ! ( $parent_page instanceof WP_Post ) ) {
		return;
	}

	$config     = gms_get_demo_config();
	$menu_order = 10;

	foreach ( (array) ( $config['services'] ?? [] ) as $service ) {
		$title   = (string) ( $service['title'] ?? '' );
		$slug    = (string) ( $service['slug'] ?? '' );
		$summary = (string) ( $service['description'] ?? '' );

		if ( '' === $title || '' === $slug ) {
			continue;
		}

		$page = get_page_by_path( 'services/' . $slug, OBJECT, 'page' );

		if ( $page instanceof WP_Post ) {
			$updates = [ 'ID' => $page->ID ];
			$current_title = html_entity_decode( (string) $page->post_title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			if ( $current_title !== $title ) {
				$updates['post_title'] = $title;
			}

			if ( $page->post_name !== $slug ) {
				$updates['post_name'] = $slug;
			}

			if ( (int) $page->post_parent !== (int) $parent_page->ID ) {
				$updates['post_parent'] = $parent_page->ID;
			}

			if ( (int) $page->menu_order !== $menu_order ) {
				$updates['menu_order'] = $menu_order;
			}

			if ( 'publish' !== $page->post_status ) {
				$updates['post_status'] = 'publish';
			}

			if ( '' === trim( (string) $page->post_excerpt ) ) {
				$updates['post_excerpt'] = $summary;
			}

			if ( '' === trim( wp_strip_all_tags( (string) $page->post_content ) ) ) {
				$updates['post_content'] = $summary;
			}

			if ( count( $updates ) > 1 ) {
				wp_update_post( $updates );
			}

			$page_id = $page->ID;
		} else {
			$page_id = wp_insert_post(
				[
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_parent'  => $parent_page->ID,
					'post_excerpt' => $summary,
					'post_content' => $summary,
					'post_author'  => 1,
					'menu_order'   => $menu_order,
				]
			);
		}

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			if ( 'single-service.php' !== get_post_meta( $page_id, '_wp_page_template', true ) ) {
				update_post_meta( $page_id, '_wp_page_template', 'single-service.php' );
			}
		}

		$menu_order += 10;
	}
}
add_action( 'init', 'gms_ensure_service_detail_pages_exist', 21 );

function gms_ensure_industry_detail_pages_exist() {
	if ( function_exists( 'gms_should_skip_runtime_maintenance_tasks' ) && gms_should_skip_runtime_maintenance_tasks() ) {
		return;
	}

	$parent_page = get_page_by_path( 'industries' );

	if ( ! ( $parent_page instanceof WP_Post ) ) {
		return;
	}

	$menu_order = 10;

	foreach ( gms_get_industry_page_map() as $title => $data ) {
		$slug    = $data['slug'] ?? '';
		$summary = $data['summary'] ?? '';

		if ( '' === $slug ) {
			continue;
		}

		$page = get_page_by_path( 'industries/' . $slug, OBJECT, 'page' );

		if ( $page instanceof WP_Post ) {
			$updates = [
				'ID' => $page->ID,
			];

			$current_title = html_entity_decode( (string) $page->post_title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			if ( $current_title !== $title ) {
				$updates['post_title'] = $title;
			}

			if ( $page->post_name !== $slug ) {
				$updates['post_name'] = $slug;
			}

			if ( (int) $page->post_parent !== (int) $parent_page->ID ) {
				$updates['post_parent'] = $parent_page->ID;
			}

			if ( (int) $page->menu_order !== $menu_order ) {
				$updates['menu_order'] = $menu_order;
			}

			if ( 'publish' !== $page->post_status ) {
				$updates['post_status'] = 'publish';
			}

			if ( '' === trim( (string) $page->post_excerpt ) ) {
				$updates['post_excerpt'] = $summary;
			}

			if ( '' === trim( wp_strip_all_tags( (string) $page->post_content ) ) ) {
				$updates['post_content'] = $summary;
			}

			if ( count( $updates ) > 1 ) {
				wp_update_post( $updates );
			}

			$page_id = $page->ID;
		} else {
			$page_id = wp_insert_post(
				[
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_parent'  => $parent_page->ID,
					'post_excerpt' => $summary,
					'post_content' => $summary,
					'post_author'  => 1,
					'menu_order'   => $menu_order,
				]
			);
		}

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			if ( 'single-service.php' !== get_post_meta( $page_id, '_wp_page_template', true ) ) {
				update_post_meta( $page_id, '_wp_page_template', 'single-service.php' );
			}
		}

		$menu_order += 10;
	}
}
add_action( 'init', 'gms_ensure_industry_detail_pages_exist', 21 );

function gms_sync_industries_elementor_nodes( array &$elements ): bool {
	$updated = false;
	$industry_video_url = get_theme_file_uri( 'assets/images/industry-video.mp4' );
	$industry_image_url = get_theme_file_uri( 'assets/images/industry-hero-lock.png' );

	foreach ( $elements as &$element ) {
		if ( 'widget' === ( $element['elType'] ?? '' ) ) {
			$widget_type = $element['widgetType'] ?? '';
			$settings    = $element['settings'] ?? [];

			if ( 'gms-hero' === $widget_type ) {
				$slides = $settings['slides'] ?? [];
				$slide  = is_array( $slides[0] ?? null ) ? $slides[0] : [];

				$settings['slides'] = [
					array_merge(
						$slide,
						[
							'layout'         => 'split',
							'label'          => 'Industries',
							'title'          => 'Security Verticals Supported',
							'copy'           => 'Choose the vertical you serve and open its dedicated industry page.',
							'art_media_type' => 'video',
							'art_image'      => is_array( $slide['art_image'] ?? null ) && ! empty( $slide['art_image']['url'] ) ? $slide['art_image'] : [ 'url' => $industry_image_url ],
							'art_video_url'  => [ 'url' => $industry_video_url ],
							'primary_url'    => is_array( $slide['primary_url'] ?? null ) ? $slide['primary_url'] : [ 'url' => home_url( '/contact-us/' ) ],
							'secondary_url'  => is_array( $slide['secondary_url'] ?? null ) ? $slide['secondary_url'] : [ 'url' => home_url( '/about-us/' ) ],
						]
					),
				];

				$element['settings'] = $settings;
				$updated             = true;
			}

			if ( 'gms-card-grid' === $widget_type ) {
				$cards = $settings['cards'] ?? [];

				$settings['eyebrow']     = 'Industries';
				$settings['title']       = 'Security Verticals Supported';
				$settings['description'] = 'Choose the sector you serve and open its dedicated industry page.';

				if ( is_array( $cards ) ) {
					$normalized_cards = [];

					foreach ( $cards as $card ) {
						$title = $card['title'] ?? '';

						if ( is_array( $title ) ) {
							$title = $title['title'] ?? '';
						}

						$clean_title = gms_clean_industry_name( (string) $title );

						if ( '' === $clean_title ) {
							continue;
						}

						$normalized_cards[] = array_merge(
							$card,
							[
								'meta'        => '',
								'icon'        => gms_get_industry_icon( $clean_title ),
								'title'       => $clean_title,
								'text'        => gms_get_industry_summary( $clean_title ),
								'bullets'     => '',
								'button_text' => 'Learn More',
								'button_url'  => [ 'url' => gms_get_industry_url( $clean_title ) ],
							]
						);
					}

					$settings['cards'] = $normalized_cards;
				}

				$element['settings'] = $settings;
				$updated             = true;
			}

			if ( 'gms-cta-banner' === $widget_type ) {
				$existing_image     = $settings['image'] ?? [];
				$existing_image_url = is_array( $existing_image ) ? trim( (string) ( $existing_image['url'] ?? '' ) ) : '';
				$default_cta_image  = get_theme_file_uri( 'assets/images/security-dashboard-visual.png' );
				$should_replace_image = '' === $existing_image_url || false !== strpos( $existing_image_url, 'assets/images/image-3.png' );

				$settings = array_merge(
					$settings,
					[
						'eyebrow'     => 'FAQ\'s',
						'title'       => 'Ready to build trust that drives revenue?',
						'description' => 'You bring the expertise, we will turn it into visibility, credibility, and pipeline momentum.',
						'button_text' => 'Schedule a Free Consultation',
						'button_url'  => is_array( $settings['button_url'] ?? null ) ? $settings['button_url'] : [ 'url' => home_url( '/contact-us/' ) ],
						'image'       => $should_replace_image ? [ 'url' => $default_cta_image ] : $existing_image,
					]
				);

				$element['settings'] = $settings;
				$updated             = true;
			}
		}

		if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
			$updated = gms_sync_industries_elementor_nodes( $element['elements'] ) || $updated;
		}
	}
	unset( $element );

	return $updated;
}

function gms_sync_industries_elementor_grid() {
	if ( function_exists( 'gms_should_skip_runtime_elementor_sync' ) && gms_should_skip_runtime_elementor_sync() ) {
		return;
	}

	$sync_version = '2026-04-07-industries-page-v3';

	if ( get_option( 'gms_industries_grid_sync_version' ) === $sync_version ) {
		return;
	}

	$page = get_page_by_path( 'industries' );

	if ( ! ( $page instanceof WP_Post ) ) {
		return;
	}

	$raw_data = get_post_meta( $page->ID, '_elementor_data', true );

	if ( ! is_string( $raw_data ) || '' === trim( $raw_data ) ) {
		update_option( 'gms_industries_grid_sync_version', $sync_version );
		return;
	}

	$data = json_decode( $raw_data, true );

	if ( ! is_array( $data ) ) {
		return;
	}

	if ( gms_sync_industries_elementor_nodes( $data ) ) {
		update_post_meta( $page->ID, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $page->ID, '_elementor_edit_mode', 'builder' );
		clean_post_cache( $page->ID );

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	update_option( 'gms_industries_grid_sync_version', $sync_version );
}
add_action( 'init', 'gms_sync_industries_elementor_grid', 22 );
/**
 * Handle AJAX audit lead submission.
 */
function gms_handle_audit_lead_submission() {
	// Allow cross-origin for logged-out users going through admin-post.php.
	if ( ! isset( $_POST['gms_audit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gms_audit_nonce'] ) ), 'gms_audit_lead' ) ) {
		wp_send_json_error( [ 'message' => 'Invalid security token.' ], 403 );
	}

	$name        = sanitize_text_field( wp_unslash( $_POST['name']        ?? '' ) );
	$email       = sanitize_email( wp_unslash( $_POST['email']            ?? '' ) );
	$company     = sanitize_text_field( wp_unslash( $_POST['company']     ?? '' ) );
	$website_url = esc_url_raw( wp_unslash( $_POST['website_url']         ?? '' ) );
	$ip_address  = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

	if ( '' === $name || ! is_email( $email ) || '' === $website_url ) {
		wp_send_json_error( [ 'message' => 'Name, valid email, and website URL are required.' ], 422 );
	}

	// ─── Store in database ───
	global $wpdb;
	$table = $wpdb->prefix . 'gms_audit_leads';

	$wpdb->insert(
		$table,
		[
			'name'        => $name,
			'email'       => $email,
			'company'     => $company,
			'website_url' => $website_url,
			'ip_address'  => $ip_address,
			'created_at'  => current_time( 'mysql' ),
		],
		[ '%s', '%s', '%s', '%s', '%s', '%s' ]
	);

	// ─── Email notification ───
	$recipient = 'jayantisuthar094@gmail.com';
	$subject   = sprintf( 'New Website Audit Lead: %s', $name );
	$body      = implode( "\n", [
		'A new lead has submitted a website audit request.',
		'',
		'Name:    ' . $name,
		'Email:   ' . $email,
		'Company: ' . ( $company ?: 'Not provided' ),
		'Website: ' . $website_url,
		'IP:      ' . $ip_address,
		'Time:    ' . current_time( 'Y-m-d H:i:s' ),
	] );
	$headers   = [
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	];

	gms_write_mail_debug_log( [
		'event'     => 'audit_lead_attempt',
		'mailer'    => gms_is_smtp_ready() ? 'smtp' : 'default',
		'recipient' => $recipient,
		'name'      => $name,
		'email'     => $email,
		'website'   => $website_url,
	] );

	$sent = wp_mail( $recipient, $subject, $body, $headers );

	gms_write_mail_debug_log( [
		'event'     => $sent ? 'audit_lead_sent' : 'audit_lead_failed',
		'mailer'    => gms_is_smtp_ready() ? 'smtp' : 'default',
		'recipient' => $recipient,
		'name'      => $name,
	] );

	// Retry once on failure after a brief pause.
	if ( ! $sent ) {
		sleep( 2 );
		$sent = wp_mail( $recipient, $subject, $body, $headers );
		gms_write_mail_debug_log( [
			'event'     => $sent ? 'audit_lead_retry_sent' : 'audit_lead_retry_failed',
			'mailer'    => gms_is_smtp_ready() ? 'smtp' : 'default',
			'recipient' => $recipient,
			'name'      => $name,
		] );
	}

	// ─── Optional webhook ───
	$webhook_url = get_theme_mod( 'gms_audit_webhook_url', '' );
	if ( '' !== $webhook_url ) {
		wp_remote_post(
			$webhook_url,
			[
				'body'      => wp_json_encode( [
					'name'        => $name,
					'email'       => $email,
					'company'     => $company,
					'website_url' => $website_url,
					'timestamp'   => current_time( 'c' ),
				] ),
				'headers'   => [ 'Content-Type' => 'application/json' ],
				'timeout'   => 5,
				'blocking'  => false,
				'sslverify' => false,
			]
		);
	}

	// ─── Increment sites scanned counter ───
	$current_scans = (int) get_option( 'gms_audit_sites_scanned', 12400 );
	update_option( 'gms_audit_sites_scanned', $current_scans + 1 );

	wp_send_json_success( [ 'message' => 'Lead captured.' ] );
}
add_action( 'admin_post_nopriv_gms_audit_lead', 'gms_handle_audit_lead_submission' );
add_action( 'admin_post_gms_audit_lead', 'gms_handle_audit_lead_submission' );
add_action( 'wp_ajax_nopriv_gms_audit_lead', 'gms_handle_audit_lead_submission' );
add_action( 'wp_ajax_gms_audit_lead', 'gms_handle_audit_lead_submission' );

/**
 * Return the configured Google PageSpeed Insights API key.
 *
 * @return string
 */
function gms_get_pagespeed_api_key() {
	if ( defined( 'GMS_PAGESPEED_API_KEY' ) && GMS_PAGESPEED_API_KEY ) {
		return (string) GMS_PAGESPEED_API_KEY;
	}

	return '';
}

/**
 * Return the JSON debug log path for audit scans.
 *
 * @return string
 */
function gms_get_audit_debug_log_path() {
	return trailingslashit( ABSPATH ) . 'tmp/audit-debug.json';
}

/**
 * Persist the latest audit diagnostics for local verification.
 *
 * @param array $payload Debug payload.
 * @return void
 */
function gms_write_audit_debug_log( $payload ) {
	$path      = gms_get_audit_debug_log_path();
	$directory = dirname( $path );

	if ( ! is_dir( $directory ) ) {
		wp_mkdir_p( $directory );
	}

	$encoded = wp_json_encode(
		[
			'updated_at' => gmdate( 'c' ),
			'latest'     => $payload,
		],
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	);

	if ( false !== $encoded ) {
		file_put_contents( $path, $encoded, LOCK_EX );
	}
}

/**
 * Normalize and validate an audit target URL.
 *
 * @param string $raw_url Raw URL input.
 * @return string|WP_Error
 */
function gms_normalize_audit_url( $raw_url ) {
	$raw_url = trim( (string) $raw_url );

	if ( '' === $raw_url ) {
		return new WP_Error( 'audit_url_empty', 'A website URL is required.' );
	}

	if ( ! preg_match( '#^https?://#i', $raw_url ) ) {
		$raw_url = 'https://' . ltrim( $raw_url, '/' );
	}

	$parts = wp_parse_url( $raw_url );

	if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return new WP_Error( 'audit_url_invalid', 'Please enter a valid public website URL.' );
	}

	$scheme = strtolower( (string) $parts['scheme'] );
	$host   = strtolower( (string) $parts['host'] );

	if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
		return new WP_Error( 'audit_url_invalid_scheme', 'Only http and https URLs can be audited.' );
	}

	if ( in_array( $host, [ 'localhost', '127.0.0.1', '::1' ], true ) || str_ends_with( $host, '.local' ) ) {
		return new WP_Error( 'audit_url_private_host', 'Local and private hosts cannot be audited.' );
	}

	if ( filter_var( $host, FILTER_VALIDATE_IP ) && ! filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return new WP_Error( 'audit_url_private_ip', 'Private IP targets cannot be audited.' );
	}

	$normalized = $scheme . '://' . $host;

	if ( ! empty( $parts['port'] ) ) {
		$normalized .= ':' . (int) $parts['port'];
	}

	if ( ! empty( $parts['path'] ) ) {
		$normalized .= $parts['path'];
	} else {
		$normalized .= '/';
	}

	if ( ! empty( $parts['query'] ) ) {
		$normalized .= '?' . $parts['query'];
	}

	if ( ! wp_http_validate_url( $normalized ) ) {
		return new WP_Error( 'audit_url_invalid', 'Please enter a valid public website URL.' );
	}

	return esc_url_raw( $normalized );
}

/**
 * Execute a cURL request for live audit lookups.
 *
 * @param string $url       Request URL.
 * @param array  $args      Request arguments.
 * @param string $method    HTTP method.
 * @param bool   $sslverify Whether to verify TLS certificates.
 * @return array
 */
function gms_execute_curl_request( $url, $args, $method, $sslverify ) {
	$handle      = curl_init( $url );
	$headers_out = [];
	$header_list = [];

	foreach ( (array) $args['headers'] as $name => $value ) {
		$header_list[] = $name . ': ' . $value;
	}

	curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $handle, CURLOPT_MAXREDIRS, max( 0, (int) $args['redirection'] ) );
	curl_setopt( $handle, CURLOPT_TIMEOUT, max( 1, (int) $args['timeout'] ) );
	curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, min( 20, max( 1, (int) $args['timeout'] ) ) );
	curl_setopt( $handle, CURLOPT_ENCODING, '' );
	curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, (bool) $sslverify );
	curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, (bool) $sslverify ? 2 : 0 );
	curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, $method );
	curl_setopt(
		$handle,
		CURLOPT_HEADERFUNCTION,
		static function ( $curl_handle, $header_line ) use ( &$headers_out ) {
			$length = strlen( $header_line );
			$header = trim( $header_line );

			if ( '' === $header || false === strpos( $header, ':' ) ) {
				return $length;
			}

			list( $name, $value ) = explode( ':', $header, 2 );
			$name  = strtolower( trim( $name ) );
			$value = trim( $value );

			if ( isset( $headers_out[ $name ] ) ) {
				if ( is_array( $headers_out[ $name ] ) ) {
					$headers_out[ $name ][] = $value;
				} else {
					$headers_out[ $name ] = [ $headers_out[ $name ], $value ];
				}
			} else {
				$headers_out[ $name ] = $value;
			}

			return $length;
		}
	);

	if ( ! empty( $header_list ) ) {
		curl_setopt( $handle, CURLOPT_HTTPHEADER, $header_list );
	}

	if ( ! empty( $args['user_agent'] ) ) {
		curl_setopt( $handle, CURLOPT_USERAGENT, (string) $args['user_agent'] );
	}

	if ( ! empty( $args['referer'] ) ) {
		curl_setopt( $handle, CURLOPT_REFERER, (string) $args['referer'] );
	}

	if ( 'HEAD' === $method ) {
		curl_setopt( $handle, CURLOPT_NOBODY, true );
	} elseif ( 'POST' === $method ) {
		curl_setopt( $handle, CURLOPT_POST, true );

		if ( null !== $args['body'] ) {
			curl_setopt( $handle, CURLOPT_POSTFIELDS, is_array( $args['body'] ) ? http_build_query( $args['body'] ) : (string) $args['body'] );
		}
	} elseif ( null !== $args['body'] ) {
		curl_setopt( $handle, CURLOPT_POSTFIELDS, is_array( $args['body'] ) ? http_build_query( $args['body'] ) : (string) $args['body'] );
	}

	$body          = curl_exec( $handle );
	$error_message = curl_errno( $handle ) ? curl_error( $handle ) : '';
	$status_code   = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
	$effective_url = (string) curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );

	return [
		'success'            => '' === $error_message,
		'code'               => $status_code,
		'body'               => false === $body ? '' : (string) $body,
		'headers'            => $headers_out,
		'error'              => $error_message,
		'effective_url'      => $effective_url ?: $url,
		'sslverify_disabled' => ! $sslverify,
	];
}

/**
 * Perform a remote HTTP request, preferring cURL so the audit can inspect live sites.
 *
 * @param string $url  Request URL.
 * @param array  $args Request arguments.
 * @return array
 */
function gms_http_request( $url, $args = [] ) {
	$args = wp_parse_args(
		$args,
		[
			'method'      => 'GET',
			'headers'     => [],
			'timeout'     => 45,
			'redirection' => 5,
			'body'        => null,
			'user_agent'  => '',
			'referer'     => '',
			'sslverify'   => true,
		]
	);

	$method = strtoupper( (string) $args['method'] );

	if ( function_exists( 'curl_init' ) ) {
		$response = gms_execute_curl_request( $url, $args, $method, (bool) $args['sslverify'] );

		if ( $response['success'] || ! $args['sslverify'] ) {
			return $response;
		}

		if ( false !== stripos( $response['error'], 'certificate verify locations' ) || false !== stripos( $response['error'], 'ssl certificate problem' ) ) {
			return gms_execute_curl_request( $url, $args, $method, false );
		}

		return $response;
	}

	$request_args = [
		'method'      => $method,
		'headers'     => (array) $args['headers'],
		'timeout'     => max( 1, (int) $args['timeout'] ),
		'redirection' => max( 0, (int) $args['redirection'] ),
		'body'        => $args['body'],
		'user-agent'  => (string) $args['user_agent'],
		'referer'     => (string) $args['referer'],
		'sslverify'   => (bool) $args['sslverify'],
	];

	$response = wp_remote_request( $url, $request_args );

	if ( is_wp_error( $response ) ) {
		return [
			'success'       => false,
			'code'          => 0,
			'body'          => '',
			'headers'       => [],
			'error'         => $response->get_error_message(),
			'effective_url' => $url,
		];
	}

	$headers = [];

	foreach ( (array) wp_remote_retrieve_headers( $response ) as $name => $value ) {
		$headers[ strtolower( (string) $name ) ] = $value;
	}

	return [
		'success'       => true,
		'code'          => (int) wp_remote_retrieve_response_code( $response ),
		'body'          => (string) wp_remote_retrieve_body( $response ),
		'headers'       => $headers,
		'error'         => '',
		'effective_url' => $url,
	];
}

/**
 * Recursively locate the first value for a given key.
 *
 * @param mixed  $subject  Value to inspect.
 * @param string $target   Target key.
 * @return mixed|null
 */
function gms_find_recursive_key( $subject, $target ) {
	if ( ! is_array( $subject ) ) {
		return null;
	}

	foreach ( $subject as $key => $value ) {
		if ( (string) $key === $target ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$nested = gms_find_recursive_key( $value, $target );

			if ( null !== $nested ) {
				return $nested;
			}
		}
	}

	return null;
}

/**
 * Return the User-Agent pool for API bypass attempts.
 *
 * @return array
 */
function gms_get_audit_user_agents() {
	return [
		'desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
		'mobile'  => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3 Mobile/15E148 Safari/604.1',
	];
}

/**
 * Reduce HTML-heavy strings to short, readable issue copy.
 *
 * @param string $text  Raw text.
 * @param int    $limit Max length.
 * @return string
 */
function gms_plain_text_excerpt( $text, $limit = 180 ) {
	$text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, 'UTF-8' );
	$text = preg_replace( '/\[([^\]]+)\]\([^)]+\)/', '$1', $text );
	$text = preg_replace( '/\s+/', ' ', $text );
	$text = trim( (string) $text );

	if ( '' === $text ) {
		return '';
	}

	if ( strlen( $text ) <= $limit ) {
		return $text;
	}

	return rtrim( substr( $text, 0, max( 0, $limit - 3 ) ) ) . '...';
}

/**
 * Translate a numeric score into a UI level token.
 *
 * @param int|null $score Score value.
 * @return string
 */
function gms_get_score_level( $score ) {
	if ( null === $score ) {
		return 'neutral';
	}

	if ( $score >= 80 ) {
		return 'good';
	}

	if ( $score >= 60 ) {
		return 'average';
	}

	return 'poor';
}

/**
 * Convert a numeric score into the UI label text.
 *
 * @param int|null $score Score value.
 * @return string
 */
function gms_get_score_label( $score ) {
	if ( null === $score ) {
		return 'Unavailable';
	}

	if ( $score >= 80 ) {
		return 'Good';
	}

	if ( $score >= 60 ) {
		return 'Average';
	}

	return 'Needs Work';
}

/**
 * Build the score payload expected by the audit UI.
 *
 * @param int|null $score  Score value.
 * @param string   $label  Label text.
 * @param string   $source Source label.
 * @return array
 */
function gms_build_score_payload( $score, $label, $source ) {
	return [
		'value'  => null === $score ? null : (int) max( 0, min( 100, $score ) ),
		'label'  => (string) $label,
		'level'  => gms_get_score_level( $score ),
		'source' => (string) $source,
	];
}

/**
 * Build a fallback security score from live response headers.
 *
 * @param array $headers Header bag.
 * @return int|null
 */
function gms_calculate_header_security_score( $headers ) {
	if ( empty( $headers ) || ! is_array( $headers ) ) {
		return null;
	}

	$score            = 100;
	$content_policy   = gms_get_header_value( $headers, 'content-security-policy' );
	$hsts             = gms_get_header_value( $headers, 'strict-transport-security' );
	$content_type     = gms_get_header_value( $headers, 'x-content-type-options' );
	$x_frame_options  = gms_get_header_value( $headers, 'x-frame-options' );
	$referrer_policy  = gms_get_header_value( $headers, 'referrer-policy' );
	$permissions      = gms_get_header_value( $headers, 'permissions-policy' );

	if ( '' === $content_policy ) {
		$score -= 25;
	}

	if ( '' === $hsts ) {
		$score -= 20;
	} elseif ( false === stripos( $hsts, 'max-age=' ) ) {
		$score -= 10;
	}

	if ( false === stripos( $content_type, 'nosniff' ) ) {
		$score -= 15;
	}

	if ( '' === $x_frame_options && false === stripos( $content_policy, 'frame-ancestors' ) ) {
		$score -= 15;
	}

	if ( '' === $referrer_policy ) {
		$score -= 10;
	}

	if ( '' === $permissions ) {
		$score -= 5;
	}

	return (int) max( 0, min( 100, $score ) );
}

/**
 * Convert raw upstream scanner errors into cleaner user-facing text.
 *
 * @param string $source          Source identifier.
 * @param string $message         Raw message.
 * @param bool   $has_score_fallback Whether a fallback score is available.
 * @return string
 */
function gms_format_audit_message( $source, $message, $has_score_fallback = false ) {
	$message       = trim( (string) $message );
	$message_lower = strtolower( $message );
	$source        = strtolower( (string) $source );

	if ( '' === $message ) {
		return '';
	}

	if ( 'mozilla' === $source ) {
		if ( false !== strpos( $message_lower, 'unexpected http status code 504' ) || false !== strpos( $message_lower, 'site seems to be down' ) ) {
			if ( $has_score_fallback ) {
				return 'Mozilla Observatory could not complete the external security scan for this site. The security score below is based on live header analysis instead.';
			}

			return 'Mozilla Observatory could not complete the external security scan because the target site returned unstable responses.';
		}

		if ( $has_score_fallback ) {
			return 'Mozilla Observatory could not complete the external security scan for this site. The security score below is based on live header analysis instead.';
		}
	}

	if ( 'pagespeed' === $source && ( false !== strpos( $message_lower, 'failed_document_request' ) || false !== strpos( $message_lower, 'timed out' ) ) ) {
		return 'Google PageSpeed had trouble loading the target site reliably. The audit retried automatically, but the site appears to respond inconsistently to external crawlers.';
	}

	return gms_plain_text_excerpt( $message, 220 );
}

/**
 * Derive the Observatory scan target from the user URL.
 *
 * @param string $url Normalized URL.
 * @return string
 */
function gms_build_mozilla_target( $url ) {
	$parts = wp_parse_url( $url );
	$host  = strtolower( (string) ( $parts['host'] ?? '' ) );
	$path  = isset( $parts['path'] ) ? (string) $parts['path'] : '';

	if ( ! empty( $parts['port'] ) ) {
		$host .= ':' . (int) $parts['port'];
	}

	if ( '' !== $path && '/' !== $path ) {
		$host .= $path;
	}

	return $host;
}

/**
 * Determine whether a PageSpeed error looks transient and worth retrying once.
 *
 * @param int    $status_code   HTTP status.
 * @param string $error_status  Google status.
 * @param string $error_message Google error message.
 * @return bool
 */
function gms_is_pagespeed_transient_error( $status_code, $error_status, $error_message ) {
	$error_status  = strtoupper( (string) $error_status );
	$error_message = strtolower( (string) $error_message );

	if ( $status_code >= 500 ) {
		return true;
	}

	if ( in_array( $error_status, [ 'UNAVAILABLE', 'DEADLINE_EXCEEDED', 'INTERNAL' ], true ) ) {
		return true;
	}

	foreach ( [ 'failed_document_request', 'err_timed_out', 'timed out', 'deadline exceeded', 'temporarily unavailable' ] as $needle ) {
		if ( false !== strpos( $error_message, $needle ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Fetch Google PageSpeed Insights data with retry and anonymous fallback logic.
 *
 * @param string $url      Target URL.
 * @param string $strategy PageSpeed strategy.
 * @return array
 */
function gms_fetch_pagespeed_with_retries( $url, $strategy ) {
	$api_key        = gms_get_pagespeed_api_key();
	$user_agents    = gms_get_audit_user_agents();
	$primary_ua_key = ( 'mobile' === $strategy ) ? 'mobile' : 'desktop';
	$backup_ua_key  = ( 'mobile' === $primary_ua_key ) ? 'desktop' : 'mobile';
	$attempts       = [];

	if ( '' !== $api_key ) {
		$attempts[] = [
			'mode'      => 'keyed',
			'use_key'   => true,
			'ua_key'    => $primary_ua_key,
			'cache_bust'=> rand( 1, 9999 ),
		];
		$attempts[] = [
			'mode'      => 'keyed',
			'use_key'   => true,
			'ua_key'    => $backup_ua_key,
			'cache_bust'=> rand( 1, 9999 ),
		];
	}

	$attempts[] = [
		'mode'      => 'anonymous',
		'use_key'   => false,
		'ua_key'    => $backup_ua_key,
		'cache_bust'=> rand( 1, 9999 ),
	];

	$debug               = [
		'resolved_mode'      => '',
		'quota_limit_value'  => null,
		'anonymous_fallback' => false,
		'attempts'           => [],
	];
	$skip_remaining_keyed = false;
	$last_error_message   = 'PageSpeed Insights did not return a usable response.';

	foreach ( $attempts as $attempt ) {
		if ( $attempt['use_key'] && ( '' === $api_key || $skip_remaining_keyed ) ) {
			continue;
		}

		$query_args = [
			'url'       => $url,
			'strategy'  => $strategy,
			'locale'    => 'en_US',
			'utm_source'=> 'growmysecurity-audit',
			'cb'        => $attempt['cache_bust'],
		];

		if ( $attempt['use_key'] ) {
			$query_args['key'] = $api_key;
		}

		$request_try = 0;
		$max_request_tries = 2;

		while ( $request_try < $max_request_tries ) {
			++$request_try;

			$endpoint = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?' . http_build_query( $query_args, '', '&', PHP_QUERY_RFC3986 ) . '&category=performance&category=seo';
			$request  = gms_http_request(
				$endpoint,
				[
					'method'     => 'GET',
					'headers'    => [
						'Accept'        => 'application/json',
						'Cache-Control' => 'no-cache, no-store, max-age=0',
						'Pragma'        => 'no-cache',
					],
					'timeout'    => 65,
					'redirection'=> 3,
					'user_agent' => $user_agents[ $attempt['ua_key'] ],
					'referer'    => home_url( '/' ),
				]
			);

			$decoded              = json_decode( $request['body'], true );
			$google_error         = is_array( $decoded ) ? ( $decoded['error'] ?? null ) : null;
			$error_code           = is_array( $google_error ) ? (int) ( $google_error['code'] ?? 0 ) : 0;
			$error_status         = is_array( $google_error ) ? (string) ( $google_error['status'] ?? '' ) : '';
			$error_message        = is_array( $google_error ) ? (string) ( $google_error['message'] ?? '' ) : (string) $request['error'];
			$quota_limit_value    = is_array( $decoded ) ? gms_find_recursive_key( $decoded, 'quota_limit_value' ) : null;
			$is_quota_error       = 429 === $request['code'] || 429 === $error_code || 'RESOURCE_EXHAUSTED' === $error_status || false !== stripos( strtolower( $error_message ), 'quota' );
			$is_status_zero       = 0 === $request['code'] || 0 === $error_code;
			$is_auth_error        = ( $request['code'] >= 400 && $request['code'] < 500 && false !== stripos( strtolower( $error_message ), 'api key' ) ) || 'PERMISSION_DENIED' === $error_status || false !== stripos( strtolower( $error_message ), 'api key not valid' );
			$is_transient_error   = gms_is_pagespeed_transient_error( (int) $request['code'], $error_status, $error_message );
			$will_retry_transient = $is_transient_error && $request_try < $max_request_tries;

			if ( null !== $quota_limit_value ) {
				$debug['quota_limit_value'] = $quota_limit_value;
			}

			$debug['attempts'][] = [
				'mode'               => $attempt['mode'],
				'used_key'           => (bool) $attempt['use_key'],
				'sslverify_disabled' => (bool) ( $request['sslverify_disabled'] ?? false ),
				'strategy'           => $strategy,
				'user_agent_profile' => $attempt['ua_key'],
				'cache_buster'       => $attempt['cache_bust'],
				'request_try'        => $request_try,
				'transient_retry'    => $will_retry_transient,
				'status_code'        => (int) $request['code'],
				'google_error_code'  => $error_code,
				'google_status'      => $error_status,
				'message'            => $error_message,
				'quota_limit_value'  => $quota_limit_value,
			];

			if ( $request['success'] && $request['code'] >= 200 && $request['code'] < 300 && is_array( $decoded ) && ! empty( $decoded['lighthouseResult']['categories'] ) ) {
				$debug['resolved_mode'] = $attempt['mode'];

				return [
					'success' => true,
					'data'    => $decoded,
					'debug'   => $debug,
				];
			}

			$last_error_message = $error_message ?: $last_error_message;

			if ( $attempt['use_key'] && ( $is_quota_error || $is_status_zero || $is_auth_error ) ) {
				$skip_remaining_keyed        = true;
				$debug['anonymous_fallback'] = true;
			}

			if ( ! $will_retry_transient ) {
				break;
			}

			$query_args['cb'] = rand( 1, 9999 );
		}
	}

	return [
		'success' => false,
		'data'    => null,
		'debug'   => $debug,
		'error'   => $last_error_message,
	];
}

/**
 * Fetch a Mozilla Observatory scan summary.
 *
 * @param string $url Target URL.
 * @return array
 */
function gms_fetch_mozilla_observatory( $url ) {
	$target   = gms_build_mozilla_target( $url );
	$endpoint = add_query_arg(
		[
			'host' => $target,
		],
		'https://observatory-api.mdn.mozilla.net/api/v2/scan'
	);
	$request  = gms_http_request(
		$endpoint,
		[
			'method'      => 'POST',
			'headers'     => [
				'Accept' => 'application/json',
			],
			'timeout'     => 45,
			'redirection' => 3,
			'user_agent'  => gms_get_audit_user_agents()['desktop'],
			'referer'     => 'https://growmysecurity.com/',
		]
	);
	$decoded  = json_decode( $request['body'], true );

	if ( $request['success'] && $request['code'] >= 200 && $request['code'] < 300 && is_array( $decoded ) && ( isset( $decoded['grade'] ) || isset( $decoded['score'] ) ) ) {
		return [
			'success' => true,
			'data'    => $decoded,
			'debug'   => [
				'status_code'   => (int) $request['code'],
				'sslverify_disabled' => (bool) ( $request['sslverify_disabled'] ?? false ),
				'grade'         => (string) ( $decoded['grade'] ?? '' ),
				'score'         => isset( $decoded['score'] ) ? (int) round( (float) $decoded['score'] ) : null,
				'tests_failed'  => isset( $decoded['tests_failed'] ) ? (int) $decoded['tests_failed'] : null,
				'tests_passed'  => isset( $decoded['tests_passed'] ) ? (int) $decoded['tests_passed'] : null,
				'tests_quantity'=> isset( $decoded['tests_quantity'] ) ? (int) $decoded['tests_quantity'] : null,
				'details_url'   => (string) ( $decoded['details_url'] ?? '' ),
			],
		];
	}

	return [
		'success' => false,
		'data'    => null,
		'debug'   => [
			'status_code' => (int) $request['code'],
			'sslverify_disabled' => (bool) ( $request['sslverify_disabled'] ?? false ),
			'message'     => is_array( $decoded ) ? (string) ( $decoded['message'] ?? '' ) : (string) $request['error'],
			'error'       => is_array( $decoded ) ? (string) ( $decoded['error'] ?? '' ) : '',
		],
		'error'   => is_array( $decoded ) ? (string) ( $decoded['message'] ?? 'Mozilla Observatory did not return a usable response.' ) : (string) $request['error'],
	];
}

/**
 * Fetch the target site's live response headers.
 *
 * @param string $url Target URL.
 * @return array
 */
function gms_fetch_live_security_headers( $url ) {
	$headers = [
		'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Range'  => 'bytes=0-2048',
	];

	$request = gms_http_request(
		$url,
		[
			'method'      => 'GET',
			'headers'     => $headers,
			'timeout'     => 30,
			'redirection' => 5,
			'user_agent'  => gms_get_audit_user_agents()['desktop'],
			'referer'     => 'https://growmysecurity.com/',
		]
	);

	return [
		'success'       => $request['success'] && ! empty( $request['headers'] ),
		'status_code'   => (int) $request['code'],
		'headers'       => (array) $request['headers'],
		'error'         => (string) $request['error'],
		'effective_url' => (string) $request['effective_url'],
		'sslverify_disabled' => (bool) ( $request['sslverify_disabled'] ?? false ),
	];
}

/**
 * Read a response header value from a normalized header array.
 *
 * @param array  $headers Header bag.
 * @param string $name    Header name.
 * @return string
 */
function gms_get_header_value( $headers, $name ) {
	$value = $headers[ strtolower( $name ) ] ?? '';

	if ( is_array( $value ) ) {
		return trim( (string) end( $value ) );
	}

	return trim( (string) $value );
}

/**
 * Collect Lighthouse issues for a given category.
 *
 * @param array  $lighthouse Lighthouse result.
 * @param string $category   Category key.
 * @param string $tag        UI issue tag.
 * @param int    $limit      Max issues.
 * @return array
 */
function gms_collect_pagespeed_issues( $lighthouse, $category, $tag, $limit ) {
	$issues   = [];
	$audits   = is_array( $lighthouse['audits'] ?? null ) ? $lighthouse['audits'] : [];
	$auditrefs = is_array( $lighthouse['categories'][ $category ]['auditRefs'] ?? null ) ? $lighthouse['categories'][ $category ]['auditRefs'] : [];

	foreach ( $auditrefs as $audit_ref ) {
		$audit_id = (string) ( $audit_ref['id'] ?? '' );

		if ( '' === $audit_id || empty( $audits[ $audit_id ] ) ) {
			continue;
		}

		$audit = $audits[ $audit_id ];
		$score = isset( $audit['score'] ) && is_numeric( $audit['score'] ) ? (float) $audit['score'] : null;

		if ( null === $score || $score >= 0.999 ) {
			continue;
		}

		$severity = 'info';

		if ( $score <= 0.49 ) {
			$severity = 'critical';
		} elseif ( $score <= 0.89 ) {
			$severity = 'warning';
		}

		$description = gms_plain_text_excerpt( $audit['description'] ?? '', 190 );
		$display     = gms_plain_text_excerpt( $audit['displayValue'] ?? '', 80 );

		if ( '' !== $display && false === stripos( $description, $display ) ) {
			$description = trim( $description . ' Current result: ' . $display . '.' );
		}

		$priority = (int) round( ( 1 - $score ) * 1000 );

		if ( isset( $audit['details']['overallSavingsMs'] ) && is_numeric( $audit['details']['overallSavingsMs'] ) ) {
			$priority += (int) round( min( 1000, (float) $audit['details']['overallSavingsMs'] / 10 ) );
		}

		if ( isset( $audit['details']['overallSavingsBytes'] ) && is_numeric( $audit['details']['overallSavingsBytes'] ) ) {
			$priority += (int) round( min( 1000, (float) $audit['details']['overallSavingsBytes'] / 1024 ) );
		}

		$issues[] = [
			'severity' => $severity,
			'label'    => gms_plain_text_excerpt( $audit['title'] ?? $audit_id, 100 ),
			'desc'     => $description,
			'tag'      => $tag,
			'priority' => $priority,
		];
	}

	usort(
		$issues,
		static function ( $left, $right ) {
			return (int) $right['priority'] <=> (int) $left['priority'];
		}
	);

	$issues = array_slice( $issues, 0, max( 0, (int) $limit ) );

	foreach ( $issues as &$issue ) {
		unset( $issue['priority'] );
	}
	unset( $issue );

	return $issues;
}

/**
 * Collect direct security findings from the target site's response headers.
 *
 * @param array $headers          Header bag.
 * @param array $mozilla_summary  Observatory summary payload.
 * @param bool  $headers_available Whether direct headers were fetched.
 * @return array
 */
function gms_build_security_issues( $headers, $mozilla_summary, $headers_available ) {
	$issues          = [];
	$content_policy  = gms_get_header_value( $headers, 'content-security-policy' );
	$hsts            = gms_get_header_value( $headers, 'strict-transport-security' );
	$content_type    = gms_get_header_value( $headers, 'x-content-type-options' );
	$x_frame_options = gms_get_header_value( $headers, 'x-frame-options' );
	$referrer_policy = gms_get_header_value( $headers, 'referrer-policy' );
	$permissions     = gms_get_header_value( $headers, 'permissions-policy' );

	if ( $headers_available ) {
		if ( '' === $content_policy ) {
			$issues[] = [
				'severity' => 'critical',
				'label'    => 'Missing Content Security Policy (CSP)',
				'desc'     => 'A live header check did not find a Content-Security-Policy header, which increases exposure to XSS and injection attacks.',
				'tag'      => 'security',
			];
		}

		if ( '' === $hsts ) {
			$issues[] = [
				'severity' => 'critical',
				'label'    => 'Missing HTTP Strict Transport Security (HSTS)',
				'desc'     => 'Strict-Transport-Security was not present on the audited response, so browsers are not being told to stay on HTTPS.',
				'tag'      => 'security',
			];
		} elseif ( false === stripos( $hsts, 'max-age=' ) ) {
			$issues[] = [
				'severity' => 'warning',
				'label'    => 'Weak HSTS Configuration',
				'desc'     => 'The Strict-Transport-Security header is present but does not expose a clear max-age directive.',
				'tag'      => 'security',
			];
		}

		if ( false === stripos( $content_type, 'nosniff' ) ) {
			$issues[] = [
				'severity' => 'warning',
				'label'    => 'Missing X-Content-Type-Options: nosniff',
				'desc'     => 'Browsers are not explicitly instructed to disable MIME-type sniffing on the audited response.',
				'tag'      => 'security',
			];
		}

		if ( '' === $x_frame_options && false === stripos( $content_policy, 'frame-ancestors' ) ) {
			$issues[] = [
				'severity' => 'warning',
				'label'    => 'Missing Clickjacking Protection',
				'desc'     => 'Neither X-Frame-Options nor a CSP frame-ancestors directive was detected on the live response.',
				'tag'      => 'security',
			];
		}

		if ( '' === $referrer_policy ) {
			$issues[] = [
				'severity' => 'info',
				'label'    => 'Missing Referrer-Policy Header',
				'desc'     => 'The live response does not declare a Referrer-Policy, so referrer data may be shared more broadly than intended.',
				'tag'      => 'security',
			];
		}

		if ( '' === $permissions ) {
			$issues[] = [
				'severity' => 'info',
				'label'    => 'Missing Permissions-Policy Header',
				'desc'     => 'A Permissions-Policy header was not detected, so browser feature access is not being explicitly constrained.',
				'tag'      => 'security',
			];
		}
	}

	if ( empty( $issues ) && ! empty( $mozilla_summary['tests_failed'] ) ) {
		$failed   = (int) $mozilla_summary['tests_failed'];
		$total    = (int) ( $mozilla_summary['tests_quantity'] ?? 0 );
		$severity = $failed >= 3 ? 'warning' : 'info';
		$issues[] = [
			'severity' => $severity,
			'label'    => 'Mozilla Observatory reported failing security checks',
			'desc'     => sprintf( 'Mozilla Observatory marked %1$d of %2$d security checks as failing for the audited host.', $failed, max( $total, $failed ) ),
			'tag'      => 'security',
		];
	}

	return $issues;
}

/**
 * Build fallback diagnostic issues when upstream audit services cannot score a site.
 *
 * @param array  $pagespeed    PageSpeed response payload.
 * @param array  $mozilla      Mozilla response payload.
 * @param array  $live_headers Header probe payload.
 * @param string $url          Target URL.
 * @return array
 */
function gms_build_unavailable_audit_issues( $pagespeed, $mozilla, $live_headers, $url ) {
	$issues = [];

	if ( ! empty( $pagespeed['error'] ) ) {
		$issues[] = [
			'severity' => 'critical',
			'label'    => 'Google Lighthouse could not load the site reliably',
			'desc'     => gms_plain_text_excerpt( (string) $pagespeed['error'], 200 ),
			'tag'      => 'performance',
		];
	}

	if ( ! empty( $mozilla['error'] ) ) {
		$issues[] = [
			'severity' => 'warning',
			'label'    => 'Mozilla Observatory could not complete a security scan',
			'desc'     => gms_plain_text_excerpt( (string) $mozilla['error'], 200 ),
			'tag'      => 'security',
		];
	}

	if ( ! empty( $live_headers['error'] ) || ! empty( $live_headers['status_code'] ) ) {
		$header_issue = 'The direct header check did not complete successfully.';

		if ( ! empty( $live_headers['error'] ) ) {
			$header_issue = gms_plain_text_excerpt( (string) $live_headers['error'], 200 );
		} elseif ( ! empty( $live_headers['status_code'] ) ) {
			$header_issue = sprintf( 'The live header check returned HTTP %d before a stable audit response was collected.', (int) $live_headers['status_code'] );
		}

		$issues[] = [
			'severity' => 'warning',
			'label'    => 'Live security header check did not complete',
			'desc'     => $header_issue,
			'tag'      => 'security',
		];
	}

	if ( empty( $issues ) ) {
		$issues[] = [
			'severity' => 'warning',
			'label'    => 'The site did not return stable audit data',
			'desc'     => sprintf( 'The remote audit services were unable to produce a stable scan for %s. This usually means the target site is timing out, blocking audit crawlers, or returning inconsistent responses.', $url ),
			'tag'      => 'performance',
		];
	}

	return gms_prepare_issue_list( $issues, 6 );
}

/**
 * Sort and deduplicate issue collections.
 *
 * @param array $issues Issue list.
 * @param int   $limit  Max issue count.
 * @return array
 */
function gms_prepare_issue_list( $issues, $limit = 10 ) {
	$unique = [];

	foreach ( $issues as $issue ) {
		$key = strtolower( trim( (string) ( $issue['label'] ?? '' ) ) );

		if ( '' === $key || isset( $unique[ $key ] ) ) {
			continue;
		}

		$unique[ $key ] = $issue;
	}

	$issues = array_values( $unique );

	usort(
		$issues,
		static function ( $left, $right ) {
			$priority_map = [
				'critical' => 3,
				'warning'  => 2,
				'info'     => 1,
			];
			$left_priority  = $priority_map[ $left['severity'] ?? 'info' ] ?? 1;
			$right_priority = $priority_map[ $right['severity'] ?? 'info' ] ?? 1;

			if ( $left_priority === $right_priority ) {
				return strcmp( (string) ( $left['label'] ?? '' ), (string) ( $right['label'] ?? '' ) );
			}

			return $right_priority <=> $left_priority;
		}
	);

	return array_slice( $issues, 0, max( 0, (int) $limit ) );
}

/**
 * Persist the latest audit payload against matching lead records.
 *
 * @param string $website_url Target URL.
 * @param array  $payload     Audit payload.
 * @return void
 */
function gms_store_audit_payload( $website_url, $payload ) {
	global $wpdb;

	$table        = $wpdb->prefix . 'gms_audit_leads';
	$encoded_data = wp_json_encode( $payload );

	if ( false === $encoded_data || '' === $website_url ) {
		return;
	}

	$wpdb->update(
		$table,
		[
			'audit_scores' => $encoded_data,
		],
		[
			'website_url' => $website_url,
		],
		[
			'%s',
		],
		[
			'%s',
		]
	);
}

/**
 * ─── AI VISIBILITY (GEO) SCORING ───────────────────────────────────────────
 * Calculates how visible a website is to AI engines (ChatGPT, Gemini, etc.)
 * by checking robots.txt bot permissions, Schema.org markup, meta tags,
 * and brand mentions via Serper.dev search API.
 */

/**
 * Get the Serper.dev API key.
 *
 * @return string
 */
function gms_get_serper_api_key() {
	return defined( 'GMS_SERPER_API_KEY' ) ? trim( (string) GMS_SERPER_API_KEY ) : '';
}

/**
 * Fetch and analyse robots.txt for AI bot permissions.
 *
 * @param string $url Target URL.
 * @return array { 'score' => int 0-100, 'details' => array }
 */
function gms_check_ai_bot_permissions( $url ) {
	$parsed  = wp_parse_url( $url );
	$origin  = ( $parsed['scheme'] ?? 'https' ) . '://' . ( $parsed['host'] ?? '' );
	$robots_url = trailingslashit( $origin ) . 'robots.txt';

	$ai_bots = [
		'GPTBot'           => 'OpenAI / ChatGPT',
		'Google-Extended'  => 'Google Gemini',
		'CCBot'            => 'Common Crawl (Claude training data)',
		'anthropic-ai'     => 'Anthropic / Claude',
		'PerplexityBot'    => 'Perplexity AI',
		'Bytespider'       => 'ByteDance / TikTok AI',
	];

	$response = wp_remote_get( $robots_url, [
		'timeout'    => 10,
		'sslverify'  => false,
		'user-agent' => 'Mozilla/5.0 (compatible; GrowMySecurityAudit/1.0)',
	] );

	$details = [];

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		// No robots.txt = all bots allowed (good for AI visibility).
		foreach ( $ai_bots as $bot => $label ) {
			$details[] = [
				'bot'     => $bot,
				'label'   => $label,
				'allowed' => true,
				'reason'  => 'No robots.txt found – bot is allowed by default.',
			];
		}
		return [ 'score' => 100, 'details' => $details, 'robots_found' => false ];
	}

	$body = wp_remote_retrieve_body( $response );
	$lines = preg_split( '/\r?\n/', $body );

	// Simple parser: track current user-agent context.
	$rules          = []; // bot_lower => [ 'disallow_all' => bool ].
	$current_agents = [];

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line || '#' === $line[0] ) {
			continue;
		}

		if ( preg_match( '/^user-agent\s*:\s*(.+)/i', $line, $m ) ) {
			$agent = trim( $m[1] );
			$current_agents = [ strtolower( $agent ) ];
		} elseif ( preg_match( '/^disallow\s*:\s*(.*)/i', $line, $m ) ) {
			$path = trim( $m[1] );
			if ( '/' === $path ) {
				foreach ( $current_agents as $ca ) {
					$rules[ $ca ]['disallow_all'] = true;
				}
			}
		} elseif ( preg_match( '/^allow\s*:\s*(.*)/i', $line, $m ) ) {
			$path = trim( $m[1] );
			if ( '/' === $path ) {
				foreach ( $current_agents as $ca ) {
					$rules[ $ca ]['disallow_all'] = false;
				}
			}
		}
	}

	$allowed_count = 0;
	$total_bots    = count( $ai_bots );

	foreach ( $ai_bots as $bot => $label ) {
		$bot_lower  = strtolower( $bot );
		$is_blocked = false;
		$reason     = 'Allowed (no specific rule found).';

		// Check specific bot rule first, then wildcard *.
		if ( isset( $rules[ $bot_lower ] ) && ! empty( $rules[ $bot_lower ]['disallow_all'] ) ) {
			$is_blocked = true;
			$reason     = 'Blocked by Disallow: / for ' . $bot;
		} elseif ( isset( $rules['*'] ) && ! empty( $rules['*']['disallow_all'] ) && ! isset( $rules[ $bot_lower ] ) ) {
			$is_blocked = true;
			$reason     = 'Blocked by wildcard Disallow: / (no override for ' . $bot . ').';
		}

		if ( ! $is_blocked ) {
			++$allowed_count;
		}

		$details[] = [
			'bot'     => $bot,
			'label'   => $label,
			'allowed' => ! $is_blocked,
			'reason'  => $reason,
		];
	}

	$score = (int) round( ( $allowed_count / max( 1, $total_bots ) ) * 100 );

	return [ 'score' => $score, 'details' => $details, 'robots_found' => true ];
}

/**
 * Scan a website's HTML for Schema.org / JSON-LD structured data.
 *
 * @param string $url Target URL.
 * @return array { 'score' => int, 'schemas_found' => array, 'details' => array }
 */
function gms_check_schema_markup( $url ) {
	$response = wp_remote_get( $url, [
		'timeout'    => 15,
		'sslverify'  => false,
		'user-agent' => 'Mozilla/5.0 (compatible; GrowMySecurityAudit/1.0)',
	] );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return [
			'score'         => 0,
			'schemas_found' => [],
			'details'       => [ 'Could not fetch the page to check for Schema markup.' ],
		];
	}

	$body = wp_remote_retrieve_body( $response );

	// Extract all JSON-LD blocks.
	$schemas_found = [];
	$details       = [];

	if ( preg_match_all( '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $body, $matches ) ) {
		foreach ( $matches[1] as $json_block ) {
			$decoded = json_decode( trim( $json_block ), true );
			if ( is_array( $decoded ) ) {
				$type = $decoded['@type'] ?? ( $decoded[0]['@type'] ?? 'Unknown' );
				if ( is_array( $type ) ) {
					$type = implode( ', ', $type );
				}
				$schemas_found[] = $type;
			}
		}
	}

	// Check for important AI-friendly meta signals.
	$has_meta_description = (bool) preg_match( '/<meta[^>]+name=["\']description["\'][^>]*>/i', $body );
	$has_og_tags          = (bool) preg_match( '/<meta[^>]+property=["\']og:/i', $body );
	$has_canonical        = (bool) preg_match( '/<link[^>]+rel=["\']canonical["\'][^>]*>/i', $body );

	$important_types = [ 'Organization', 'LocalBusiness', 'WebSite', 'WebPage', 'Service', 'Product', 'FAQPage', 'Article', 'BreadcrumbList' ];
	$found_important = array_intersect( $schemas_found, $important_types );

	// Score: max 100.
	$score = 0;
	if ( count( $schemas_found ) > 0 ) {
		$score += 25;
		$details[] = count( $schemas_found ) . ' JSON-LD schema block(s) found.';
	} else {
		$details[] = 'No JSON-LD structured data found.';
	}

	if ( count( $found_important ) > 0 ) {
		$score += 25;
		$details[] = 'Important schema types detected: ' . implode( ', ', $found_important ) . '.';
	} else {
		$details[] = 'No high-value schema types (Organization, LocalBusiness, Service) found.';
	}

	if ( $has_meta_description ) {
		$score += 15;
		$details[] = 'Meta description tag present.';
	} else {
		$details[] = 'Missing meta description tag.';
	}

	if ( $has_og_tags ) {
		$score += 15;
		$details[] = 'Open Graph (social sharing) tags detected.';
	} else {
		$details[] = 'No Open Graph tags found.';
	}

	if ( $has_canonical ) {
		$score += 10;
		$details[] = 'Canonical URL tag present.';
	} else {
		$details[] = 'Missing canonical URL tag.';
	}

	// Bonus for multiple schemas.
	if ( count( $schemas_found ) >= 3 ) {
		$score += 10;
	}

	return [
		'score'         => min( 100, $score ),
		'schemas_found' => $schemas_found,
		'details'       => $details,
	];
}

/**
 * Search for brand mentions using Serper.dev API.
 *
 * @param string $url     Target URL.
 * @param string $brand   Brand/company name extracted from the site.
 * @return array { 'score' => int, 'mentions' => int, 'snippets' => array, 'details' => array }
 */
function gms_check_brand_mentions( $url, $brand = '' ) {
	$api_key = gms_get_serper_api_key();

	if ( '' === $api_key ) {
		return [
			'score'    => 0,
			'mentions' => 0,
			'snippets' => [],
			'details'  => [ 'Serper API key not configured.' ],
		];
	}

	$parsed   = wp_parse_url( $url );
	$hostname = $parsed['host'] ?? '';

	// If no brand name provided, derive from hostname.
	if ( '' === $brand ) {
		$brand = preg_replace( '/^www\./', '', $hostname );
		$brand = preg_replace( '/\.(com|net|org|io|co|us|uk|dev|live|agency|security)$/i', '', $brand );
		$brand = str_replace( [ '-', '_', '.' ], ' ', $brand );
		$brand = ucwords( $brand );
	}

	$queries = [
		'"' . $brand . '" services',
		'site:' . $hostname,
	];

	$total_mentions = 0;
	$all_snippets   = [];
	$details        = [];

	foreach ( $queries as $query ) {
		$response = wp_remote_post( 'https://google.serper.dev/search', [
			'timeout' => 12,
			'headers' => [
				'X-API-KEY'    => $api_key,
				'Content-Type' => 'application/json',
			],
			'body'    => wp_json_encode( [
				'q'   => $query,
				'num' => 10,
			] ),
		] );

		if ( is_wp_error( $response ) ) {
			$details[] = 'Search query failed: ' . $query;
			continue;
		}

		$body    = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body, true );

		if ( ! is_array( $decoded ) ) {
			continue;
		}

		// Count organic results.
		$organic = $decoded['organic'] ?? [];
		foreach ( $organic as $result ) {
			++$total_mentions;
			if ( count( $all_snippets ) < 5 ) {
				$all_snippets[] = [
					'title'   => $result['title'] ?? '',
					'snippet' => $result['snippet'] ?? '',
					'link'    => $result['link'] ?? '',
				];
			}
		}

		// Check for AI overview / knowledge graph presence.
		if ( ! empty( $decoded['knowledgeGraph'] ) ) {
			$total_mentions += 5; // Bonus for knowledge graph.
			$details[] = 'Brand appears in Google Knowledge Graph.';
		}

		if ( ! empty( $decoded['answerBox'] ) ) {
			$total_mentions += 3; // Bonus for answer box.
			$details[] = 'Brand content appears in Google Answer Box.';
		}
	}

	// Score calculation based on mentions.
	if ( $total_mentions >= 15 ) {
		$score = 100;
	} elseif ( $total_mentions >= 10 ) {
		$score = 80;
	} elseif ( $total_mentions >= 5 ) {
		$score = 60;
	} elseif ( $total_mentions >= 2 ) {
		$score = 40;
	} elseif ( $total_mentions >= 1 ) {
		$score = 20;
	} else {
		$score = 0;
	}

	$details[] = $total_mentions . ' brand mention(s) found across search results.';

	return [
		'score'    => $score,
		'mentions' => $total_mentions,
		'snippets' => $all_snippets,
		'details'  => $details,
	];
}

/**
 * Calculate the composite AI Visibility score.
 *
 * @param string $url Target URL.
 * @return array Full AI visibility payload.
 */
function gms_calculate_ai_visibility( $url ) {
	$bot_check    = gms_check_ai_bot_permissions( $url );
	$schema_check = gms_check_schema_markup( $url );
	$brand_check  = gms_check_brand_mentions( $url );

	// Weighted composite: bots 30%, schema 35%, brand 35%.
	$composite = (int) round(
		( $bot_check['score'] * 0.30 ) +
		( $schema_check['score'] * 0.35 ) +
		( $brand_check['score'] * 0.35 )
	);

	$composite = min( 100, max( 0, $composite ) );

	// Build issues list.
	$issues = [];

	// Bot permission issues.
	foreach ( $bot_check['details'] as $bot_detail ) {
		if ( ! $bot_detail['allowed'] ) {
			$issues[] = [
				'label'    => $bot_detail['label'] . ' (' . $bot_detail['bot'] . ') is blocked',
				'desc'     => $bot_detail['reason'] . ' This prevents this AI engine from indexing your content.',
				'severity' => 'critical',
				'tag'      => 'ai',
			];
		}
	}

	// Schema issues.
	if ( $schema_check['score'] < 50 ) {
		$issues[] = [
			'label'    => 'Insufficient structured data for AI engines',
			'desc'     => 'Add JSON-LD Schema.org markup (Organization, LocalBusiness, Service) so AI models can accurately understand and cite your business.',
			'severity' => 'warning',
			'tag'      => 'ai',
		];
	}

	// Brand mention issues.
	if ( $brand_check['score'] < 40 ) {
		$issues[] = [
			'label'    => 'Low brand visibility in search results',
			'desc'     => 'Your brand has limited presence in search engine results. Build authoritative backlinks and publish consistent content to improve AI citation likelihood.',
			'severity' => 'warning',
			'tag'      => 'ai',
		];
	}

	return [
		'score'   => $composite,
		'label'   => gms_get_score_label( $composite ),
		'source'  => 'GEO Analysis (Robots.txt + Schema + Search Mentions)',
		'breakdown' => [
			'bot_permissions' => $bot_check['score'],
			'schema_markup'   => $schema_check['score'],
			'brand_mentions'  => $brand_check['score'],
		],
		'details' => [
			'bots'   => $bot_check,
			'schema' => $schema_check,
			'brand'  => $brand_check,
		],
		'issues'  => $issues,
	];
}

/**
 * AJAX handler for live Website Audit data.
 *
 * @return void
 */
function gms_ajax_fetch_real_audit_data() {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 );
	}

	if ( ! isset( $_POST['gms_audit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gms_audit_nonce'] ) ), 'gms_audit_fetch' ) ) {
		wp_send_json_error( [ 'message' => 'Invalid security token.' ], 403 );
	}

	$raw_url  = wp_unslash( $_POST['website_url'] ?? '' );
	$strategy = sanitize_key( wp_unslash( $_POST['strategy'] ?? 'desktop' ) );

	if ( ! in_array( $strategy, [ 'desktop', 'mobile' ], true ) ) {
		$strategy = 'desktop';
	}

	$normalized_url = gms_normalize_audit_url( $raw_url );

	if ( is_wp_error( $normalized_url ) ) {
		wp_send_json_error( [ 'message' => $normalized_url->get_error_message() ], 422 );
	}

	$pagespeed      = gms_fetch_pagespeed_with_retries( $normalized_url, $strategy );
	$mozilla        = gms_fetch_mozilla_observatory( $normalized_url );
	$live_headers   = gms_fetch_live_security_headers( $normalized_url );
	$ai_visibility  = gms_calculate_ai_visibility( $normalized_url );
	$lighthouse   = is_array( $pagespeed['data']['lighthouseResult'] ?? null ) ? $pagespeed['data']['lighthouseResult'] : [];
	$categories   = is_array( $lighthouse['categories'] ?? null ) ? $lighthouse['categories'] : [];

	$performance_score = isset( $categories['performance']['score'] ) && is_numeric( $categories['performance']['score'] ) ? (int) round( (float) $categories['performance']['score'] * 100 ) : null;
	$seo_score         = isset( $categories['seo']['score'] ) && is_numeric( $categories['seo']['score'] ) ? (int) round( (float) $categories['seo']['score'] * 100 ) : null;
	$observatory_score = isset( $mozilla['data']['score'] ) && is_numeric( $mozilla['data']['score'] ) ? (int) round( min( 100, max( 0, (float) $mozilla['data']['score'] ) ) ) : null;
	$observatory_grade = (string) ( $mozilla['data']['grade'] ?? '' );
	$header_security_score = (bool) $live_headers['success'] ? gms_calculate_header_security_score( $live_headers['headers'] ) : null;
	$security_score        = null !== $observatory_score ? $observatory_score : $header_security_score;
	$security_label        = null !== $observatory_score ? ( '' !== $observatory_grade ? $observatory_grade : gms_get_score_label( $observatory_score ) ) : gms_get_score_label( $header_security_score );
	$security_source       = null !== $observatory_score ? 'Mozilla Observatory' : ( null !== $header_security_score ? 'Live Header Analysis' : 'Mozilla Observatory' );
	$security_issues   = gms_build_security_issues(
		$live_headers['headers'],
		[
			'tests_failed'   => $mozilla['data']['tests_failed'] ?? 0,
			'tests_quantity' => $mozilla['data']['tests_quantity'] ?? 0,
		],
		(bool) $live_headers['success']
	);
	$performance_issues = ! empty( $lighthouse ) ? gms_collect_pagespeed_issues( $lighthouse, 'performance', 'performance', 4 ) : [];
	$seo_issues         = ! empty( $lighthouse ) ? gms_collect_pagespeed_issues( $lighthouse, 'seo', 'seo', 4 ) : [];
	$messages           = [];
	$pagespeed_mode     = (string) ( $pagespeed['debug']['resolved_mode'] ?? '' );
	$pagespeed_mode_label = 'Unavailable';

	if ( ! $pagespeed['success'] ) {
		$messages[] = gms_format_audit_message( 'pagespeed', $pagespeed['error'] ?? 'Google PageSpeed data was unavailable for this scan.' );
	}

	if ( ! $mozilla['success'] ) {
		$messages[] = gms_format_audit_message( 'mozilla', $mozilla['error'] ?? 'Mozilla Observatory data was unavailable for this scan.', null !== $header_security_score );
	}

	if ( 'anonymous' === $pagespeed_mode ) {
		$pagespeed_mode_label = 'Anonymous fallback';
	} elseif ( 'keyed' === $pagespeed_mode ) {
		$pagespeed_mode_label = 'API key';
	} elseif ( ! empty( $pagespeed['debug']['anonymous_fallback'] ) ) {
		$pagespeed_mode_label = 'Anonymous fallback attempted';
	}

	$payload = [
		'url'           => $normalized_url,
		'strategy'      => $strategy,
		'strategyLabel' => 'mobile' === $strategy ? 'Mobile' : 'Desktop',
		'scores'        => [
			'security'    => gms_build_score_payload(
				$security_score,
				$security_label,
				$security_source
			),
			'performance' => gms_build_score_payload(
				$performance_score,
				gms_get_score_label( $performance_score ),
				'Google PageSpeed Insights'
			),
			'seo'         => gms_build_score_payload(
				$seo_score,
				gms_get_score_label( $seo_score ),
				'Google PageSpeed Insights'
			),
			'ai_visibility' => gms_build_score_payload(
				$ai_visibility['score'],
				$ai_visibility['label'],
				$ai_visibility['source']
			),
		],
		'issues'        => gms_prepare_issue_list(
			array_merge(
				$security_issues,
				$performance_issues,
				$seo_issues,
				$ai_visibility['issues'] ?? []
			),
			12
		),
		'meta'          => [
			'summary'               => sprintf( '%s scan powered by Google PageSpeed Insights, Mozilla Observatory, and AI Visibility analysis.', 'mobile' === $strategy ? 'Mobile' : 'Desktop' ),
			'scannedAt'             => current_time( 'c' ),
			'pagespeedMode'         => $pagespeed_mode,
			'pagespeedModeLabel'    => $pagespeed_mode_label,
			'pagespeedAnonymous'    => (bool) ( $pagespeed['debug']['anonymous_fallback'] ?? false ),
			'observatoryGrade'      => $observatory_grade,
			'observatoryDetailsUrl' => (string) ( $mozilla['data']['details_url'] ?? '' ),
			'headersVerified'       => (bool) $live_headers['success'],
			'securitySource'        => $security_source,
			'messages'              => array_values( array_unique( array_filter( $messages ) ) ),
		],
	];

	if ( null === $performance_score && null === $seo_score && null === $security_score ) {
		$payload['issues'] = gms_build_unavailable_audit_issues( $pagespeed, $mozilla, $live_headers, $normalized_url );
		$payload['meta']['summary'] = 'The site did not return stable enough responses for a full live score, but the audit diagnostics below show what failed.';
	}

	gms_store_audit_payload( $normalized_url, $payload );

	gms_write_audit_debug_log(
		[
			'request' => [
				'url'      => $normalized_url,
				'strategy' => $strategy,
			],
			'pagespeed' => $pagespeed['debug'],
			'mozilla'   => $mozilla['debug'],
			'headers'   => [
				'success'     => (bool) $live_headers['success'],
				'status_code' => (int) $live_headers['status_code'],
				'effective_url' => (string) $live_headers['effective_url'],
				'sslverify_disabled' => (bool) $live_headers['sslverify_disabled'],
				'captured'    => [
					'content-security-policy'  => gms_get_header_value( $live_headers['headers'], 'content-security-policy' ),
					'strict-transport-security'=> gms_get_header_value( $live_headers['headers'], 'strict-transport-security' ),
					'x-content-type-options'   => gms_get_header_value( $live_headers['headers'], 'x-content-type-options' ),
					'x-frame-options'          => gms_get_header_value( $live_headers['headers'], 'x-frame-options' ),
					'referrer-policy'          => gms_get_header_value( $live_headers['headers'], 'referrer-policy' ),
					'permissions-policy'       => gms_get_header_value( $live_headers['headers'], 'permissions-policy' ),
				],
				'error'       => (string) $live_headers['error'],
			],
		]
	);

	wp_send_json_success( $payload );
}
add_action( 'wp_ajax_nopriv_gms_fetch_real_audit_data', 'gms_ajax_fetch_real_audit_data' );
add_action( 'wp_ajax_gms_fetch_real_audit_data', 'gms_ajax_fetch_real_audit_data' );

/**
 * Handle score update (fire-and-forget from client).
 */
function gms_handle_audit_score_update() {
	if ( ! isset( $_POST['gms_audit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gms_audit_nonce'] ) ), 'gms_audit_fetch' ) ) {
		wp_send_json_error( [], 403 );
	}

	$website_url = esc_url_raw( wp_unslash( $_POST['website_url'] ?? '' ) );
	$scores      = sanitize_text_field( wp_unslash( $_POST['scores'] ?? '' ) );

	if ( '' === $website_url || '' === $scores ) {
		wp_send_json_error( [], 422 );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'gms_audit_leads';

	$wpdb->update(
		$table,
		[ 'audit_scores' => $scores ],
		[ 'website_url' => $website_url ],
		[ '%s' ],
		[ '%s' ]
	);

	wp_send_json_success();
}
add_action( 'admin_post_nopriv_gms_audit_update_scores', 'gms_handle_audit_score_update' );
add_action( 'admin_post_gms_audit_update_scores', 'gms_handle_audit_score_update' );
add_action( 'wp_ajax_nopriv_gms_audit_update_scores', 'gms_handle_audit_score_update' );
add_action( 'wp_ajax_gms_audit_update_scores', 'gms_handle_audit_score_update' );

/**
 * Enqueue audit page assets.
 */
function gms_enqueue_audit_page_assets() {
	if ( ! is_page( 'website-audit' ) ) {
		return;
	}

	wp_enqueue_style(
		'grow-my-security-front-page-font',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'grow-my-security-audit',
		get_theme_file_uri( 'assets/css/website-audit.css' ),
		[ 'grow-my-security-style' ],
		gms_asset_version( 'assets/css/website-audit.css' )
	);

	wp_enqueue_script(
		'jspdf',
		'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
		[],
		'2.5.1',
		true
	);

	wp_enqueue_script(
		'jspdf-autotable',
		'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js',
		[ 'jspdf' ],
		'3.8.4',
		true
	);

	wp_enqueue_script(
		'grow-my-security-audit',
		get_theme_file_uri( 'assets/js/website-audit.js' ),
		[ 'jspdf', 'jspdf-autotable' ],
		gms_asset_version( 'assets/js/website-audit.js' ),
		true
	);
	wp_script_add_data( 'grow-my-security-audit', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_audit_page_assets', 1010 );

/**
 * Customizer: add webhook URL field for audit leads.
 */
function gms_audit_customizer_settings( $wp_customize ) {
	$wp_customize->add_setting( 'gms_audit_webhook_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	] );

	// Only add the control if the brand section exists.
	$section_id = $wp_customize->get_section( 'gms_brand_section' ) ? 'gms_brand_section' : 'title_tagline';

	$wp_customize->add_control( 'gms_audit_webhook_url', [
		'label'       => __( 'Audit Lead Webhook URL', 'grow-my-security' ),
		'description' => __( 'Optional Zapier / CRM webhook to receive audit leads as JSON POST.', 'grow-my-security' ),
		'section'     => $section_id,
		'type'        => 'url',
	] );
}
add_action( 'customize_register', 'gms_audit_customizer_settings', 30 );

/**
 * Check whether the current page should use the shared service detail experience.
 */
function gms_is_service_detail_page() {
	if ( ! is_page() ) {
		return false;
	}

	global $post;

	if ( ! ( $post instanceof WP_Post ) ) {
		return false;
	}

	if ( 'single-service.php' === get_page_template_slug( $post ) ) {
		return true;
	}

	$parent = $post->post_parent ? get_post( $post->post_parent ) : null;

	return $parent instanceof WP_Post && in_array( $parent->post_name, [ 'services', 'industries' ], true );
}

/**
 * Route service and industry detail pages to the shared detail template.
 */
function gms_industry_template_include( $template ) {
	if ( ! gms_is_service_detail_page() ) {
		return $template;
	}

	$new_template = get_template_directory() . '/single-service.php';

	return file_exists( $new_template ) ? $new_template : $template;
}
add_filter( 'template_include', 'gms_industry_template_include' );

/**
 * Enqueue shared detail-page assets.
 */
function gms_register_service_page_assets_globally() {
	wp_register_style(
		'grow-my-security-service-detail',
		get_theme_file_uri( 'assets/css/service-detail.css' ),
		[ 'grow-my-security-style' ],
		gms_asset_version( 'assets/css/service-detail.css' )
	);

	wp_register_style(
		'grow-my-security-service-inner-premium',
		get_theme_file_uri( 'assets/css/service-inner-premium.css' ),
		[ 'grow-my-security-service-detail' ],
		gms_asset_version( 'assets/css/service-inner-premium.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'gms_register_service_page_assets_globally', 5 );
add_action( 'elementor/editor/before_enqueue_scripts', 'gms_register_service_page_assets_globally' );

function gms_enqueue_service_page_assets() {
	if ( ! gms_is_service_detail_page() ) {
		return;
	}

	wp_enqueue_style( 'grow-my-security-service-detail' );
	wp_enqueue_style( 'grow-my-security-service-inner-premium' );
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_service_page_assets' );

function gms_enqueue_case_study_assets() {
	// Enqueue on homepage, single case study, case study archive, AND the physical Case Studies page.
	if ( is_front_page() || is_singular( 'gms_case_study' ) || is_post_type_archive( 'gms_case_study' ) || is_page( 'case-studies' ) ) {
		wp_enqueue_style(
			'grow-my-security-case-studies',
			get_theme_file_uri( 'assets/css/case-studies.css' ),
			[ 'grow-my-security-style' ],
			gms_asset_version( 'assets/css/case-studies.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'gms_enqueue_case_study_assets' );

/**
 * Supreme Nuclear Fix: All-in-one Head Injection with MutationObserver.
 */








