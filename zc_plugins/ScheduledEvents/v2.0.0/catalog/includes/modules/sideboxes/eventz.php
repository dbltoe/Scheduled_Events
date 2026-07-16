<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: eventz.php 2026-07-16 05:30:22Z dbltoe $
 */
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $template, $current_page_base;

$eventzSideboxMode = defined('SCHEDULED_EVENTS_SIDEBOX_MODE') ? SCHEDULED_EVENTS_SIDEBOX_MODE : 'information';
$eventzStatusEnabled = !defined('SCHEDULED_EVENTS_STATUS') || SCHEDULED_EVENTS_STATUS !== 'False';

// This box only renders itself for mode 'sidebox' (a scrolling carousel -
// requires a Bootstrap-based template, e.g. ZCA Bootstrap or a clone). Mode
// 'information' is handled separately by EventzObserver, which injects a
// link into the core Information sidebox instead. The master
// SCHEDULED_EVENTS_STATUS switch overrides both when set to False.
if ($eventzStatusEnabled && $eventzSideboxMode === 'sidebox') {
    $eventzWindowDays = defined('SCHEDULED_EVENTS_WINDOW_DAYS') ? (int)SCHEDULED_EVENTS_WINDOW_DAYS : 30;
    $eventzMaxItems = defined('SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS') ? (int)SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS : 5;
    $eventzSideboxEvents = EventzService::getQualifyingEvents($eventzWindowDays, $eventzMaxItems);

    // Always show the box (with a "no events" notice when empty), rather
    // than disappearing entirely - consistent with the main events page.
    $title = defined('SCHEDULED_EVENTS_SIDEBOX_TITLE') ? SCHEDULED_EVENTS_SIDEBOX_TITLE : 'Upcoming Events';
    $title_link = zen_href_link('events');

    ob_start();
    require($template->get_template_dir('tpl_eventz.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_eventz.php');
    $content = ob_get_clean();

    require($template->get_template_dir('tpl_box.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_box.php');
}
