<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;
use Repeaterly\Includes\Acf;

/**
 * With ACF deactivated, every Acf value helper must still answer, from post meta or empty, and
 * never fatal. Brain Monkey defines ACF's functions for the rest of a process once any test
 * stubs them, so each case runs in its own process where they were never defined.
 */
class AcfInactiveTest extends TestCase
{
    protected function set_up()
    {
        parent::set_up();

        require_once dirname(__DIR__) . '/includes/acf-context.php';
        require_once dirname(__DIR__) . '/includes/acf.php';

        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('wp_is_post_revision')->justReturn(false);
        Functions\when('get_the_ID')->justReturn(7);
        Functions\when('get_post_meta')->alias(static function ($post_id, $key = '', $single = false) {
            return 7 === (int) $post_id && 'subtitle' === $key ? 'Stored subtitle' : '';
        });
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_field_value_reads_post_meta()
    {
        $this->assertFalse(function_exists('get_field'), 'precondition: ACF is not loaded in this process');

        $this->assertSame('Stored subtitle', Acf::get_field_value('subtitle', 7));
        $this->assertSame('Stored subtitle', Acf::get_field_value('subtitle'), 'current post');
        $this->assertSame('', Acf::get_field_value('missing', 7));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_text_url_and_gallery_helpers()
    {
        $this->assertFalse(function_exists('get_field'), 'precondition: ACF is not loaded in this process');

        $this->assertSame('Stored subtitle', Acf::get_field_text('subtitle', 7));
        $this->assertSame('', Acf::get_field_text('subtitle', false, true), 'no repeater rows without ACF');
        $this->assertSame('Stored subtitle', Acf::get_field_url('subtitle', 7), 'plain meta, as the URL tag always read it');
        $this->assertSame('', Acf::get_field_url('missing', 7));
        $this->assertSame([], Acf::get_gallery_images('subtitle', 7));
    }
}
