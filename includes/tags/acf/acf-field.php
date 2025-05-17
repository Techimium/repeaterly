<?php

namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Acf;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class ACF_Field extends Tag
{

	public function get_name()
	{
		return 'repeaterly-acf-field';
	}

	public function get_title()
	{
		return esc_html__('ACF Field', 'repeaterly');
	}

	public function get_group()
	{
		return 'repeaterly';
	}

	public function get_categories()
	{
		return [
			Module::TEXT_CATEGORY,
			Module::POST_META_CATEGORY,
		];
	}

	protected function register_controls()
	{
		$this->add_control(
			'key',
			[
				'label' => esc_html__('Field Name (Meta Key)', 'repeaterly'),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__('Enter ACF field key/name (e.g. my_custom_field)', 'repeaterly'),
				'description' => esc_html__( 'Supported ACF fields: text, textarea, number, email, password, WYSIWYG, select, checkbox, radio, true/false, oEmbed, Google Map, date picker, time picker, date/time picker, color picker.', 'repeaterly' ),
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	public function render()
	{
		$key = $this->get_settings('key');

		if (empty($key)) {
			return;
		}

		$value = Acf::get_field_value($key);

		if (is_string($value) || is_numeric($value)) {
			echo wp_kses_post($value);
		}
	}
}
