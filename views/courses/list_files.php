<?
$page_title  = "Dateien";
$page_id     = "courses-list_files";
$back_button = TRUE;
$popups      = "";
$this->set_layout("layouts/single_page");
?>

<? if (sizeof($files)) { ?>

  <? if (StudipMobile::DROPBOX_ENABLED) : ?>
  <a href="<?= $controller->url_for("courses/dropfiles", $seminar_id) ?>"
     class="externallink" data-ajax="false" data-role="button"
     data-theme="b">
    Alle Dateien in meine Dropbox
  </a><br>
  <? endif ?>



  <ul id="files" data-role="listview" data-split-icon="info" data-split-theme="d" data-filter="true">
    <? foreach($files as $file) { ?>

      <?
      $popup_id = "popup-file-" . $file['id'];
      $filesize = round($file["filesize"] / 1024) . ' kB';
      $new_content = object_get_visit($seminar_id, "documents", false) < $file['chdate'];
      ?>

      <li>
        <a href="<?= $file["link"] ?>" class="externallink" data-ajax="false">
          <img src="<?=$plugin_path ?><?=$file["icon_link"] ?>" class="ui-li-icon">
            <h2 class="<?= $new_content ? 'new-content' : '' ?>">
              <?= Studip\Mobile\Helper::out($file["name"]) ?>
              <span class=file-size><?= $filesize ?></span>
            </h2>
            <? if (trim($file["description"]) !== '') : ?>
              <p><?= Studip\Mobile\Helper::out($file["description"]) ?></p>
            <? endif ?>
        </a>

        <a href="#<?= $popup_id ?>" class="file-details-switch" data-rel="popup">Info</a>

        <? $popups .= $this->render_partial('courses/_file_popup',
                                           compact("popup_id", "file", "filesize")) ?>

      </li>
    <? } ?>
  </ul>

  <?= $popups ?>

<? } else { ?>
  <ul data-role="listview" data-inset="true" data-theme="e">
    <li>Zu dieser Veranstaltung sind leider keine Dateien vorhanden.</li>
  </ul>
<? } ?>
