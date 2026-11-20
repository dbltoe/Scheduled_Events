<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: EventzService.php 2026-07-16 05:30:22Z dbltoe $
 */
namespace Zencart\Plugins\Catalog\ScheduledEvents;

/**
 * Shared query logic for the Scheduled Events plugin (storefront page + sidebox).
 */
class EventzService
{
    /**
     * Events qualify once today falls within [startDate - windowDays, stopDate]
     * AND the event is marked active - a store owner can pause an event
     * (e.g. a scheduling conflict) without deleting it or touching its dates.
     *
     * Two notifiers let an add-on change what qualifies without editing
     * this plugin (both fire for the events page and for the sidebox; the
     * first parameter says which, by its limit):
     * - NOTIFY_EVENTZ_QUALIFYING_EVENTS_QUERY: ['window_days', 'limit'],
     *   then by reference the WHERE clause and the ORDER BY clause (the
     *   table is aliased e). Append with " AND ...", using TABLE_* constants
     *   and escaped values only.
     * - NOTIFY_EVENTZ_QUALIFYING_EVENTS_RESULT: the same array, then by
     *   reference the list of event rows, before the sidebox's limit is
     *   applied. Rows carry every column of the eventz table.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getQualifyingEvents(int $windowDays, ?int $limit = null): array
    {
        global $db, $zco_notifier;

        $notifyParams = ['window_days' => $windowDays, 'limit' => $limit];
        $where = "e.active = 1
                  AND DATE_SUB(e.startDate, INTERVAL " . (int)$windowDays . " DAY) <= CURDATE()
                  AND e.stopDate >= CURDATE()";
        $orderBy = 'e.startDate ASC';
        if (isset($zco_notifier) && is_object($zco_notifier)) {
            $zco_notifier->notify('NOTIFY_EVENTZ_QUALIFYING_EVENTS_QUERY', $notifyParams, $where, $orderBy);
        }

        $sql = "SELECT e.*
                FROM " . TABLE_EVENTZ . " e
                WHERE " . $where . "
                ORDER BY " . $orderBy;

        $result = $db->Execute($sql);

        $events = [];
        while (!$result->EOF) {
            $events[] = $result->fields;
            $result->MoveNext();
        }

        if (isset($zco_notifier) && is_object($zco_notifier)) {
            $zco_notifier->notify('NOTIFY_EVENTZ_QUALIFYING_EVENTS_RESULT', $notifyParams, $events);
            $events = is_array($events) ? array_values($events) : [];
        }

        if ($limit !== null && $limit > 0) {
            $events = array_slice($events, 0, $limit);
        }

        return $events;
    }

    /**
     * The date range used in the "no events scheduled" notice: today through
     * today + windowDays (the span within which a qualifying event could start).
     *
     * @return array{from: string, to: string}
     */
    public static function getWindowDateRange(int $windowDays): array
    {
        $from = new \DateTime();
        $to = (new \DateTime())->modify('+' . (int)$windowDays . ' days');

        return [
            'from' => $from->format('F j, Y'),
            'to' => $to->format('F j, Y'),
        ];
    }

    /**
     * Shared display logic for boothLocation/eventInformation: each is a
     * plain-text description with a separate, optional URL. The description
     * becomes the link text, but only if the URL actually validates -
     * otherwise the description is shown as plain text and the URL is
     * silently ignored, so the store owner never has to author HTML or
     * worry about a bad link breaking the page.
     */
    public static function buildLinkedText(string $text, ?string $url): string
    {
        $text = trim(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $url = trim(html_entity_decode((string)$url, ENT_QUOTES, 'UTF-8'));

        if ($text === '') {
            return '';
        }

        if ($url !== '' && self::isValidUrl($url)) {
            return '<a href="' . zen_output_string_protected($url) . '" target="_blank" rel="noopener noreferrer">'
                . zen_output_string_protected($text) . '</a>';
        }

        return zen_output_string_protected($text);
    }

    /**
     * Browsers treat a bare hyphen as a valid line-break point even with no
     * surrounding whitespace, so a compound term like "4-H" can wrap
     * awkwardly on narrow screens. Swap only "tight" hyphens - no
     * whitespace on either side - for a non-breaking hyphen (U+2011); a
     * spaced dash used to separate phrases (e.g. "Milwaukee - Summerfest")
     * is left untouched, since it already wraps at the surrounding space
     * rather than at the hyphen itself.
     */
    public static function preventTightHyphenWrap(string $text): string
    {
        // preg_replace() gives null on text that isn't valid UTF-8; show
        // that text as it is rather than fail on the string return type.
        $result = preg_replace('/(?<=\S)-(?=\S)/u', "\u{2011}", $text);

        return $result ?? $text;
    }

    /**
     * Stored event text, escaped once for the page.
     *
     * Before v2.1.0 the admin's request sanitizer HTML-escaped most event
     * fields on save, so "Smith & Sons" may be stored as "Smith &amp; Sons".
     * Decoding first means those older events show as typed instead of with
     * a visible "&amp;", and text saved from v2.1.0 on (stored as typed) is
     * unaffected.
     */
    public static function text(?string $value): string
    {
        return zen_output_string_protected(html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8'));
    }

    /** @var bool the stylesheet and color rules were already written on this page */
    private static $stylesDone = false;

    /**
     * The plugin's stylesheet link (stamped with the file's time, so browsers fetch it
     * again whenever it changes) and the store's chosen colors, once per page. Every
     * place the plugin draws (the events page, the Events sidebox, and add-ons' event
     * pages) calls this, so they all look the same whichever renders first.
     */
    public static function styles(): string
    {
        if (self::$stylesDone) {
            return '';
        }
        self::$stylesDone = true;
        $file = dirname(__DIR__) . '/templates/default/css/eventz.css';
        $stamp = is_file($file) ? '?v=' . filemtime($file) : '';
        return '<link rel="stylesheet" href="' . zen_output_string_protected(self::assetUrl('includes/templates/default/css/eventz.css') . $stamp) . '">'
            . self::colorCss();
    }

    /**
     * A hex color setting ("#9a044f" or "#fff"), or '' when it's blank or not a color.
     */
    public static function color(string $constant): string
    {
        $value = defined($constant) ? trim((string)constant($constant)) : '';
        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) === 1 ? strtolower($value) : '';
    }

    /**
     * The store's chosen colors (Configuration > Scheduled Events) as rules for the
     * plugin's own areas: the events page (and add-on pages using its .eventzPage
     * wrapper) and the Events sidebox. A blank setting adds nothing, so the template's
     * own color shows. A chosen color is the store owner's explicit choice, so it wins
     * over any template or site rule (!important).
     */
    public static function colorCss(): string
    {
        $link = self::color('SCHEDULED_EVENTS_LINK_COLOR');
        $hover = self::color('SCHEDULED_EVENTS_LINK_HOVER_COLOR');
        $label = self::color('SCHEDULED_EVENTS_LABEL_COLOR');
        $css = '';
        if ($link !== '') {
            $css .= '.eventzPage a,.eventzPage .eventzDirectionsTrigger,.eventzCarousel a{color:' . $link . '!important}';
        }
        if ($hover !== '') {
            $css .= '.eventzPage a:hover,.eventzPage a:focus,.eventzPage .eventzDirectionsTrigger:hover,.eventzPage .eventzDirectionsTrigger:focus,'
                . '.eventzCarousel a:hover,.eventzCarousel a:focus{color:' . $hover . '!important}';
        }
        if ($label !== '') {
            $css .= '.eventzPage .eventzField>strong{color:' . $label . '!important}';
        }
        return $css === '' ? '' : '<style>' . $css . '</style>';
    }

    /**
     * Address of one of this plugin's own storefront files (stylesheet),
     * built from where this class sits so a new version folder never needs
     * a path edited, and from DIR_WS_CATALOG so a store installed in a
     * subfolder works.
     */
    public static function assetUrl(string $relativePath): string
    {
        global $request_type;

        $base = DIR_WS_CATALOG;
        if (isset($request_type) && $request_type === 'SSL' && defined('DIR_WS_HTTPS_CATALOG')) {
            $base = DIR_WS_HTTPS_CATALOG;
        }

        // .../zc_plugins/ScheduledEvents/<version>/catalog/includes/classes
        $versionFolder = basename(dirname(__DIR__, 3));

        return $base . 'zc_plugins/ScheduledEvents/' . rawurlencode($versionFolder) . '/catalog/' . ltrim($relativePath, '/');
    }

    private static function isValidUrl(string $url): bool
    {
        // A root-relative path (e.g. /images/foo.jpg) always resolves against
        // this store's own domain, but FILTER_VALIDATE_URL requires a host
        // and rejects it - accept it directly instead of forcing store
        // owners into absolute URLs just to pass validation.
        if (stripos($url, '/') === 0 && stripos($url, '//') !== 0) {
            return preg_match('#^/[A-Za-z0-9\-._~!$&\'()*+,;=:@%/]*$#', $url) === 1;
        }

        // filter_var() doesn't accept protocol-relative URLs (//example.com/x)
        // on their own, so give it a scheme to validate against first.
        $candidate = (stripos($url, '//') === 0) ? 'https:' . $url : $url;

        if (filter_var($candidate, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // FILTER_VALIDATE_URL accepts any scheme, including ones a browser
        // would run as script ("javascript://..."). Event links are web
        // pages and map images, so only http and https become links.
        $scheme = strtolower((string)parse_url($candidate, PHP_URL_SCHEME));

        return $scheme === 'http' || $scheme === 'https';
    }

    /**
     * drivingDirections may already be a full Google Maps URL, or plain
     * address/place text to be turned into a Google Maps directions link.
     */
    public static function buildDrivingDirectionsUrl(string $drivingDirections): string
    {
        $value = trim(html_entity_decode($drivingDirections, ENT_QUOTES, 'UTF-8'));

        if (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0) {
            return $value;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($value);
    }

    /**
     * If drivingDirections holds a Google Maps <iframe> embed code (or a bare
     * embed URL), extract the URL to display in an in-page popup instead of
     * the plain "open in a new tab" link. Returns null for a plain address
     * or a non-embed URL, so the caller can fall back to
     * buildDrivingDirectionsUrl() in that case.
     */
    public static function extractMapEmbedUrl(string $drivingDirections): ?string
    {
        $value = trim(html_entity_decode($drivingDirections, ENT_QUOTES, 'UTF-8'));

        if (preg_match('/<iframe\b[^>]*\ssrc\s*=\s*"([^"]+)"/i', $value, $matches) === 1) {
            $value = trim($matches[1]);
        }

        if ($value !== '' && self::isValidUrl($value) && stripos($value, '/maps/embed') !== false) {
            return $value;
        }

        return null;
    }
}
