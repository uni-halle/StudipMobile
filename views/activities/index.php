<?
$this->set_layout("layouts/single_page");
$page_title = _("Aktivitäten");
$page_id = "activities-index";

$categories = array(
  'files'   => _("Neue Datei"),
  'forum'   => _("Neuer Beitrag"),
  'info'    => _("Neue Info"),
  'news'    => _("Neue Ankündigung"),
  'surveys' => _("Neue Evaluation"),
  'votings' => _("Neue Umfrage"),
  'wiki'    => _("Neue Wikiseite")
);

$normalize_time = function ($time) { return 86400 * intval($time / 86400); };
$last_date = null;

$beautify_date = function ($time) use ($normalize_time) {
  $now       = time();
  $today     = $normalize_time($now);
  $yesterday = $today - 86400;

  if ($time > $now) {
    return _("zukünftig");
  }

  if ($time >= $today) {
    return _("heute");
  }

  if ($time >= $yesterday) {
    return _("gestern");
  }
  return _("am") . " " . date("d.m.Y", $time);
};
?>

<ul id="activities" data-role="listview" data-filter="true" data-filter-placeholder="Suchen">
  <? foreach ($activities as $activity) { ?>
    <? $atime = $normalize_time($activity['updated']); ?>
    <? if ($last_date != $atime) : ?>
      <? $last_date = $atime; ?>
      <li data-role="list-divider"><?= $beautify_date($last_date)?></li>
    <? endif ?>

    <li class="activity" data-activity="<?= $activity['id'] ?>">
      <?= $this->render_partial('activities/_activity', compact('activity', 'categories')) ?>
    </li>
  <? } ?>
</ul>
