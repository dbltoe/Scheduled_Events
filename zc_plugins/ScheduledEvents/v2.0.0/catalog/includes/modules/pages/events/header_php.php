<?php
use Zencart\Plugins\Catalog\ScheduledEvents\EventzService;

global $breadcrumb, $zco_notifier, $eventzEvents, $eventzWindowRange, $eventzWindowDays;

$zco_notifier->notify('NOTIFY_HEADER_START_EVENTS');

$breadcrumb->add(NAVBAR_TITLE);

$eventzWindowDays = defined('SCHEDULED_EVENTS_WINDOW_DAYS') ? (int)SCHEDULED_EVENTS_WINDOW_DAYS : 30;
$eventzEvents = EventzService::getQualifyingEvents($eventzWindowDays);
$eventzWindowRange = EventzService::getWindowDateRange($eventzWindowDays);

$zco_notifier->notify('NOTIFY_HEADER_END_EVENTS');
