<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: eventz_filenames.php 2026-10-20 dbltoe $
 */

/**
 * Scheduled Events - admin page filename constant
 *
 * The plugin's root filenames.php only auto-loads from ZC 2.2.0 on. On 2.0.x
 * and 2.1.x nothing else defines FILENAME_EVENTZ, so admin/eventz.php fatals
 * with "Undefined constant" and the Extras menu entry can't resolve its link.
 * Plugin extra_datafiles load on every admin page in all supported versions.
 */
if (!defined('FILENAME_EVENTZ')) {
    define('FILENAME_EVENTZ', 'eventz.php');
}
