<?php

namespace Repeaterly\Includes\Tags\Base;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Post_Content extends Tag
{

	public function get_name()
	{
		return 'repeaterly-post-content';
	}

	public function get_title()
	{
		return __('Post Content', 'repeaterly');
	}

	public function get_group()
	{
		return 'repeaterly';
	}

	public function get_categories()
	{
		return [Module::TEXT_CATEGORY];
	}

	public function render()
	{
		$content = !empty(get_the_content()) ? get_the_content() : $this->get_settings( 'fallback' );
		
		echo apply_filters( 'the_content', $content );
	}
}
