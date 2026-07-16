<?php
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $breadcrumb, $zco_notifier, $eventzEvents, $eventzWindowRange, $eventzWindowDays, $language;

// Defensive fallback: this page's own lang.events.php should auto-load via
// the main_page=events convention, but that hasn't reliably happened on
// every tested store/routing setup. Load it directly if it hasn't already.
// Plugin files stay inside zc_plugins/... permanently (never copied into
// includes/languages/), so __DIR__ - a known-fixed path relative to this
// file's own real location - is used instead of any of Zen Cart's
// DIR_FS_*/DIR_WS_* constants, which proved unreliable here.
if (!defined('NAVBAR_TITLE')) {
    $eventzLanguageDir = $language ?? ($_SESSION['language'] ?? 'english');
    $eventzLangFile = __DIR__ . '/../../../languages/' . $eventzLanguageDir . '/lang.events.php';

    if (is_file($eventzLangFile)) {
        $eventzDefines = require $eventzLangFile;
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
