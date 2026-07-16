<?php
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $template, $current_page_base;

$eventzSideboxMode = defined('SCHEDULED_EVENTS_SIDEBOX_MODE') ? SCHEDULED_EVENTS_SIDEBOX_MODE : 'none';
$eventzStatusEnabled = !defined('SCHEDULED_EVENTS_STATUS') || SCHEDULED_EVENTS_STATUS !== 'False';

// This box only renders itself for 'link'/'slider'. Mode 'information' is
// handled separately by eventzObserver, which injects a link into the core
// Information sidebox instead; 'none' displays nothing anywhere. The master
// SCHEDULED_EVENTS_STATUS switch overrides all of that when set to False.
if ($eventzStatusEnabled && ($eventzSideboxMode === 'link' || $eventzSideboxMode === 'slider')) {
    $eventzSideboxEvents = [];

    if ($eventzSideboxMode === 'slider') {
        $eventzWindowDays = defined('SCHEDULED_EVENTS_WINDOW_DAYS') ? (int)SCHEDULED_EVENTS_WINDOW_DAYS : 30;
        $eventzMaxItems = defined('SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS') ? (int)SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS : 5;
        $eventzSideboxEvents = EventzService::getQualifyingEvents($eventzWindowDays, $eventzMaxItems);
    }

    // 'link' mode always shows; 'slider' mode only shows when there's something to slide
    if ($eventzSideboxMode === 'link' || !empty($eventzSideboxEvents)) {
        $title = defined('SCHEDULED_EVENTS_SIDEBOX_TITLE') ? SCHEDULED_EVENTS_SIDEBOX_TITLE : 'Upcoming Events';
        $title_link = zen_href_link(FILENAME_DEFAULT, 'main_page=events');

        ob_start();
        require($template->get_template_dir('tpl_eventz.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_eventz.php');
        $content = ob_get_clean();

        require($template->get_template_dir('tpl_box.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_box.php');
    }
}
