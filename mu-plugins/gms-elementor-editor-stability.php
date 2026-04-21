<?php
/**
 * Stabilize Elementor editor requests without affecting the live site UI.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect Elementor editor and preview requests early enough for MU-plugin filters.
 */
function gms_is_elementor_editor_like_request(): bool {
	if ( isset( $_GET['action'] ) && 'elementor' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
		return true;
	}

	if ( isset( $_GET['elementor-preview'] ) ) {
		return true;
	}

	if ( ! wp_doing_ajax() ) {
		return false;
	}

	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

	return in_array( $action, [ 'elementor_ajax', 'elementor' ], true );
}

/**
 * Detect requests that should boot WordPress with a lighter plugin stack.
 */
function gms_should_use_lightweight_editor_bootstrap(): bool {
	if ( gms_is_elementor_editor_like_request() ) {
		return true;
	}

	return defined( 'WP_CLI' ) && WP_CLI;
}

/**
 * Maintenance plugins that should stay out of the public request path.
 *
 * These plugins are useful for migration and staging workflows, but loading
 * them on every front-end and REST request slows WP Studio down without
 * changing the live markup.
 *
 * @return string[]
 */
function gms_get_maintenance_only_plugins(): array {
	return [
		'all-in-one-wp-migration/all-in-one-wp-migration.php',
		'wp-staging/wp-staging.php',
	];
}

/**
 * Detect direct admin requests that belong to the maintenance plugins.
 */
function gms_is_maintenance_plugin_request(): bool {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	if ( '' !== $page && ( 0 === strpos( $page, 'ai1wm' ) || 0 === strpos( $page, 'wpstg' ) ) ) {
		return true;
	}

	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

	if ( '' !== $action && ( 0 === strpos( $action, 'ai1wm' ) || 0 === strpos( $action, 'wpstg' ) ) ) {
		return true;
	}

	$request_uri = strtolower( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) );

	return false !== strpos( $request_uri, 'all-in-one-wp-migration' ) || false !== strpos( $request_uri, 'wp-staging' );
}

/**
 * Prune maintenance-only plugins from requests that do not need them.
 */
function gms_should_prune_maintenance_plugins_from_request(): bool {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}

	if ( gms_is_maintenance_plugin_request() ) {
		return false;
	}

	if ( gms_should_use_lightweight_editor_bootstrap() ) {
		return true;
	}

	if ( wp_doing_ajax() ) {
		return true;
	}

	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
		return true;
	}

	return ! is_admin();
}

/**
 * Build a stable fake cache payload for Elementor option-based asset caches.
 */
function gms_get_editor_stubbed_option_cache_payload( array $value = [] ): array {
	return [
		'timeout' => current_time( 'timestamp' ) + HOUR_IN_SECONDS,
		'value'   => wp_json_encode( $value ),
	];
}

/**
 * Get the small set of hosts that should remain reachable during editor boot.
 *
 * WP Studio uses a single PHP worker for the local site, so slow outbound
 * requests to Elementor services can make the entire site feel frozen while
 * the editor is opening.
 *
 * @return string[]
 */
function gms_get_editor_safe_hosts(): array {
	$hosts = [
		'localhost',
		'127.0.0.1',
		'::1',
	];

	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );

	if ( is_string( $site_host ) && '' !== $site_host ) {
		$hosts[] = strtolower( $site_host );
	}

	return array_values( array_unique( $hosts ) );
}

/**
 * Determine whether a URL host should be treated as local/safe.
 */
function gms_is_editor_safe_host( string $host ): bool {
	$host = strtolower( trim( $host ) );

	if ( '' === $host ) {
		return true;
	}

	if ( in_array( $host, gms_get_editor_safe_hosts(), true ) ) {
		return true;
	}

	return str_ends_with( $host, '.wp.build' );
}

/**
 * Elementor editor requests need the common app before AI/editor assets boot.
 *
 * In WP Studio the editor can reach `elementor/editor/before_enqueue_scripts`
 * before Elementor has initialized its common connect component, which lets the
 * AI module fatal while building its connect URL. Boot the common app early for
 * editor-like requests only so the live site remains untouched.
 */
function gms_bootstrap_elementor_common_for_editor(): void {
	if ( ! gms_is_elementor_editor_like_request() || ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}

	$plugin = \Elementor\Plugin::$instance;

	if ( ! $plugin || ! method_exists( $plugin, 'init_common' ) || ! empty( $plugin->common ) ) {
		return;
	}

	$plugin->init_common();
}
add_action( 'elementor/init', 'gms_bootstrap_elementor_common_for_editor', 1 );

/**
 * Quiet noisy notices and deprecations during Elementor editor requests.
 *
 * WP Studio runs the site through a single PHP worker, so repeated notice
 * logging during editor boot can make the whole local site feel frozen.
 */
foreach ( [ 'deprecated_function_trigger_error', 'deprecated_argument_trigger_error', 'deprecated_hook_trigger_error', 'doing_it_wrong_trigger_error' ] as $gms_editor_notice_filter ) {
	add_filter(
		$gms_editor_notice_filter,
		static function ( $trigger ) {
			if ( ! gms_should_use_lightweight_editor_bootstrap() ) {
				return $trigger;
			}

			return false;
		},
		1
	);
}

if ( gms_should_use_lightweight_editor_bootstrap() ) {
	error_reporting( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR );
}

foreach ( [ 'editor_events', 'e_pro_free_trial_popup' ] as $gms_editor_experiment ) {
	add_filter(
		'pre_option_elementor_experiment-' . $gms_editor_experiment,
		static function ( $value ) {
			if ( ! gms_should_use_lightweight_editor_bootstrap() ) {
				return $value;
			}

			return 'inactive';
		},
		1
	);
}

add_filter(
	'option_active_plugins',
	static function ( $plugins ) {
		if ( ! gms_should_prune_maintenance_plugins_from_request() || ! is_array( $plugins ) ) {
			return $plugins;
		}

		return array_values( array_diff( $plugins, gms_get_maintenance_only_plugins() ) );
	},
	1
);

add_filter(
	'pre_http_request',
	static function ( $pre, $parsed_args, $url ) {
		if ( ! gms_should_use_lightweight_editor_bootstrap() || ! is_string( $url ) || '' === $url ) {
			return $pre;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! is_string( $host ) || gms_is_editor_safe_host( $host ) ) {
			return $pre;
		}

		return new WP_Error(
			'gms_editor_remote_blocked',
			sprintf(
				'Blocked remote request to %s during Elementor editor bootstrap.',
				$host
			)
		);
	},
	1,
	3
);

/**
 * Force-disable Elementor AI on editor requests only.
 *
 * The editor spinner was being triggered by AI control bootstrapping in the
 * sidebar. Returning "0" here prevents the AI module from booting for the
 * request, while keeping the front-end output unchanged.
 */
add_filter(
	'get_user_option_elementor_enable_ai',
	static function ( $result ) {
		if ( ! gms_is_elementor_editor_like_request() ) {
			return $result;
		}

		return '0';
	},
	1
);

foreach ( [ 'user_option_elementor_enable_ai', 'default_user_option_elementor_enable_ai' ] as $gms_editor_ai_filter ) {
	add_filter(
		$gms_editor_ai_filter,
		static function ( $result ) {
			if ( ! gms_is_elementor_editor_like_request() ) {
				return $result;
			}

			return '0';
		},
		1
	);
}

/**
 * Dequeue any AI assets that may already have been registered by Elementor.
 */
function gms_dequeue_elementor_editor_ai_assets(): void {
	foreach ( [ 'elementor-ai', 'elementor-ai-layout', 'e-react-promotions', 'editor-v4-opt-in-alphachip', 'pro-free-trial-popup', 'pro-install-events' ] as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}
add_action( 'elementor/editor/before_enqueue_scripts', 'gms_dequeue_elementor_editor_ai_assets', 1000 );

/**
 * Remove non-essential editor packages that are failing in WP Studio.
 *
 * These modules add pages-panel, checklist, and kit-defaults UX on top of the
 * core builder. They are not required for editing the homepage itself, and
 * their failing REST calls were leaving the panel in a loading state.
 */
function gms_stabilize_optional_elementor_editor_packages( $packages ) {
	if ( ! gms_is_elementor_editor_like_request() || ! is_array( $packages ) ) {
		return $packages;
	}

	$blocked_handles = [
		'editor-site-navigation',
	];

	foreach ( $packages as $key => $package ) {
		if ( is_string( $package ) && in_array( $package, $blocked_handles, true ) ) {
			unset( $packages[ $key ] );
			continue;
		}

		if ( is_string( $key ) && in_array( $key, $blocked_handles, true ) ) {
			unset( $packages[ $key ] );
			continue;
		}

		if ( is_array( $package ) && ! empty( $package['package'] ) && '@elementor/editor-site-navigation' === $package['package'] ) {
			unset( $packages[ $key ] );
		}
	}

	return array_is_list( $packages ) ? array_values( $packages ) : $packages;
}
add_filter( 'elementor/editor/v2/packages', 'gms_stabilize_optional_elementor_editor_packages', PHP_INT_MAX );

add_filter(
	'elementor/editor/v2/scripts/env',
	static function ( $env ) {
		if ( ! gms_is_elementor_editor_like_request() || ! is_array( $env ) ) {
			return $env;
		}

		if ( empty( $env['@elementor/editor-site-navigation'] ) || ! is_array( $env['@elementor/editor-site-navigation'] ) ) {
			$env['@elementor/editor-site-navigation'] = [];
		}

		$env['@elementor/editor-site-navigation']['is_pages_panel_active'] = false;

		return $env;
	},
	PHP_INT_MAX
);

function gms_dequeue_optional_elementor_editor_assets(): void {
	if ( ! gms_is_elementor_editor_like_request() ) {
		return;
	}

	foreach ( [ 'e-checklist', 'elementor-kit-elements-defaults-editor' ] as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}
add_action( 'elementor/editor/before_enqueue_scripts', 'gms_dequeue_optional_elementor_editor_assets', 1001 );

add_filter(
	'elementor/editor/v2/enqueued_scripts',
	static function ( $scripts ) {
		if ( ! gms_is_elementor_editor_like_request() || ! is_array( $scripts ) ) {
			return $scripts;
		}

		return array_values(
			array_filter(
				$scripts,
				static function ( $handle ) {
					return ! in_array( $handle, [ 'e-checklist', 'elementor-kit-elements-defaults-editor' ], true );
				}
			)
		);
	},
	PHP_INT_MAX
);

/**
 * Clear Elementor's panel loading state once the real sidebar has rendered.
 *
 * In WP Studio the editor occasionally leaves `body.elementor-panel-loading`
 * behind even after the panel DOM is fully built. The sidebar is already
 * usable at that point, but the loading overlay keeps covering it. This
 * editor-only guard removes the stuck loading state after the actual panel
 * header/content/footer are present.
 */
function gms_get_elementor_panel_loading_fallback_script(): string {
	return <<<'JS'
(function () {
	if (window.gmsElementorPanelLoadingGuard) {
		return;
	}

	window.gmsElementorPanelLoadingGuard = true;

	const contentSelectors = [
		'#elementor-panel-content-wrapper .elementor-control',
		'#elementor-panel-content-wrapper .elementor-panel-navigation',
		'#elementor-panel-content-wrapper .elementor-panel-category',
		'#elementor-panel-content-wrapper .elementor-panel-page-menu',
		'#elementor-panel-content-wrapper .elementor-template-library-menu-items',
		'#elementor-panel-content-wrapper .elementor-control-type-section'
	].join(',');

	const headerSelectors = [
		'#elementor-panel-header-wrapper .elementor-header-button',
		'#elementor-panel-header-wrapper h2',
		'#elementor-panel-header-wrapper button'
	].join(',');

	const footerSelectors = [
		'#elementor-panel-footer .elementor-panel-footer-tool',
		'#elementor-panel-footer #elementor-panel-saver-button-publish',
		'#elementor-panel-footer .elementor-panel-container > *'
	].join(',');

	const hasMeaningfulText = (node) => {
		return !!node && (node.textContent || '').replace(/\s+/g, '').length > 0;
	};

	const panelLooksReady = () => {
		const content = document.querySelector('#elementor-panel-content-wrapper');
		const header = document.querySelector('#elementor-panel-header-wrapper');
		const footer = document.querySelector('#elementor-panel-footer');

		if (document.querySelector(contentSelectors)) {
			return true;
		}

		if (document.querySelector(headerSelectors) && document.querySelector(footerSelectors) && content && content.children.length > 0) {
			return true;
		}

		if (content && Array.from(content.children).some((child) => child.nodeType === 1 && (child.children.length > 0 || hasMeaningfulText(child)))) {
			return true;
		}

		return !!(header && footer && hasMeaningfulText(header) && content && hasMeaningfulText(content));
	};

	const clearStuckPanelLoading = () => {
		if (!document.body || !document.body.classList.contains('elementor-panel-loading') || !panelLooksReady()) {
			return;
		}

		document.body.classList.remove('elementor-panel-loading');

		const loading = document.querySelector('#elementor-panel-state-loading');

		if (loading) {
			loading.setAttribute('hidden', 'hidden');
			loading.setAttribute('aria-hidden', 'true');
			loading.style.display = 'none';
		}
	};

	let scheduled = false;

	const scheduleCheck = () => {
		if (scheduled) {
			return;
		}

		scheduled = true;

		window.requestAnimationFrame(() => {
			scheduled = false;
			clearStuckPanelLoading();
		});
	};

	const observer = new MutationObserver(scheduleCheck);

	const start = () => {
		scheduleCheck();

		observer.observe(document.documentElement, {
			subtree: true,
			childList: true,
			attributes: true,
			attributeFilter: ['class', 'hidden', 'inert']
		});

		window.setTimeout(scheduleCheck, 250);
		window.setTimeout(scheduleCheck, 1000);
		window.setTimeout(scheduleCheck, 2500);
		window.setTimeout(scheduleCheck, 5000);
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start, { once: true });
	} else {
		start();
	}

	window.addEventListener('load', scheduleCheck);
	document.addEventListener('elementor/panel/init', () => {
		window.setTimeout(scheduleCheck, 100);
		window.setTimeout(scheduleCheck, 500);
		window.setTimeout(scheduleCheck, 1500);
	});
})();
JS;
}

/**
 * Print the panel loading fallback in the footer if script queue injection fails.
 */
function gms_print_elementor_panel_loading_fallback_script(): void {
	if ( ! gms_is_elementor_editor_like_request() ) {
		return;
	}

	static $printed = false;

	if ( $printed ) {
		return;
	}

	$printed = true;

	echo "<script id=\"gms-elementor-panel-loading-fallback\">\n";
	echo gms_get_elementor_panel_loading_fallback_script();
	echo "\n</script>\n";
}

function gms_enqueue_elementor_panel_loading_fallback(): void {
	if ( ! gms_is_elementor_editor_like_request() ) {
		return;
	}

	$script = gms_get_elementor_panel_loading_fallback_script();

	$handle = wp_script_is( 'elementor-editor', 'registered' ) || wp_script_is( 'elementor-editor', 'enqueued' )
		? 'elementor-editor'
		: 'jquery-core';

	if ( ! wp_add_inline_script( $handle, $script, 'after' ) ) {
		add_action( 'admin_print_footer_scripts', 'gms_print_elementor_panel_loading_fallback_script', PHP_INT_MAX );
	}
}
add_action( 'elementor/editor/after_enqueue_scripts', 'gms_enqueue_elementor_panel_loading_fallback', PHP_INT_MAX );

add_action(
	'elementor/init',
	static function (): void {
		if ( ! gms_is_elementor_editor_like_request() || ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$plugin = \Elementor\Plugin::$instance;

		if ( ! $plugin || empty( $plugin->modules_manager ) ) {
			return;
		}

		$promotions = $plugin->modules_manager->get_modules( 'promotions' );

		if ( $promotions ) {
			remove_action( 'elementor/editor/before_enqueue_scripts', [ $promotions, 'enqueue_react_data' ] );
			remove_action( 'elementor/editor/before_enqueue_scripts', [ $promotions, 'enqueue_editor_v4_alphachip' ] );
			remove_filter( 'elementor/editor/localize_settings', [ $promotions, 'add_v4_promotions_data' ] );
		}

		$pro_free_trial_popup = $plugin->modules_manager->get_modules( 'pro-free-trial-popup' );

		if ( $pro_free_trial_popup ) {
			remove_action( 'elementor/editor/before_enqueue_scripts', [ $pro_free_trial_popup, 'maybe_enqueue_popup' ] );
		}

	},
	100
);

add_action(
	'elementor/editor/after_enqueue_styles',
	static function (): void {
		wp_dequeue_style( 'elementor-ai-editor' );
		wp_deregister_style( 'elementor-ai-editor' );
	},
	1000
);

add_filter(
	'elementor/editor/localize_settings',
	static function ( array $settings ): array {
		if ( ! gms_is_elementor_editor_like_request() ) {
			return $settings;
		}

		unset( $settings['promotion'], $settings['promotions'], $settings['v4Promotions'] );

		if ( empty( $settings['library_connect'] ) || ! is_array( $settings['library_connect'] ) ) {
			$settings['library_connect'] = [];
		}

		if ( ! array_key_exists( 'is_connected', $settings['library_connect'] ) ) {
			$settings['library_connect']['is_connected'] = false;
		}

		$settings['editor_events'] = [
			'can_send_events' => false,
			'flags_enabled' => false,
			'session_replays' => [],
			'token' => '',
		];

		if ( empty( $settings['user'] ) || ! is_array( $settings['user'] ) ) {
			$settings['user'] = [];
		}

		if ( empty( $settings['user']['top_bar'] ) || ! is_array( $settings['user']['top_bar'] ) ) {
			$settings['user']['top_bar'] = [];
		}

		foreach ( [ 'connect_url', 'my_elementor_url' ] as $top_bar_key ) {
			if ( ! array_key_exists( $top_bar_key, $settings['user']['top_bar'] ) ) {
				$settings['user']['top_bar'][ $top_bar_key ] = '';
			}
		}

		unset( $settings['v4Promotions'] );

		return $settings;
	},
	PHP_INT_MAX
);

add_filter(
	'elementor/common/localize_settings',
	static function ( array $settings ): array {
		if ( ! gms_is_elementor_editor_like_request() ) {
			return $settings;
		}

		if ( empty( $settings['library_connect'] ) || ! is_array( $settings['library_connect'] ) ) {
			$settings['library_connect'] = [];
		}

		if ( ! array_key_exists( 'is_connected', $settings['library_connect'] ) ) {
			$settings['library_connect']['is_connected'] = false;
		}

		$settings['editor_events'] = [
			'can_send_events' => false,
			'flags_enabled' => false,
			'session_replays' => [],
			'token' => '',
		];

		if ( isset( $settings['experimentalFeatures']['editor_events'] ) ) {
			$settings['experimentalFeatures']['editor_events'] = false;
		}

		return $settings;
	},
	PHP_INT_MAX
);

add_action(
	'elementor/preview/enqueue_styles',
	static function (): void {
		if ( ! gms_is_elementor_editor_like_request() ) {
			return;
		}

		wp_dequeue_style( 'elementor-ai-layout-preview' );
		wp_deregister_style( 'elementor-ai-layout-preview' );
	},
	1000
);

add_action(
	'plugins_loaded',
	static function (): void {
		if ( ! gms_should_use_lightweight_editor_bootstrap() ) {
			return;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			add_filter(
				'pre_transient_elementor_remote_info_api_data_' . ELEMENTOR_VERSION,
				static function ( $value ) {
					return [
						'pro_widgets'       => [],
						'upgrade_notice'    => [],
						'admin_notice'      => [],
						'canary_deployment' => [],
					];
				},
				1
			);
		}

		add_filter(
			'pre_option__elementor_free_to_pro_upsell',
			static function ( $value ) {
				return gms_get_editor_stubbed_option_cache_payload( [] );
			},
			1
		);

		add_filter(
			'pre_option__elementor_v4_promotions',
			static function ( $value ) {
				return gms_get_editor_stubbed_option_cache_payload( [] );
			},
			1
		);
	},
	1
);

/**
 * Remove AI behavior from text-like controls during editor requests only.
 *
 * This keeps the control inputs fully editable while preventing Elementor's AI
 * behavior layer from attaching to every text field in the sidebar.
 */
function gms_disable_elementor_ai_controls_for_editor( $element, $section_id, $args = [] ): void {
	if ( ! gms_is_elementor_editor_like_request() || ! is_object( $element ) ) {
		return;
	}

	if ( ! method_exists( $element, 'get_section_controls' ) || ! method_exists( $element, 'update_control' ) ) {
		return;
	}

	$controls = $element->get_section_controls( $section_id );

	if ( ! is_array( $controls ) || [] === $controls ) {
		return;
	}

	foreach ( $controls as $control_id => $control ) {
		$type = (string) ( $control['type'] ?? '' );

		if ( ! in_array( $type, [ 'text', 'textarea', 'wysiwyg', 'code', 'media' ], true ) ) {
			continue;
		}

		if ( array_key_exists( 'ai', $control ) && false === $control['ai'] ) {
			continue;
		}

		$element->update_control( $control_id, [ 'ai' => false ] );
	}
}
add_action( 'elementor/element/after_section_end', 'gms_disable_elementor_ai_controls_for_editor', 999, 3 );
