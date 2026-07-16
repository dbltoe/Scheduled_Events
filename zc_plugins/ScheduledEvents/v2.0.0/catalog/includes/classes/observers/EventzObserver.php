<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: EventzObserver.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Injects a Scheduled Events link into the core Information sidebox when
 * SCHEDULED_EVENTS_SIDEBOX_MODE is 'information'. Mode 'sidebox' instead
 * shows the plugin's own scrolling carousel box - see catalog/includes/
 * modules/sideboxes/eventz.php.
 */
class EventzObserver extends base
{
    public function __construct()
    {
        $this->attach($this, ['NOTIFY_INFORMATION_SIDEBOX_ADDITIONS']);
    }

    protected function update(&$class, $eventID, $p1, &$p2, &$p3 = null, &$p4 = null, &$p5 = null)
    {
        if ($eventID !== 'NOTIFY_INFORMATION_SIDEBOX_ADDITIONS') {
            return;
        }

        if (defined('SCHEDULED_EVENTS_STATUS') && SCHEDULED_EVENTS_STATUS === 'False') {
            return;
        }

        $eventzSideboxMode = defined('SCHEDULED_EVENTS_SIDEBOX_MODE') ? SCHEDULED_EVENTS_SIDEBOX_MODE : 'information';
        if ($eventzSideboxMode !== 'information') {
            return;
        }

        global $current_page_base;
        if ($current_page_base === 'events') {
            // Pointless to promote the events page while already viewing it.
            return;
        }

        // $p2 is the $information array, passed by reference from information.php
        $information = &$p2;

        $linkText = defined('SCHEDULED_EVENTS_SIDEBOX_TITLE') ? SCHEDULED_EVENTS_SIDEBOX_TITLE : 'Scheduled Events';

        // list-group-item/list-group-item-action match the other Information
        // sidebox links on Bootstrap-based templates (e.g. ZCA Bootstrap); on
        // non-Bootstrap templates (e.g. responsive_classic) these classes have
        // no matching CSS and are simply ignored, so this is safe either way.
        $information[] = '<a class="list-group-item list-group-item-action" href="' . zen_href_link('events') . '">' . zen_output_string_protected($linkText) . '</a>';
    }
}
