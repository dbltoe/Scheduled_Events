<?php
/**
 * Auto-loads for both catalog and admin (ZC 2.2.0+). FILENAME_EVENTZ must be
 * available admin-wide (not just while eventz.php itself is loaded) because
 * the admin_pages/menu system resolves it dynamically wherever the Catalog
 * menu renders, not only at the moment it's registered.
 */
if (!defined('FILENAME_EVENTZ')) {
    define('FILENAME_EVENTZ', 'eventz.php');
}
