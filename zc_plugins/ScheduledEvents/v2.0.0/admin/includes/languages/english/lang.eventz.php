<?php
/**
 * Scheduled Events - admin language file
 *
 * ZC 2.2.x's ArraysLanguageLoader requires this file to `return` a flat
 * CONSTANT_NAME => value array (it calls define() on each pair itself),
 * not call define() directly.
 *
 * FILENAME_EVENTZ lives in the plugin's root filenames.php and
 * BOX_CATALOG_EVENTZ in extra_definitions/lang.eventz_extra_definitions.php -
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

    'ENTRY_EVENTZ_NAME' => 'Name:',
    'ENTRY_EVENTZ_PLACE' => 'Place:',
    'ENTRY_EVENTZ_START_DATE' => 'Start Date:',
    'ENTRY_EVENTZ_STOP_DATE' => 'Stop Date:',
    'ENTRY_EVENTZ_COMMENTS' => 'Comments:',
    'ENTRY_EVENTZ_BOOTH_LOCATION' => 'Booth Location:',
    'ENTRY_EVENTZ_EVENT_INFORMATION' => 'Event Information (URL):',
    'ENTRY_EVENTZ_DRIVING_DIRECTIONS' => 'Driving Directions (address or URL):',

    'TEXT_EVENTZ_NO_EVENTS' => 'No events have been added yet.',
    'TEXT_EVENTZ_DELETE_INTRO' => 'Are you sure you want to delete the following event?',
    'TEXT_EVENTZ_DATE_FORMAT_HELP' => 'Format: YYYY-MM-DD',
    'TEXT_EVENTZ_EVENT_INFO_HELP' => 'Full URL to the event\'s website. Displayed on the storefront as a "More Information" link.',
    'TEXT_EVENTZ_DRIVING_DIRECTIONS_HELP' => 'Either a full address (a Google Maps directions link will be built automatically) or a complete URL.',

    'SUCCESS_EVENTZ_INSERTED' => 'Success: Event has been added.',
    'SUCCESS_EVENTZ_UPDATED' => 'Success: Event has been updated.',
    'SUCCESS_EVENTZ_DELETED' => 'Success: Event has been deleted.',
    'ERROR_EVENTZ_NAME_REQUIRED' => 'Error: Name is required.',
    'ERROR_EVENTZ_PLACE_REQUIRED' => 'Error: Place is required.',
    'ERROR_EVENTZ_START_DATE_INVALID' => 'Error: Start Date is required and must be a valid date (YYYY-MM-DD).',
    'ERROR_EVENTZ_STOP_DATE_INVALID' => 'Error: Stop Date is required and must be a valid date (YYYY-MM-DD).',
    'ERROR_EVENTZ_STOP_BEFORE_START' => 'Error: Stop Date cannot be before Start Date.',
    'ERROR_EVENTZ_NOT_FOUND' => 'Error: Event not found.',

    'IMAGE_EVENTZ_NEW_EVENT' => 'Add Event',
];
