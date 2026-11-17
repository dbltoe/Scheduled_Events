<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: tpl_events_default.php 2026-07-16 05:30:22Z dbltoe $
 */
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $eventzEvents, $eventzWindowRange, $zco_notifier;

// Add-on output points (HTML strings, by reference; whatever an observer
// puts there is echoed as is, so it must escape its own content):
// - NOTIFY_EVENTZ_PAGE_LIST_TOP: ['events' => list], above the listing or
//   the no-events message (e.g. filters, a calendar view, a subscribe link);
// - NOTIFY_EVENTZ_EVENT_DETAILS_END: ['event' => row], at the end of each
//   event's details (e.g. a ticket link, an .ics download, a countdown).
$eventzNotify = static function ($eventId, array $params) use ($zco_notifier) {
    $html = '';
    if (isset($zco_notifier) && is_object($zco_notifier)) {
        $zco_notifier->notify($eventId, $params, $html);
    }
    return is_string($html) ? $html : '';
};
?>
<link rel="stylesheet" href="<?php echo zen_output_string_protected(EventzService::assetUrl('includes/templates/default/css/eventz.css')); ?>">
<div id="eventzPage" class="eventzPage">
  <h1 class="eventzPageTitle"><?php echo SCHEDULED_EVENTS_PAGE_TITLE; ?></h1>
  <hr class="eventzHr eventzHrTop">
<?php echo $eventzNotify('NOTIFY_EVENTZ_PAGE_LIST_TOP', ['events' => $eventzEvents]); ?>

<?php if (empty($eventzEvents)) { ?>
  <h2 class="eventzNoEvents">
    <?php echo strtr(SCHEDULED_EVENTS_NO_EVENTS_TEXT, ['{from}' => $eventzWindowRange['from'], '{to}' => $eventzWindowRange['to']]); ?>
  </h2>
  <h3 class="eventzNoEventsSubtext"><?php echo SCHEDULED_EVENTS_NO_EVENTS_SUBTEXT; ?></h3>
<?php } else { ?>
  <?php $eventzLastKey = array_key_last($eventzEvents); ?>
  <?php foreach ($eventzEvents as $eventzKey => $eventzEvent) { ?>
  <?php $eventzMapEmbedUrl = !empty($eventzEvent['drivingDirections']) ? EventzService::extractMapEmbedUrl($eventzEvent['drivingDirections']) : null; ?>
  <h2 id="eventz-event-<?php echo (int)$eventzEvent['id']; ?>" class="eventzHeader"><?php echo EventzService::text(EventzService::preventTightHyphenWrap((string)$eventzEvent['name'])); ?></h2>
  <hr class="eventzHr">
  <blockquote class="eventzDetails">
    <div class="eventzField eventzPlace">
      <strong><?php echo SCHEDULED_EVENTS_PLACE_LABEL; ?></strong> <?php echo EventzService::text($eventzEvent['place']); ?>
    </div>
    <div class="eventzField eventzStartDate">
      <strong><?php echo SCHEDULED_EVENTS_START_DATE_LABEL; ?></strong> <?php echo zen_date_long($eventzEvent['startDate']); ?>
    </div>
    <div class="eventzField eventzStopDate">
      <strong><?php echo SCHEDULED_EVENTS_STOP_DATE_LABEL; ?></strong> <?php echo zen_date_long($eventzEvent['stopDate']); ?>
    </div>
<?php if (!empty($eventzEvent['comments'])) { ?>
    <div class="eventzField eventzComments">
      <strong><?php echo SCHEDULED_EVENTS_COMMENTS_LABEL; ?></strong> <?php echo EventzService::text($eventzEvent['comments']); ?>
    </div>
<?php } ?>
<?php if (!empty($eventzEvent['boothLocation'])) { ?>
    <div class="eventzField eventzBoothLocation">
      <strong><?php echo SCHEDULED_EVENTS_BOOTH_LOCATION_LABEL; ?></strong> <?php echo EventzService::buildLinkedText($eventzEvent['boothLocation'], $eventzEvent['boothLocationUrl'] ?? null); ?>
    </div>
<?php } ?>
<?php if (!empty($eventzEvent['eventInformation'])) { ?>
    <div class="eventzField eventzEventInformation">
      <strong><?php echo SCHEDULED_EVENTS_EVENT_INFO_LABEL; ?></strong>
      <?php echo EventzService::buildLinkedText($eventzEvent['eventInformation'], $eventzEvent['eventInformationUrl'] ?? null); ?>
    </div>
<?php } ?>
<?php if (!empty($eventzEvent['drivingDirections'])) { ?>
    <div class="eventzField eventzDrivingDirections">
      <strong><?php echo SCHEDULED_EVENTS_DRIVING_DIRECTIONS_LABEL; ?></strong>
<?php if ($eventzMapEmbedUrl !== null) { ?>
      <button type="button" class="eventzDirectionsTrigger" data-eventz-modal="eventz-directions-modal-<?php echo (int)$eventzEvent['id']; ?>"><?php echo TEXT_EVENTZ_DIRECTIONS_LINK; ?></button>
<?php } else { ?>
      <a href="<?php echo zen_output_string_protected(EventzService::buildDrivingDirectionsUrl($eventzEvent['drivingDirections'])); ?>" target="_blank" rel="noopener noreferrer"><?php echo TEXT_EVENTZ_DIRECTIONS_LINK; ?></a>
<?php } ?>
    </div>
<?php } ?>
<?php echo $eventzNotify('NOTIFY_EVENTZ_EVENT_DETAILS_END', ['event' => $eventzEvent]); ?>
  </blockquote>
<?php if ($eventzMapEmbedUrl !== null) { ?>
  <div id="eventz-directions-modal-<?php echo (int)$eventzEvent['id']; ?>" class="eventzModalOverlay">
    <div class="eventzModalContent">
      <button type="button" class="eventzModalClose" aria-label="Close">&times;</button>
      <iframe data-eventz-src="<?php echo zen_output_string_protected($eventzMapEmbedUrl); ?>" class="eventzModalIframe" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
  </div>
<?php } ?>
<?php if ($eventzKey !== $eventzLastKey) { ?>
  <hr class="eventzHr eventzHrBetween">
<?php } ?>
  <?php } ?>
<?php } ?>
</div>

<?php if (!empty($eventzEvents)) { ?>
<script>
document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-eventz-modal]');
    if (trigger) {
        e.preventDefault();
        var modal = document.getElementById(trigger.getAttribute('data-eventz-modal'));
        if (modal) {
            var iframe = modal.querySelector('.eventzModalIframe');
            if (iframe && !iframe.getAttribute('src') && iframe.dataset.eventzSrc) {
                iframe.setAttribute('src', iframe.dataset.eventzSrc);
            }
            modal.classList.add('eventzModalOpen');
        }
        return;
    }

    var closeBtn = e.target.closest('.eventzModalClose');
    if (closeBtn) {
        var overlay = closeBtn.closest('.eventzModalOverlay');
        if (overlay) {
            overlay.classList.remove('eventzModalOpen');
        }
        return;
    }

    if (e.target.classList && e.target.classList.contains('eventzModalOverlay')) {
        e.target.classList.remove('eventzModalOpen');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.eventzModalOverlay.eventzModalOpen').forEach(function (overlay) {
            overlay.classList.remove('eventzModalOpen');
        });
    }
});
</script>
<?php } ?>
