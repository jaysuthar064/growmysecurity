<?php
/**
 * Local-only compatibility route for Studio's auto-login URL when Laragon serves
 * the site on port 8881.
 */

add_action(
	'init',
	function () {
		$request_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
		$host         = $_SERVER['HTTP_HOST'] ?? '';

		if ('/studio-auto-login' !== $request_path || 'localhost:8881' !== $host) {
			return;
		}

		if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
			wp_die('Local auto-login is only available from this machine.', 403);
		}

		$user = get_user_by('login', 'admin');

		if (!$user) {
			wp_die('Local admin user not found.', 500);
		}

		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID, true);
		do_action('wp_login', $user->user_login, $user);

		$redirect_to = $_GET['redirect_to'] ?? admin_url();

		if (0 !== strpos($redirect_to, 'http://localhost:8881/')) {
			$redirect_to = admin_url();
		}

		wp_safe_redirect($redirect_to);
		exit;
	}
);
