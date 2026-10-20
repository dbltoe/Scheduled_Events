<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: tpl_eventz.php 2026-07-16 05:30:22Z dbltoe $
 */
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $eventzSideboxEvents, $title_link;
?>
<?php if (empty($eventzSideboxEvents)) { ?>
<div class="eventzCarouselEmpty">
  <?php echo defined('SCHEDULED_EVENTS_SIDEBOX_NO_EVENTS_TEXT') ? SCHEDULED_EVENTS_SIDEBOX_NO_EVENTS_TEXT : 'No events scheduled right now.'; ?>
</div>
<?php } else { ?>
<div id="eventzCarousel" class="eventzCarousel carousel slide carousel-fade" data-ride="carousel" data-interval="7000">
  <div class="eventzCarouselInner carousel-inner">
<?php foreach ($eventzSideboxEvents as $eventzIndex => $eventzEvent) { ?>
    <div class="eventzCarouselItem carousel-item<?php echo ($eventzIndex === 0) ? ' active' : ''; ?>">
      <a class="eventzCarouselLink" href="<?php echo zen_output_string_protected($title_link . '#eventz-event-' . (int)$eventzEvent['id']); ?>">
        <div class="eventzCarouselName"><?php echo zen_output_string_protected(EventzService::preventTightHyphenWrap($eventzEvent['name'])); ?></div>
        <div class="eventzCarouselStartDate"><?php echo zen_date_long($eventzEvent['startDate']); ?></div>
        <div class="eventzCarouselStopDate"><?php echo zen_date_long($eventzEvent['stopDate']); ?></div>
      </a>
    </div>
<?php } ?>
  </div>
<?php if (count($eventzSideboxEvents) > 1) { ?>
  <button class="eventzCarouselPrev carousel-control-prev" type="button" data-target="#eventzCarousel" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="eventzCarouselNext carousel-control-next" type="button" data-target="#eventzCarousel" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
<?php } ?>
</div>
<?php } ?>
