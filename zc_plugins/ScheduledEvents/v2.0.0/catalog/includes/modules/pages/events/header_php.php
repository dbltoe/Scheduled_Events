<?php
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $breadcrumb, $zco_notifier, $eventzEvents, $eventzWindowRange, $eventzWindowDays, $language;

// Defensive fallback: this page's own lang.events.php should auto-load via
// the main_page=events convention, but that hasn't reliably happened on
// every tested store/routing setup. Load it directly if it hasn't already.
if (!defined('NAVBAR_TITLE')) {
    $eventzLanguageDir = $language ?? ($_SESSION['language'] ?? 'english');
    $eventzLangFile = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $eventzLanguageDir . '/lang.events.php';
    $eventzLangFileExists = is_file($eventzLangFile);

    // TEMPORARY diagnostic - remove once the root cause is confirmed.
    trigger_error(
        'Scheduled Events debug: DIR_FS_CATALOG=' . var_export(defined('DIR_FS_CATALOG') ? DIR_FS_CATALOG : null, true)
        . ' DIR_WS_LANGUAGES=' . var_export(defined('DIR_WS_LANGUAGES') ? DIR_WS_LANGUAGES : null, true)
        . ' lang dir=' . var_export($eventzLanguageDir, true)
        . ' computed path=' . var_export($eventzLangFile, true)
        . ' is_file=' . var_export($eventzLangFileExists, true),
        E_USER_WARNING
    );

    if ($eventzLangFileExists) {
        $eventzDefines = require $eventzLangFile;

        // TEMPORARY diagnostic - remove once the root cause is confirmed.
        trigger_error(
            'Scheduled Events debug: require returned ' . gettype($eventzDefines)
            . ', count=' . (is_array($eventzDefines) ? count($eventzDefines) : 'n/a'),
            E_USER_WARNING
        );

        if (is_array($eventzDefines)) {
            foreach ($eventzDefines as $eventzDefineKey => $eventzDefineValue) {
                if (!defined($eventzDefineKey)) {
                    define($eventzDefineKey, $eventzDefineValue);
                }
            }
        }
    }
}

if (defined('SCHEDULED_EVENTS_STATUS') && SCHEDULED_EVENTS_STATUS === 'False') {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

$zco_notifier->notify('NOTIFY_HEADER_START_EVENTS');

$breadcrumb->add(NAVBAR_TITLE);

$eventzWindowDays = defined('SCHEDULED_EVENTS_WINDOW_DAYS') ? (int)SCHEDULED_EVENTS_WINDOW_DAYS : 30;
$eventzEvents = EventzService::getQualifyingEvents($eventzWindowDays);
$eventzWindowRange = EventzService::getWindowDateRange($eventzWindowDays);

$zco_notifier->notify('NOTIFY_HEADER_END_EVENTS');
