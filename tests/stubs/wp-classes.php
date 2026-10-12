<?php

/**
 * Minimal stand-ins for the WordPress core classes the ACF value code type-checks against. Only
 * the properties that code reads; loaded when no real WordPress is present.
 */

if (!class_exists('WP_Post')) {
    class WP_Post
    {
        public $ID;
        public $post_title = '';
        public $post_type = 'post';

        public function __construct($ID, $post_title = '')
        {
            $this->ID = $ID;
            $this->post_title = $post_title;
        }
    }
}

if (!class_exists('WP_Term')) {
    class WP_Term
    {
        public $term_id;
        public $taxonomy;

        public function __construct($term_id, $taxonomy = 'category')
        {
            $this->term_id = $term_id;
            $this->taxonomy = $taxonomy;
        }
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error
    {
    }
}
