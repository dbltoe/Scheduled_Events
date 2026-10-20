<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: database_tables.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Scheduled Events - database table constant (storefront)
 *
 * DB_PREFIX is applied here so the constant is correct whether or not
 * this Zen Cart installation uses a table prefix.
 */
if (!defined('TABLE_EVENTZ')) {
    define('TABLE_EVENTZ', DB_PREFIX . 'eventz');
}
