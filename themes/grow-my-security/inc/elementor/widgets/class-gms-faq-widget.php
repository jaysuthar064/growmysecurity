<?php
/**
 * FAQ widget.
 *
 * @package GrowMySecurity
 */

namespace GMS\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Faq_Widget extends GMS_Widget_Base {
	public function get_name() {
		return 'gms-faq';
	}

	public function get_title() {
		return __( 'GMS FAQ', 'grow-my-security' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	protected function register_controls() {
		$config = \gms_get_demo_config();
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'FAQ Content', 'grow-my-security' ),
			]
		);
		$this->add_control(
			'layout',
			[
				'label'   => __( 'Layout', 'grow-my-security' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'stacked',
				'options' => [
					'stacked' => __( 'Stacked', 'grow-my-security' ),
					'split'   => __( 'Split', 'grow-my-security' ),
				],
			]
		);
		$this->add_section_heading_controls(
			[
				'eyebrow'     => 'FAQ',
				'title'       => 'Read Frequently Asked Question',
				'description' => 'Straight answers about how we are helping brands find their voice and connect with their audience in meaningful ways.',
			]
		);
		$this->add_control( 'primary_button_text', [ 'label' => __( 'Primary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$this->add_control( 'primary_button_url', [ 'label' => __( 'Primary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$this->add_control( 'secondary_button_text', [ 'label' => __( 'Secondary Button Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$this->add_control( 'secondary_button_url', [ 'label' => __( 'Secondary Button URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );
		$this->add_control( 'footer_text', [ 'label' => __( 'Footer Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$this->add_control( 'footer_link_text', [ 'label' => __( 'Footer Link Text', 'grow-my-security' ), 'type' => Controls_Manager::TEXT ] );
		$this->add_control( 'footer_link_url', [ 'label' => __( 'Footer Link URL', 'grow-my-security' ), 'type' => Controls_Manager::URL ] );

		$repeater = new Repeater();
		$repeater->add_control( 'question', [ 'label' => __( 'Question', 'grow-my-security' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ] );
		$repeater->add_control( 'answer', [ 'label' => __( 'Answer', 'grow-my-security' ), 'type' => Controls_Manager::TEXTAREA ] );
		$this->add_control(
			'items',
			[
				'label'       => __( 'Questions', 'grow-my-security' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $config['faqs'],
				'title_field' => '{{{ question }}}',
			]
		);
		$this->end_controls_section();

		$this->add_widget_style_controls( 'faq_section_style', '{{WRAPPER}} .gms-widget' );
		$this->add_box_style_controls( 'faq_box', '{{WRAPPER}} .gms-faq-item' );
	}

	protected function render_split_layout( array $settings, array $items ): void {
		?>
		<section class="gms-widget gms-homepage-section gms-homepage-section--faq">
			<div class="gms-homepage-shell">
				<div class="gms-homepage-faq">
					<div class="gms-homepage-faq__intro">
						<div class="gms-homepage-faq__intro-card">
							<div class="gms-homepage-chip"><span class="gms-homepage-chip__icon gms-homepage-chip__icon--faq" aria-hidden="true"></span><span><?php echo esc_html( $settings['eyebrow'] ?? '' ); ?></span></div>
							<h2><?php echo wp_kses_post( nl2br( esc_html( $settings['title'] ?? '' ) ) ); ?></h2>
							<div class="gms-homepage-faq__desc">
								<?php if ( ! empty( $settings['description'] ) ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
							</div>
							<div class="gms-homepage-faq__actions">
								<?php if ( ! empty( $settings['primary_button_text'] ) && $this->has_valid_link( $settings['primary_button_url'] ?? [] ) ) : ?>
									<?php $this->add_render_attribute( 'faq-primary-' . $this->get_id(), 'class', 'gms-homepage-button gms-homepage-button--primary gms-homepage-button--faq-cta' ); ?>
									<?php $this->add_link_attributes( 'faq-primary-' . $this->get_id(), $settings['primary_button_url'] ?? [] ); ?>
									<a <?php echo $this->get_render_attribute_string( 'faq-primary-' . $this->get_id() ); ?>><span><?php echo esc_html( $settings['primary_button_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
								<?php endif; ?>
								<?php if ( ! empty( $settings['secondary_button_text'] ) && $this->has_valid_link( $settings['secondary_button_url'] ?? [] ) ) : ?>
									<?php $this->add_render_attribute( 'faq-secondary-' . $this->get_id(), 'class', 'gms-homepage-button gms-homepage-button--secondary' ); ?>
									<?php $this->add_link_attributes( 'faq-secondary-' . $this->get_id(), $settings['secondary_button_url'] ?? [] ); ?>
									<a <?php echo $this->get_render_attribute_string( 'faq-secondary-' . $this->get_id() ); ?>><span><?php echo esc_html( $settings['secondary_button_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="gms-homepage-faq__accordion" data-faq-accordion>
						<?php foreach ( $items as $index => $item ) : ?>
							<?php $is_open = 0 === $index; ?>
							<article class="gms-homepage-faq__item<?php echo $is_open ? ' is-open' : ''; ?>">
								<h3><button class="gms-homepage-faq__question" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" data-faq-trigger><span><?php echo esc_html( $item['question'] ?? '' ); ?></span><span class="gms-homepage-faq__icon" aria-hidden="true"></span></button></h3>
								<div class="gms-homepage-faq__answer"<?php echo $is_open ? '' : ' hidden'; ?> data-faq-panel><p><?php echo wp_kses_post( $item['answer'] ?? '' ); ?></p></div>
							</article>
						<?php endforeach; ?>
						<?php if ( ! empty( $settings['footer_text'] ) || ! empty( $settings['footer_link_text'] ) ) : ?>
							<div class="gms-homepage-faq__prompt">
								<p><?php echo esc_html( $settings['footer_text'] ?? '' ); ?></p>
								<?php if ( ! empty( $settings['footer_link_text'] ) && $this->has_valid_link( $settings['footer_link_url'] ?? [] ) ) : ?>
									<?php $this->add_render_attribute( 'faq-footer-' . $this->get_id(), 'class', '' ); ?>
									<?php $this->add_link_attributes( 'faq-footer-' . $this->get_id(), $settings['footer_link_url'] ?? [] ); ?>
									<a <?php echo $this->get_render_attribute_string( 'faq-footer-' . $this->get_id() ); ?>><span><?php echo esc_html( $settings['footer_link_text'] ); ?></span><span class="gms-homepage-button__arrow" aria-hidden="true"></span></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	protected function render_stacked_layout( array $settings, array $items ): void {
		$current_post_id = get_queried_object_id();

		if ( ! $current_post_id && isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof \WP_Post ) {
			$current_post_id = (int) $GLOBALS['post']->ID;
		}

		$request_path = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		}

		$is_faq_page = ( $current_post_id > 0 && 'faq' === get_post_field( 'post_name', $current_post_id ) ) || '/faq' === untrailingslashit( $request_path );
		$search_id   = 'gms-faq-search-' . $this->get_id();
		?>
		<section class="gms-widget gms-faq-stacked"<?php echo $is_faq_page ? ' data-faq-search-shell' : ''; ?>>
			<?php $this->render_section_heading( $settings ); ?>
			<?php if ( $is_faq_page ) : ?>
				<div class="gms-approved-faq-search">
					<label class="gms-approved-faq-search__label" for="<?php echo esc_attr( $search_id ); ?>"><?php esc_html_e( 'Search FAQs', 'grow-my-security' ); ?></label>
					<input id="<?php echo esc_attr( $search_id ); ?>" class="gms-approved-faq-search__input" type="search" placeholder="<?php esc_attr_e( 'Type a keyword or question', 'grow-my-security' ); ?>" autocomplete="off" data-faq-search-input>
				</div>
			<?php endif; ?>
			<div class="gms-faq-list" data-faq-accordion<?php echo $is_faq_page ? ' data-faq-search-list' : ''; ?>>
				<?php foreach ( $items as $index => $item ) : ?>
					<?php $is_open = 0 === $index; ?>
					<div class="gms-faq-item<?php echo $is_open ? ' is-open' : ''; ?>">
						<h3><button class="gms-faq-question" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" data-faq-trigger><span><?php echo esc_html( $item['question'] ?? '' ); ?></span><span class="gms-homepage-faq__icon" aria-hidden="true"></span></button></h3>
						<div class="gms-faq-answer"<?php echo $is_open ? '' : ' hidden'; ?> data-faq-panel><p><?php echo wp_kses_post( $item['answer'] ?? '' ); ?></p></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( $is_faq_page ) : ?>
				<p class="gms-approved-faq-empty" hidden data-faq-search-empty aria-live="polite"><?php esc_html_e( 'No FAQs matched your search.', 'grow-my-security' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings();
		$items    = $settings['items'] ?? [];

		if ( empty( $items ) ) {
			return;
		}

		if ( 'split' === ( $settings['layout'] ?? 'stacked' ) ) {
			$this->render_split_layout( $settings, $items );
		} else {
			$this->render_stacked_layout( $settings, $items );
		}
		?>
		<script>
		(function() {
			const initAccordion = (container) => {
				const items = container.querySelectorAll('.gms-homepage-faq__item, .gms-faq-item');
				items.forEach(item => {
					const trigger = item.querySelector('[data-faq-trigger]');
					const panel = item.querySelector('[data-faq-panel]');
					if (!trigger || !panel) return;

					trigger.addEventListener('click', () => {
						const isOpen = item.classList.contains('is-open');
						
						// Close all others in this accordion
						const siblingItems = container.querySelectorAll('.gms-homepage-faq__item, .gms-faq-item');
						siblingItems.forEach(sibling => {
							if (sibling !== item) {
								sibling.classList.remove('is-open');
								const sTrigger = sibling.querySelector('[data-faq-trigger]');
								const sPanel = sibling.querySelector('[data-faq-panel]');
								if (sTrigger) sTrigger.setAttribute('aria-expanded', 'false');
								if (sPanel) sPanel.setAttribute('hidden', '');
							}
						});

						// Toggle current
						if (isOpen) {
							item.classList.remove('is-open');
							trigger.setAttribute('aria-expanded', 'false');
							panel.setAttribute('hidden', '');
						} else {
							item.classList.add('is-open');
							trigger.setAttribute('aria-expanded', 'true');
							panel.removeAttribute('hidden');
						}
					});
				});
			};

			const container = document.querySelector('[data-faq-accordion]');
			if (container) {
				initAccordion(container);
			}

			// Elementor support
			if (window.elementorFrontend) {
				elementorFrontend.hooks.addAction('frontend/element_ready/gms-faq.default', function($scope) {
					const accordion = $scope[0].querySelector('[data-faq-accordion]');
					if (accordion) initAccordion(accordion);
				});
			}
		})();
		</script>
		<?php
	}
}
