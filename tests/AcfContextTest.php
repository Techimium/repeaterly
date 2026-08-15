<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use Repeaterly\Includes\Acf;
use Repeaterly\Includes\Acf_Context;

/**
 * Ports every case from the retired tests/acf-context-test.php (P0-01) onto
 * PHPUnit + Brain Monkey. Each test is self-contained (no shared imperative
 * state between cases, unlike the original script) so PHPUnit can run them
 * in any order.
 *
 * test_current_post_context(), test_external_post_context(),
 * test_options_page_ids_pass_through() and test_nested_sub_field_context()
 * are the named preview-safe ACF context matrix required by the
 * test-foundation spec.
 */
class AcfContextTest extends TestCase
{
    protected function set_up()
    {
        parent::set_up();

        require_once dirname(__DIR__) . '/includes/acf-context.php';
        require_once dirname(__DIR__) . '/includes/acf.php';

        // Brain Monkey requires every WP function actually reached in a test to be
        // stubbed (function_exists() alone doesn't skip a real call). Default the
        // autosave/revision checks to "no" so tests that don't care about that
        // branch don't have to restate it; tests that do care override below.
        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('wp_is_post_revision')->justReturn(false);
    }

    protected function tear_down()
    {
        unset($GLOBALS['post']);

        parent::tear_down();
    }

    public function test_autosave_render_post_normalizes_to_parent()
    {
        $GLOBALS['post'] = (object) ['ID' => 609, 'post_type' => 'revision'];

        Functions\when('wp_is_post_autosave')->alias(function ($post_id) {
            return 609 === $post_id ? 7 : false;
        });
        Functions\when('wp_is_post_revision')->justReturn(false);
        Functions\when('get_sub_field_object')->justReturn(false);

        $this->assertSame(7, Acf_Context::get_render_post_id(), 'autosave render post normalizes to its parent');
        $this->assertSame(7, Acf_Context::resolve_field_post_id('members'), 'top-level preview field uses the parent page');
    }

    public function test_revision_render_post_normalizes_to_parent()
    {
        $GLOBALS['post'] = (object) ['ID' => 610, 'post_type' => 'revision'];

        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('wp_is_post_revision')->alias(function ($post_id) {
            return 610 === $post_id ? 7 : false;
        });

        $this->assertSame(7, Acf_Context::resolve_post_id(false), 'revision render post normalizes to its parent');
    }

    public function test_relationship_render_post_wins_over_main_query()
    {
        $GLOBALS['post'] = (object) ['ID' => 42, 'post_type' => 'post'];

        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('wp_is_post_revision')->justReturn(false);
        Functions\when('get_queried_object')->justReturn((object) ['ID' => 999, 'post_type' => 'page']);

        $this->assertSame(42, Acf_Context::resolve_post_id(false), 'relationship render post wins over the main query');
    }

    public function test_main_queried_post_is_fallback_without_render_post()
    {
        $GLOBALS['post'] = null;

        Functions\when('get_the_ID')->justReturn(0);
        Functions\when('get_queried_object')->justReturn((object) ['ID' => 7, 'post_type' => 'page']);

        $this->assertSame(7, Acf_Context::resolve_post_id(false), 'main queried post is the fallback without a render post');
    }

    public function test_explicit_revision_id_normalizes_to_parent()
    {
        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('wp_is_post_revision')->alias(function ($post_id) {
            return 610 === $post_id ? 7 : false;
        });

        $this->assertSame(7, Acf_Context::resolve_post_id(610), 'explicit revision normalizes to its parent');
    }

    public function test_options_page_ids_pass_through()
    {
        $this->assertSame('option', Acf_Context::resolve_post_id('option'), 'default options-page ID passes through');
        $this->assertSame('theme-settings', Acf_Context::resolve_post_id('theme-settings'), 'custom options-page ID passes through');
    }

    public function test_current_post_context()
    {
        $GLOBALS['post'] = (object) ['ID' => 7, 'post_type' => 'page'];

        Filters\expectAdded('acf/pre_load_post_id')->atLeast()->once()->with(Mockery::type('Closure'), PHP_INT_MAX, 2);
        Filters\expectRemoved('acf/pre_load_post_id')->atLeast()->once()->with(Mockery::type('Closure'), PHP_INT_MAX);

        // An active row exists for a different field name (e.g. inside a nested
        // repeater loop elsewhere on the page); it must not be mistaken for an
        // active row of 'projects'.
        Functions\when('get_sub_field_object')->alias(function ($key) {
            return 'nested_items' === $key ? ['type' => 'repeater'] : false;
        });
        Functions\when('get_field_object')->alias(function ($key, $post_id, $format_value = true, $load_value = true) {
            if ('projects' !== $key || 7 !== $post_id) {
                return false;
            }

            $field = ['type' => 'repeater'];

            if ($load_value) {
                $field['value'] = [['title' => 'Top level']];
            }

            return $field;
        });
        Functions\when('get_field')->alias(function ($key, $post_id) {
            return ('projects' === $key && 7 === $post_id) ? [['title' => 'Top level']] : null;
        });
        Functions\when('have_rows')->alias(function ($key, $post_id) {
            return 'projects' === $key && 7 === $post_id;
        });

        $top_level = Acf::resolve_field('projects', false, false, false);
        $this->assertSame(7, $top_level['post_id'], 'unrelated active row does not hijack a top-level field');

        $this->assertSame([['title' => 'Top level']], Acf::get_raw_field_value('projects'), 'top-level raw value uses explicit render post');
        $this->assertSame([['title' => 'Top level']], Acf::get_field_value('projects'), 'the pre-2.2 formatted-value API remains compatible for older Pro builds');
        $this->assertTrue(Acf::have_rows('projects', 7), 'row checks bypass ACF preview remapping for the explicit parent');
    }

    public function test_nested_sub_field_context()
    {
        Functions\when('get_sub_field_object')->alias(function ($key) {
            return 'nested_items' === $key ? ['type' => 'repeater'] : false;
        });
        Functions\when('get_sub_field')->alias(function ($key) {
            return 'nested_items' === $key ? [['title' => 'Nested']] : null;
        });

        $nested = Acf::resolve_field('nested_items', false, false, false);
        $this->assertFalse($nested['post_id'], 'confirmed nested field preserves active-row context');
        $this->assertSame('repeater', $nested['field']['type'], 'nested field metadata is returned');
        $this->assertSame([['title' => 'Nested']], Acf::get_raw_field_value('nested_items'), 'nested raw value comes from the active row');
    }

    public function test_external_post_context()
    {
        // The same field name is active in a nested row elsewhere, but an
        // explicit external post ID must win regardless.
        Functions\when('get_sub_field_object')->alias(function ($key) {
            return 'nested_items' === $key ? ['type' => 'repeater'] : false;
        });
        Functions\when('get_field_object')->alias(function ($key, $post_id) {
            return ('nested_items' === $key && 22 === $post_id) ? ['type' => 'repeater'] : false;
        });

        $explicit = Acf::resolve_field('nested_items', 22, false, false);
        $this->assertSame(22, $explicit['post_id'], 'explicit external source ignores an active same-named row');
    }
}
