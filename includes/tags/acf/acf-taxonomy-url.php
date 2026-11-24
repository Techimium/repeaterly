<?php
namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Traits\Has_ACF_Taxonomy_Term_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ACF_Taxonomy_URL extends Data_Tag {
	use Has_ACF_Taxonomy_Term_Source;

	public function get_name() {
		return 'repeaterly-acf-taxonomy-url';
	}

	public function get_title() {
		return esc_html__( 'ACF Taxonomy URL', 'repeaterly' );
	}

	public function get_group() {
		return 'repeaterly';
	}

	public function get_categories() {
		return [ Module::URL_CATEGORY ];
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


	public function get_value( array $options = [] ) {
		$term = $this->get_selected_term();
		$link = $term ? get_term_link( $term ) : '';
		$value = is_string( $link ) ? $link : '';

		if ( '' === $value ) {
			$value = $this->get_settings( 'fallback' );
		}

		return esc_url( is_string( $value ) ? $value : '' );
	}
}
