<?php
/**
 * Provides inline modal help content for the Scheduled Events admin page.
 */
class EventzAdminObserver extends base
{
    public function __construct()
    {
        $this->attach($this, ['NOTIFIER_PLUGIN_HELP_PAGE_URL_LOOKUP']);
    }

    protected function update(&$class, $eventID, $page, &$help_page)
    {
        if ($eventID !== 'NOTIFIER_PLUGIN_HELP_PAGE_URL_LOOKUP' || $page !== FILENAME_EVENTZ) {
            return;
        }

        $help_page = [
            'body' => '<h4>' . HEADING_TITLE_EVENTZ . '</h4>'
                . '<p>Add, edit, or delete events from the list on this page. Each event needs a Name, Place, Start Date, and Stop Date; Comments, Booth Location, Event Information, and Driving Directions are optional.</p>'
                . '<p>Event Information should be the event\'s website URL. Driving Directions can be a plain address (a Google Maps link is built automatically) or a full URL.</p>'
                . '<p>Whether and how upcoming events are shown on the storefront &mdash; the page heading, field labels, the display window (30/60/90 days), and the sidebox mode &mdash; is controlled under Configuration &gt; Scheduled Events.</p>',
        ];
    }
}
