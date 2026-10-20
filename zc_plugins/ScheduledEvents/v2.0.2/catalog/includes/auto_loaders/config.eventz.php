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
 * Registers EventzObserver so it can hook NOTIFY_INFORMATION_SIDEBOX_ADDITIONS
 * on every storefront page load.
 */
$autoLoadConfig[999][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/EventzObserver.php',
    'classPath' => DIR_WS_CLASSES,
];
$autoLoadConfig[999][] = [
    'autoType' => 'classInstantiate',
    'className' => 'EventzObserver',
    'objectName' => 'EventzObserver',
];
