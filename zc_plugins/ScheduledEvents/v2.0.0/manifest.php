<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: manifest.php 2026-07-16 05:30:22Z dbltoe $
 */

// -----
// Read Me / GitHub buttons shown in the Plugin Manager's info box, matching the pattern
// established for Social Contact Footer and Admin Add Customer. The Read Me URL is derived
// from this file's own on-disk location (rather than a hardcoded version string) so it
// can't go stale on a future version bump. Zen Cart's shipped zc_plugins/.htaccess denies
// everything then explicitly re-allows .html, so readme.html is reachable by design.
//
$sePluginRelativeDir = basename(dirname(__DIR__)) . '/' . basename(__DIR__);
$seReadmeUrl = (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/') . 'zc_plugins/' . $sePluginRelativeDir . '/readme.html';
$seGithubUrl = 'https://github.com/dbltoe/Scheduled_Events';
$seForumUrl = 'https://www.zen-cart.com/threads/107224';
$seButtonGap = '6px';
$seLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $seButtonGap . '">'
    . '<a href="' . $seReadmeUrl . '" target="_blank" rel="noopener noreferrer"'
    . ' class="btn btn-primary" role="button"'
    . ' style="margin:0 ' . $seButtonGap . ' 0 0">Read Me</a>'
    . '<a href="' . $seGithubUrl . '" target="_blank" rel="noopener noreferrer"'
    . ' class="btn btn-primary" role="button"'
    . ' style="margin:0 ' . $seButtonGap . ' 0 0">GitHub</a>'
    . '</div>'
    . '<div style="margin:6px 0 0;padding:0 0 0 ' . $seButtonGap . '">'
    . '<a href="' . $seForumUrl . '" target="_blank" rel="noopener noreferrer">Forum Support Thread</a>'
    . '</div>';

/**
 * Scheduled Events plugin manifest
 */
return [
    'pluginVersion' => 'v2.0.0',
    'pluginName' => 'Scheduled Events',
    'pluginDescription' => 'Displays upcoming scheduled events (shows, fairs, expos, etc.) on their own storefront page, with an optional link in the Information sidebox or its own sidebox (simple link or slider) placeable via the Layout Boxes Controller. Events are managed from a dedicated admin screen. Replaces the older, non-encapsulated version of this plugin; requires Zen Cart 2.0.0 or later.' . $seLinks,
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    'pluginId' => 860,
    'zcVersions' => ['v200', 'v201', 'v210', 'v221', 'v222', 'v223'],
    'changelog' => 'changelog.txt',
    'github_repo' => $seGithubUrl,
    'pluginGroups' => [],
];
