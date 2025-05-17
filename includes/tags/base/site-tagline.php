<?php
namespace Repeaterly\Includes\Tags\Base;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Site_Tagline extends Tag {
	public function get_name() {
		return 'repeaterly-site-tagline';
	}

	public function get_title() {
		return esc_html__( 'Site Tagline', 'repeaterly' );
	}

	public function get_group() {
		return 'repeaterly';
	}

	public function get_categories() {
		return [ Module::TEXT_CATEGORY ];
	}

	public function render() {
		echo wp_kses_post( get_bloginfo( 'description' ) );
	}
}
