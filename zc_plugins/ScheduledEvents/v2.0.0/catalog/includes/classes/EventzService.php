<?php
namespace Zencart\Plugins\Catalog\ScheduledEvents;

/**
 * Shared query logic for the Scheduled Events plugin (storefront page + sidebox).
 */
class EventzService
{
    /**
     * Events qualify once today falls within [startDate - windowDays, stopDate].
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getQualifyingEvents(int $windowDays, ?int $limit = null): array
    {
        global $db;

        $sql = "SELECT id, name, place, startDate, stopDate, comments, boothLocation,
                       boothLocationUrl, eventInformation, eventInformationUrl, drivingDirections
                FROM " . TABLE_EVENTZ . "
                WHERE DATE_SUB(startDate, INTERVAL " . (int)$windowDays . " DAY) <= CURDATE()
                  AND stopDate >= CURDATE()
                ORDER BY startDate ASC";

        $result = $db->Execute($sql);

        $events = [];
        while (!$result->EOF) {
            $events[] = $result->fields;
            $result->MoveNext();
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

    private static function isValidUrl(string $url): bool
    {
        // filter_var() doesn't accept protocol-relative URLs (//example.com/x)
        // on their own, so give it a scheme to validate against first.
        $candidate = (stripos($url, '//') === 0) ? 'https:' . $url : $url;

        return filter_var($candidate, FILTER_VALIDATE_URL) !== false;
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
