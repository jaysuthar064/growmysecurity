<?php
/**
 * Contact form widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Contact_Form_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-contact-form';
	}

	public function get_title() {
		return __( 'GMS Contact Form', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	protected function register_controls() {
		$config           = \gms_get_demo_config();
		$contact_defaults = \function_exists( 'gms_get_contact_page_defaults' )
			? \gms_get_contact_page_defaults()
			: [
				'response_note' => 'We respond to all inquiries within 1 business day',
				'panel_image'   => [ 'url' => \get_theme_file_uri( 'assets/images/contact-us-panel-visual.png' ) ],
				'email_heading' => 'Prefer Email?',
				'email_note'    => 'We respect your inbox. No spam, ever.',
				'call_heading'  => 'Need To Move Fast?',
				'call_text'     => 'Book a call directly',
				'call_url'      => [ 'url' => \home_url( '/contact-us/' ) ],
			];
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Contact Content', 'grow-my-security' ),
			]
		);
		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'Contact Us',
				'title'       => 'Ready to build trust-led pipeline?',
				'description' => "Let's build a stronger pipeline for your security business that is secure, scalable, and measurable.",
			]
		);
		$this->add_control( 'email', [ 'label' => __( 'Email', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $config['branding']['email'] ] );
		$this->add_control( 'phone', [ 'label' => __( 'Phone', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $config['branding']['phone'] ] );
		$this->add_control( 'address', [ 'label' => __( 'Address', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $config['branding']['address'] ] );
		$this->add_control( 'hours', [ 'label' => __( 'Working Hours', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Monday-Friday, 09:00AM - 06:00PM' ] );
		$this->add_control( 'services_list', [ 'label' => __( 'Services List', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ) ] );
		$this->add_control( 'submit_text', [ 'label' => __( 'Submit Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => 'Get My Free Quote' ] );
		$this->add_control( 'footer_chips', [ 'label' => __( 'Footer Chips', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA, 'description' => __( 'One per line.', 'grow-my-security' ), 'default' => "24-48 hour response\nNo spam, ever\nLocal experts" ] );
		$this->add_control( 'response_note', [ 'label' => __( 'Response Note', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $contact_defaults['response_note'] ?? '' ] );
		$this->add_control( 'panel_image', [ 'label' => __( 'Panel Image', 'grow-my-security' ), 'type' => Controls_Manager::MEDIA, 'default' => $contact_defaults['panel_image'] ?? [] ] );
		$this->add_control( 'email_heading', [ 'label' => __( 'Email Heading', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $contact_defaults['email_heading'] ?? '' ] );
		$this->add_control( 'email_note', [ 'label' => __( 'Email Note', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $contact_defaults['email_note'] ?? '' ] );
		$this->add_control( 'call_heading', [ 'label' => __( 'Call Heading', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $contact_defaults['call_heading'] ?? '' ] );
		$this->add_control( 'call_text', [ 'label' => __( 'Call Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'default' => $contact_defaults['call_text'] ?? '' ] );
		$this->add_control( 'call_url', [ 'label' => __( 'Call URL', 'grow-my-security' ), 'type' => Controls_Manager::URL, 'default' => $contact_defaults['call_url'] ?? [] ] );
		$this->end_controls_section();

		$this->add_widget_style_controls( 'contact_form_section_style', '{{WRAPPER}} .gms-widget' );
	}

	private function render_lines( string $text, string $tag = 'li' ): void {
		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}
			echo '<' . esc_attr( $tag ) . '>' . esc_html( trim( $line ) ) . '</' . esc_attr( $tag ) . '>';
		}
	}

	private function get_services( array $settings ): string {
		$services = $settings['services_list'] ?? '';

		if ( '' !== trim( $services ) ) {
			return $services;
		}

		return implode(
			"\n",
			[
				'Branding Services',
				'Search Engine Optimization',
				'Fractional CMO Services',
				'Social Media Marketing',
				'Website Design',
				'Website Development',
				'Advertising Services',
				'AI Solutions',
			]
		);
	}

	private function render_home_quote( array $settings, array $config ): void {
		$services = preg_split( '/\r\n|\r|\n/', $this->get_services( $settings ) );
		$services = array_values( array_filter( array_map( 'trim', $services ) ) );
		$details  = [
			[ 'icon' => '&#9742;', 'label' => __( 'Phone', 'grow-my-security' ), 'value' => (string) ( $settings['phone'] ?? '' ) ],
			[ 'icon' => '&#9993;', 'label' => __( 'Email', 'grow-my-security' ), 'value' => (string) ( $settings['email'] ?? '' ) ],
			[ 'icon' => '&#8962;', 'label' => __( 'Head Office', 'grow-my-security' ), 'value' => (string) ( $settings['address'] ?? '' ) ],
			[ 'icon' => '&#9716;', 'label' => __( 'Working Hours', 'grow-my-security' ), 'value' => (string) ( $settings['hours'] ?? '' ) ],
		];
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--quote">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-quote">
					<div class="gms-homepage-quote__content">
						<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--contact" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
						<h2><?php echo esc_html( $settings['title'] ?? '' ); ?></h2>
						<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
						<div class="gms-homepage-quote__services">
							<h3><?php esc_html_e( "What services you'll get", 'grow-my-security' ); ?></h3>
							<ul><?php foreach ( $services as $service ) : ?><li><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><span><?php echo esc_html( $service ); ?></span></li><?php endforeach; ?></ul>
						</div>
						<div class="gms-homepage-quote__details">
							<?php foreach ( $details as $detail ) : ?>
								<?php if ( '' === trim( (string) $detail['value'] ) ) { continue; } ?>
								<div class="gms-homepage-quote__detail"><div class="gms-homepage-quote__detail-icon" aria-hidden="true"><?php echo $detail['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div><p><?php echo esc_html( $detail['label'] ); ?></p><h3><?php if ( 'Phone' === $detail['label'] ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) $detail['value'] ) ); ?>"><?php echo esc_html( $detail['value'] ); ?></a><?php elseif ( 'Email' === $detail['label'] ) : ?><a href="mailto:<?php echo esc_attr( $detail['value'] ); ?>"><?php echo esc_html( $detail['value'] ); ?></a><?php else : ?><?php echo esc_html( $detail['value'] ); ?><?php endif; ?></h3></div></div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="gms-homepage-quote__form-card">
						<h3><?php esc_html_e( 'Start your project today - no obligation', 'grow-my-security' ); ?></h3>
						<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
							<input type="hidden" name="action" value="gms_contact_form">
							<input type="hidden" name="privacy_acceptance" value="1">
							<input type="hidden" name="bot_check" value="1">
							<?php wp_nonce_field( 'gms_contact_form', 'gms_contact_nonce' ); ?>
							<label class="gms-homepage-field gms-homepage-field--active"><span><?php esc_html_e( 'Full Name', 'grow-my-security' ); ?></span><input type="text" name="full_name" placeholder="<?php esc_attr_e( 'Enter full name', 'grow-my-security' ); ?>" autocomplete="name" required></label>
							<label class="gms-homepage-field"><span><?php esc_html_e( 'Email Address', 'grow-my-security' ); ?></span><input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter email address', 'grow-my-security' ); ?>" autocomplete="email" required></label>
							<label class="gms-homepage-field"><span><?php esc_html_e( 'Phone', 'grow-my-security' ); ?></span><input type="tel" name="phone" placeholder="<?php esc_attr_e( '(555) 123-4567', 'grow-my-security' ); ?>" autocomplete="tel"></label>
							<label class="gms-homepage-field"><span><?php esc_html_e( "Services you're interested in", 'grow-my-security' ); ?></span><span class="gms-homepage-select-wrap"><select name="service_interest"><?php foreach ( $services as $service ) : ?><option value="<?php echo esc_attr( $service ); ?>"<?php selected( 'Fractional CMO Services', $service ); ?>><?php echo esc_html( $service ); ?></option><?php endforeach; ?></select></span></label>
							<label class="gms-homepage-field"><span><?php esc_html_e( 'Project description', 'grow-my-security' ); ?></span><textarea name="message" placeholder="<?php esc_attr_e( 'Tell us more about your project, timeline, budget, or other specific requirements', 'grow-my-security' ); ?>"></textarea></label>
							<button class="gms-homepage-button gms-homepage-button--primary gms-homepage-button--full" type="submit"><span><?php echo esc_html( $settings['submit_text'] ?? 'Get My Free Quote' ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></button>
						</form>
						<?php if ( ! empty( $settings['footer_chips'] ) ) : ?>
							<div class="gms-homepage-quote__reassurance"><?php foreach ( preg_split( '/\r\n|\r|\n/', $settings['footer_chips'] ) as $chip ) : ?><?php if ( '' === trim( $chip ) ) { continue; } ?><span><span class="gms-homepage-inline-icon gms-homepage-inline-icon--check" aria-hidden="true"></span><?php echo esc_html( trim( $chip ) ); ?></span><?php endforeach; ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	private function render_standard_contact( array $settings, array $config ): void {
		?>
		<section class="gms-widget gms-contact-widget">
			<div class="gms-contact-widget__panel">
				<?php $this->render_section_heading( $settings ); ?>
				<div class="gms-contact-widget__services">
					<h3><?php esc_html_e( "What service you'll get", 'grow-my-security' ); ?></h3>
					<ul><?php $this->render_lines( $this->get_services( $settings ) ); ?></ul>
				</div>
				<div class="gms-contact-widget__details">
					<a class="gms-contact-detail-card gms-contact-detail-card--link" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) $settings['phone'] ) ); ?>"><strong><?php esc_html_e( 'Phone', 'grow-my-security' ); ?></strong><span><?php echo esc_html( $settings['phone'] ); ?></span></a>
					<a class="gms-contact-detail-card gms-contact-detail-card--link" href="mailto:<?php echo esc_attr( $settings['email'] ); ?>"><strong><?php esc_html_e( 'Email', 'grow-my-security' ); ?></strong><span><?php echo esc_html( $settings['email'] ); ?></span></a>
					<div class="gms-contact-detail-card"><strong><?php esc_html_e( 'Head Office', 'grow-my-security' ); ?></strong><span><?php echo esc_html( $settings['address'] ); ?></span></div>
					<div class="gms-contact-detail-card"><strong><?php esc_html_e( 'Working Hours', 'grow-my-security' ); ?></strong><span><?php echo esc_html( $settings['hours'] ); ?></span></div>
				</div>
			</div>
			<form class="gms-contact-widget__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="gms_contact_form">
				<input type="hidden" name="privacy_acceptance" value="1">
				<input type="hidden" name="bot_check" value="1">
				<?php wp_nonce_field( 'gms_contact_form', 'gms_contact_nonce' ); ?>
				<h3><?php esc_html_e( 'Start your project today - no obligation', 'grow-my-security' ); ?></h3>
				<label><span><?php esc_html_e( 'Full Name', 'grow-my-security' ); ?></span><input type="text" name="full_name" placeholder="Enter full name" autocomplete="name" required></label>
				<label><span><?php esc_html_e( 'Email Address', 'grow-my-security' ); ?></span><input type="email" name="email" placeholder="Enter email address" autocomplete="email" required></label>
				<label><span><?php esc_html_e( 'Phone', 'grow-my-security' ); ?></span><input type="text" name="phone" placeholder="+91 1234567890" autocomplete="tel" inputmode="tel"></label>
				<label><span><?php esc_html_e( "Services you're interested in", 'grow-my-security' ); ?></span><select name="service_interest"><?php foreach ( $config['services'] as $service ) : ?><option value="<?php echo esc_attr( $service['title'] ); ?>"><?php echo esc_html( $service['title'] ); ?></option><?php endforeach; ?></select></label>
				<label><span><?php esc_html_e( 'Project description', 'grow-my-security' ); ?></span><textarea name="message" placeholder="Tell us more about your project, timeline, budget, or other specific requirements." autocomplete="off"></textarea></label>
				<div class="gms-form-submit"><button type="submit"><?php echo esc_html( $settings['submit_text'] ?? 'Get My Free Quote' ); ?></button></div>
				<?php if ( ! empty( $settings['footer_chips'] ) ) : ?><div class="gms-contact-widget__chips"><?php foreach ( preg_split( '/\r\n|\r|\n/', $settings['footer_chips'] ) as $chip ) : ?><?php if ( '' === trim( $chip ) ) { continue; } ?><span><?php echo esc_html( trim( $chip ) ); ?></span><?php endforeach; ?></div><?php endif; ?>
			</form>
		</section>
		<?php
	}

	private function get_current_page_slug(): string {
		$current_post = get_post();

		if ( ! ( $current_post instanceof \WP_Post ) && class_exists( '\Elementor\Plugin' ) ) {
			$plugin = \Elementor\Plugin::$instance ?? null;

			if ( $plugin && isset( $plugin->editor ) && method_exists( $plugin->editor, 'get_post_id' ) ) {
				$editor_post_id = (int) $plugin->editor->get_post_id();

				if ( $editor_post_id > 0 ) {
					$current_post = get_post( $editor_post_id );
				}
			}
		}

		return $current_post instanceof \WP_Post ? (string) $current_post->post_name : '';
	}

	private function render_contact_page_layout( array $settings ): void {
		if ( ! \function_exists( 'gms_render_contact_page_layout' ) ) {
			$this->render_standard_contact( $settings, \gms_get_demo_config() );
			return;
		}

		\gms_render_contact_page_layout(
			[
				'eyebrow'       => (string) ( $settings['eyebrow'] ?? '' ),
				'title'         => (string) ( $settings['title'] ?? '' ),
				'description'   => (string) ( $settings['description'] ?? '' ),
				'submit_text'   => (string) ( $settings['submit_text'] ?? '' ),
				'email'         => (string) ( $settings['email'] ?? '' ),
				'response_note' => (string) ( $settings['response_note'] ?? '' ),
				'panel_image'   => $settings['panel_image'] ?? [],
				'email_heading' => (string) ( $settings['email_heading'] ?? '' ),
				'email_note'    => (string) ( $settings['email_note'] ?? '' ),
				'call_heading'  => (string) ( $settings['call_heading'] ?? '' ),
				'call_text'     => (string) ( $settings['call_text'] ?? '' ),
				'call_url'      => $settings['call_url'] ?? [],
			]
		);
	}

	protected function render() {
		$settings = $this->get_settings();
		$config   = \gms_get_demo_config();

		if ( \function_exists( 'gms_is_homepage_elementor_context' ) && \gms_is_homepage_elementor_context() ) {
			$this->render_home_quote( $settings, $config );
			return;
		}

		if ( 'contact-us' === $this->get_current_page_slug() ) {
			$this->render_contact_page_layout( $settings );
			return;
		}

		$this->render_standard_contact( $settings, $config );
	}
}
