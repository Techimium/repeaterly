<?php
namespace Repeaterly\Includes\Tags\Base;

use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Post_Featured_Image extends Data_Tag {

	public function get_name() {
		return 'repeaterly-post-featured-image';
	}

	public function get_group() {
		return 'repeaterly';
	}

	public function get_categories() {
		return [
			Module::IMAGE_CATEGORY,
			Module::MEDIA_CATEGORY,
		];
	}

	public function get_title() {
		return esc_html__( 'Featured Image', 'repeaterly' );
	}

	public function get_value( array $options = [] ) {
		$thumbnail_id = get_post_thumbnail_id();

		if ( $thumbnail_id ) {
			$image_data = [
				'id' => $thumbnail_id,
				'url' => wp_get_attachment_image_src( $thumbnail_id, 'full' )[0],
			];
		} else {
			$image_data = $this->get_settings( 'fallback' );
		}

		return $image_data;
	}

	protected function register_controls() {
		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'repeaterly' ),
				'type' => Controls_Manager::MEDIA,
			]
		);
	}
}
