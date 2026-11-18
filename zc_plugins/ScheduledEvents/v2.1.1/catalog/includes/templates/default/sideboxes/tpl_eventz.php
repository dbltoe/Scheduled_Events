<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: tpl_eventz.php 2026-07-16 05:30:22Z dbltoe $
 */
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $eventzSideboxEvents, $title_link, $zco_notifier;
?>
<?php if (empty($eventzSideboxEvents)) { ?>
<div class="eventzCarouselEmpty">
  <?php echo defined('SCHEDULED_EVENTS_SIDEBOX_NO_EVENTS_TEXT') ? SCHEDULED_EVENTS_SIDEBOX_NO_EVENTS_TEXT : 'No events scheduled right now.'; ?>
</div>
<?php } else { ?>
<?php
// Both Bootstrap 4 (data-*) and Bootstrap 5 (data-bs-*) carousel attributes,
// and both screen-reader classes (sr-only is BS4, visually-hidden BS5), so
// the box scrolls on ZCA Bootstrap and on Bootstrap 5 templates alike. On a
// template without Bootstrap's carousel the sidebox's own script turns this
// into a plain list (see modules/sideboxes/eventz.php).
// $title_link comes from zen_href_link(), already HTML-ready (&amp;), so it
// is not escaped a second time.
?>
<div id="eventzCarousel" class="eventzCarousel carousel slide carousel-fade" data-ride="carousel" data-interval="7000" data-bs-ride="carousel" data-bs-interval="7000">
  <div class="eventzCarouselInner carousel-inner">
<?php foreach ($eventzSideboxEvents as $eventzIndex => $eventzEvent) { ?>
    <div class="eventzCarouselItem carousel-item<?php echo ($eventzIndex === 0) ? ' active' : ''; ?>">
      <a class="eventzCarouselLink" href="<?php echo $title_link . '#eventz-event-' . (int)$eventzEvent['id']; ?>">
        <div class="eventzCarouselName"><?php echo EventzService::text(EventzService::preventTightHyphenWrap((string)$eventzEvent['name'])); ?></div>
        <div class="eventzCarouselStartDate"><?php echo zen_date_long($eventzEvent['startDate']); ?></div>
        <div class="eventzCarouselStopDate"><?php echo zen_date_long($eventzEvent['stopDate']); ?></div>
<?php
        // Add-on point: more content for this event inside its link (e.g. a
        // flyer thumbnail), as HTML by reference; the add-on escapes it.
        $eventzItemHtml = '';
        if (isset($zco_notifier) && is_object($zco_notifier)) {
            $zco_notifier->notify('NOTIFY_EVENTZ_SIDEBOX_ITEM_END', ['event' => $eventzEvent], $eventzItemHtml);
        }
        echo is_string($eventzItemHtml) ? $eventzItemHtml : '';
?>
      </a>
    </div>
<?php } ?>
  </div>
<?php if (count($eventzSideboxEvents) > 1) { ?>
  <button class="eventzCarouselPrev carousel-control-prev" type="button" data-target="#eventzCarousel" data-slide="prev" data-bs-target="#eventzCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden sr-only">Previous</span>
  </button>
  <button class="eventzCarouselNext carousel-control-next" type="button" data-target="#eventzCarousel" data-slide="next" data-bs-target="#eventzCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden sr-only">Next</span>
  </button>
<?php } ?>
</div>
<?php } ?>
