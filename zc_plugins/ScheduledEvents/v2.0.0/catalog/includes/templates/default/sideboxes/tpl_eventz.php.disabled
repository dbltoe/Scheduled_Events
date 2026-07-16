<?php
global $eventzSideboxMode, $eventzSideboxEvents, $title_link;
?>
<?php if ($eventzSideboxMode === 'link') { ?>
  <div class="eventzSideboxLink">
    <a href="<?php echo zen_output_string_protected($title_link); ?>"><?php echo TEXT_EVENTZ_MORE_INFO_LINK; ?></a>
  </div>
<?php } else { ?>
  <div id="eventzCarousel" class="eventzCarousel carousel slide" data-bs-ride="carousel" data-ride="carousel">
    <div class="eventzCarouselInner carousel-inner">
<?php foreach ($eventzSideboxEvents as $eventzIndex => $eventzEvent) { ?>
      <div class="eventzCarouselItem carousel-item<?php echo ($eventzIndex === 0) ? ' active' : ''; ?>">
        <a class="eventzCarouselLink" href="<?php echo zen_output_string_protected($title_link . '#eventz-event-' . (int)$eventzEvent['id']); ?>">
          <div class="eventzCarouselName"><?php echo zen_output_string_protected($eventzEvent['name']); ?></div>
          <div class="eventzCarouselDates"><?php echo zen_date_long($eventzEvent['startDate']); ?> &ndash; <?php echo zen_date_long($eventzEvent['stopDate']); ?></div>
        </a>
      </div>
<?php } ?>
    </div>
<?php if (count($eventzSideboxEvents) > 1) { ?>
    <button class="eventzCarouselPrev carousel-control-prev" type="button" data-bs-target="#eventzCarousel" data-target="#eventzCarousel" data-bs-slide="prev" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="eventzCarouselNext carousel-control-next" type="button" data-bs-target="#eventzCarousel" data-target="#eventzCarousel" data-bs-slide="next" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
<?php } ?>
  </div>
<?php } ?>
