<?php
namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Acf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ACF_Gallery extends Data_Tag {

	public function get_name() {
		return 'repeaterly-acf-gallery';
	}

	public function get_title()
	{
		return esc_html__('ACF Gallery Field', 'repeaterly');
	}

	public function get_categories() {
		return [ Module::GALLERY_CATEGORY ];
	}

	public function get_group()
	{
		return 'repeaterly';
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	public function get_value( array $options = [] ) {
		$images = [];

		$value = Acf::get_field_value($this->get_settings('key'));

		if ( is_array( $value ) && ! empty( $value ) ) {
			foreach ( $value as $image ) {
				$images[] = [
					'id' => $image['ID'],
				];
			}
		}

		return $images;
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
	}

	public function get_supported_fields() {
		return [
			'gallery',
		];
	}
}
