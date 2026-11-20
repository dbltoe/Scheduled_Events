<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: init_eventz_color_field.php 2026-11-20 dbltoe $
 */

/**
 * Scheduled Events - the color fields under Configuration > Scheduled Events.
 *
 * Each color setting's set_function is 'eventz_cfg_color(', so Configuration
 * draws it with this: a choice of Use Site Colors (the setting is saved blank
 * and the template's own color shows) or Enter Code (a hex color, typed or
 * picked with the color box beside it). Zen Cart 1.5.8 to 2.1 call it with the
 * value only (one setting edited at a time, field "configuration_value"); 2.2
 * and later add the field's key (all settings on one form, fields
 * "configuration[KEY]"). Only that field is saved; the two choices just show or
 * hide it, and Use Site Colors empties it.
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (!function_exists('eventz_cfg_color')) {
    /**
     * @param string $value the saved value (already HTML-escaped by Configuration)
     * @param string $key   the field's key on Zen Cart 2.2 and later
     */
    function eventz_cfg_color($value, $key = ''): string
    {
        $name = $key !== '' ? 'configuration[' . $key . ']' : 'configuration_value';
        $value = trim(html_entity_decode(stripslashes((string)$value), ENT_QUOTES, 'UTF-8'));
        $pick = '#000000';
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1) {
            $pick = strtolower($value);
        } elseif (preg_match('/^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/', $value, $m) === 1) {
            $pick = strtolower('#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3]);
        }
        $useSite = $value === '';
        $id = 'eventzColor' . substr(md5($name), 0, 8);
        $e = static function ($text) {
            return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
        };
        $text = static function ($constant, $fallback) {
            return defined($constant) ? (string)constant($constant) : $fallback;
        };

        // The code box shows only while Enter Code is chosen: hidden from the start for a
        // blank setting, and switched by the two choices, Use Site Colors also emptying
        // it so the setting saves blank.
        return '<div class="eventzColorField' . ($useSite ? ' eventzColorSite' : '') . '" id="' . $id . '">'
            . '<style>#' . $id . '{display:flex;flex-wrap:wrap;gap:0.4rem 1.25rem;align-items:center}'
            . '#' . $id . ' .eventzColorChoice{display:flex;gap:0.4rem;align-items:center;margin:0;font-weight:normal;cursor:pointer}'
            . '#' . $id . ' .eventzColorChoice input{width:1.15rem;height:1.15rem;margin:0}'
            . '#' . $id . ' .eventzColorCode{display:flex;gap:0.5rem;align-items:center;flex:1 1 14rem}'
            . '#' . $id . '.eventzColorSite .eventzColorCode{display:none}</style>'
            . '<label class="eventzColorChoice"><input type="radio" name="' . $id . '_mode" value="site"' . ($useSite ? ' checked' : '')
            . ' onchange="var b=document.getElementById(\'' . $id . '\');b.classList.add(\'eventzColorSite\');b.querySelector(\'.eventzColorText\').value=\'\';"> '
            . $e($text('TEXT_EVENTZ_COLOR_USE_SITE', 'Use Site Colors')) . '</label>'
            . '<label class="eventzColorChoice"><input type="radio" name="' . $id . '_mode" value="code"' . ($useSite ? '' : ' checked')
            . ' onchange="var b=document.getElementById(\'' . $id . '\'),t=b.querySelector(\'.eventzColorText\');b.classList.remove(\'eventzColorSite\');if(t.value===\'\'){t.value=b.querySelector(\'input[type=color]\').value;}t.focus();"> '
            . $e($text('TEXT_EVENTZ_COLOR_ENTER_CODE', 'Enter Code')) . '</label>'
            . '<span class="eventzColorCode">'
            . '<input type="text" name="' . $e($name) . '" value="' . $e($value) . '" class="form-control cfgInput eventzColorText" placeholder="#9a044f" maxlength="7"'
            . ' pattern="#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})" aria-label="' . $e($text('TEXT_EVENTZ_COLOR_CODE', 'Color Code')) . '"'
            . ' title="' . $e($text('TEXT_EVENTZ_COLOR_HINT', 'A hex color such as #9a044f.')) . '">'
            . '<input type="color" value="' . $pick . '" aria-label="' . $e($text('TEXT_EVENTZ_PICK_COLOR', 'Pick a Color')) . '"'
            . ' title="' . $e($text('TEXT_EVENTZ_PICK_COLOR', 'Pick a Color')) . '"'
            . ' style="flex:none;width:3rem;height:2.25rem;padding:0;border:1px solid #767676;cursor:pointer"'
            . ' oninput="this.previousElementSibling.value=this.value">'
            . '</span>'
            . '</div>';
    }
}
