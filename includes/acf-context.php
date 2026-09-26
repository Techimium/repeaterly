<?php

namespace Repeaterly\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Decides which post ID Repeaterly passes to ACF.
 *
 * Top-level fields get an explicit post ID, because ACF's implicit "current post" can be a
 * preview revision while Elementor renders the parent. Sub-fields of the current repeater row
 * keep false, so ACF reads them from that row.
 */
class Acf_Context
{
    /**
     * Turn a post, revision or autosave into its parent post ID.
     *
     * @param mixed $post_id Post ID, WP_Post, or a non-numeric ACF ID ('option', 'term_5', ...).
     * @return mixed Parent post ID; false for 0; a non-numeric ID unchanged.
     */
    public static function normalize_post_id($post_id)
    {
        if (is_object($post_id) && isset($post_id->post_type, $post_id->ID)) {
            $post_id = $post_id->ID;
        }

        if (!is_numeric($post_id)) {
            return $post_id;
        }

        $post_id = (int) $post_id;

        if (!$post_id) {
            return false;
        }

        $parent_id = function_exists('wp_is_post_autosave') ? wp_is_post_autosave($post_id) : false;

        if (!$parent_id && function_exists('wp_is_post_revision')) {
            $parent_id = wp_is_post_revision($post_id);
        }

        return $parent_id ? (int) $parent_id : $post_id;
    }

    /**
     * Get the post being rendered (the global post, or the loop's current post).
     *
     * @return int|false
     */
    public static function get_render_post_id()
    {
        global $post;

        if (is_object($post) && isset($post->ID)) {
            $post_id = self::normalize_post_id($post->ID);

            if ($post_id) {
                return (int) $post_id;
            }
        }

        if (function_exists('get_the_ID')) {
            $post_id = self::normalize_post_id(get_the_ID());

            if ($post_id) {
                return (int) $post_id;
            }
        }

        return false;
    }

    /**
     * Get the post of the main query, when the main query is a single post.
     *
     * @return int|false
     */
    public static function get_main_queried_post_id()
    {
        if (function_exists('get_queried_object')) {
            $queried_object = get_queried_object();

            if (is_object($queried_object) && isset($queried_object->post_type, $queried_object->ID)) {
                $post_id = self::normalize_post_id($queried_object->ID);

                return $post_id ? (int) $post_id : false;
            }
        }

        return false;
    }

    /**
     * Get the post ID to read from: the given one, or the current post when none is given.
     *
     * @param mixed $post_id Post, term or options ID; empty for the current post.
     * @return mixed Post ID, the given non-numeric ID, or false when there is no current post.
     */
    public static function resolve_post_id($post_id = false)
    {
        if (!self::is_empty_post_id($post_id)) {
            return self::normalize_post_id($post_id);
        }

        $render_post_id = self::get_render_post_id();

        return $render_post_id ? $render_post_id : self::get_main_queried_post_id();
    }

    /**
     * Get the post ID to read a field from.
     *
     * @param string $field_name Field name.
     * @param mixed  $post_id    Post, term or options ID; empty for the current post.
     * @return mixed false when the field is a sub-field of the current repeater row, or when
     *               there is no current post; otherwise the post ID to read from.
     */
    public static function resolve_field_post_id($field_name, $post_id = false)
    {
        if (!self::is_empty_post_id($post_id)) {
            return self::normalize_post_id($post_id);
        }

        if (self::is_sub_field_of_current_row($field_name)) {
            return false;
        }

        return self::resolve_post_id(false);
    }

    /**
     * Run an ACF call for this post ID without ACF swapping it for its preview revision.
     *
     * @param mixed    $post_id  Post ID the callback reads from.
     * @param callable $callback The ACF call.
     * @return mixed The callback's return value.
     */
    public static function with_acf_post_id($post_id, callable $callback)
    {
        $post_id = self::normalize_post_id($post_id);

        if (!is_numeric($post_id) || !function_exists('add_filter') || !function_exists('remove_filter')) {
            return $callback();
        }

        $target_post_id = (int) $post_id;
        $preload = static function ($preloaded_post_id, $requested_post_id) use ($target_post_id) {
            if (null !== $preloaded_post_id) {
                return $preloaded_post_id;
            }

            if (is_numeric($requested_post_id) && (int) $requested_post_id === $target_post_id) {
                return $target_post_id;
            }

            return null;
        };

        add_filter('acf/pre_load_post_id', $preload, PHP_INT_MAX, 2);

        try {
            return $callback();
        } finally {
            remove_filter('acf/pre_load_post_id', $preload, PHP_INT_MAX);
        }
    }

    /**
     * Check whether a post ID is empty (false, null, '', 0 or '0'), meaning "the current post".
     *
     * @param mixed $post_id
     * @return bool
     */
    public static function is_empty_post_id($post_id)
    {
        return false === $post_id || null === $post_id || '' === $post_id || 0 === $post_id || '0' === $post_id;
    }

    /**
     * Check whether the field is a sub-field of the repeater or flexible content row being looped.
     *
     * @param string $field_name
     * @return bool
     */
    private static function is_sub_field_of_current_row($field_name)
    {
        if (!is_string($field_name) || '' === $field_name || !function_exists('get_sub_field_object')) {
            return false;
        }

        try {
            return !empty(get_sub_field_object($field_name, false, false));
        } catch (\Throwable $e) {
            return false;
        }
    }
}
