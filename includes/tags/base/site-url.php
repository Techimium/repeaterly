<?php
namespace Repeaterly\Includes\Tags\Base;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Site_URL extends Data_Tag {

	public function get_name() {
		return 'repeaterly-site-url';
	}

	public function get_title() {
		return esc_html__( 'Site URL', 'repeaterly' );
	}

	public function get_group() {
		return 'repeaterly';
	}

	public function get_categories() {
		return [ Module::URL_CATEGORY ];
	}

	public function get_value( array $options = [] ) {
		return home_url();
	}
}

