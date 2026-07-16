<?php
/**
 * Points the Help button on the Scheduled Events admin page at the plugin's
 * GitHub repo. NOTIFIER_PLUGIN_HELP_PAGE_URL_LOOKUP's $help_page is rendered
 * as a plain URL string (opened in a new tab) on this Zen Cart version -
 * the docs-described ['body' => ...] inline-modal format caused an "Array
 * to string conversion" warning here, since header_navigation.php just does
 * <a href="<?= $url ?>">, with no array handling at all.
 */
class EventzAdminObserver extends base
{
    public function __construct()
    {
        $this->attach($this, ['NOTIFIER_PLUGIN_HELP_PAGE_URL_LOOKUP']);
    }

    protected function update(&$class, $eventID, $page, &$help_page)
    {
        if ($eventID !== 'NOTIFIER_PLUGIN_HELP_PAGE_URL_LOOKUP') {
            return;
        }

        // FILENAME_EVENTZ is only defined while the eventz.php page itself is
        // loaded (it comes from that page's own language file), so this must
        // be checked before use - this observer's update() fires on every
        // admin page, not just ours. $page arrives as basename($PHP_SELF,
        // '.php'), i.e. without the ".php" extension.
        if (!defined('FILENAME_EVENTZ') || $page !== basename(FILENAME_EVENTZ, '.php')) {
            return;
        }

        $help_page = 'https://github.com/dbltoe/Scheduled_Events';
    }
}
