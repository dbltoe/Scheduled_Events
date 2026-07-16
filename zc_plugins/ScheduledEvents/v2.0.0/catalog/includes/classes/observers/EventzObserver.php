<?php
/**
 * Injects a Scheduled Events link into the core Information sidebox.
 *
 * The plugin's own standalone sidebox (simple link / slider, placed via
 * Layout Boxes Controller) is on hold for now - see catalog/includes/
 * modules/sideboxes/eventz.php.disabled and tpl_eventz.php.disabled for the
 * removed implementation, kept for when those options come back.
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

        // $p2 is the $information array, passed by reference from information.php
        $information = &$p2;

        $linkText = defined('SCHEDULED_EVENTS_SIDEBOX_TITLE') ? SCHEDULED_EVENTS_SIDEBOX_TITLE : 'Scheduled Events';

        $information[] = '<a href="' . zen_href_link('events') . '">' . zen_output_string_protected($linkText) . '</a>';
    }
}
