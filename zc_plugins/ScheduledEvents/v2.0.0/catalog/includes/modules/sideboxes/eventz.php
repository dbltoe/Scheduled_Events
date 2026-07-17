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

$eventzAdditionalSideboxEnabled = defined('SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX') && SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX === 'True';
$eventzStatusEnabled = !defined('SCHEDULED_EVENTS_STATUS') || SCHEDULED_EVENTS_STATUS !== 'False';

// This box only renders itself when SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX is
// enabled - a second, separate promotional listing (a scrolling carousel -
// requires a Bootstrap-based template, e.g. ZCA Bootstrap or a clone) in
// addition to the Information sidebox link that EventzObserver always adds.
// The master SCHEDULED_EVENTS_STATUS switch overrides both when set to
// False. Also suppressed on the events page itself - pointless to promote a
// page while already viewing it.
if ($eventzStatusEnabled && $eventzAdditionalSideboxEnabled && $current_page_base !== 'events') {
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

    // This box can appear on any storefront page, but eventz.css is only
    // otherwise linked from tpl_events_default.php - echo it here too so its
    // styling (and the relocation script below) works wherever the box
    // actually renders.
    echo '<link rel="stylesheet" href="/zc_plugins/ScheduledEvents/v2.0.0/catalog/includes/templates/default/css/eventz.css">';

    // Deliberately not requiring core's tpl_box.php generic box wrapper: on
    // this store, its fallback path (a "template_default" directory) doesn't
    // exist for the active Bootstrap-based template, causing a fatal error.
    // Building the box shell directly here instead - but using the exact
    // same classes as core sideboxes (e.g. Information, Bestsellers): "card
    // mb-3" plus "leftBoxCard"/"rightBoxCard" on the container, and
    // "card-header" plus "leftBoxHeading"/"rightBoxHeading" on the heading.
    // The latter pair is what this template's own stylesheet_zca_colors.css
    // actually themes (via CSS custom properties like
    // --zca_sidebox_header_background_color) - "card"/"card-header" alone
    // only gets Bootstrap's generic default styling, not this store's theme
    // colors. Both leftBox*/rightBox* variants are included since this box
    // may be placed in either column and they're styled identically.
    echo '<div class="eventzSideboxContainer leftBoxCard rightBoxCard card mb-3">';
    echo '<h4 class="eventzSideboxHeading leftBoxHeading rightBoxHeading card-header">' . zen_output_string_protected($title) . '</h4>';
    echo '<div class="eventzSideboxContent sideBoxContent">' . $content . '</div>';
    echo '</div>';

    // The column this box lives in (placed via Layout Boxes Controller) is
    // typically hidden below the "lg" breakpoint on Bootstrap-based
    // templates (e.g. class "d-none d-lg-block"), which hides this box along
    // with everything else in that column on phones/tablets. Rather than
    // touch the template's own column markup (which varies by template and
    // isn't something this plugin controls), relocate just this box into the
    // same Bootstrap ".row" as a full-width column on narrow viewports, and
    // move it back to its original spot above the "lg" breakpoint.
    echo '<script>
(function () {
    var eventzBox = document.querySelector(".eventzSideboxContainer");
    if (!eventzBox) { return; }

    var eventzOriginalParent = eventzBox.parentNode;
    var eventzOriginalNext = eventzBox.nextSibling;
    var eventzRow = eventzBox.closest(".row");
    if (!eventzRow) { return; }

    var eventzMobileQuery = window.matchMedia("(max-width: 991.98px)");

    function eventzPlaceSidebox(query) {
        if (query.matches) {
            eventzBox.classList.add("col-12", "eventzSideboxMobile");
            eventzRow.appendChild(eventzBox);
        } else {
            eventzBox.classList.remove("col-12", "eventzSideboxMobile");
            eventzOriginalParent.insertBefore(eventzBox, eventzOriginalNext);
        }
    }

    eventzPlaceSidebox(eventzMobileQuery);
    if (eventzMobileQuery.addEventListener) {
        eventzMobileQuery.addEventListener("change", eventzPlaceSidebox);
    } else {
        eventzMobileQuery.addListener(eventzPlaceSidebox);
    }
})();
</script>';
}
