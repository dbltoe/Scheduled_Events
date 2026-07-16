<?php
/**
 * Injects a Scheduled Events link into the core Information sidebox when
 * SCHEDULED_EVENTS_SIDEBOX_MODE is set to 'information'.
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

        if (!defined('SCHEDULED_EVENTS_SIDEBOX_MODE') || SCHEDULED_EVENTS_SIDEBOX_MODE !== 'information') {
            return;
        }

        if (defined('SCHEDULED_EVENTS_STATUS') && SCHEDULED_EVENTS_STATUS === 'False') {
            return;
        }

        // $p2 is the $information array, passed by reference from information.php
        $information = &$p2;

        $linkText = defined('SCHEDULED_EVENTS_SIDEBOX_TITLE') ? SCHEDULED_EVENTS_SIDEBOX_TITLE : 'Scheduled Events';

        $information[] = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'main_page=events') . '">' . zen_output_string_protected($linkText) . '</a>';
    }
}
