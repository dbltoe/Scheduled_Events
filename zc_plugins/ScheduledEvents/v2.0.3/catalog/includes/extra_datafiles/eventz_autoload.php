<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: eventz_autoload.php 2026-10-20 dbltoe $
 */

/**
 * Scheduled Events - storefront class autoloading on ZC 2.0.x
 *
 * ZC 2.0.x maps a plugin's Zencart\Plugins\Catalog\<Key> namespace to
 * <version>/classes/, not catalog/includes/classes/ as 2.1.0+ does, so the
 * events page and sidebox fatal with "Class EventzService not found". The
 * autoloader appends base directories, so adding this one is harmless where
 * core already maps it. Plugin extra_datafiles are included from inside a
 * method, hence $GLOBALS.
 */
if (isset($GLOBALS['psr4Autoloader']) && $GLOBALS['psr4Autoloader'] instanceof \Aura\Autoload\Loader) {
    $GLOBALS['psr4Autoloader']->addPrefix('Zencart\\Plugins\\Catalog\\ScheduledEvents', dirname(__DIR__) . '/classes/');
}
