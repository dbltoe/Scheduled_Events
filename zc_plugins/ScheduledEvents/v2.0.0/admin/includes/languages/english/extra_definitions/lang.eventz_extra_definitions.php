<?php
/**
 * Loaded on every admin page via Zen Cart's ArraysLanguageLoader, which
 * requires an extra_definitions file to `return` a flat CONSTANT_NAME =>
 * value array - it calls define() on each pair itself. A file with no
 * return statement (e.g. one using define() directly, as this used to)
 * implicitly returns int(1) here, which is a fatal TypeError.
 *
 * - BOX_HEADING_EVENTZ: friendly name for the "eventz" sidebox wherever
 *   admin lists boxes by language key, e.g. Design > Layout Boxes Controller.
 * - BOX_CATALOG_EVENTZ: menu text for the Catalog > Scheduled Events
 *   admin_pages entry (the events list page).
 * - BOX_CONFIGURATION_SCHEDULED_EVENTS: menu text for the
 *   Configuration > Scheduled Events admin_pages entry.
 * All three must be defined admin-wide, not just on their own page, since
 * the sidebar menu (and Layout Boxes Controller) render on every page.
 */
return [
    'BOX_HEADING_EVENTZ' => 'Scheduled Events',
    'BOX_CATALOG_EVENTZ' => 'Scheduled Events',
    'BOX_CONFIGURATION_SCHEDULED_EVENTS' => 'Scheduled Events',
];
