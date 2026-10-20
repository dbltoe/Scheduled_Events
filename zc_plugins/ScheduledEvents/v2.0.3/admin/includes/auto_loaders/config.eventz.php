<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
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
