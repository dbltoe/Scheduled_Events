<?php
/**
 * Scheduled Events - admin language file
 *
 * FILENAME_EVENTZ lives in the plugin's root filenames.php and
 * BOX_CATALOG_EVENTZ in extra_definitions/eventz.php - both need to be
 * available admin-wide, not just while this page itself is loaded.
 */
define('HEADING_TITLE_EVENTZ', 'Scheduled Events');
define('HEADING_TITLE_EVENTZ_NEW', 'Add Event');
define('HEADING_TITLE_EVENTZ_EDIT', 'Edit Event');
define('HEADING_TITLE_EVENTZ_DELETE', 'Delete Event');

define('TABLE_HEADING_EVENTZ_NAME', 'Name');
define('TABLE_HEADING_EVENTZ_PLACE', 'Place');
define('TABLE_HEADING_EVENTZ_START_DATE', 'Start Date');
define('TABLE_HEADING_EVENTZ_STOP_DATE', 'Stop Date');
define('TABLE_HEADING_EVENTZ_ACTION', 'Action');

define('ENTRY_EVENTZ_NAME', 'Name:');
define('ENTRY_EVENTZ_PLACE', 'Place:');
define('ENTRY_EVENTZ_START_DATE', 'Start Date:');
define('ENTRY_EVENTZ_STOP_DATE', 'Stop Date:');
define('ENTRY_EVENTZ_COMMENTS', 'Comments:');
define('ENTRY_EVENTZ_BOOTH_LOCATION', 'Booth Location:');
define('ENTRY_EVENTZ_EVENT_INFORMATION', 'Event Information (URL):');
define('ENTRY_EVENTZ_DRIVING_DIRECTIONS', 'Driving Directions (address or URL):');

define('TEXT_EVENTZ_NO_EVENTS', 'No events have been added yet.');
define('TEXT_EVENTZ_DELETE_INTRO', 'Are you sure you want to delete the following event?');
define('TEXT_EVENTZ_DATE_FORMAT_HELP', 'Format: YYYY-MM-DD');
define('TEXT_EVENTZ_EVENT_INFO_HELP', 'Full URL to the event\'s website. Displayed on the storefront as a "' . 'More Information' . '" link.');
define('TEXT_EVENTZ_DRIVING_DIRECTIONS_HELP', 'Either a full address (a Google Maps directions link will be built automatically) or a complete URL.');

define('SUCCESS_EVENTZ_INSERTED', 'Success: Event has been added.');
define('SUCCESS_EVENTZ_UPDATED', 'Success: Event has been updated.');
define('SUCCESS_EVENTZ_DELETED', 'Success: Event has been deleted.');
define('ERROR_EVENTZ_NAME_REQUIRED', 'Error: Name is required.');
define('ERROR_EVENTZ_PLACE_REQUIRED', 'Error: Place is required.');
define('ERROR_EVENTZ_START_DATE_INVALID', 'Error: Start Date is required and must be a valid date (YYYY-MM-DD).');
define('ERROR_EVENTZ_STOP_DATE_INVALID', 'Error: Stop Date is required and must be a valid date (YYYY-MM-DD).');
define('ERROR_EVENTZ_STOP_BEFORE_START', 'Error: Stop Date cannot be before Start Date.');
define('ERROR_EVENTZ_NOT_FOUND', 'Error: Event not found.');

define('IMAGE_EVENTZ_NEW_EVENT', 'Add Event');
