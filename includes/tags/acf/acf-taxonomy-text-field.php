<?php
namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Acf;
use Repeaterly\Includes\Traits\Has_ACF_Taxonomy_Term_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ACF_Taxonomy_Text_Field extends Tag {
	use Has_ACF_Taxonomy_Term_Source;

	public function get_name() {
		return 'repeaterly-acf-taxonomy-text-field';
	}

	public function get_title() {
		return esc_html__( 'ACF Taxonomy Text Field', 'repeaterly' );
	}

	public function get_group() {
		return 'repeaterly';
	}

	public function get_categories() {
		return [ Module::TEXT_CATEGORY ];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	protected function register_controls() {
		$this->register_acf_taxonomy_controls(true);

		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'repeaterly' ),
			]
		);
	}

	public function render() {
		$source = $this->get_term_field_source();
		$value = $source ? Acf::get_field_text( $source[0], $source[1] ) : '';

		if ( '' === $value ) {
			$value = $this->get_settings( 'fallback' );
		}

		echo wp_kses_post( is_string( $value ) ? $value : '' );
	}
}
