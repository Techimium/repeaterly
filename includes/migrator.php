<?php

namespace Repeaterly\Includes;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Records which version of the plugin last ran on this site, and runs any
 * one-shot upgrade work owed between that version and the running one.
 *
 * Deliberately not driven by register_activation_hook: WordPress updates a
 * plugin in place, without deactivating and reactivating it, so that hook does
 * not fire on the one path that matters. Nor by upgrader_process_complete,
 * which runs inside the *old* code's process and so can never carry the new
 * version's migration. The only reliable trigger is an ordinary request, which
 * is why run() is called from the plugin's init().
 *
 * Repeaterly Pro carries its own copy of this class. The two are kept separate
 * on purpose: the plugins release independently, and giving Pro a dependency on
 * a Free helper for something this small would force a minimum-Free-version
 * bump and lock their release order together for no user-visible gain.
 */
class Migrator
{
    public static $version_option = 'repeaterly_version';

    /**
     * The ordinary request costs one option read and no write: the stored
     * version already equals the running one, so this returns immediately.
     *
     * The new version is recorded only after the steps have been attempted. A
     * request that dies partway through therefore leaves the stored version
     * alone and the steps are attempted again next time — which is the other
     * half of why steps() must be idempotent.
     */
    public static function run(string $current_version): void
    {
        $stored = (string) get_option(self::$version_option, '');

        if (version_compare($stored, $current_version, '==')) {
            return;
        }

        $steps = static::steps();

        // Sorted rather than trusted to be declared in order, so appending a
        // step in the wrong place cannot silently reorder the upgrade path.
        uksort($steps, 'version_compare');

        foreach ($steps as $version => $callback) {
            if (version_compare($stored, $version, '<') && version_compare($version, $current_version, '<=')) {
                $callback();
            }
        }

        update_option(self::$version_option, $current_version);
    }

    /**
     * Upgrade steps keyed by the plugin version that introduced them. A step
     * runs when the stored version is below its key and its key is at or below
     * the running version; run() sorts them before deciding.
     *
     * Protected, not private, so a test can substitute a registry — there is
     * nothing to order or skip while the real one is empty.
     *
     * Two contracts bind everything added to this list:
     *
     * - **Idempotent.** A step may run more than once — a request can die
     *   before the version is recorded, and two concurrent requests can both
     *   read a stale version. Running twice must be indistinguishable from
     *   running once.
     *
     * - **No-op safe on a fresh install.** An absent stored version sorts below
     *   every key, so a brand-new site runs every step ever registered. That is
     *   intentional: at the release that introduced this option, an absent
     *   version was genuinely ambiguous between "new install" and "upgraded
     *   from a version that never recorded one". This plugin stored no options
     *   at all before now, so there is no artifact left anywhere on the site
     *   that could tell the two apart. A step that finds nothing to do must
     *   simply do nothing.
     *
     * Empty by design — the option exists so that a future release has somewhere
     * to hang migration work, not because any is owed today.
     *
     * @return array<string,callable>
     */
    protected static function steps(): array
    {
        return [];
    }
}
