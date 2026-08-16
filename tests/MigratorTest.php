<?php

/**
 * Covers Repeaterly\Includes\Migrator: recording the running version, the
 * no-write fast path, and which upgrade steps run for a given stored version.
 *
 * The real step registry is empty by design, so ordering and range selection
 * are driven through Migrator_Double, which substitutes a registry via the
 * protected steps() seam. Options are held in memory for each test.
 */

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;
use Repeaterly\Includes\Migrator;

require_once dirname(__DIR__) . '/includes/migrator.php';

/**
 * Declared here rather than in a fixture file because the parent has to be
 * loaded before this declaration is compiled, and the plugin's includes are not
 * covered by the dev autoloader (see the require_once above).
 */
class Migrator_Double extends Migrator
{
    /** @var array<string,callable> substituted registry */
    public static $registry = [];

    /** @var array<int,string> step versions that ran, in the order they ran */
    public static $ran = [];

    protected static function steps(): array
    {
        return static::$registry;
    }

    public static function step(string $version): callable
    {
        return function () use ($version) {
            static::$ran[] = $version;
        };
    }
}

class MigratorTest extends TestCase
{
    /** @var array<string,mixed> in-memory wp_options */
    private $options = [];

    /** @var int every update_option call, to prove the fast path writes nothing */
    private $writes = 0;

    private $key;

    protected function set_up()
    {
        parent::set_up();

        $this->key = Migrator::$version_option;
        $this->options = [];
        $this->writes = 0;

        Migrator_Double::$registry = [];
        Migrator_Double::$ran = [];

        $options = &$this->options;
        $writes = &$this->writes;

        Functions\when('get_option')->alias(function ($key, $default = false) use (&$options) {
            return array_key_exists($key, $options) ? $options[$key] : $default;
        });
        Functions\when('update_option')->alias(function ($key, $value) use (&$options, &$writes) {
            $options[$key] = $value;
            $writes++;
            return true;
        });
    }

    private function stored()
    {
        return array_key_exists($this->key, $this->options) ? $this->options[$this->key] : null;
    }

    // ----------------------------------------------------------- recording

    public function test_a_site_with_no_stored_version_records_the_running_one()
    {
        Migrator::run('2.2.0');

        $this->assertSame('2.2.0', $this->stored());
    }

    public function test_an_updated_plugin_replaces_the_stored_version()
    {
        $this->options[$this->key] = '2.1.0';

        Migrator::run('2.2.0');

        $this->assertSame('2.2.0', $this->stored());
    }

    public function test_an_unchanged_plugin_writes_nothing()
    {
        $this->options[$this->key] = '2.2.0';

        Migrator::run('2.2.0');

        $this->assertSame(0, $this->writes, 'the ordinary request must cost a read and no write');
    }

    /**
     * The stamp answers "what code last ran here", so a rollback has to move it
     * backwards. version_compare() is used rather than a greater-than check
     * precisely so this case is handled instead of silently skipped.
     */
    public function test_a_rolled_back_plugin_records_the_version_actually_running()
    {
        $this->options[$this->key] = '2.3.0';

        Migrator::run('2.2.0');

        $this->assertSame('2.2.0', $this->stored());
    }

    public function test_an_empty_registry_still_records_the_version()
    {
        $this->options[$this->key] = '2.1.0';

        Migrator::run('2.2.0');

        $this->assertSame('2.2.0', $this->stored(), 'recording does not depend on there being work to do');
    }

    // --------------------------------------------------------------- steps

    public function test_only_steps_above_the_stored_version_run()
    {
        $this->options[$this->key] = '2.2.0';
        Migrator_Double::$registry = [
            '2.1.0' => Migrator_Double::step('2.1.0'),
            '2.2.0' => Migrator_Double::step('2.2.0'),
            '2.3.0' => Migrator_Double::step('2.3.0'),
        ];

        Migrator_Double::run('2.3.0');

        $this->assertSame(['2.3.0'], Migrator_Double::$ran, 'work already done must not be repeated');
    }

    public function test_steps_above_the_running_version_do_not_run()
    {
        $this->options[$this->key] = '2.1.0';
        Migrator_Double::$registry = [
            '2.2.0' => Migrator_Double::step('2.2.0'),
            '2.4.0' => Migrator_Double::step('2.4.0'),
        ];

        Migrator_Double::run('2.2.0');

        $this->assertSame(['2.2.0'], Migrator_Double::$ran, 'a step from a version this site is not running yet must wait');
    }

    public function test_steps_run_in_ascending_version_order_however_declared()
    {
        Migrator_Double::$registry = [
            '2.10.0' => Migrator_Double::step('2.10.0'),
            '2.2.0'  => Migrator_Double::step('2.2.0'),
            '2.9.0'  => Migrator_Double::step('2.9.0'),
        ];

        Migrator_Double::run('3.0.0');

        $this->assertSame(
            ['2.2.0', '2.9.0', '2.10.0'],
            Migrator_Double::$ran,
            'declaration order must not decide the upgrade path, and 2.10 sorts above 2.9'
        );
    }

    /**
     * A fresh install and an upgrade from a version that never recorded one are
     * indistinguishable — and this plugin stored no options at all before now,
     * so nothing on the site could tell them apart. An absent stamp therefore
     * runs everything, which is what makes the "no-op safe on a fresh install"
     * contract load-bearing.
     */
    public function test_an_absent_version_runs_every_step()
    {
        Migrator_Double::$registry = [
            '2.2.0' => Migrator_Double::step('2.2.0'),
            '2.3.0' => Migrator_Double::step('2.3.0'),
        ];

        Migrator_Double::run('2.3.0');

        $this->assertSame(['2.2.0', '2.3.0'], Migrator_Double::$ran);
    }

    public function test_steps_do_not_run_again_once_the_version_is_recorded()
    {
        Migrator_Double::$registry = ['2.2.0' => Migrator_Double::step('2.2.0')];

        Migrator_Double::run('2.2.0');
        Migrator_Double::run('2.2.0');
        Migrator_Double::run('2.2.0');

        $this->assertSame(['2.2.0'], Migrator_Double::$ran);
    }

    /**
     * The version is recorded only after the steps have been attempted, so a
     * request that dies mid-step leaves the stamp alone and the next request
     * retries. A step observing the new version already stored would mean a
     * fatal halfway through had silently marked the upgrade complete.
     */
    public function test_the_version_is_recorded_only_after_steps_have_run()
    {
        $this->options[$this->key] = '2.1.0';
        $seen = null;
        $options = &$this->options;
        $key = $this->key;

        Migrator_Double::$registry = [
            '2.2.0' => function () use (&$seen, &$options, $key) {
                $seen = $options[$key];
            },
        ];

        Migrator_Double::run('2.2.0');

        $this->assertSame('2.1.0', $seen, 'the stamp must still be the old version while a step is running');
        $this->assertSame('2.2.0', $this->stored());
    }
}
