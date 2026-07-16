<?php
/**
 * Loaded on every admin page (not just eventz.php) so the "eventz" sidebox
 * has a friendly name wherever admin lists boxes by their language key,
 * e.g. Design > Layout Boxes Controller.
 */
define('BOX_HEADING_EVENTZ', 'Scheduled Events');

/**
 * Menu text for the Catalog > Scheduled Events admin_pages entry (the events
 * list page). Needed admin-wide, same reasoning as BOX_HEADING_EVENTZ above.
 */
if (!defined('BOX_CATALOG_EVENTZ')) {
    define('BOX_CATALOG_EVENTZ', 'Scheduled Events');
}

/**
 * Menu text for the Configuration > Scheduled Events admin_pages entry.
 * Needed admin-wide since the Configuration menu renders on every admin page.
 */
define('BOX_CONFIGURATION_SCHEDULED_EVENTS', 'Scheduled Events');
