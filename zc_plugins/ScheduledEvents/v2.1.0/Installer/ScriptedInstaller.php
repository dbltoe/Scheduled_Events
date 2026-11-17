<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: ScriptedInstaller.php 2026-07-16 05:30:22Z dbltoe $
 */
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected const CONFIGURATION_GROUP_TITLE = 'Scheduled Events';

    // plugin_control.unique_key for this plugin: its zc_plugins folder name.
    protected const PLUGIN_KEY = 'ScheduledEvents';

    protected function executeInstall(): bool
    {
        $this->installEventzTable();
        $this->installConfiguration();
        $this->installAdminPage();
        $this->seRefreshPluginDescription();

        return !$this->errorContainer->hasErrors();
    }

    protected function executeUninstall(): bool
    {
        $this->uninstallAdminPage();
        $this->uninstallConfiguration();
        $this->uninstallEventzTable();
        $this->uninstallLayoutBox();
        $this->seRefreshPluginDescription();

        return !$this->errorContainer->hasErrors();
    }

    // $oldVersion is optional because ZC 1.5.8 and 2.0.x declare
    // executeUpgrade() with no parameters; a required one here is a fatal
    // signature mismatch the moment the installer class loads, so Install
    // fails there too. 2.1.0+ passes it.
    protected function executeUpgrade($oldVersion = null): bool
    {
        // Written to be self-healing rather than to assume what a given
        // v2.0.0 install actually contains. installEventzTable() below only
        // creates the table when it is missing, so on an upgrade it is a
        // no-op - anything that changed *inside* an existing table or in the
        // configuration/admin-page rows has to be reconciled explicitly here.
        // Each step checks current state first, so running this against an
        // install that is already current changes nothing.
        $this->installEventzTable();
        $this->upgradeEventzTableColumns();
        $this->installConfiguration();
        $this->removeEmptyDuplicateConfigurationGroups();
        $this->removeRetiredConfigurationKeys();
        $this->installAdminPage();
        $this->seRefreshPluginDescription();

        return !$this->errorContainer->hasErrors();
    }

    /**
     * Save the manifest's description again, now that the Settings page has
     * been registered (install, upgrade) or removed (uninstall).
     *
     * Needed on v1.5.8, v2.0 and v2.1, which store plugin_control.description only
     * when Plugin Manager first sees the plugin, before Install, so the
     * Settings button (built in manifest.php from the registered admin page)
     * would never appear there. v2.2 and later rewrite the description from
     * the manifest on every Plugin Manager visit, so this is a no-op there in
     * effect. Core's own install/uninstall step changes only status and
     * version, so this description stays.
     *
     * @return bool
     */
    protected function seRefreshPluginDescription()
    {
        if (!defined('TABLE_PLUGIN_CONTROL')) {
            return true;
        }
        $manifest = require dirname(__DIR__) . '/manifest.php';
        if (!is_array($manifest) || !isset($manifest['pluginDescription'])) {
            return true;
        }

        return $this->executeInstallerSql(
            "UPDATE " . TABLE_PLUGIN_CONTROL . "
                SET description = '" . $this->dbConn->prepare_input($manifest['pluginDescription']) . "'
              WHERE unique_key = '" . $this->dbConn->prepare_input(self::PLUGIN_KEY) . "'
              LIMIT 1"
        );
    }

    /**
     * Bring an existing eventz table up to the current column set.
     *
     * The `active` field (pause an event without deleting it) was added after
     * the first packaged release, and CREATE TABLE IF NOT EXISTS will not add
     * a column to a table that already exists - so without this, upgrading
     * such an install leaves the storefront querying a column that isn't
     * there.
     */
    protected function upgradeEventzTableColumns(): void
    {
        global $sniffer;

        if (!defined('TABLE_EVENTZ')) {
            define('TABLE_EVENTZ', DB_PREFIX . 'eventz');
        }

        if ($sniffer->table_exists(TABLE_EVENTZ) !== true) {
            return;
        }

        if ($sniffer->field_exists(TABLE_EVENTZ, 'active') !== true) {
            $this->executeInstallerSql(
                "ALTER TABLE " . TABLE_EVENTZ . "
                 ADD COLUMN active tinyint(1) NOT NULL DEFAULT 1"
            );
        }
    }

    /**
     * Drop configuration keys that earlier versions created and this one no
     * longer uses, so they don't linger as dead rows in the settings group.
     *
     * SCHEDULED_EVENTS_SIDEBOX_MODE was an either/or dropdown (Information
     * Listing vs Bootstrap Sidebox); it was replaced by the additive
     * SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX switch, with the Information
     * listing now always on.
     */
    protected function removeRetiredConfigurationKeys(): void
    {
        if (!defined('TABLE_CONFIGURATION')) {
            return;
        }

        $retiredKeys = [
            'SCHEDULED_EVENTS_SIDEBOX_MODE',
        ];

        foreach ($retiredKeys as $retiredKey) {
            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key = '" . zen_db_input($retiredKey) . "'"
            );
        }
    }

    protected function installEventzTable(): void
    {
        global $sniffer;

        if (!defined('TABLE_EVENTZ')) {
            define('TABLE_EVENTZ', DB_PREFIX . 'eventz');
        }

        if ($sniffer->table_exists(TABLE_EVENTZ) !== true) {
            $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_EVENTZ . " (
                id int(11) NOT NULL auto_increment,
                name varchar(255) NOT NULL,
                place varchar(255) NOT NULL,
                startDate date NOT NULL,
                stopDate date NOT NULL,
                comments mediumtext,
                boothLocation varchar(255),
                boothLocationUrl varchar(500),
                eventInformation mediumtext,
                eventInformationUrl varchar(500),
                drivingDirections mediumtext,
                active tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $this->executeInstallerSql($sql);

            if ($sniffer->table_exists(TABLE_EVENTZ) !== true) {
                $this->errorContainer->addError(
                    0,
                    'Unable to create table ' . TABLE_EVENTZ,
                    false,
                    defined('TEXT_EVENTZ_INSTALL_ERROR_TABLE')
                        ? TEXT_EVENTZ_INSTALL_ERROR_TABLE
                        : 'Scheduled Events: unable to create the eventz database table.'
                );
            }
        }
    }

    protected function uninstallEventzTable(): void
    {
        if (!defined('TABLE_EVENTZ')) {
            define('TABLE_EVENTZ', DB_PREFIX . 'eventz');
        }

        $this->executeInstallerSql("DROP TABLE IF EXISTS " . TABLE_EVENTZ);
    }

    /**
     * Return the Scheduled Events configuration group's id, creating the
     * group only when there isn't one.
     *
     * Core's addConfigurationGroup() can't be relied on for that: on 2.1.0+
     * it looks the title up as a configuration KEY, never finds the group,
     * and inserts a second one on every upgrade; on 2.0.x it takes the title
     * as a separate first argument and, given no sort order, fails on its
     * own malformed UPDATE ("WHERE  WHERE"). So the group is written here.
     */
    protected function getConfigurationGroupId(): int
    {
        $groupId = $this->findConfigurationGroupId();
        if ($groupId !== 0) {
            return $groupId;
        }

        $this->executeInstallerSql(
            "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
                (configuration_group_title, configuration_group_description, sort_order, visible)
             VALUES
                ('" . zen_db_input(self::CONFIGURATION_GROUP_TITLE) . "', 'Display options, labels, and time-frame settings for the Scheduled Events plugin.', 0, 1)"
        );
        $groupId = $this->findConfigurationGroupId();

        // Same as core when no sort order is given: sort by the new id.
        if ($groupId !== 0) {
            $this->executeInstallerSql(
                "UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = " . $groupId . " WHERE configuration_group_id = " . $groupId
            );
        }
        return $groupId;
    }

    /**
     * Id of the existing Scheduled Events configuration group, or 0. If
     * earlier failed upgrades left duplicates, the group holding the keys wins.
     */
    protected function findConfigurationGroupId(): int
    {
        $check = $this->dbConn->Execute(
            "SELECT g.configuration_group_id
               FROM " . TABLE_CONFIGURATION_GROUP . " g
                    LEFT JOIN " . TABLE_CONFIGURATION . " c ON c.configuration_group_id = g.configuration_group_id
              WHERE g.configuration_group_title = '" . zen_db_input(self::CONFIGURATION_GROUP_TITLE) . "'
              GROUP BY g.configuration_group_id
              ORDER BY COUNT(c.configuration_key) DESC, g.configuration_group_id ASC
              LIMIT 1",
            null, false, 0, true // bypass the per-request query cache: this is re-read right after the insert
        );
        return $check->EOF ? 0 : (int)$check->fields['configuration_group_id'];
    }

    /**
     * Ids of every configuration group carrying this plugin's title, so
     * uninstall also clears any duplicate an earlier failed upgrade left.
     */
    protected function getConfigurationGroupIds(): array
    {
        $ids = [];
        $result = $this->dbConn->Execute(
            "SELECT configuration_group_id
               FROM " . TABLE_CONFIGURATION_GROUP . "
              WHERE configuration_group_title = '" . zen_db_input(self::CONFIGURATION_GROUP_TITLE) . "'",
            null, false, 0, true // bypass the per-request query cache
        );
        while (!$result->EOF) {
            $ids[] = (int)$result->fields['configuration_group_id'];
            $result->MoveNext();
        }
        return $ids;
    }

    /**
     * Drop same-titled configuration groups that hold no keys. Upgrading to
     * v2.0.1 on ZC 2.1.0+ created one and then failed, leaving it behind.
     */
    protected function removeEmptyDuplicateConfigurationGroups(): void
    {
        $keepId = $this->findConfigurationGroupId();
        foreach ($this->getConfigurationGroupIds() as $groupId) {
            if ($groupId === $keepId) {
                continue;
            }
            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_CONFIGURATION_GROUP . "
                  WHERE configuration_group_id = " . $groupId . "
                    AND NOT EXISTS (SELECT 1 FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = " . $groupId . ")"
            );
        }
    }

    protected function installConfiguration(): void
    {
        $groupId = $this->getConfigurationGroupId();

        // Without this, the group has no clickable link under Admin > Configuration.
        // zen_register_admin_page() is a plain INSERT on a unique page_key, so
        // deregister first or an upgrade fails with a duplicate-key error.
        zen_deregister_admin_pages(['configScheduledEvents']);
        zen_register_admin_page('configScheduledEvents', 'BOX_CONFIGURATION_SCHEDULED_EVENTS', 'FILENAME_CONFIGURATION', "gID=$groupId", 'configuration', 'Y');

        $this->seConfigurationKey('SCHEDULED_EVENTS_STATUS', [
            'configuration_title' => 'Enable Scheduled Events Display',
            'configuration_value' => 'True',
            'configuration_description' => 'Master switch for the storefront Scheduled Events page and its promotion. When False, the page redirects to the store home page and no promotional listing displays, regardless of the Events Sidebox setting below. Admin management of events is unaffected either way.',
            'configuration_group_id' => $groupId,
            'sort_order' => 5,
            'set_function' => 'zen_cfg_select_option(array(\'True\', \'False\'), ',
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_PAGE_TITLE', [
            'configuration_title' => 'Page Heading',
            'configuration_value' => 'Scheduled Events',
            'configuration_description' => 'The H1 heading shown at the top of the Scheduled Events storefront page.',
            'configuration_group_id' => $groupId,
            'sort_order' => 10,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_PLACE_LABEL', [
            'configuration_title' => 'Place Label',
            'configuration_value' => 'Place:',
            'configuration_description' => 'Label shown before an event\'s place/location.',
            'configuration_group_id' => $groupId,
            'sort_order' => 20,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_START_DATE_LABEL', [
            'configuration_title' => 'Start Date Label',
            'configuration_value' => 'Start Date:',
            'configuration_description' => 'Label shown before an event\'s start date.',
            'configuration_group_id' => $groupId,
            'sort_order' => 30,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_STOP_DATE_LABEL', [
            'configuration_title' => 'Stop Date Label',
            'configuration_value' => 'Stop Date:',
            'configuration_description' => 'Label shown before an event\'s stop date.',
            'configuration_group_id' => $groupId,
            'sort_order' => 40,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_COMMENTS_LABEL', [
            'configuration_title' => 'Comments Label',
            'configuration_value' => 'Comments:',
            'configuration_description' => 'Label shown before an event\'s comments, when present.',
            'configuration_group_id' => $groupId,
            'sort_order' => 50,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_BOOTH_LOCATION_LABEL', [
            'configuration_title' => 'Booth Location Label',
            'configuration_value' => 'Booth Location:',
            'configuration_description' => 'Label shown before an event\'s booth location, when present.',
            'configuration_group_id' => $groupId,
            'sort_order' => 60,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_EVENT_INFO_LABEL', [
            'configuration_title' => 'Event Information Label',
            'configuration_value' => 'Event Information:',
            'configuration_description' => 'Label shown before the link to the event\'s website (displayed as "More Information").',
            'configuration_group_id' => $groupId,
            'sort_order' => 70,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_DRIVING_DIRECTIONS_LABEL', [
            'configuration_title' => 'Driving Directions Label',
            'configuration_value' => 'Driving Directions:',
            'configuration_description' => 'Label shown before the link to Google Maps driving directions for the event.',
            'configuration_group_id' => $groupId,
            'sort_order' => 80,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_NO_EVENTS_TEXT', [
            'configuration_title' => 'No Events Scheduled Message',
            'configuration_value' => 'No events are scheduled from {from} to {to}.',
            'configuration_description' => 'Shown (as an H2) in place of any event listing when nothing qualifies for display. The {from} and {to} tokens are replaced with the start/end dates of the current display window.',
            'configuration_group_id' => $groupId,
            'sort_order' => 90,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_NO_EVENTS_SUBTEXT', [
            'configuration_title' => 'No Events Scheduled Follow-up Text',
            'configuration_value' => 'Please check back later.',
            'configuration_description' => 'Shown on its own line below the No Events Scheduled Message above.',
            'configuration_group_id' => $groupId,
            'sort_order' => 91,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_WINDOW_DAYS', [
            'configuration_title' => 'Show Events Starting Within (days)',
            'configuration_value' => '30',
            'configuration_description' => 'An event becomes visible this many days before its Start Date, and remains visible through its Stop Date.',
            'configuration_group_id' => $groupId,
            'sort_order' => 100,
            'set_function' => 'zen_cfg_select_option(array(\'30\', \'60\', \'90\'), ',
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX', [
            'configuration_title' => 'Enable Events Sidebox',
            'configuration_value' => 'False',
            'configuration_description' => 'On Zen Cart 2.0.0 and later, a link to the Scheduled Events page is always added to the template\'s Information sidebox. Enabling this also shows the plugin\'s own Events sidebox, once you place it via Design > Layout Boxes Controller. On a Bootstrap-based template (e.g. ZCA Bootstrap or a clone) it scrolls through the upcoming events; on other templates (e.g. responsive_classic) it lists them. <strong>On Zen Cart 1.5.8 the Information sidebox offers no way to add the link, so this box is how customers find the events page there.</strong>',
            'configuration_group_id' => $groupId,
            'sort_order' => 105,
            'set_function' => 'zen_cfg_select_option(array(\'True\', \'False\'), ',
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_TITLE', [
            'configuration_title' => 'Sidebox Title/Link Text',
            'configuration_value' => 'Upcoming Events',
            'configuration_description' => 'Link text for the Information sidebox listing, and also the heading of the Events sidebox if that\'s enabled above.',
            'configuration_group_id' => $groupId,
            'sort_order' => 110,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS', [
            'configuration_title' => 'Sidebox Maximum Items',
            'configuration_value' => '5',
            'configuration_description' => 'Maximum number of qualifying events to include in the Events sidebox, if enabled.',
            'configuration_group_id' => $groupId,
            'sort_order' => 115,
        ]);

        $this->seConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_NO_EVENTS_TEXT', [
            'configuration_title' => 'Sidebox No Events Text',
            'configuration_value' => 'No events scheduled right now.',
            'configuration_description' => 'Shown in the Events sidebox in place of the event listing when no events currently qualify for display.',
            'configuration_group_id' => $groupId,
            'sort_order' => 116,
        ]);
    }

    /**
     * Add one configuration key, or bring an existing one's title,
     * description, group, sort order and set_function up to date. The
     * store's chosen value is never touched on an existing key.
     *
     * Plain SQL rather than core's addConfigurationKey(): that helper
     * (ScriptedInstallHelpers) doesn't exist on Zen Cart 1.5.8, where calling
     * it is a fatal mid-install, and on 2.0+ it skips an existing key
     * entirely, so a reworded setting would never reach an upgraded store.
     *
     * @param array<string, mixed> $props
     */
    protected function seConfigurationKey(string $key, array $props): void
    {
        $db = $this->dbConn;
        $title = $db->prepare_input((string)($props['configuration_title'] ?? ''));
        $description = $db->prepare_input((string)($props['configuration_description'] ?? ''));
        $groupId = (int)($props['configuration_group_id'] ?? 0);
        $sortOrder = (int)($props['sort_order'] ?? 0);
        $setFunction = isset($props['set_function'])
            ? "'" . $db->prepare_input((string)$props['set_function']) . "'"
            : 'NULL';

        $existing = $db->Execute(
            "SELECT configuration_id
               FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key = '" . $db->prepare_input($key) . "'
              LIMIT 1",
            null, false, 0, true // bypass the per-request query cache
        );

        if ($existing->EOF) {
            $this->executeInstallerSql(
                "INSERT INTO " . TABLE_CONFIGURATION . "
                    (configuration_title, configuration_key, configuration_value, configuration_description,
                     configuration_group_id, sort_order, set_function, date_added)
                 VALUES
                    ('" . $title . "', '" . $db->prepare_input($key) . "', '" . $db->prepare_input((string)($props['configuration_value'] ?? '')) . "', '" . $description . "',
                     " . $groupId . ", " . $sortOrder . ", " . $setFunction . ", now())"
            );
            return;
        }

        $this->executeInstallerSql(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_title = '" . $title . "',
                    configuration_description = '" . $description . "',
                    configuration_group_id = " . $groupId . ",
                    sort_order = " . $sortOrder . ",
                    set_function = " . $setFunction . "
              WHERE configuration_id = " . (int)$existing->fields['configuration_id']
        );
    }

    protected function uninstallConfiguration(): void
    {
        // Plain SQL rather than core's deleteConfigurationGroup(), which on
        // 2.0.x takes only a numeric id and never removes the group's keys.
        foreach ($this->getConfigurationGroupIds() as $groupId) {
            $this->executeInstallerSql("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = " . $groupId);
            $this->executeInstallerSql("DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = " . $groupId);
        }
    }

    protected function installAdminPage(): void
    {
        // filenames.php only auto-loads admin-wide since ZC 2.2.0; guard here
        // too so registration resolves correctly on the 1.5.8, 2.0.x and
        // 2.1.x installs this plugin also declares support for.
        if (!defined('FILENAME_EVENTZ')) {
            define('FILENAME_EVENTZ', 'eventz.php');
        }
        if (!defined('BOX_EXTRAS_EVENTZ')) {
            define('BOX_EXTRAS_EVENTZ', 'Scheduled Events');
        }

        // Deregister before registering so this is idempotent. It matters on
        // upgrade: this page used to live under Catalog, and re-registering
        // over an existing row would otherwise leave the old menu placement
        // (and its now-wrong BOX_CATALOG_EVENTZ language key) in place. On a
        // fresh install there is nothing to remove and this is a no-op.
        zen_deregister_admin_pages(['eventzList']);

        zen_register_admin_page('eventzList', 'BOX_EXTRAS_EVENTZ', 'FILENAME_EVENTZ', '', 'extras', 'Y');
    }

    protected function uninstallAdminPage(): void
    {
        zen_deregister_admin_pages([
            'eventzList',
            'configScheduledEvents',
        ]);
    }

    protected function uninstallLayoutBox(): void
    {
        // The "eventz" sidebox self-registers a layout_boxes row the first time
        // Design > Layout Boxes Controller runs; clean it up on uninstall so it
        // doesn't linger referencing a now-missing box file.
        // Core stores the box under its file name, "eventz.php" (as it does
        // "information.php"); before v2.1.0 only "eventz" was removed, which
        // matched nothing, so the placement row outlived every uninstall.
        if (defined('TABLE_LAYOUT_BOXES')) {
            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_LAYOUT_BOXES . " WHERE layout_box_name IN ('eventz.php', 'eventz')"
            );
        }
    }
}
