<?php
namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Acf;
use Repeaterly\Includes\Traits\Has_ACF_Options_Page_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ACF_Date_Time extends Tag {
	use Has_ACF_Options_Page_Source;

	public function get_name() {
		return 'repeaterly-acf-date-time';
	}

	public function get_title()
	{
		return __('ACF Date Time Field', 'repeaterly');
	}

	public function get_group()
	{
		return 'repeaterly';
	}

	public function get_categories() {
		return [
			Module::DATETIME_CATEGORY,
			Module::TEXT_CATEGORY
		];
	}

	public function render() {
		$value = Acf::get_field_value($this->get_settings( 'key' ), $this->resolve_acf_post_id($this->get_settings( 'key' )));

		echo wp_kses_post( $value );
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	protected function register_controls()
	{
		$this->add_control(
			'key',
			[
				'label' => esc_html__('Field Name (Meta Key)', 'repeaterly'),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__('Enter ACF field key/name (e.g. my_custom_field)', 'repeaterly'),
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->register_acf_options_page_controls();
	}

	public function get_supported_fields() {
		return [
			'date_time_picker',
		];
	}
}
