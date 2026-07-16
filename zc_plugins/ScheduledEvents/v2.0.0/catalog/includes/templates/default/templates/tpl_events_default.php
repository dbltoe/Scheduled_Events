<?php
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $eventzEvents, $eventzWindowRange;
?>
<div id="eventzPage" class="eventzPage">
  <h1 class="eventzPageTitle"><?php echo SCHEDULED_EVENTS_PAGE_TITLE; ?></h1>
  <hr class="eventzHr eventzHrTop">

<?php if (empty($eventzEvents)) { ?>
  <h2 class="eventzNoEvents">
    <?php echo strtr(SCHEDULED_EVENTS_NO_EVENTS_TEXT, ['{from}' => $eventzWindowRange['from'], '{to}' => $eventzWindowRange['to']]); ?>
  </h2>
  <h3 class="eventzNoEventsSubtext"><?php echo SCHEDULED_EVENTS_NO_EVENTS_SUBTEXT; ?></h3>
<?php } else { ?>
  <?php foreach ($eventzEvents as $eventzEvent) { ?>
  <h2 id="eventz-event-<?php echo (int)$eventzEvent['id']; ?>" class="eventzHeader"><?php echo zen_output_string_protected($eventzEvent['name']); ?></h2>
  <hr class="eventzHr">
  <blockquote class="eventzDetails">
    <div class="eventzField eventzPlace">
      <strong><?php echo SCHEDULED_EVENTS_PLACE_LABEL; ?></strong> <?php echo zen_output_string_protected($eventzEvent['place']); ?>
    </div>
    <div class="eventzField eventzStartDate">
      <strong><?php echo SCHEDULED_EVENTS_START_DATE_LABEL; ?></strong> <?php echo zen_date_long($eventzEvent['startDate']); ?>
    </div>
    <div class="eventzField eventzStopDate">
      <strong><?php echo SCHEDULED_EVENTS_STOP_DATE_LABEL; ?></strong> <?php echo zen_date_long($eventzEvent['stopDate']); ?>
    </div>
<?php if (!empty($eventzEvent['comments'])) { ?>
    <div class="eventzField eventzComments">
      <strong><?php echo SCHEDULED_EVENTS_COMMENTS_LABEL; ?></strong> <?php echo zen_output_string_protected($eventzEvent['comments']); ?>
    </div>
<?php } ?>
<?php if (!empty($eventzEvent['boothLocation'])) { ?>
    <div class="eventzField eventzBoothLocation">
      <strong><?php echo SCHEDULED_EVENTS_BOOTH_LOCATION_LABEL; ?></strong> <?php echo $eventzEvent['boothLocation']; ?>
    </div>
<?php } ?>
<?php if (!empty($eventzEvent['eventInformation'])) { ?>
    <div class="eventzField eventzEventInformation">
      <strong><?php echo SCHEDULED_EVENTS_EVENT_INFO_LABEL; ?></strong>
      <a href="<?php echo zen_output_string_protected(EventzService::buildEventInfoUrl($eventzEvent['eventInformation'])); ?>" target="_blank" rel="noopener"><?php echo TEXT_EVENTZ_MORE_INFO_LINK; ?></a>
    </div>
<?php } ?>
<?php if (!empty($eventzEvent['drivingDirections'])) { ?>
    <div class="eventzField eventzDrivingDirections">
      <strong><?php echo SCHEDULED_EVENTS_DRIVING_DIRECTIONS_LABEL; ?></strong>
      <a href="<?php echo zen_output_string_protected(EventzService::buildDrivingDirectionsUrl($eventzEvent['drivingDirections'])); ?>" target="_blank" rel="noopener"><?php echo TEXT_EVENTZ_DIRECTIONS_LINK; ?></a>
    </div>
<?php } ?>
  </blockquote>
  <?php } ?>
<?php } ?>
</div>
