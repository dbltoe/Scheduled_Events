<?php
/**
 * Scheduled Events - database table constant (storefront)
 *
 * DB_PREFIX is applied here so the constant is correct whether or not
 * this Zen Cart installation uses a table prefix.
 */
if (!defined('TABLE_EVENTZ')) {
    define('TABLE_EVENTZ', DB_PREFIX . 'eventz');
}
