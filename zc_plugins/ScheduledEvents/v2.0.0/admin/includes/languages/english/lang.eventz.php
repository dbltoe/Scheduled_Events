<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: lang.eventz.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Scheduled Events - admin language file
 *
 * ZC 2.2.x's ArraysLanguageLoader requires this file to `return` a flat
 * CONSTANT_NAME => value array (it calls define() on each pair itself),
 * not call define() directly.
 *
 * FILENAME_EVENTZ lives in the plugin's root filenames.php and
 * BOX_EXTRAS_EVENTZ in extra_definitions/lang.eventz_extra_definitions.php -
 * both need to be available admin-wide, not just while this page itself is
 * loaded.
 */
return [
    'HEADING_TITLE_EVENTZ' => 'Scheduled Events',
    'HEADING_TITLE_EVENTZ_NEW' => 'Add Event',
    'HEADING_TITLE_EVENTZ_EDIT' => 'Edit Event',
    'HEADING_TITLE_EVENTZ_DELETE' => 'Delete Event',

    'TABLE_HEADING_EVENTZ_NAME' => 'Name',
    'TABLE_HEADING_EVENTZ_PLACE' => 'Place',
    'TABLE_HEADING_EVENTZ_START_DATE' => 'Start Date',
    'TABLE_HEADING_EVENTZ_STOP_DATE' => 'Stop Date',
    'TABLE_HEADING_EVENTZ_ACTION' => 'Action',
    'TABLE_HEADING_EVENTZ_STATUS' => 'Status',

    'ENTRY_EVENTZ_NAME' => 'Name:',
    'ENTRY_EVENTZ_PLACE' => 'Place:',
    'ENTRY_EVENTZ_START_DATE' => 'Start Date:',
    'ENTRY_EVENTZ_STOP_DATE' => 'Stop Date:',
    'ENTRY_EVENTZ_COMMENTS' => 'Comments:',
    'ENTRY_EVENTZ_BOOTH_LOCATION' => 'Booth Location:',
    'ENTRY_EVENTZ_BOOTH_LOCATION_URL' => 'Booth Location Map URL:',
    'ENTRY_EVENTZ_EVENT_INFORMATION' => 'Event Information:',
    'ENTRY_EVENTZ_EVENT_INFORMATION_URL' => 'Event Information URL:',
    'ENTRY_EVENTZ_DRIVING_DIRECTIONS' => 'Driving Directions (address or URL):',
    'ENTRY_EVENTZ_ACTIVE' => 'Active:',

    'TEXT_EVENTZ_BOOTH_LOCATION_HELP' => 'Plain text describing the booth location (e.g. "Space #16"). This text becomes a link if a Map URL is also given below.',
    'TEXT_EVENTZ_BOOTH_LOCATION_URL_HELP' => 'Optional. A URL to a map/floor plan, or a path to an image already on this store. If uploaded via the admin Image Manager, use /images/mapName.jpg; if placed directly in your template\'s own images folder, use /includes/templates/your_template/images/mapName.jpg instead. Test the path by opening it in a browser first (yourdomain.com/images/mapName.jpg) - a bad path is silently ignored, not shown as an error. If valid, the Booth Location text above becomes a link to it (opens in a new tab); otherwise it\'s ignored and the text displays as-is.',
    'TEXT_EVENTZ_ACTIVE_HELP' => 'Uncheck to pause this event - it will be hidden from the storefront page and sidebox, without deleting it or changing its dates. Paused events remain visible here via "Show All".',
    'TEXT_EVENTZ_NO_EVENTS' => 'No Events Have Been Added Yet.',
    'TEXT_EVENTZ_NO_ACTIVE_EVENTS' => 'No Active Events Have Been Added Yet.',
    'TEXT_EVENTZ_SHOW_ALL' => 'Show All (including paused events)',
    'TEXT_EVENTZ_SHOW_ACTIVE_ONLY' => 'Show Active Only',
    'TEXT_EVENTZ_STATUS_ACTIVE' => 'Active',
    'TEXT_EVENTZ_STATUS_INACTIVE' => 'Paused',
    'TEXT_EVENTZ_DELETE_INTRO' => 'Are you sure you want to delete the following event?',
    'TEXT_EVENTZ_REQUIRED_INFORMATION' => 'Required Information',
    'TEXT_EVENTZ_SORT_BY' => 'Sort By:',
    'TEXT_EVENTZ_DATE_FORMAT_HELP' => 'Format: YYYY-MM-DD',
    'TEXT_EVENTZ_EVENT_INFO_HELP' => 'Plain text describing the event information (e.g. "Visit the official show website"). This text becomes a link if an Information URL is also given below.',
    'TEXT_EVENTZ_EVENT_INFO_URL_HELP' => 'Optional. A URL to the event\'s website. If valid, the Event Information text above becomes a link to it (opens in a new tab); otherwise it\'s ignored and the text displays as-is.',
    'TEXT_EVENTZ_DRIVING_DIRECTIONS_HELP' => 'A plain address or Google Maps link opens Google Maps in a new tab (no map shown here). A Google Maps "Embed a map" &lt;iframe&gt; code instead opens as an on-page pop-up map.',

    'SUCCESS_EVENTZ_INSERTED' => 'Success: Event has been added.',
    'SUCCESS_EVENTZ_UPDATED' => 'Success: Event has been updated.',
    'SUCCESS_EVENTZ_DELETED' => 'Success: Event has been deleted.',
    'ERROR_EVENTZ_NAME_REQUIRED' => 'Error: Name is required.',
    'ERROR_EVENTZ_PLACE_REQUIRED' => 'Error: Place is required.',
    'ERROR_EVENTZ_BOOTH_LOCATION_REQUIRED' => 'Error: Booth Location is required.',
    'ERROR_EVENTZ_START_DATE_INVALID' => 'Error: Start Date is required and must be a valid date (YYYY-MM-DD).',
    'ERROR_EVENTZ_STOP_DATE_INVALID' => 'Error: Stop Date is required and must be a valid date (YYYY-MM-DD).',
    'ERROR_EVENTZ_STOP_BEFORE_START' => 'Error: Stop Date cannot be before Start Date.',
    'ERROR_EVENTZ_NOT_FOUND' => 'Error: Event not found.',

    'IMAGE_EVENTZ_NEW_EVENT' => 'Add Event',
];
