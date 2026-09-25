<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;

/**
 * Base for tests of Acf / Dynamic_Content value handling. ACF is stubbed from an in-memory field
 * table: register a field with field(), or a repeater-row sub-field with sub_field(), and ACF's
 * get_field_object()/get_sub_field_object()/get_field()/get_sub_field() answer from it.
 *
 * Every PHP warning or notice raised by the code under test fails the test: rendering a field
 * must never warn, whatever its value shape.
 */
abstract class AcfTestCase extends TestCase
{
    /** Post ID used as the explicit ACF object in tests. */
    const POST_ID = 10;

    /** @var array<string, array<string, array{settings: array, raw: mixed, formatted: mixed}>> post => name => field */
    private $fields = [];

    /** @var array<string, array{settings: array, raw: mixed, formatted: mixed}> */
    private $sub_fields = [];

    /** @var array<string, mixed> Plain post meta on POST_ID with no ACF field definition. */
    private $meta = [];

    protected function set_up()
    {
        parent::set_up();

        require_once dirname(__DIR__) . '/includes/acf-context.php';
        require_once dirname(__DIR__) . '/includes/acf.php';

        $this->fields = [];
        $this->sub_fields = [];
        $this->meta = [];

        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('wp_is_post_revision')->justReturn(false);
        Functions\when('get_the_ID')->justReturn(self::POST_ID);
        Functions\when('is_tax')->justReturn(false);
        Functions\when('is_category')->justReturn(false);
        Functions\when('is_tag')->justReturn(false);

        Functions\when('get_field_object')->alias(function ($name, $post_id = false, $format_value = true, $load_value = true) {
            $field = $this->fields[(string) $post_id][$name] ?? null;

            return null === $field ? false : $this->field_object($field, $format_value, $load_value);
        });

        Functions\when('get_sub_field_object')->alias(function ($name, $format_value = true, $load_value = true) {
            $field = $this->sub_fields[$name] ?? null;

            return null === $field ? false : $this->field_object($field, $format_value, $load_value);
        });

        Functions\when('get_field')->alias(function ($name, $post_id = false, $format_value = true) {
            if (isset($this->fields[(string) $post_id][$name])) {
                $field = $this->fields[(string) $post_id][$name];

                return $format_value ? $field['formatted'] : $field['raw'];
            }

            return (string) self::POST_ID === (string) $post_id ? ($this->meta[$name] ?? null) : null;
        });

        Functions\when('get_sub_field')->alias(function ($name, $format_value = true) {
            $field = $this->sub_fields[$name] ?? null;

            if (null === $field) {
                return false;
            }

            return $format_value ? $field['formatted'] : $field['raw'];
        });

        Functions\when('get_post_meta')->alias(function ($post_id, $key = '', $single = false) {
            return (int) $post_id === self::POST_ID ? ($this->meta[$key] ?? '') : '';
        });

        // Only warnings raised by the plugin itself: test tooling (Brain Monkey on newer PHP) has
        // deprecation-style warnings of its own that say nothing about the code under test.
        $includes = dirname(__DIR__) . '/includes/';
        set_error_handler(static function ($errno, $message, $file, $line) use ($includes) {
            if (0 !== strpos((string) $file, $includes)) {
                return true;
            }

            throw new \ErrorException('PHP warning or notice: ' . $message, 0, $errno, $file, $line);
        }, E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE);
    }

    protected function tear_down()
    {
        restore_error_handler();

        parent::tear_down();
    }

    /**
     * Registers a top-level field.
     *
     * @param mixed      $formatted What ACF returns with its Return Format applied.
     * @param mixed      $raw       What ACF stores; defaults to $formatted.
     * @param int|string $post_id   ACF object it lives on (a post ID or an options-page ID).
     */
    protected function field(string $name, string $type, $formatted, array $settings = [], $raw = null, $post_id = self::POST_ID): void
    {
        $this->fields[(string) $post_id][$name] = $this->entry($name, $type, $formatted, $settings, $raw);
    }

    /** Registers a sub-field of the active repeater row. */
    protected function sub_field(string $name, string $type, $formatted, array $settings = [], $raw = null): void
    {
        $this->sub_fields[$name] = $this->entry($name, $type, $formatted, $settings, $raw);
    }

    /** Registers post meta on POST_ID with no ACF field behind it. */
    protected function meta(string $key, $value): void
    {
        $this->meta[$key] = $value;
    }

    private function entry(string $name, string $type, $formatted, array $settings, $raw): array
    {
        return [
            'settings' => ['name' => $name, 'key' => 'field_' . $name, 'type' => $type] + $settings,
            'raw' => null === $raw ? $formatted : $raw,
            'formatted' => $formatted,
        ];
    }

    private function field_object(array $field, $format_value, $load_value): array
    {
        return $field['settings'] + ['value' => $load_value ? ($format_value ? $field['formatted'] : $field['raw']) : null];
    }
}
