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

global $current_page_base;

$eventzSideboxMode = defined('SCHEDULED_EVENTS_SIDEBOX_MODE') ? SCHEDULED_EVENTS_SIDEBOX_MODE : 'Information Listing';
$eventzStatusEnabled = !defined('SCHEDULED_EVENTS_STATUS') || SCHEDULED_EVENTS_STATUS !== 'False';

// This box only renders itself for mode 'Bootstrap Sidebox' (a scrolling
// carousel - requires a Bootstrap-based template, e.g. ZCA Bootstrap or a
// clone). Mode 'Information Listing' is handled separately by
// EventzObserver, which injects a link into the core Information sidebox
// instead. The master SCHEDULED_EVENTS_STATUS switch overrides both when
// set to False. Also suppressed on the events page itself - pointless to
// promote a page while already viewing it.
if ($eventzStatusEnabled && $eventzSideboxMode === 'Bootstrap Sidebox' && $current_page_base !== 'events') {
    $eventzWindowDays = defined('SCHEDULED_EVENTS_WINDOW_DAYS') ? (int)SCHEDULED_EVENTS_WINDOW_DAYS : 30;
    $eventzMaxItems = defined('SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS') ? (int)SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS : 5;
    $eventzSideboxEvents = EventzService::getQualifyingEvents($eventzWindowDays, $eventzMaxItems);

    // Always show the box (with a "no events" notice when empty), rather
    // than disappearing entirely - consistent with the main events page.
    $title = defined('SCHEDULED_EVENTS_SIDEBOX_TITLE') ? SCHEDULED_EVENTS_SIDEBOX_TITLE : 'Upcoming Events';
    $title_link = zen_href_link('events');

    ob_start();
    require __DIR__ . '/../../templates/default/sideboxes/tpl_eventz.php';
    $content = ob_get_clean();

    // Deliberately not requiring core's tpl_box.php generic box wrapper: on
    // this store, its fallback path (a "template_default" directory) doesn't
    // exist for the active Bootstrap-based template, causing a fatal error.
    // Building the box shell directly here, styled by our own eventz.css,
    // sidesteps that entirely and works on any template.
    echo '<div class="eventzSideboxContainer">';
    echo '<h3 class="eventzSideboxHeading">' . zen_output_string_protected($title) . '</h3>';
    echo '<div class="eventzSideboxContent">' . $content . '</div>';
    echo '</div>';
}
