# Plugin Check findings — Repeaterly (Free)

Recorded during P0-06 (release hygiene). Run against the current `main` branch with:

```
wp plugin check repeaterly \
  --exclude-directories=tests,.github,vendor,node_modules,wp-assets \
  --exclude-files=composer.json,composer.lock,phpunit.xml.dist,.phpunit.result.cache,.phpunit.cache,.gitattributes,.gitignore,.distignore
```

The excludes mirror `.distignore` so the check reflects what actually ships in the wp.org
package, not this dev checkout.

## Fixed

- `missing_direct_file_access_protection` (`includes/acf-context.php`) — added the standard
  `if ( ! defined( 'ABSPATH' ) ) { exit; }` guard used throughout the rest of the plugin. While
  auditing this, a full sweep found nine more files with the same gap that Plugin Check's
  single-pass run didn't surface in one report: `includes/acf.php`, `includes/dynamic-content.php`,
  `includes/hook.php`, `includes/utils.php`, `includes/widgets/accordion.php`,
  `includes/widgets/heading.php`, `includes/widgets/icon-list.php`, `includes/widgets/image.php`,
  `includes/widgets/promotion.php`. All ten now guard against direct access; none change
  rendered output. (`tests/bootstrap.php` was updated to define `ABSPATH` before loading the
  autoloader, matching Pro's existing test bootstrap, so the test suite continues to exercise
  these classes without tripping the new guards.)

## Allow-listed (reviewed, not changed)

Dynamic hook names PHPCS can't statically resolve, but which are already properly prefixed:

- `WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound` on
  `Hook::DYNAMIC_TEXT_OPTIONS`, `Hook::DYNAMIC_LINK_OPTIONS`, `Hook::DYNAMIC_IMAGE_OPTIONS`,
  `Hook::DYNAMIC_VALUE` in `includes/dynamic-content.php` — each constant's value (see
  `includes/hook.php`) already starts with `repeaterly/`; PHPCS just can't resolve a class
  constant used as a hook name argument.

Invoking existing WordPress core filters, not defining new ones:

- `WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound` on `the_content`
  (`includes/tags/base/post-content.php`) and `the_excerpt`
  (`includes/tags/base/post-excerpt.php`) — these call `apply_filters('the_content', ...)` /
  `apply_filters('the_excerpt', ...)` to run post content through WordPress's own core
  filters (as `the_content()`/`the_excerpt()` normally would), not to register a new,
  unprefixed hook.

Marketing subtitle, not a naming mismatch:

- `mismatched_plugin_name` (readme.txt) — the readme's H1 ("Repeaterly - ACF Repeater, Flexible
  Content & Dynamic Tags for Elementor") is an intentional, longer wp.org search-optimized
  title; the plugin header's `Plugin Name: Repeaterly` is what actually registers and displays
  in wp-admin. Both are correct for their purpose.

Out of scope for this release per the proposal's explicit non-goal (only the two Pro grid
widget files were normalized to LF; see `.gitattributes`):

- `Internal.LineEndings.Mixed` on `repeaterly.php`, `includes/traits/has-acf-options-page-source.php`,
  `includes/widgets/button.php`, `includes/widgets/image-gallery.php`, and the
  `includes/tags/acf/*` files (`acf-color.php`, `acf-date-time.php`, `acf-field.php`,
  `acf-gallery.php`, `acf-image.php`, `acf-number.php`, `acf-url.php`) — legacy CRLF files
  predating this cycle. Deferred to a future full-repo line-ending pass; adding the ABSPATH
  guard above incidentally normalized several other previously-CRLF files to LF as a side
  effect of the text edit, which is harmless but wasn't a deliberate part of this item's scope.
