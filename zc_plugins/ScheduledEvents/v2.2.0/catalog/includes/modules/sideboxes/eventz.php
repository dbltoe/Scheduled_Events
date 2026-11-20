<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: eventz.php 2026-07-16 05:30:22Z dbltoe $
 */
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $current_page_base;

$eventzAdditionalSideboxEnabled = defined('SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX') && SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX === 'True';
$eventzStatusEnabled = !defined('SCHEDULED_EVENTS_STATUS') || SCHEDULED_EVENTS_STATUS !== 'False';

// This box only renders itself when SCHEDULED_EVENTS_ADDITIONAL_SIDEBOX
// ("Enable Events Sidebox") is on: a separate promotional listing in
// addition to the Information sidebox link that EventzObserver adds on Zen
// Cart 2.0.0 and later. On 1.5.8, whose Information sidebox has no hook for
// that link, this box is the storefront's way to the events page. It scrolls
// on a Bootstrap-based template (e.g. ZCA Bootstrap or a clone, Bootstrap 4
// or 5) and is a plain list elsewhere (e.g. responsive_classic).
// The master SCHEDULED_EVENTS_STATUS switch overrides both when set to
// False. Also suppressed on the events page itself - pointless to promote a
// page while already viewing it.
if ($eventzStatusEnabled && $eventzAdditionalSideboxEnabled && $current_page_base !== 'events') {
    $eventzWindowDays = defined('SCHEDULED_EVENTS_WINDOW_DAYS') ? (int)SCHEDULED_EVENTS_WINDOW_DAYS : 30;
    $eventzMaxItems = defined('SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS') ? (int)SCHEDULED_EVENTS_SIDEBOX_MAX_ITEMS : 5;
    $eventzSideboxEvents = EventzService::getQualifyingEvents($eventzWindowDays, $eventzMaxItems);

    // Always show the box (with a "no events" notice when empty), rather
    // than disappearing entirely - consistent with the main events page.
    $eventzTitle = defined('SCHEDULED_EVENTS_SIDEBOX_TITLE') ? SCHEDULED_EVENTS_SIDEBOX_TITLE : 'Upcoming Events';
    // tpl_eventz.php links each event to its place on the events page.
    $title_link = zen_href_link('events');

    ob_start();
    require __DIR__ . '/../../templates/default/sideboxes/tpl_eventz.php';
    $content = '<div class="eventzSideboxContent sideBoxContent">' . ob_get_clean() . '</div>';

    // This box can appear on any storefront page, but eventz.css is only
    // otherwise linked from tpl_events_default.php - echo it here too so its
    // styling (and the scripts below) works wherever the box actually
    // renders.
    echo EventzService::styles();

    // The box's frame comes from the active template's own sidebox wrapper
    // (tpl_box_default_left.php or _right.php, whichever column it sits in),
    // exactly as core's sideboxes do, so it matches the template: an h3 box
    // on responsive_classic, a themed card on ZCA Bootstrap, and whatever a
    // Bootstrap 5 or custom template uses. Like core, the heading links to
    // the events page (with the template's "[more]"), so the page is reachable
    // from here even when no event currently qualifies.
    global $template, $column_box_default, $box_id, $column_width;
    $eventzBoxFile = '';
    if (isset($template, $column_box_default) && is_object($template) && is_string($column_box_default)) {
        $eventzBoxFile = $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
    }

    if ($eventzBoxFile !== '' && is_file($eventzBoxFile)) {
        $title = zen_output_string_protected($eventzTitle);
        $title_link = 'events';
        require $eventzBoxFile;
    } else {
        // Fallback, for a store where the wrapper can't be found (one
        // tested store had no template_default folder): the box frame is
        // built here with the classes core's Bootstrap sideboxes use.
        echo '<div class="eventzSideboxContainer leftBoxCard rightBoxCard card mb-3">';
        echo '<h4 class="eventzSideboxHeading leftBoxHeading rightBoxHeading card-header"><a href="' . zen_href_link('events') . '">' . zen_output_string_protected($eventzTitle) . '</a></h4>';
        echo $content;
        echo '</div>';
    }

    // The scripts below find the box from its content, whichever frame it
    // has, and mark it so eventz.css and the relocation can target it.
    echo '<script>
(function () {
    var eventzContent = document.querySelector(".eventzSideboxContent");
    if (!eventzContent) { return; }
    var eventzBox = eventzContent.closest(".eventzSideboxContainer, .card, .leftBoxContainer, .rightBoxContainer") || eventzContent;
    eventzBox.classList.add("eventzSideboxContainer");
})();
</script>';

    // A template without Bootstrap's carousel (e.g. responsive_classic, and
    // every 1.5.8 store that kept it) would show dead Previous/Next buttons,
    // or with Bootstrap's CSS but not its script, only the first event. Once
    // the page has loaded, check for the carousel script; without it, mark
    // the box so eventz.css shows the events as a plain list instead.
    echo '<script>
(function () {
    function eventzCheckCarousel() {
        var eventzBox = document.querySelector(".eventzSideboxContainer");
        if (!eventzBox || !eventzBox.querySelector(".eventzCarousel")) { return; }
        var eventzHasCarousel = (window.bootstrap && window.bootstrap.Carousel)
            || (window.jQuery && window.jQuery.fn && window.jQuery.fn.carousel);
        if (!eventzHasCarousel) {
            eventzBox.classList.add("eventzNoCarousel");
        }
    }
    if (document.readyState === "complete") {
        eventzCheckCarousel();
    } else {
        window.addEventListener("load", eventzCheckCarousel);
    }
})();
</script>';

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
