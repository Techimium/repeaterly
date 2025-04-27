<?php
namespace Repeaterly\Includes\Widgets;

use Repeaterly\Includes\Dynamic_Content;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Widget_Icon_List;

class Icon_List extends Widget_Icon_List {

	public function get_name() {
		return 'repeaterly-icon-list';
	}

	public function get_title() {
		return esc_html__( 'ACF Repeater List', 'repeaterly' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories()
    {
        return ['repeaterly'];
    }

	public function get_keywords() {
		return [ 'icon list', 'icon', 'list', 'dynamic' ];
	}

	protected function is_dynamic_content(): bool {
		return true;
	}

	protected function register_controls() {
		parent::register_controls();
		$this->remove_control('icon_list');

		$this->start_injection( [
			'type' => 'section',
			'at' => 'start',
			'of' => 'section_icon',
		] );

		$this->add_control('repeater_field', [
            'label' => __('Repeater Field Name', 'repeaterly'),
			'description' => Dynamic_Content::REPEATER_DESCRIPTION,
			'label_block' => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __('Enter the repeater field name', 'repeaterly'),
        ]);
		
		$this->add_control('sub_field', [
            'label' => __('Sub Field Name', 'repeaterly'),
			'description' => Dynamic_Content::SUBFIELD_DESCRIPTION,
			'label_block' => true,
			'separator' => 'after',
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __('Enter the sub field name', 'repeaterly'),
        ]);

		$this->add_control(
			'selected_icon',
			[
				'label' => esc_html__( 'Icon', 'repeaterly' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-check',
					'library' => 'fa-solid',
				],
				'fa4compatibility' => 'icon',
			]
		);

		$this->end_injection();
	}

	public function get_settings_for_display( $setting_key = null ) {
		$settings = parent::get_settings_for_display( $setting_key );

		$settings['icon_list'] = [];

		$list = Dynamic_Content::get_value(Dynamic_Content::SUB, $this->get_settings('repeater_field'));
		if(empty($list)){
			$list = Dynamic_Content::get_value(Dynamic_Content::CUSTOM, $this->get_settings('repeater_field'));
		}

		$icon = $this->get_settings('selected_icon');
		
		if(!empty($list)) {
			foreach($list as $item) {
				$settings['icon_list'][] = [
					'text' => $item[$this->get_settings('sub_field')],
					'selected_icon' => $icon,
				];
			}
		}

		return $settings;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$fallback_defaults = [
			'fa fa-check',
			'fa fa-times',
			'fa fa-dot-circle-o',
		];

		$this->add_render_attribute( 'icon_list', 'class', 'elementor-icon-list-items' );
		$this->add_render_attribute( 'list_item', 'class', 'elementor-icon-list-item' );

		if ( 'inline' === $settings['view'] ) {
			$this->add_render_attribute( 'icon_list', 'class', 'elementor-inline-items' );
			$this->add_render_attribute( 'list_item', 'class', 'elementor-inline-item' );
		}
		?>
		<ul <?php $this->print_render_attribute_string( 'icon_list' ); ?>>
			<?php
			foreach ( $settings['icon_list'] as $index => $item ) :
				$repeater_setting_key = $this->get_repeater_setting_key( 'text', 'icon_list', $index );

				$this->add_render_attribute( $repeater_setting_key, 'class', 'elementor-icon-list-text' );

				$this->add_inline_editing_attributes( $repeater_setting_key );
				$migration_allowed = Icons_Manager::is_migration_allowed();
				?>
				<li <?php $this->print_render_attribute_string( 'list_item' ); ?>>
					<?php
					if ( ! empty( $item['link']['url'] ) ) {
						$link_key = 'link_' . $index;

						$this->add_link_attributes( $link_key, $item['link'] );
						?>
						<a <?php $this->print_render_attribute_string( $link_key ); ?>>

						<?php
					}

					// add old default
					if ( ! isset( $item['icon'] ) && ! $migration_allowed ) {
						$item['icon'] = isset( $fallback_defaults[ $index ] ) ? $fallback_defaults[ $index ] : 'fa fa-check';
					}

					$migrated = isset( $item['__fa4_migrated']['selected_icon'] );
					$is_new = ! isset( $item['icon'] ) && $migration_allowed;
					if ( ! empty( $item['icon'] ) || ( ! empty( $item['selected_icon']['value'] ) && $is_new ) ) :
						?>
						<span class="elementor-icon-list-icon">
							<?php
							if ( $is_new || $migrated ) {
								Icons_Manager::render_icon( $item['selected_icon'], [ 'aria-hidden' => 'true' ] );
							} else { ?>
									<i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
							<?php } ?>
						</span>
					<?php endif; ?>
					<span <?php $this->print_render_attribute_string( $repeater_setting_key ); ?>><?php echo esc_html($item['text']); ?></span>
					<?php if ( ! empty( $item['link']['url'] ) ) : ?>
						</a>
					<?php endif; ?>
				</li>
				<?php
			endforeach;
			?>
		</ul>
		<?php
	}

	protected function content_template()
    {
        return false;
    }
}
