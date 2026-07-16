<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: manifest.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Scheduled Events plugin manifest
 */
return [
    'pluginVersion' => 'v2.0.0',
    'pluginName' => 'Scheduled Events',
    'pluginDescription' => 'Displays upcoming scheduled events (shows, fairs, expos, etc.) on their own storefront page, with an optional link in the Information sidebox or its own sidebox (simple link or slider) placeable via the Layout Boxes Controller. Events are managed from a dedicated admin screen. Replaces the older, non-encapsulated version of this plugin; requires Zen Cart 2.0.0 or later.',
    'pluginAuthor' => 'dbltoe',
    'pluginId' => 0,
    'zcVersions' => ['v200', 'v201', 'v210', 'v221', 'v222', 'v223'],
    'changelog' => 'changelog.txt',
    'github_repo' => '',
    'pluginGroups' => [],
];
