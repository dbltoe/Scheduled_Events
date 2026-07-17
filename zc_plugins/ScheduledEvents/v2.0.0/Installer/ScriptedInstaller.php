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
        // No prior released versions yet; upgrades to the table/config/admin
        // page structure will be handled here as new versions are shipped.
        $this->installEventzTable();
        $this->installConfiguration();
        $this->installAdminPage();

        return !$this->errorContainer->hasErrors();
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
            'configuration_description' => 'Master switch for the storefront Scheduled Events page and sidebox. When False, the page redirects to the store home page and the sidebox never displays, regardless of the Sidebox Display Mode setting below. Admin management of events is unaffected either way.',
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

        $this->addConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_MODE', [
            'configuration_title' => 'Sidebox Display Mode',
            'configuration_value' => 'Bootstrap Sidebox',
            'configuration_description' => 'How upcoming events are promoted: "Information Listing" adds a link into the current template\'s existing Information sidebox; "Bootstrap Sidebox" instead shows the plugin\'s own scrolling sidebox, placed via Design > Layout Boxes Controller. <strong>NOTE: "Bootstrap Sidebox" requires a Bootstrap-based template (e.g. ZCA Bootstrap or a clone) - on other templates (e.g. responsive_classic), use "Information Listing" instead.</strong>',
            'configuration_group_id' => $groupId,
            'sort_order' => 105,
            'set_function' => 'zen_cfg_select_option(array(\'Information Listing\', \'Bootstrap Sidebox\'), ',
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_TITLE', [
            'configuration_title' => 'Sidebox Title/Link Text',
            'configuration_value' => 'Upcoming Events',
            'configuration_description' => 'Heading for the "sidebox" mode\'s scrolling box, or link text for "information" mode.',
            'configuration_group_id' => $groupId,
            'sort_order' => 110,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS', [
            'configuration_title' => 'Sidebox Maximum Items',
            'configuration_value' => '5',
            'configuration_description' => 'Maximum number of qualifying events to include in the "sidebox" mode\'s scrolling box.',
            'configuration_group_id' => $groupId,
            'sort_order' => 115,
        ]);

        $this->addConfigurationKey('SCHEDULED_EVENTS_SIDEBOX_NO_EVENTS_TEXT', [
            'configuration_title' => 'Sidebox No Events Text',
            'configuration_value' => 'No events scheduled right now.',
            'configuration_description' => 'Shown in the "sidebox" mode\'s box in place of the carousel when no events currently qualify for display.',
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
        if (!defined('BOX_CATALOG_EVENTZ')) {
            define('BOX_CATALOG_EVENTZ', 'Scheduled Events');
        }

        zen_register_admin_page('eventzList', 'BOX_CATALOG_EVENTZ', 'FILENAME_EVENTZ', '', 'catalog', 'Y');
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
