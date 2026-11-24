<?php
namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Acf;
use Repeaterly\Includes\Traits\Has_ACF_Taxonomy_Term_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ACF_Taxonomy_Image_Field extends Data_Tag {
	use Has_ACF_Taxonomy_Term_Source;

	public function get_name() {
		return 'repeaterly-acf-taxonomy-image-field';
	}

	public function get_title() {
		return esc_html__( 'ACF Taxonomy Image Field', 'repeaterly' );
	}

	public function get_group() {
		return 'repeaterly';
	}

	public function get_categories() {
		return [ Module::IMAGE_CATEGORY ];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	protected function register_controls() {
		$this->register_acf_taxonomy_controls( true );

		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'repeaterly' ),
				'type' => Controls_Manager::MEDIA,
			]
		);
	}

	public function get_value( array $options = [] ) {
		$source = $this->get_term_field_source();
		$value = $source ? Acf::get_field_value( $source[0], $source[1] ) : '';

		if ( empty( $value ) ) {
			$value = $this->get_settings( 'fallback' );
		}

		$image_data = [
			'id' => null,
			'url' => '',
		];

		if ( ! empty( $value ) && is_array( $value ) ) {
			$image_data['id'] = isset( $value['id'] ) && is_numeric( $value['id'] ) ? $value['id'] : null;
			$image_data['url'] = isset( $value['url'] ) && is_string( $value['url'] ) ? $value['url'] : '';
		}

		return $image_data;
	}
}
