<?
$this->set_layout("layouts/single_page");
$page_title = "Stud.IP - Activity Feed";
$page_id = "activities-index";
?>

<ul id="activities" data-role="listview" data-filter="true" data-filter-placeholder="Suchen">
  <? foreach ($activities as $activity) { ?>
    <li class="activity" data-activity="<?= $activity['id'] ?>">
      <?= $this->render_partial('activities/_activity', compact('activity')) ?>
    </li>
  <? } ?>
</ul>
