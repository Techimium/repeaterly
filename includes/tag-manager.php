<?php

namespace Repeaterly\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Repeaterly\Includes\Tags\Acf\ACF_Color;
use Repeaterly\Includes\Tags\Acf\ACF_Date_Time;
use Repeaterly\Includes\Tags\Acf\ACF_Field;
use Repeaterly\Includes\Tags\Acf\ACF_Gallery;
use Repeaterly\Includes\Tags\Acf\ACF_Image;
use Repeaterly\Includes\Tags\Acf\ACF_Number;
use Repeaterly\Includes\Tags\Acf\ACF_URL;
use Repeaterly\Includes\Tags\Base\Post_Featured_Image;
use Repeaterly\Includes\Tags\Base\Post_Content;
use Repeaterly\Includes\Tags\Base\Post_Custom_Field;
use Repeaterly\Includes\Tags\Base\Post_Date;
use Repeaterly\Includes\Tags\Base\Post_Excerpt;
use Repeaterly\Includes\Tags\Base\Post_Url;
use Repeaterly\Includes\Tags\Base\Post_Title;
use Repeaterly\Includes\Tags\Base\Site_Logo;
use Repeaterly\Includes\Tags\Base\Site_Tagline;
use Repeaterly\Includes\Tags\Base\Site_Title;
use Repeaterly\Includes\Tags\Base\Site_URL;

class Tag_Manager
{

    private static $instance;

    public function __construct()
    {
        add_action('elementor/dynamic_tags/register', [$this, 'register_tags']);
    }

    public static function init()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function register_tags($tags_manager)
    {
        $this->register_group($tags_manager);

        $tags_manager->register(new Post_Title());
        $tags_manager->register(new Post_Content());
        $tags_manager->register(new Post_Excerpt());
        $tags_manager->register(new Post_Date());
        $tags_manager->register(new Post_Custom_Field());
        $tags_manager->register(new Post_Url());
        $tags_manager->register(new Post_Featured_Image());

        $tags_manager->register(new Site_Logo());
        $tags_manager->register(new Site_Tagline());
        $tags_manager->register(new Site_Title());
        $tags_manager->register(new Site_URL());

        $tags_manager->register(new ACF_Field());
        $tags_manager->register(new ACF_Color());
        $tags_manager->register(new ACF_Date_Time());
        $tags_manager->register(new ACF_Gallery());
        $tags_manager->register(new ACF_Image());
        $tags_manager->register(new ACF_Number());
        $tags_manager->register(new ACF_URL());
    }

    public function register_group($tags_manager)
    {
        $tags_manager->register_group(
            'repeaterly',
            [
                'title' => esc_html__('Repeaterly', 'repeaterly')
            ]
        );
    }
}
