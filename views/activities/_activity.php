<? use Studip\Mobile\Helper as Helper; ?>

<? if (!empty($activity["link"])) { ?>

  <? if (Helper::isExternalLink($activity['link'])) : ?>
    <a href="<?= $activity['link'] ?>"
       class="externallink"
       data-ajax="false">

  <? else : ?>
    <a href="<?= $controller->url_for($activity['link']) ?>">
  <? endif ?>

<? } ?>

<img src="<?= $plugin_path ?>/public/images/activities/<?= $activity['category'] ?>.png"
     alt="<?= Helper::out($activity['category']) ?>"
     class="ui-li-icon">

<?= Avatar::getAvatar($activity['author_id'])
            ->getImageTag(Avatar::SMALL,
            array("class" => "ui-li-icon activity-avatar")) ?>

<h3><?= Studip\Mobile\Helper::out($activity['title']) ?></h3>

<p>
  <strong>
    <?= Helper::out($activity['author']) ?>
  </strong>
</p>

<p><?= Helper::out($activity['content']) ?></p>

<p class="ui-li-aside">
  <strong>
    <?= Helper::out($activity['readableTime']) ?>
  </strong>
</p>

<? if (!empty($activity["link"])){ ?>
  </a>
<? } ?>
