<?php
namespace Repeaterly\Includes\Tags\Acf;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Repeaterly\Includes\Traits\Has_ACF_Taxonomy_Term_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ACF_Taxonomy_Names extends Tag {
	use Has_ACF_Taxonomy_Term_Source;

	public function get_name() {
		return 'repeaterly-acf-taxonomy-names';
	}

	public function get_title() {
		return esc_html__( 'ACF Taxonomy Names', 'repeaterly' );
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
			'separator',
			[
				'label' => esc_html__( 'Separator', 'repeaterly' ),
				'type' => Controls_Manager::TEXT,
				'default' => ', ',
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'repeaterly' ),
			]
		);
	}

	public function render() {
		$names = array_map(
			static function ( $term ) {
				return esc_html( $term->name );
			},
			$this->get_selected_terms()
		);
		$separator = $this->get_settings( 'separator' );

		if ( $names ) {
			echo implode( esc_html( is_string( $separator ) ? $separator : ', ' ), $names ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- names and separator are escaped above.
			return;
		}

		$fallback = $this->get_settings( 'fallback' );

		echo esc_html( is_string( $fallback ) ? $fallback : '' );
	}
}
