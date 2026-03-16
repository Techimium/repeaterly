<?php
namespace Repeaterly\Includes\Widgets;

use Repeaterly\Includes\Dynamic_Content;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Accordion;

class Accordion extends Widget_Accordion {

	public function get_name() {
		return 'repeaterly-accordion';
	}

	public function get_title() {
		return esc_html__( 'Dynamic Accordion', 'repeaterly' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories()
    {
        return ['repeaterly'];
    }

	public function get_keywords() {
		return [ 'accordion', 'faq', 'dynamic' ];
	}

	protected function is_dynamic_content(): bool {
		return true;
	}

	/**
	 * Hide widget from panel.
	 *
	 * Hide the toggle widget from the panel if nested-accordion experiment is active.
	 *
	 * @since 3.15.0
	 * @return bool
	 */
	public function show_in_panel(): bool {
		return true;
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls() {
		parent::register_controls();
		$this->remove_control('tabs');
		$this->remove_control('deprecation_message');

		$this->start_injection( [
			'type' => 'section',
			'at' => 'start',
			'of' => 'section_title',
		] );

		$this->add_control('repeater_field', [
            'label' => __('Repeater Field Name', 'repeaterly'),
			'description' => Dynamic_Content::REPEATER_DESCRIPTION,
			'label_block' => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __('Enter the repeater field name', 'repeaterly'),
        ]);
		
		$this->add_control('title_sub_field', [
            'label' => __('Title Field Name', 'repeaterly'),
			'description' => Dynamic_Content::SUBFIELD_DESCRIPTION,
			'label_block' => true,
			'separator' => 'after',
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __('Enter the title field name', 'repeaterly'),
        ]);
		
		$this->add_control('content_sub_field', [
            'label' => __('Content Field Name', 'repeaterly'),
			'description' => Dynamic_Content::SUBFIELD_DESCRIPTION,
			'label_block' => true,
			'separator' => 'after',
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __('Enter the content field name', 'repeaterly'),
        ]);

		$this->end_injection();
	}

	public function get_script_depends() {
		return [ 'repeaterly-accordion' ];
	}

	public function get_settings_for_display( $setting_key = null ) {
		$settings = parent::get_settings_for_display( $setting_key );

		$settings['tabs'] = [];

		$list = Dynamic_Content::get_value(Dynamic_Content::SUB, $this->get_settings('repeater_field'));
		if(empty($list)){
			$list = Dynamic_Content::get_value(Dynamic_Content::CUSTOM, $this->get_settings('repeater_field'));
		}
		
		if(!empty($list)) {
			foreach($list as $item) {
				$settings['tabs'][] = [
					'tab_title' => $item[$this->get_settings('title_sub_field')],
					'tab_content' => $item[$this->get_settings('content_sub_field')],
				];
			}
		}

		return $settings;
	}

	protected function get_html_wrapper_class() {
		return 'elementor-widget-accordion';
	}

	/**
	 * Render accordion widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );

		if ( ! isset( $settings['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// @todo: remove when deprecated
			// added as bc in 2.6
			// add old default
			$settings['icon'] = 'fa fa-plus';
			$settings['icon_active'] = 'fa fa-minus';
			$settings['icon_align'] = $this->get_settings( 'icon_align' );
		}

		$is_new = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();
		$has_icon = ( ! $is_new || ! empty( $settings['selected_icon']['value'] ) );
		$id_int = substr( $this->get_id_int(), 0, 3 );
		?>
		<div class="elementor-accordion">
			<?php
			foreach ( $settings['tabs'] as $index => $item ) :
				$tab_count = $index + 1;

				$tab_title_setting_key = $this->get_repeater_setting_key( 'tab_title', 'tabs', $index );

				$tab_content_setting_key = $this->get_repeater_setting_key( 'tab_content', 'tabs', $index );

				$this->add_render_attribute( $tab_title_setting_key, [
					'id' => 'elementor-tab-title-' . $id_int . $tab_count,
					'class' => [ 'elementor-tab-title' ],
					'data-tab' => $tab_count,
					'role' => 'button',
					'aria-controls' => 'elementor-tab-content-' . $id_int . $tab_count,
					'aria-expanded' => 'false',
				] );

				$this->add_render_attribute( $tab_content_setting_key, [
					'id' => 'elementor-tab-content-' . $id_int . $tab_count,
					'class' => [ 'elementor-tab-content', 'elementor-clearfix' ],
					'data-tab' => $tab_count,
					'role' => 'region',
					'aria-labelledby' => 'elementor-tab-title-' . $id_int . $tab_count,
				] );

				$this->add_inline_editing_attributes( $tab_content_setting_key, 'advanced' );
				?>
				<div class="elementor-accordion-item">
					<<?php Utils::print_validated_html_tag( $settings['title_html_tag'] ); ?> <?php $this->print_render_attribute_string( $tab_title_setting_key ); ?>>
						<?php if ( $has_icon ) : ?>
							<span class="elementor-accordion-icon elementor-accordion-icon-<?php echo esc_attr( $settings['icon_align'] ); ?>" aria-hidden="true">
							<?php
							if ( $is_new || $migrated ) { ?>
								<span class="elementor-accordion-icon-closed"><?php Icons_Manager::render_icon( $settings['selected_icon'] ); ?></span>
								<span class="elementor-accordion-icon-opened"><?php Icons_Manager::render_icon( $settings['selected_active_icon'] ); ?></span>
							<?php } else { ?>
								<i class="elementor-accordion-icon-closed <?php echo esc_attr( $settings['icon'] ); ?>"></i>
								<i class="elementor-accordion-icon-opened <?php echo esc_attr( $settings['icon_active'] ); ?>"></i>
							<?php } ?>
							</span>
						<?php endif; ?>
						<a class="elementor-accordion-title" tabindex="0">
							<?php echo esc_html($item['tab_title']); ?>
						</a>
					</<?php Utils::print_validated_html_tag( $settings['title_html_tag'] ); ?>>
					<div <?php $this->print_render_attribute_string( $tab_content_setting_key ); ?>><?php
						$this->print_text_editor( $item['tab_content'] );
					?></div>
				</div>
			<?php endforeach; ?>
			<?php
			if ( isset( $settings['faq_schema'] ) && 'yes' === $settings['faq_schema'] ) {
				$json = [
					'@context' => 'https://schema.org',
					'@type' => 'FAQPage',
					'mainEntity' => [],
				];

				foreach ( $settings['tabs'] as $index => $item ) {
					$json['mainEntity'][] = [
						'@type' => 'Question',
						'name' => wp_strip_all_tags( $item['tab_title'] ),
						'acceptedAnswer' => [
							'@type' => 'Answer',
							'text' => $this->parse_text_editor( $item['tab_content'] ),
						],
					];
				}
				?>
				<script type="application/ld+json"><?php echo wp_json_encode( $json ); ?></script>
			<?php } ?>
		</div>
		<?php
	}

	protected function content_template()
    {
        return false;
    }
}
