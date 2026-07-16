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
                       eventInformation, drivingDirections
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
     * eventInformation may already contain a full URL entered by the admin,
     * or (rarely) a hand-authored <a> tag. Normalize to a plain href.
     */
    public static function buildEventInfoUrl(string $eventInformation): string
    {
        $value = trim($eventInformation);

        if (stripos($value, '<a ') !== false && preg_match('/href\s*=\s*"([^"]+)"/i', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    /**
     * drivingDirections may already be a full Google Maps URL, or plain
     * address/place text to be turned into a Google Maps directions link.
     */
    public static function buildDrivingDirectionsUrl(string $drivingDirections): string
    {
        $value = trim($drivingDirections);

        if (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0) {
            return $value;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($value);
    }
}
