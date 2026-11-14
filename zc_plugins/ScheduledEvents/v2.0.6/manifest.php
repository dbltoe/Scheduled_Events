<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: manifest.php 2026-07-16 05:30:22Z dbltoe $
 */

// -----
// ReadMe / Settings / Forum Thread / GitHub buttons shown in the Plugin Manager's info box.
//
// ReadMe opens README.md through the plugin's own admin page
// (index.php?cmd=scheduled_events_readme): behind the admin login, superusers only, in
// its own window. It is shown only to superusers; the page enforces that too, because
// Zen Cart 2.0 and 2.1 keep the description they first saw. Forum Thread and GitHub go
// off-site, so they open in a new window with rel="noopener noreferrer"
// (zencart/zencart#8031).
//
$seReadmeUrl = 'index.php?cmd=scheduled_events_readme';
// Zen Cart 3.0.0 reads every manifest on every admin page, early (template resolver),
// before admin access is set up: zen_is_superuser() there is a fatal "Undefined constant
// SUPERUSER_PROFILE" that takes down the whole admin. Ask only once that exists.
$seShowReadme = true;
if (function_exists('zen_is_superuser') && defined('SUPERUSER_PROFILE')) {
    $seShowReadme = zen_is_superuser();
}
$seGithubUrl = 'https://github.com/dbltoe/Scheduled_Events';
$seForumUrl = 'https://www.zen-cart.com/threads/107224';

// -----
// The Settings button: this plugin's Configuration page, shown only once the plugin is
// installed. Its gID is assigned at install, so it differs from store to store and can't
// be written here; it's read from the admin page the installer registers (page key
// configScheduledEvents). The link is relative ("index.php?cmd=configuration&gID=N")
// because Plugin Manager is an admin page, so it survives a domain or admin-folder rename.
//
// Zen Cart reads manifests only on the Plugin Manager page, but the lookup is still
// guarded so a manifest read anywhere else (no database, catalog side, CLI) just leaves
// the button out. On v2.2 and later this file is re-read on every Plugin Manager visit, so
// the button follows the install state by itself; on v2.0 and v2.1 the installer saves
// the re-read description at Install, Upgrade and Uninstall
// (seRefreshPluginDescription).
//
$seSettingsUrl = '';
if (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG === true && defined('TABLE_ADMIN_PAGES')
    && isset($GLOBALS['db']) && is_object($GLOBALS['db'])) {
    $sePage = $GLOBALS['db']->Execute(
        "SELECT main_page, page_params FROM " . TABLE_ADMIN_PAGES
        . " WHERE page_key = 'configScheduledEvents' LIMIT 1"
    );
    if (!$sePage->EOF && defined($sePage->fields['main_page'])) {
        $seSettingsUrl = 'index.php?cmd=' . constant($sePage->fields['main_page'])
            . '&' . $sePage->fields['page_params'];
    }
}

// -----
// One row: ReadMe | Settings | Forum Thread | GitHub, styled like Plugin Manager's own
// Install / Uninstall buttons. A second line would push the Author line and the core
// buttons further down the panel. Spacing is set inline, which beats any theme .btn
// margin: every gap is 4px (the wrapper supplies the space before the first button, each
// button the space after itself) and the side padding is 8px rather than Bootstrap's
// 12px, so all four fit on one line in the side panel from a 1366px-wide window up.
// Settings opens in the same tab; ReadMe, Forum Thread and GitHub each open a new one.
//
$seButtonGap = '4px';
$seButtonStyle = ' style="margin:0 ' . $seButtonGap . ' 0 0;padding-left:8px;padding-right:8px"';
$seButton = static function ($url, $label, $newTab) use ($seButtonStyle) {
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        . ($newTab ? ' target="_blank" rel="noopener noreferrer"' : '')
        . ' class="btn btn-primary" role="button"' . $seButtonStyle . '>' . $label . '</a>';
};
$seLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $seButtonGap . '">'
    . ($seShowReadme ? $seButton($seReadmeUrl, 'ReadMe', true) : '')
    . ($seSettingsUrl !== '' ? $seButton($seSettingsUrl, 'Settings', false) : '')
    . $seButton($seForumUrl, 'Forum Thread', true)
    . $seButton($seGithubUrl, 'GitHub', true)
    . '</div>';

/**
 * Scheduled Events plugin manifest
 */
return [
    'pluginVersion' => 'v2.0.6',
    'pluginName' => 'Scheduled Events',
    'pluginDescription' => 'Displays upcoming scheduled events (shows, fairs, expos, etc.) on their own storefront page, with an optional link in the Information sidebox or its own sidebox (simple link or slider) placeable via the Layout Boxes Controller. Events are managed from a dedicated admin screen. Replaces the older, non-encapsulated version of this plugin; requires Zen Cart 2.0.0 or later.' . $seLinks,
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    'pluginId' => 860,
    'zcVersions' => ['v200', 'v201', 'v210', 'v220', 'v221', 'v222', 'v223', 'v230', 'v300'],
    'changelog' => 'changelog.txt',
    'github_repo' => $seGithubUrl,
    'pluginGroups' => [],
];
