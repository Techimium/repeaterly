<?php
namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Traits\Has_ACF_Taxonomy_Term_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ACF_Taxonomy_Name extends Tag {
	use Has_ACF_Taxonomy_Term_Source;

	public function get_name() {
		return 'repeaterly-acf-taxonomy-name';
	}

	public function get_title() {
		return esc_html__( 'ACF Taxonomy Name', 'repeaterly' );
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
		$this->register_acf_taxonomy_controls(false);

		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'repeaterly' ),
			]
		);
	}

	public function render() {
		$term = $this->get_selected_term();
		$value = $term ? $term->name : $this->get_settings( 'fallback' );

		echo esc_html( is_string( $value ) ? $value : '' );
	}
}
