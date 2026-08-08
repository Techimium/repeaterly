<?php

namespace Repeaterly\Includes;

/**
 * Resolves the WordPress object Repeaterly should pass to ACF.
 *
 * ACF's implicit current object can be a preview revision even while Elementor
 * renders the parent document. Repeaterly therefore uses explicit post IDs for
 * top-level fields, but keeps false for a field confirmed inside the active ACF
 * row so nested repeaters and template previews continue to work.
 */
class Acf_Context
{
    /**
     * @param mixed $post_id
     * @return mixed
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

    /** @return int|false */
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

    /** @return int|false */
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
     * @param mixed $post_id Explicit ACF object, or false for current.
     * @return mixed
     */
    public static function resolve_post_id($post_id = false)
    {
        if (!self::is_implicit_current($post_id)) {
            return self::normalize_post_id($post_id);
        }

        $render_post_id = self::get_render_post_id();

        return $render_post_id ? $render_post_id : self::get_main_queried_post_id();
    }

    /**
     * @param string $field_name
     * @param mixed  $post_id Explicit ACF object, or false for current.
     * @return mixed false only for a confirmed active-row sub-field.
     */
    public static function resolve_field_post_id($field_name, $post_id = false)
    {
        if (!self::is_implicit_current($post_id)) {
            return self::normalize_post_id($post_id);
        }

        if (self::has_active_sub_field($field_name)) {
            return false;
        }

        return self::resolve_post_id(false);
    }

    /**
     * Run one ACF call without allowing ACF's preview filter to remap an
     * explicit parent post back to its latest revision.
     *
     * @param mixed    $post_id
     * @param callable $callback
     * @return mixed
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

    /** @param mixed $post_id */
    private static function is_implicit_current($post_id)
    {
        return false === $post_id || null === $post_id || '' === $post_id || 0 === $post_id || '0' === $post_id;
    }

    private static function has_active_sub_field($field_name)
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
