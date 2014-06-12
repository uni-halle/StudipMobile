<?php
$groups = array();
foreach ($courses as $course) {
  if (!isset($groups[$course['sem_number']])) {
    $groups[$course['sem_number']] = array();
  }
  $groups[$course['sem_number']][] = $course;
}

krsort($groups);
?>

<ul id="courses" data-role="listview" data-filter="true" data-filter-placeholder="Suchen" data-divider-theme="b">
  <? foreach ($groups as $sem_key => $group) { ?>
    <li data-role="list-divider">
      <?= Studip\Mobile\Helper::out($semester[$sem_key]['name']) ?>
    </li>
    <? foreach ($group as $course) { ?>
      <?= $this->render_partial("courses/_list_item", compact("course")) ?>
    <? } ?>
  <? } ?>
</ul>
