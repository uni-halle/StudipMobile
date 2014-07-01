<?
use Studip\Mobile\Helper as Helper;
?>

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
     alt="<?= $categories[$activity['category']] ?>"
     class="ui-li-icon">

<?= Avatar::getAvatar($activity['author_id'])
            ->getImageTag(Avatar::SMALL,
            array("class" => "ui-li-icon activity-avatar")) ?>

<h3><?= Helper::out($activity['title']) ?></h3>

<p class=author>
  <?= _("von") ?> <?= Helper::out($activity['author']) ?>
</p>

<p class=summary>
  <?= Helper::out($activity['content']) ?>
</p>

<? if (!empty($activity["link"])){ ?>
  </a>
<? } ?>
