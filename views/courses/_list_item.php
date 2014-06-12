<li class="course" data-course="<?= $course['Seminar_id'] ?>">
  <a href="<?= $controller->url_for("courses/show", $course['Seminar_id']) ?>">
    <img class="ui-li-icon ui-corner-none" src="<?= $plugin_path ?>/public/images/quickdial/seminar.png">
    <h3><?= Studip\Mobile\Helper::out($course['Name']) ?></h3>
  </a>
</li>
