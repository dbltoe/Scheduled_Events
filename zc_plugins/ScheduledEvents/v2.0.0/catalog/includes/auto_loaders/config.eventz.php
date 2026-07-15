<?php
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
