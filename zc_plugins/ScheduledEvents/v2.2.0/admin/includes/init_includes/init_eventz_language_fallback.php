<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: init_eventz_language_fallback.php 2026-11-16 dbltoe $
 */

/**
 * Scheduled Events - English fallback for the admin menu labels.
 *
 * The labels for Extras > Scheduled Events, Configuration > Scheduled Events
 * and the sidebox's name in Layout Boxes Controller live in this plugin's
 * extra_definitions file. Zen Cart 2.1 and later load the English copy
 * first, so a store with no translation still has them; 1.5.8 and 2.0.x load
 * only the admin's own language, which leaves them undefined on a
 * non-English admin, and the admin menu then fails on every page.
 *
 * This runs at breakpoint 200, after the admin's languages have loaded, and
 * defines only what is still missing, so a translation always wins.
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$eventzFallbackFile = dirname(__DIR__) . '/languages/english/extra_definitions/lang.eventz_extra_definitions.php';
if (is_file($eventzFallbackFile)) {
    $eventzFallbackDefines = require $eventzFallbackFile;
    if (is_array($eventzFallbackDefines)) {
        foreach ($eventzFallbackDefines as $eventzFallbackKey => $eventzFallbackValue) {
            if (!defined($eventzFallbackKey)) {
                define($eventzFallbackKey, $eventzFallbackValue);
            }
        }
    }
}
unset($eventzFallbackFile, $eventzFallbackDefines, $eventzFallbackKey, $eventzFallbackValue);
