<div data-role="panel" id="leftpanel" data-display="push" data-theme="a">
    <h2>Menü</h2>

    <ul data-role="listview" data-theme="a" class="nav-search" data-inset="false" id="menu_side">

      <li data-icon="false">
        <a href="<?= $controller->url_for("activities") ?>" class="externallink contentLink" data-ajax="false">
          <img src="<?= $plugin_path ?>/public/images/quickdial/bw/news.png" class="ui-li-icon ui-corner-none">
          <?=_("Activity Stream")?>
        </a>
      </li>

      <li data-icon="false">
        <a href="<?= $controller->url_for("calendar") ?>" class="externallink contentLink" data-ajax="false">
          <img src="<?= $plugin_path ?>/public/images/quickdial/bw/schedule.png" class="ui-li-icon ui-corner-none">
          <?=_("Planer")?>
        </a>
      </li>

      <li data-icon="false">
        <a href="<?= $controller->url_for("mails") ?>" class="externallink contentLink" data-ajax="false">
          <img src="<?= $plugin_path ?>/public/images/quickdial/bw/mail.png"   class="ui-li-icon ui-corner-none" />
          <?=_("Nachrichten")?>
        </a>
      </li>

      <li data-icon="false">
        <a href="<?= $controller->url_for("courses") ?>" class="externallink contentLink" data-ajax="false">
          <img src="<?= $plugin_path ?>/public/images/quickdial/bw/seminar.png"   class="ui-li-icon ui-corner-none" />
          <?=_("Veranstaltungen")?>
        </a>
      </li>

      <li data-icon="false">
        <a href="<?= $controller->url_for("profiles/show") ?>" class="externallink contentLink" data-ajax="false">
          <img src="<?= $plugin_path ?>/public/images/quickdial/bw/profile.png"   class="ui-li-icon ui-corner-none" />
          <?=_("Ich")?>
        </a>
      </li>

      <li data-icon="false">
        <a href="mailto:kursmanager@uos.de?subject=Stud.IP Mobile Feedback" class="externallink contentLink" data-ajax="false">
          <img src="<?= $plugin_path ?>/public/images/quickdial/bw/info-circle.png"   class="ui-li-icon ui-corner-none" />
          <?=_("Feedback")?>
        </a>
      </li>

      <li data-icon="false">
        <a href="<?= $controller->url_for("session/destroy") ?>" class="externallink contentLink" data-ajax="false">
          <img src="<?= $plugin_path ?>/public/images/quickdial/bw/logout.png"   class="ui-li-icon ui-corner-none" />
          <?=_("Logout")?>
        </a>
      </li>    
    </ul>
</div><!-- /panel -->
