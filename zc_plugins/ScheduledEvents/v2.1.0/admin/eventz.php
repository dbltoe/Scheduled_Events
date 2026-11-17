<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License v2.0
 * @version $Id: eventz.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Scheduled Events - admin CRUD page for the eventz table
 */

// This page is only ever reached as admin/index.php?cmd=eventz, and index.php
// loads application_bootstrap.php (which defines IS_ADMIN_FLAG) before routing
// here - so on the legitimate path this guard always passes and costs nothing.
// It matters because zc_plugins/ is web-reachable wherever the shipped
// .htaccess isn't honored (nginx reads none; Apache under AllowOverride None).
// Without it, a direct request for this file falls into the relative require
// below, which fails and emits a fatal that discloses the full server path.
if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    http_response_code(404);
    exit;
}

require('includes/application_top.php');

// Language. This page's own lang.eventz.php normally loads by Zen Cart's
// per-page convention, but that hasn't reliably happened on every tested
// store/routing setup, and Zen Cart 1.5.8 and 2.0.x have no English fallback
// for plugin language files: on a non-English admin without a translation,
// every constant below would be undefined, a fatal on PHP 8. So fill any gap
// from the admin's own language first, then from English. Anything already
// defined (by core's loader or a translation) wins.
// Plugin files stay inside zc_plugins/... permanently (never copied into
// admin/includes/languages/), so __DIR__ - a known-fixed path relative to
// this file's own real location - is used instead of any of Zen Cart's
// DIR_FS_*/DIR_WS_* constants, which proved unreliable on the catalog side.
// Core validates the language against the languages table before setting
// it, so it isn't attacker-controlled in practice - but it still ends up in
// an include path, so it is constrained to a bare directory name rather than
// taken on trust.
$eventzLanguageDirs = [];
$eventzLanguageDir = $language ?? ($_SESSION['language'] ?? 'english');
if (is_string($eventzLanguageDir) && preg_match('/^[a-zA-Z0-9_-]+$/', $eventzLanguageDir) === 1) {
    $eventzLanguageDirs[] = $eventzLanguageDir;
}
$eventzLanguageDirs[] = 'english';
foreach (array_unique($eventzLanguageDirs) as $eventzLanguageDir) {
    $eventzLangFile = __DIR__ . '/includes/languages/' . $eventzLanguageDir . '/lang.eventz.php';
    if (!is_file($eventzLangFile)) {
        continue;
    }
    $eventzDefines = require $eventzLangFile;
    if (!is_array($eventzDefines)) {
        continue;
    }
    foreach ($eventzDefines as $eventzDefineKey => $eventzDefineValue) {
        if (!defined($eventzDefineKey)) {
            define($eventzDefineKey, $eventzDefineValue);
        }
    }
}

/**
 * Stored text as the admin typed it. Before v2.1.0 the admin's request
 * sanitizer HTML-escaped most of these fields on save ("&" was stored as
 * "&amp;"); this undoes that for display and editing, so older events read
 * and re-save correctly.
 */
function eventzStoredText($value)
{
    return html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
}

function eventzValidateDate($value)
{
    $value = trim((string)$value);
    $date = DateTime::createFromFormat('Y-m-d', $value);

    if ($date === false || $date->format('Y-m-d') !== $value) {
        return null;
    }

    return $value;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Whitelisted column/direction pairs only - never build ORDER BY from raw
// user input.
$eventzSortOptions = [
    'startDate_desc' => ['column' => 'startDate', 'direction' => 'DESC', 'label' => 'Start Date (Newest First)'],
    'startDate_asc' => ['column' => 'startDate', 'direction' => 'ASC', 'label' => 'Start Date (Oldest First)'],
    'stopDate_desc' => ['column' => 'stopDate', 'direction' => 'DESC', 'label' => 'Stop Date (Newest First)'],
    'stopDate_asc' => ['column' => 'stopDate', 'direction' => 'ASC', 'label' => 'Stop Date (Oldest First)'],
    'name_asc' => ['column' => 'name', 'direction' => 'ASC', 'label' => 'Name (A-Z)'],
    'name_desc' => ['column' => 'name', 'direction' => 'DESC', 'label' => 'Name (Z-A)'],
    'place_asc' => ['column' => 'place', 'direction' => 'ASC', 'label' => 'Place (A-Z)'],
    'place_desc' => ['column' => 'place', 'direction' => 'DESC', 'label' => 'Place (Z-A)'],
];
$eventzSelectedSort = isset($_GET['sort']) && isset($eventzSortOptions[$_GET['sort']]) ? $_GET['sort'] : 'startDate_desc';
$eventzShowAll = isset($_GET['showAll']) && $_GET['showAll'] === '1';

// Changes come only from this page's own forms, which always POST. Zen Cart
// checks the form's security token only on a POST, so an add, change or
// delete arriving any other way (a link, an image tag) is refused.
$eventzEditing = null;
$eventzIsPost = isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
if (in_array($action, ['insert', 'update', 'deleteconfirm'], true) && !$eventzIsPost) {
    zen_redirect(zen_href_link(FILENAME_EVENTZ));
}

switch ($action) {
    case 'insert':
    case 'update':
        $eventzData = [
            'name' => zen_db_prepare_input($_POST['name'] ?? ''),
            'place' => zen_db_prepare_input($_POST['place'] ?? ''),
            'startDate' => zen_db_prepare_input($_POST['startDate'] ?? ''),
            'stopDate' => zen_db_prepare_input($_POST['stopDate'] ?? ''),
            'comments' => zen_db_prepare_input($_POST['comments'] ?? ''),
            'boothLocation' => zen_db_prepare_input($_POST['boothLocation'] ?? ''),
            'boothLocationUrl' => zen_db_prepare_input($_POST['boothLocationUrl'] ?? ''),
            'eventInformation' => zen_db_prepare_input($_POST['eventInformation'] ?? ''),
            'eventInformationUrl' => zen_db_prepare_input($_POST['eventInformationUrl'] ?? ''),
            'drivingDirections' => zen_db_prepare_input($_POST['drivingDirections'] ?? ''),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];

        $errors = [];

        if ($eventzData['name'] === '') {
            $errors[] = ERROR_EVENTZ_NAME_REQUIRED;
        }
        if ($eventzData['place'] === '') {
            $errors[] = ERROR_EVENTZ_PLACE_REQUIRED;
        }
        if ($eventzData['boothLocation'] === '') {
            $errors[] = ERROR_EVENTZ_BOOTH_LOCATION_REQUIRED;
        }

        $startDate = eventzValidateDate($eventzData['startDate']);
        if ($startDate === null) {
            $errors[] = ERROR_EVENTZ_START_DATE_INVALID;
        }

        $stopDate = eventzValidateDate($eventzData['stopDate']);
        if ($stopDate === null) {
            $errors[] = ERROR_EVENTZ_STOP_DATE_INVALID;
        }

        if ($startDate !== null && $stopDate !== null && $stopDate < $startDate) {
            $errors[] = ERROR_EVENTZ_STOP_BEFORE_START;
        }

        // Add-on point: check the add-on's own fields (read from $_POST) and
        // add messages to the error list; an error shows the form again.
        $zco_notifier->notify('NOTIFY_EVENTZ_ADMIN_VALIDATE', ['action' => $action, 'id' => $id], $eventzData, $errors);
        $errors = is_array($errors) ? $errors : [];

        if ($action === 'update') {
            $eventzExists = $id > 0 ? $db->Execute("SELECT id FROM " . TABLE_EVENTZ . " WHERE id = " . (int)$id) : null;
            if ($eventzExists === null || $eventzExists->EOF) {
                $messageStack->add_session(ERROR_EVENTZ_NOT_FOUND, 'error');
                zen_redirect(zen_href_link(FILENAME_EVENTZ));
            }
        }

        // On an error, show the form again right here with what was typed,
        // rather than redirecting: a redirect lost every entry (and, through
        // the &amp; in an admin link, the event's id).
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $messageStack->add($error, 'error');
            }
            $eventzEditing = $eventzData;
            if ($action === 'update') {
                $eventzEditing['id'] = $id;
            }
            $action = ($action === 'insert') ? 'new' : 'edit';
            break;
        }

        $sqlData = [
            'name' => $eventzData['name'],
            'place' => $eventzData['place'],
            'startDate' => $startDate,
            'stopDate' => $stopDate,
            'comments' => $eventzData['comments'],
            'boothLocation' => $eventzData['boothLocation'],
            'boothLocationUrl' => $eventzData['boothLocationUrl'],
            'eventInformation' => $eventzData['eventInformation'],
            'eventInformationUrl' => $eventzData['eventInformationUrl'],
            'drivingDirections' => $eventzData['drivingDirections'],
            'active' => $eventzData['active'],
        ];

        if ($action === 'insert') {
            zen_db_perform(TABLE_EVENTZ, $sqlData);
            $eventzSavedId = (int)$db->insert_ID();
            $messageStack->add_session(SUCCESS_EVENTZ_INSERTED, 'success');
        } else {
            zen_db_perform(TABLE_EVENTZ, $sqlData, 'update', "id = " . (int)$id);
            $eventzSavedId = (int)$id;
            $messageStack->add_session(SUCCESS_EVENTZ_UPDATED, 'success');
        }

        // Add-on point: the event is saved; save the add-on's own fields
        // (read from $_POST) against this id.
        $zco_notifier->notify('NOTIFY_EVENTZ_ADMIN_EVENT_SAVED', ['action' => $action, 'id' => $eventzSavedId, 'data' => $sqlData]);

        zen_redirect(zen_href_link(FILENAME_EVENTZ));
        break;

    case 'deleteconfirm':
        $db->Execute("DELETE FROM " . TABLE_EVENTZ . " WHERE id = " . (int)$id);
        // Add-on point: remove the add-on's own rows for this event.
        $zco_notifier->notify('NOTIFY_EVENTZ_ADMIN_EVENT_DELETED', ['id' => (int)$id]);
        $messageStack->add_session(SUCCESS_EVENTZ_DELETED, 'success');
        zen_redirect(zen_href_link(FILENAME_EVENTZ));
        break;

    default:
        break;
}

if ($eventzEditing === null && ($action === 'edit' || $action === 'delete') && $id > 0) {
    $result = $db->Execute("SELECT * FROM " . TABLE_EVENTZ . " WHERE id = " . (int)$id);
    if (!$result->EOF) {
        $eventzEditing = $result->fields;
        foreach (['name', 'place', 'comments', 'boothLocation', 'boothLocationUrl', 'eventInformation', 'eventInformationUrl', 'drivingDirections'] as $eventzTextField) {
            $eventzEditing[$eventzTextField] = eventzStoredText($eventzEditing[$eventzTextField] ?? '');
        }
    } else {
        $messageStack->add_session(ERROR_EVENTZ_NOT_FOUND, 'error');
        zen_redirect(zen_href_link(FILENAME_EVENTZ));
    }
}

$eventzPageTitle = HEADING_TITLE_EVENTZ;
if ($action === 'new') {
    $eventzPageTitle = HEADING_TITLE_EVENTZ_NEW;
} elseif ($action === 'edit') {
    $eventzPageTitle = HEADING_TITLE_EVENTZ_EDIT;
} elseif ($action === 'delete') {
    $eventzPageTitle = HEADING_TITLE_EVENTZ_DELETE;
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<?php require(DIR_WS_INCLUDES . 'admin_html_head.php'); ?>
<?php
// The plugin's own version folder, read from where this file sits, so the
// path never needs editing for a new version; DIR_WS_CATALOG covers a store
// installed in a subfolder.
?>
<link rel="stylesheet" href="<?php echo DIR_WS_CATALOG . 'zc_plugins/ScheduledEvents/' . rawurlencode(basename(dirname(__DIR__))) . '/admin/includes/css/eventz.css'; ?>">
<title><?php echo TITLE; ?></title>
</head>
<body>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div class="content-area eventzContent">
  <h1><?php echo $eventzPageTitle; ?></h1>

<?php if ($action === 'new' || $action === 'edit') { ?>

  <p class="eventzRequiredNotice">* <?php echo TEXT_EVENTZ_REQUIRED_INFORMATION; ?></p>

  <?php echo zen_draw_form('eventz', FILENAME_EVENTZ, 'action=' . ($action === 'new' ? 'insert' : 'update') . (!empty($eventzEditing['id']) ? '&id=' . (int)$eventzEditing['id'] : ''), 'post', 'class="form-horizontal" enctype="multipart/form-data"'); ?>

  <div class="row font-weight-bold bg-primary py-3">
    <div class="col-md-3"><?php echo TABLE_HEADING_EVENTZ_FIELD; ?></div>
    <div class="col-md-3"><?php echo TABLE_HEADING_EVENTZ_VALUE; ?></div>
    <div class="col-md-6"></div>
  </div>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="name"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_NAME; ?></strong><span class="eventzRequired">*</span></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('name', $eventzEditing['name'] ?? '', 'id="name" class="form-control" maxlength="255" required'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_NAME_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="place"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_PLACE; ?></strong><span class="eventzRequired">*</span></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('place', $eventzEditing['place'] ?? '', 'id="place" class="form-control" maxlength="255" required'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_PLACE_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="startDate"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_START_DATE; ?></strong><span class="eventzRequired">*</span></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('startDate', $eventzEditing['startDate'] ?? '', 'id="startDate" class="form-control" required', false, 'date'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_DATE_FORMAT_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="stopDate"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_STOP_DATE; ?></strong><span class="eventzRequired">*</span></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('stopDate', $eventzEditing['stopDate'] ?? '', 'id="stopDate" class="form-control" required', false, 'date'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_DATE_FORMAT_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="comments"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_COMMENTS; ?></strong></label></div>
    <div class="col-md-3"><?php echo zen_draw_textarea_field('comments', 'soft', 60, 4, htmlspecialchars((string)($eventzEditing['comments'] ?? ''), ENT_QUOTES, 'UTF-8'),'id="comments" class="form-control"'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_COMMENTS_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="boothLocation"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_BOOTH_LOCATION; ?></strong><span class="eventzRequired">*</span></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('boothLocation', $eventzEditing['boothLocation'] ?? '', 'id="boothLocation" class="form-control" maxlength="255" required'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_BOOTH_LOCATION_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="boothLocationUrl"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_BOOTH_LOCATION_URL; ?></strong></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('boothLocationUrl', $eventzEditing['boothLocationUrl'] ?? '', 'id="boothLocationUrl" class="form-control" maxlength="500"'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_BOOTH_LOCATION_URL_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="eventInformation"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_EVENT_INFORMATION; ?></strong></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('eventInformation', $eventzEditing['eventInformation'] ?? '', 'id="eventInformation" class="form-control"'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_EVENT_INFO_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="eventInformationUrl"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_EVENT_INFORMATION_URL; ?></strong></label></div>
    <div class="col-md-3"><?php echo zen_draw_input_field('eventInformationUrl', $eventzEditing['eventInformationUrl'] ?? '', 'id="eventInformationUrl" class="form-control" maxlength="500"'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_EVENT_INFO_URL_HELP; ?></div>
  </div>
  <hr>

  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="drivingDirections"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_DRIVING_DIRECTIONS; ?></strong></label></div>
    <div class="col-md-3"><?php echo zen_draw_textarea_field('drivingDirections', 'soft', 60, 4, htmlspecialchars((string)($eventzEditing['drivingDirections'] ?? ''), ENT_QUOTES, 'UTF-8'),'id="drivingDirections" class="form-control"'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_DRIVING_DIRECTIONS_HELP; ?></div>
  </div>
  <hr>

<?php
  // Add-on point: extra form rows, as HTML (by reference), above Active.
  // The add-on escapes its own values and registers its own fields with
  // the admin request sanitizer.
  $eventzExtraRows = '';
  $zco_notifier->notify('NOTIFY_EVENTZ_ADMIN_FORM_EXTRA_ROWS', ['action' => $action, 'event' => $eventzEditing ?? []], $eventzExtraRows);
  echo is_string($eventzExtraRows) ? $eventzExtraRows : '';
?>
  <div class="row row-hover align-items-center py-2">
    <div class="col-md-3"><label for="active"><strong class="eventzFieldLabel"><?php echo ENTRY_EVENTZ_ACTIVE; ?></strong></label></div>
    <div class="col-md-3"><?php echo zen_draw_checkbox_field('active', '1', !isset($eventzEditing['active']) || (int)$eventzEditing['active'] === 1, 'id="active" class="form-check-input"'); ?></div>
    <div class="col-md-6 bg-info p-3"><?php echo TEXT_EVENTZ_ACTIVE_HELP; ?></div>
  </div>
  <hr>

  <div class="row py-2">
    <div class="col-md-3"></div>
    <div class="col-md-9">
      <button type="submit" class="btn btn-primary"><?php echo IMAGE_SAVE; ?></button>
      <a class="btn btn-secondary" href="<?php echo zen_href_link(FILENAME_EVENTZ); ?>"><?php echo IMAGE_CANCEL; ?></a>
    </div>
  </div>
  </form>

<?php } elseif ($action === 'delete' && $eventzEditing) { ?>

  <p><?php echo TEXT_EVENTZ_DELETE_INTRO; ?></p>
  <p><strong><?php echo zen_output_string_protected($eventzEditing['name']); ?></strong> &mdash; <?php echo zen_output_string_protected($eventzEditing['place']); ?> (<?php echo $eventzEditing['startDate']; ?> - <?php echo $eventzEditing['stopDate']; ?>)</p>

  <?php echo zen_draw_form('eventzdelete', FILENAME_EVENTZ, 'action=deleteconfirm&id=' . (int)$eventzEditing['id'], 'post'); ?>
    <button type="submit" class="btn btn-danger"><?php echo IMAGE_DELETE; ?></button>
    <a class="btn btn-secondary" href="<?php echo zen_href_link(FILENAME_EVENTZ); ?>"><?php echo IMAGE_CANCEL; ?></a>
  </form>

<?php } else { ?>

  <p>
    <a class="btn btn-primary" href="<?php echo zen_href_link(FILENAME_EVENTZ, 'action=new'); ?>"><?php echo IMAGE_EVENTZ_NEW_EVENT; ?></a>
<?php
    // Add-on point: more buttons beside Add Event (e.g. Import), as HTML by reference.
    $eventzListTopHtml = '';
    $zco_notifier->notify('NOTIFY_EVENTZ_ADMIN_LIST_TOP', ['show_all' => $eventzShowAll, 'sort' => $eventzSelectedSort], $eventzListTopHtml);
    echo is_string($eventzListTopHtml) ? $eventzListTopHtml : '';
?>
  </p>

  <?php echo zen_draw_form('eventzSort', FILENAME_EVENTZ, '', 'get'); ?>
    <label for="eventzSort"><?php echo TEXT_EVENTZ_SORT_BY; ?></label>
    <select name="sort" id="eventzSort" class="form-control" onchange="this.form.submit()">
    <?php foreach ($eventzSortOptions as $eventzSortKey => $eventzSortOption) { ?>
      <option value="<?php echo $eventzSortKey; ?>"<?php echo ($eventzSortKey === $eventzSelectedSort) ? ' selected' : ''; ?>><?php echo $eventzSortOption['label']; ?></option>
    <?php } ?>
    </select>
    <?php if ($eventzShowAll) { ?>
    <input type="hidden" name="showAll" value="1">
    <?php } ?>
  </form>

  <p>
  <?php if ($eventzShowAll) { ?>
    <a href="<?php echo zen_href_link(FILENAME_EVENTZ, 'sort=' . $eventzSelectedSort); ?>"><?php echo TEXT_EVENTZ_SHOW_ACTIVE_ONLY; ?></a>
  <?php } else { ?>
    <a href="<?php echo zen_href_link(FILENAME_EVENTZ, 'sort=' . $eventzSelectedSort . '&showAll=1'); ?>"><?php echo TEXT_EVENTZ_SHOW_ALL; ?></a>
  <?php } ?>
  </p>

  <?php
  $eventzSortColumn = $eventzSortOptions[$eventzSelectedSort]['column'];
  $eventzSortDirection = $eventzSortOptions[$eventzSelectedSort]['direction'];
  $eventzListSql = "SELECT id, name, place, startDate, stopDate, active FROM " . TABLE_EVENTZ;
  if (!$eventzShowAll) {
      $eventzListSql .= " WHERE active = 1";
  }
  $eventzListSql .= " ORDER BY " . $eventzSortColumn . " " . $eventzSortDirection;
  $eventzListResult = $db->Execute($eventzListSql);
  ?>

  <?php if ($eventzListResult->EOF) { ?>
    <h2 class="eventzNoEventsAdmin"><?php echo $eventzShowAll ? TEXT_EVENTZ_NO_EVENTS : TEXT_EVENTZ_NO_ACTIVE_EVENTS; ?></h2>
  <?php } else { ?>
  <table class="table table-striped">
    <thead>
      <tr>
        <th><?php echo TABLE_HEADING_EVENTZ_NAME; ?></th>
        <th><?php echo TABLE_HEADING_EVENTZ_PLACE; ?></th>
        <th><?php echo TABLE_HEADING_EVENTZ_START_DATE; ?></th>
        <th><?php echo TABLE_HEADING_EVENTZ_STOP_DATE; ?></th>
        <?php if ($eventzShowAll) { ?>
        <th><?php echo TABLE_HEADING_EVENTZ_STATUS; ?></th>
        <?php } ?>
        <th><?php echo TABLE_HEADING_EVENTZ_ACTION; ?></th>
      </tr>
    </thead>
    <tbody>
    <?php while (!$eventzListResult->EOF) { ?>
      <tr<?php echo ((int)$eventzListResult->fields['active'] !== 1) ? ' class="eventzInactiveRow"' : ''; ?>>
        <td><?php echo zen_output_string_protected(eventzStoredText($eventzListResult->fields['name'])); ?></td>
        <td><?php echo zen_output_string_protected(eventzStoredText($eventzListResult->fields['place'])); ?></td>
        <td><?php echo $eventzListResult->fields['startDate']; ?></td>
        <td><?php echo $eventzListResult->fields['stopDate']; ?></td>
        <?php if ($eventzShowAll) { ?>
        <td><?php echo ((int)$eventzListResult->fields['active'] === 1) ? TEXT_EVENTZ_STATUS_ACTIVE : TEXT_EVENTZ_STATUS_INACTIVE; ?></td>
        <?php } ?>
        <td>
          <a href="<?php echo zen_href_link(FILENAME_EVENTZ, 'action=edit&id=' . (int)$eventzListResult->fields['id']); ?>"><?php echo IMAGE_EDIT; ?></a>
          &nbsp;|&nbsp;
          <a href="<?php echo zen_href_link(FILENAME_EVENTZ, 'action=delete&id=' . (int)$eventzListResult->fields['id']); ?>"><?php echo IMAGE_DELETE; ?></a>
<?php
          // Add-on point: more actions for this event (e.g. Duplicate), as HTML by reference.
          $eventzRowActions = '';
          $zco_notifier->notify('NOTIFY_EVENTZ_ADMIN_LIST_ROW_ACTIONS', ['event' => $eventzListResult->fields], $eventzRowActions);
          echo is_string($eventzRowActions) ? $eventzRowActions : '';
?>
        </td>
      </tr>
    <?php
      $eventzListResult->MoveNext();
    } ?>
    </tbody>
  </table>
  <?php } ?>

<?php } ?>

</div>

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>
