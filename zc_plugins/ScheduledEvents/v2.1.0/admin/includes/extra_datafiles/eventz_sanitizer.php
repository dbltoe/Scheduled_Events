<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: eventz_sanitizer.php 2026-11-16 dbltoe $
 */

/**
 * Scheduled Events - how Zen Cart's admin request sanitizer treats the
 * event form's fields (Extras > Scheduled Events only).
 *
 * Every admin request passes through AdminRequestSanitizer. A field nobody
 * registers gets its strictest treatment, HTML-escaping, so an event at
 * "Smith & Sons Arena" was stored as "Smith &amp; Sons Arena" and showed
 * that way on the storefront. Each field is registered here with the rule
 * core itself uses for the same kind of field, scoped to this page alone:
 *
 * - short text (name, place, booth location, event information): the rule
 *   core gives names and titles, which keeps & and quotes;
 * - longer free text (comments, driving directions, which may hold a
 *   Google Maps embed code): the rule core gives order comments and
 *   product descriptions;
 * - the two URLs: the rule core gives product and manufacturer URLs.
 *
 * Start and stop dates are already registered by core (FLOAT_VALUE_REGEX,
 * which keeps digits and dashes) and are validated by the page.
 *
 * Plugin admin extra_datafiles load right after core requires the
 * sanitizer class and before init_sanitize.php runs it, on every release
 * from 1.5.8 through 3.0.0, so registering here takes effect on this same
 * request.
 */
if (!class_exists('AdminRequestSanitizer')) {
    return;
}

$eventzSanitizerRules = [
    'name' => 'WORDS_AND_SYMBOLS_REGEX',
    'place' => 'WORDS_AND_SYMBOLS_REGEX',
    'boothLocation' => 'WORDS_AND_SYMBOLS_REGEX',
    'eventInformation' => 'WORDS_AND_SYMBOLS_REGEX',
    'comments' => 'PRODUCT_DESC_REGEX',
    'drivingDirections' => 'PRODUCT_DESC_REGEX',
    'boothLocationUrl' => 'PRODUCT_URL_REGEX',
    'eventInformationUrl' => 'PRODUCT_URL_REGEX',
];

$eventzSanitizerGroup = [];
foreach ($eventzSanitizerRules as $eventzField => $eventzType) {
    $eventzSanitizerGroup[$eventzField] = [
        'sanitizerType' => $eventzType,
        'method' => 'post',
        'pages' => ['eventz'],
    ];
}
AdminRequestSanitizer::getInstance()->addComplexSanitization($eventzSanitizerGroup);
unset($eventzSanitizerRules, $eventzSanitizerGroup, $eventzField, $eventzType);
