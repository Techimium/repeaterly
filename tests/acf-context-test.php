<?php

// Isolated regression coverage for the shared resolver; no WordPress bootstrap required.
$GLOBALS['test_post_id'] = 0;
$GLOBALS['test_queried_object'] = false;
$GLOBALS['test_autosaves'] = [];
$GLOBALS['test_revisions'] = [];
$GLOBALS['test_active_fields'] = [];
$GLOBALS['test_active_values'] = [];
$GLOBALS['test_fields'] = [];
$GLOBALS['test_values'] = [];
$GLOBALS['test_rows'] = [];
$GLOBALS['test_preview_map'] = [7 => 609];
$GLOBALS['test_filters'] = [];

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['test_filters'][$hook][$priority][] = $callback;
}

function remove_filter($hook, $callback, $priority = 10)
{
    if (empty($GLOBALS['test_filters'][$hook][$priority])) {
        return;
    }

    foreach ($GLOBALS['test_filters'][$hook][$priority] as $index => $registered) {
        if ($registered === $callback) {
            unset($GLOBALS['test_filters'][$hook][$priority][$index]);
        }
    }
}

function test_acf_valid_post_id($post_id)
{
    $preloaded = null;

    if (!empty($GLOBALS['test_filters']['acf/pre_load_post_id'])) {
        ksort($GLOBALS['test_filters']['acf/pre_load_post_id']);
        foreach ($GLOBALS['test_filters']['acf/pre_load_post_id'] as $callbacks) {
            foreach ($callbacks as $callback) {
                $preloaded = $callback($preloaded, $post_id);
            }
        }
    }

    if (null !== $preloaded) {
        return $preloaded;
    }

    return $GLOBALS['test_preview_map'][$post_id] ?? $post_id;
}

function get_the_ID()
{
    return $GLOBALS['test_post_id'];
}

function get_queried_object()
{
    return $GLOBALS['test_queried_object'];
}

function wp_is_post_autosave($post_id)
{
    return $GLOBALS['test_autosaves'][$post_id] ?? false;
}

function wp_is_post_revision($post_id)
{
    return $GLOBALS['test_revisions'][$post_id] ?? false;
}

function get_sub_field_object($key, $format_value = true, $load_value = true)
{
    if (!isset($GLOBALS['test_active_fields'][$key])) {
        return false;
    }

    $field = $GLOBALS['test_active_fields'][$key];

    if ($load_value) {
        $field['value'] = $GLOBALS['test_active_values'][$key] ?? null;
    }

    return $field;
}

function get_field_object($key, $post_id = false, $format_value = true, $load_value = true)
{
    $post_id = test_acf_valid_post_id($post_id);

    if (!isset($GLOBALS['test_fields'][$post_id][$key])) {
        return false;
    }

    $field = $GLOBALS['test_fields'][$post_id][$key];

    if ($load_value) {
        $field['value'] = $GLOBALS['test_values'][$post_id][$key] ?? null;
    }

    return $field;
}

function get_sub_field($key)
{
    return $GLOBALS['test_active_values'][$key] ?? null;
}

function get_field($key, $post_id = false)
{
    $post_id = test_acf_valid_post_id($post_id);
    return $GLOBALS['test_values'][$post_id][$key] ?? null;
}

function have_rows($key, $post_id = false)
{
    $post_id = test_acf_valid_post_id($post_id);
    return !empty($GLOBALS['test_rows'][$post_id][$key]);
}

function get_post_meta($post_id, $key, $single)
{
    return $GLOBALS['test_values'][$post_id][$key] ?? null;
}

require_once dirname(__DIR__) . '/includes/acf-context.php';
require_once dirname(__DIR__) . '/includes/acf.php';

use Repeaterly\Includes\Acf;
use Repeaterly\Includes\Acf_Context;

function assert_same($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, sprintf("FAIL: %s\nExpected: %s\nActual: %s\n", $message, var_export($expected, true), var_export($actual, true)));
        exit(1);
    }
}

$post = (object) ['ID' => 609, 'post_type' => 'revision'];
$GLOBALS['test_autosaves'][609] = 7;
$GLOBALS['test_post_id'] = 609;
$GLOBALS['test_queried_object'] = (object) ['ID' => 7, 'post_type' => 'page'];
assert_same(7, Acf_Context::get_render_post_id(), 'autosave render post normalizes to its parent');
assert_same(7, Acf_Context::resolve_field_post_id('members'), 'top-level preview field uses the parent page');

$post = (object) ['ID' => 610, 'post_type' => 'revision'];
$GLOBALS['test_autosaves'] = [];
$GLOBALS['test_revisions'][610] = 7;
assert_same(7, Acf_Context::resolve_post_id(false), 'revision render post normalizes to its parent');

$post = (object) ['ID' => 42, 'post_type' => 'post'];
$GLOBALS['test_post_id'] = 42;
assert_same(42, Acf_Context::resolve_post_id(false), 'relationship render post wins over the main query');

$post = null;
$GLOBALS['test_post_id'] = 0;
assert_same(7, Acf_Context::resolve_post_id(false), 'main queried post is the fallback without a render post');
assert_same('option', Acf_Context::resolve_post_id('option'), 'default options-page ID passes through');
assert_same('theme-settings', Acf_Context::resolve_post_id('theme-settings'), 'custom options-page ID passes through');
assert_same(7, Acf_Context::resolve_post_id(610), 'explicit revision normalizes to its parent');

$post = (object) ['ID' => 7, 'post_type' => 'page'];
$GLOBALS['test_post_id'] = 7;
$GLOBALS['test_active_fields'] = ['nested_items' => ['type' => 'repeater']];
$GLOBALS['test_active_values'] = ['nested_items' => [['title' => 'Nested']]];
$GLOBALS['test_fields'][7]['projects'] = ['type' => 'repeater'];
$GLOBALS['test_values'][7]['projects'] = [['title' => 'Top level']];
$GLOBALS['test_rows'][7]['projects'] = [['title' => 'Top level']];

$nested = Acf::resolve_field('nested_items', false, false, false);
assert_same(false, $nested['post_id'], 'confirmed nested field preserves active-row context');
assert_same('repeater', $nested['field']['type'], 'nested field metadata is returned');
assert_same([['title' => 'Nested']], Acf::get_raw_field_value('nested_items'), 'nested raw value comes from the active row');

$top_level = Acf::resolve_field('projects', false, false, false);
assert_same(7, $top_level['post_id'], 'unrelated active row does not hijack a top-level field');
assert_same([['title' => 'Top level']], Acf::get_raw_field_value('projects'), 'top-level raw value uses explicit render post');
assert_same([['title' => 'Top level']], Acf::get_field_value('projects'), 'the pre-2.2 formatted-value API remains compatible for older Pro builds');
assert_same(true, Acf::have_rows('projects', 7), 'row checks bypass ACF preview remapping for the explicit parent');
assert_same([], array_values($GLOBALS['test_filters']['acf/pre_load_post_id'][PHP_INT_MAX]), 'scoped ACF pre-load filter is removed after each call');

$GLOBALS['test_fields'][22]['nested_items'] = ['type' => 'repeater'];
$explicit = Acf::resolve_field('nested_items', 22, false, false);
assert_same(22, $explicit['post_id'], 'explicit external source ignores an active same-named row');

fwrite(STDOUT, "acf-context-test: OK\n");
