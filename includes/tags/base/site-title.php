<?php
namespace Repeaterly\Includes\Tags\Base;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Site_Title extends Tag {
	public function get_name() {
		return 'repeaterly-site-title';
	}

	public function get_title() {
		return esc_html__( 'Site Title', 'repeaterly' );
	}

	public function get_group() {
		return 'repeaterly';
	}

	public function get_categories() {
		return [ Module::TEXT_CATEGORY ];
	}

	public function render() {
		echo wp_kses_post( get_bloginfo() );
	}
}
