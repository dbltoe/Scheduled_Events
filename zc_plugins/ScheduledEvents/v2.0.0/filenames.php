<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: filenames.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Auto-loads for both catalog and admin (ZC 2.2.0+). FILENAME_EVENTZ must be
 * available admin-wide (not just while eventz.php itself is loaded) because
 * the admin_pages/menu system resolves it dynamically wherever the Catalog
 * menu renders, not only at the moment it's registered.
 */
if (!defined('FILENAME_EVENTZ')) {
    define('FILENAME_EVENTZ', 'eventz.php');
}
