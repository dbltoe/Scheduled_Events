<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: config.eventz.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Registers EventzAdminObserver so it can hook NOTIFIER_PLUGIN_HELP_PAGE_URL_LOOKUP
 * on every admin page load (the Help button only calls it on the current page).
 */
$autoLoadConfig[999][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/EventzAdminObserver.php',
    'classPath' => DIR_WS_CLASSES,
];
$autoLoadConfig[999][] = [
    'autoType' => 'classInstantiate',
    'className' => 'EventzAdminObserver',
    'objectName' => 'EventzAdminObserver',
];

/**
 * English fallback for the plugin's admin menu labels on Zen Cart 1.5.8 and
 * 2.0.x, which load no English copy for a non-English admin. Breakpoint 200
 * is after the admin's languages load, so a translation still wins.
 */
$autoLoadConfig[200][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_eventz_language_fallback.php',
];

/**
 * The color fields under Configuration > Scheduled Events (eventz_cfg_color).
 */
$autoLoadConfig[200][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_eventz_color_field.php',
];
