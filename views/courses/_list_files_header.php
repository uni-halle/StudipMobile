<div data-role="navbar">
    <ul>
      <li>
        <a href="<?= $controller->url_for("courses/", $course->id) ?>"
           class="ui-btn"
           data-theme="a">
          <?= Studip\Mobile\Helper::out($course->name) ?>
        </a>
      </li>
    </ul>
</div>
