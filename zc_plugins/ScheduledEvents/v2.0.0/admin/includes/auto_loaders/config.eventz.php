<?php
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
