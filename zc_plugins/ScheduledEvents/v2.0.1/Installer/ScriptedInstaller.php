<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: ScriptedInstaller.php 2026-07-16 05:30:22Z dbltoe $
 */
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall(): bool
    {
        $this->installEventzTable();
        $this->installConfiguration();
        $this->installAdminPage();

        return !$this->errorContainer->hasErrors();
    }

    protected function executeUninstall(): bool
    {
        $this->uninstallAdminPage();
        $this->uninstallConfiguration();
        $this->uninstallEventzTable();
        $this->uninstallLayoutBox();

        return !$this->errorContainer->hasErrors();
    }

    protected function executeUpgrade($oldVersion): bool
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
        $this->removeRetiredConfigurationKeys();
        $this->installAdminPage();

        return !$this->errorContainer->hasErrors();
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
                    TEXT_EVENTZ_INSTALL_ERROR_TABLE
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

    protected function installConfiguration(): void
    {
        $groupId = $this->addConfigurationGroup([
            'configuration_group_title' => 'Scheduled Events',
            'configuration_group_description' => 'Display options, labels, and time-frame settings for the Scheduled Events plugin.',
            'sort_order' => 0,
            'visible' => 1,
        ]);

        // Without this, the group has no clickable link under Admin > Configuration.
        zen_register_admin_page('configScheduledEvents', 'BOX_CONFIGURATION_SCHEDULED_EVENTS', 'FILENAME_CONFIGURATION', "gID=$groupId", 'configuration', 'Y');

        $this->addConfigurationKey('SCHEDULED_EVENTS_STATUS', [
            'configuration_title' => 'Enable Scheduled Events Display',
            'configuration_value' => 'True',
            'configuration_description' => 'Master switch for the storefront Scheduled Events page and its promotion. When False, the page redirects to the store home page and no promotional listing displays, regardless of the Additional Bootstrap Sidebox setting below. Admin management of events is unaffected either way.',
            'configuration_group_id' => $groupId,
            'sort_order' => 5,
            'set_function' => 'zen_cfg_select_option(array(\'True\', \'False\'), ',
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_PAGE_TITLE', [
            'configuration_title' => 'Page Heading',
            'configuration_value' => 'Scheduled Events',
            'configuration_description' => 'The H1 heading shown at the top of the Scheduled Events storefront page.',
            'configuration_group_id' => $groupId,
            'sort_order' => 10,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_PLACE_LABEL', [
            'configuration_title' => 'Place Label',
            'configuration_value' => 'Place:',
            'configuration_description' => 'Label shown before an event\'s place/location.',
            'configuration_group_id' => $groupId,
            'sort_order' => 20,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_START_DATE_LABEL', [
            'configuration_title' => 'Start Date Label',
            'configuration_value' => 'Start Date:',
            'configuration_description' => 'Label shown before an event\'s start date.',
            'configuration_group_id' => $groupId,
            'sort_order' => 30,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_STOP_DATE_LABEL', [
            'configuration_title' => 'Stop Date Label',
            'configuration_value' => 'Stop Date:',
            'configuration_description' => 'Label shown before an event\'s stop date.',
            'configuration_group_id' => $groupId,
            'sort_order' => 40,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_COMMENTS_LABEL', [
            'configuration_title' => 'Comments Label',
            'configuration_value' => 'Comments:',
            'configuration_description' => 'Label shown before an event\'s comments, when present.',
            'configuration_group_id' => $groupId,
            'sort_order' => 50,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_BOOTH_LOCATION_LABEL', [
            'configuration_title' => 'Booth Location Label',
            'configuration_value' => 'Booth Location:',
            'configuration_description' => 'Label shown before an event\'s booth location, when present.',
            'configuration_group_id' => $groupId,
            'sort_order' => 60,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_EVENT_INFO_LABEL', [
            'configuration_title' => 'Event Information Label',
            'configuration_value' => 'Event Information:',
            'configuration_description' => 'Label shown before the link to the event\'s website (displayed as "More Information").',
            'configuration_group_id' => $groupId,
            'sort_order' => 70,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_DRIVING_DIRECTIONS_LABEL', [
            'configuration_title' => 'Driving Directions Label',
            'configuration_value' => 'Driving Directions:',
            'configuration_description' => 'Label shown before the link to Google Maps driving directions for the event.',
            'configuration_group_id' => $groupId,
            'sort_order' => 80,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_NO_EVENTS_TEXT', [
            'configuration_title' => 'No Events Scheduled Message',
            'configuration_value' => 'No events are scheduled from {from} to {to}.',
            'configuration_description' => 'Shown (as an H2) in place of any event listing when nothing qualifies for display. The {from} and {to} tokens are replaced with the start/end dates of the current display window.',
            'configuration_group_id' => $groupId,
            'sort_order' => 90,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_NO_EVENTS_SUBTEXT', [
            'configuration_title' => 'No Events Scheduled Follow-up Text',
            'configuration_value' => 'Please check back later.',
            'configuration_description' => 'Shown on its own line below the No Events Scheduled Message above.',
            'configuration_group_id' => $groupId,
            'sort_order' => 91,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_WINDOW_DAYS', [
            'configuration_title' => 'Show Events Starting Within (days)',
            'configuration_value' => '30',
            'configuration_description' => 'An event becomes visible this many days before its Start Date, and remains visible through its Stop Date.',
            'configuration_group_id' => $groupId,
            'sort_order' => 100,
            'set_function' => 'zen_cfg_select_option(array(\'30\', \'60\', \'90\'), ',
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX', [
            'configuration_title' => 'Enable Additional Bootstrap Sidebox',
            'configuration_value' => 'False',
            'configuration_description' => 'A link to the Scheduled Events page is always added into the current template\'s existing Information sidebox. Enabling this adds a second, separate promotional listing: the plugin\'s own auto-scrolling sidebox, placed via Design > Layout Boxes Controller. <strong>NOTE: Only enable this if your template is Bootstrap-based (e.g. ZCA Bootstrap or a clone) and you want a second listing location - it has no effect on other templates (e.g. responsive_classic).</strong>',
            'configuration_group_id' => $groupId,
            'sort_order' => 105,
            'set_function' => 'zen_cfg_select_option(array(\'True\', \'False\'), ',
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_TITLE', [
            'configuration_title' => 'Sidebox Title/Link Text',
            'configuration_value' => 'Upcoming Events',
            'configuration_description' => 'Link text for the Information sidebox listing, and also the heading for the additional Bootstrap sidebox if that\'s enabled above.',
            'configuration_group_id' => $groupId,
            'sort_order' => 110,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS', [
            'configuration_title' => 'Sidebox Maximum Items',
            'configuration_value' => '5',
            'configuration_description' => 'Maximum number of qualifying events to include in the additional Bootstrap sidebox\'s scrolling box, if enabled.',
            'configuration_group_id' => $groupId,
            'sort_order' => 115,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_NO_EVENTS_TEXT', [
            'configuration_title' => 'Sidebox No Events Text',
            'configuration_value' => 'No events scheduled right now.',
            'configuration_description' => 'Shown in the additional Bootstrap sidebox in place of the carousel when no events currently qualify for display.',
            'configuration_group_id' => $groupId,
            'sort_order' => 116,
        ]);
    }

    protected function uninstallConfiguration(): void
    {
        // deleteAllKeysInGroup=true removes every key belonging to the group.
        $this->deleteConfigurationGroup('Scheduled Events', true);
    }

    protected function installAdminPage(): void
    {
        // filenames.php only auto-loads admin-wide since ZC 2.2.0; guard here
        // too so registration resolves correctly on the 2.0.0/2.1.0 installs
        // this plugin also declares support for.
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
        if (defined('TABLE_LAYOUT_BOXES')) {
            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_LAYOUT_BOXES . " WHERE layout_box_name = 'eventz'"
            );
        }
    }
}
