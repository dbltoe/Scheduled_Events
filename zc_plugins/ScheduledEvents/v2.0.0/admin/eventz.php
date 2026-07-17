<?php
/**
 * @package scheduled events
 * @subpackage plugins
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://zen-cart.com GNU Public License V2.0
 * @version $Id: eventz.php 2026-07-16 05:30:22Z dbltoe $
 */

/**
 * Scheduled Events - admin CRUD page for the eventz table
 */
require('includes/application_top.php');

// Defensive fallback: this page's own lang.eventz.php should auto-load via
// the per-page convention, but that hasn't reliably happened on every
// tested store/routing setup. Load it directly if it hasn't already.
// Plugin files stay inside zc_plugins/... permanently (never copied into
// admin/includes/languages/), so __DIR__ - a known-fixed path relative to
// this file's own real location - is used instead of any of Zen Cart's
// DIR_FS_*/DIR_WS_* constants, which proved unreliable on the catalog side.
if (!defined('HEADING_TITLE_EVENTZ')) {
    $eventzLanguageDir = $language ?? ($_SESSION['language'] ?? 'english');
    $eventzLangFile = __DIR__ . '/includes/languages/' . $eventzLanguageDir . '/lang.eventz.php';
    if (is_file($eventzLangFile)) {
        $eventzDefines = require $eventzLangFile;
        if (is_array($eventzDefines)) {
            foreach ($eventzDefines as $eventzDefineKey => $eventzDefineValue) {
                if (!defined($eventzDefineKey)) {
                    define($eventzDefineKey, $eventzDefineValue);
                }
            }
        }
    }
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

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $messageStack->add_session($error, 'error');
            }
            $redirectParams = 'action=' . ($action === 'insert' ? 'new' : 'edit') . ($id > 0 ? '&id=' . $id : '');
            zen_redirect(zen_href_link(FILENAME_EVENTZ, $redirectParams));
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
            $messageStack->add_session(SUCCESS_EVENTZ_INSERTED, 'success');
        } else {
            zen_db_perform(TABLE_EVENTZ, $sqlData, 'update', "id = " . (int)$id);
            $messageStack->add_session(SUCCESS_EVENTZ_UPDATED, 'success');
        }

        zen_redirect(zen_href_link(FILENAME_EVENTZ));
        break;

    case 'deleteconfirm':
        $db->Execute("DELETE FROM " . TABLE_EVENTZ . " WHERE id = " . (int)$id);
        $messageStack->add_session(SUCCESS_EVENTZ_DELETED, 'success');
        zen_redirect(zen_href_link(FILENAME_EVENTZ));
        break;

    default:
        break;
}

$eventzEditing = null;
if (($action === 'edit' || $action === 'delete') && $id > 0) {
    $result = $db->Execute("SELECT * FROM " . TABLE_EVENTZ . " WHERE id = " . (int)$id);
    if (!$result->EOF) {
        $eventzEditing = $result->fields;
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
<link rel="stylesheet" href="/zc_plugins/ScheduledEvents/v2.0.0/admin/includes/css/eventz.css">
<title><?php echo TITLE; ?></title>
</head>
<body>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div class="content-area eventzContent">
  <h1><?php echo $eventzPageTitle; ?></h1>

<?php if ($action === 'new' || $action === 'edit') { ?>

  <p class="eventzRequiredNotice">* <?php echo TEXT_EVENTZ_REQUIRED_INFORMATION; ?></p>

  <?php echo zen_draw_form('eventz', FILENAME_EVENTZ, 'action=' . ($action === 'new' ? 'insert' : 'update') . ($eventzEditing ? '&id=' . (int)$eventzEditing['id'] : ''), 'post'); ?>

  <div class="form-group">
    <label for="name"><?php echo ENTRY_EVENTZ_NAME; ?><span class="eventzRequired">*</span></label>
    <?php echo zen_draw_input_field('name', $eventzEditing['name'] ?? '', 'id="name" class="form-control" maxlength="255" required'); ?>
  </div>

  <div class="form-group">
    <label for="place"><?php echo ENTRY_EVENTZ_PLACE; ?><span class="eventzRequired">*</span></label>
    <?php echo zen_draw_input_field('place', $eventzEditing['place'] ?? '', 'id="place" class="form-control" maxlength="255" required'); ?>
  </div>

  <div class="form-group">
    <label for="startDate"><?php echo ENTRY_EVENTZ_START_DATE; ?><span class="eventzRequired">*</span></label>
    <?php echo zen_draw_input_field('startDate', $eventzEditing['startDate'] ?? '', 'id="startDate" class="form-control" required', false, 'date'); ?>
    <small><?php echo TEXT_EVENTZ_DATE_FORMAT_HELP; ?></small>
  </div>

  <div class="form-group">
    <label for="stopDate"><?php echo ENTRY_EVENTZ_STOP_DATE; ?><span class="eventzRequired">*</span></label>
    <?php echo zen_draw_input_field('stopDate', $eventzEditing['stopDate'] ?? '', 'id="stopDate" class="form-control" required', false, 'date'); ?>
    <small><?php echo TEXT_EVENTZ_DATE_FORMAT_HELP; ?></small>
  </div>

  <div class="form-group">
    <label for="comments"><?php echo ENTRY_EVENTZ_COMMENTS; ?></label>
    <?php echo zen_draw_textarea_field('comments', 'soft', 60, 4, $eventzEditing['comments'] ?? '', 'id="comments" class="form-control"'); ?>
  </div>

  <div class="form-group">
    <label for="boothLocation"><?php echo ENTRY_EVENTZ_BOOTH_LOCATION; ?><span class="eventzRequired">*</span></label>
    <?php echo zen_draw_input_field('boothLocation', $eventzEditing['boothLocation'] ?? '', 'id="boothLocation" class="form-control" maxlength="255" required'); ?>
    <small><?php echo TEXT_EVENTZ_BOOTH_LOCATION_HELP; ?></small>
  </div>

  <div class="form-group">
    <label for="boothLocationUrl"><?php echo ENTRY_EVENTZ_BOOTH_LOCATION_URL; ?></label>
    <?php echo zen_draw_input_field('boothLocationUrl', $eventzEditing['boothLocationUrl'] ?? '', 'id="boothLocationUrl" class="form-control" maxlength="500"'); ?>
    <small><?php echo TEXT_EVENTZ_BOOTH_LOCATION_URL_HELP; ?></small>
  </div>

  <div class="form-group">
    <label for="eventInformation"><?php echo ENTRY_EVENTZ_EVENT_INFORMATION; ?></label>
    <?php echo zen_draw_input_field('eventInformation', $eventzEditing['eventInformation'] ?? '', 'id="eventInformation" class="form-control"'); ?>
    <small><?php echo TEXT_EVENTZ_EVENT_INFO_HELP; ?></small>
  </div>

  <div class="form-group">
    <label for="eventInformationUrl"><?php echo ENTRY_EVENTZ_EVENT_INFORMATION_URL; ?></label>
    <?php echo zen_draw_input_field('eventInformationUrl', $eventzEditing['eventInformationUrl'] ?? '', 'id="eventInformationUrl" class="form-control" maxlength="500"'); ?>
    <small><?php echo TEXT_EVENTZ_EVENT_INFO_URL_HELP; ?></small>
  </div>

  <div class="form-group">
    <label for="drivingDirections"><?php echo ENTRY_EVENTZ_DRIVING_DIRECTIONS; ?></label>
    <?php echo zen_draw_textarea_field('drivingDirections', 'soft', 60, 4, $eventzEditing['drivingDirections'] ?? '', 'id="drivingDirections" class="form-control"'); ?>
    <small><?php echo TEXT_EVENTZ_DRIVING_DIRECTIONS_HELP; ?></small>
  </div>

  <div class="form-group form-check">
    <?php echo zen_draw_checkbox_field('active', '1', !isset($eventzEditing['active']) || (int)$eventzEditing['active'] === 1, 'id="active" class="form-check-input"'); ?>
    <label for="active" class="form-check-label"><?php echo ENTRY_EVENTZ_ACTIVE; ?></label>
    <small><?php echo TEXT_EVENTZ_ACTIVE_HELP; ?></small>
  </div>

  <div class="form-group">
    <button type="submit" class="btn btn-primary"><?php echo IMAGE_SAVE; ?></button>
    <a class="btn btn-secondary" href="<?php echo zen_href_link(FILENAME_EVENTZ); ?>"><?php echo IMAGE_CANCEL; ?></a>
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
        <td><?php echo zen_output_string_protected($eventzListResult->fields['name']); ?></td>
        <td><?php echo zen_output_string_protected($eventzListResult->fields['place']); ?></td>
        <td><?php echo $eventzListResult->fields['startDate']; ?></td>
        <td><?php echo $eventzListResult->fields['stopDate']; ?></td>
        <?php if ($eventzShowAll) { ?>
        <td><?php echo ((int)$eventzListResult->fields['active'] === 1) ? TEXT_EVENTZ_STATUS_ACTIVE : TEXT_EVENTZ_STATUS_INACTIVE; ?></td>
        <?php } ?>
        <td>
          <a href="<?php echo zen_href_link(FILENAME_EVENTZ, 'action=edit&id=' . (int)$eventzListResult->fields['id']); ?>"><?php echo IMAGE_EDIT; ?></a>
          &nbsp;|&nbsp;
          <a href="<?php echo zen_href_link(FILENAME_EVENTZ, 'action=delete&id=' . (int)$eventzListResult->fields['id']); ?>"><?php echo IMAGE_DELETE; ?></a>
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
