<?php

namespace Repeaterly\Tests;

use Brain\Monkey;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as Polyfill_Test_Case;

/**
 * Shared base for Repeaterly test classes: wires Brain Monkey's per-test
 * WP/ACF/Elementor function mocking through the polyfilled set_up()/tear_down()
 * hooks so subclasses stay compatible across the PHPUnit majors this plugin's
 * PHP 7.4-8.4 CI matrix installs (see tests/README.md).
 */
abstract class TestCase extends Polyfill_Test_Case
{
    protected function set_up()
    {
        parent::set_up();
        Monkey\setUp();
    }

    protected function tear_down()
    {
        Monkey\tearDown();
        parent::tear_down();
    }
}
