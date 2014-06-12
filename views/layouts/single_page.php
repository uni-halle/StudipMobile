<? $this->set_layout("layouts/base") ?>

<div data-role="page" id="<?= $page_id ?: '' ?>"  <?= $back_button ? 'data-add-back-btn="true"' : '' ?>>

  <?= $this->render_partial("layouts/_side_menu") ?>

  <div data-role="header"  data-theme="<?=TOOLBAR_THEME ?>">
  <? if (!$no_side_menu) echo $this->render_partial("layouts/_side_menu_link") ?>
    <h1><?= $page_title ?: 'Stud.IP' ?></h1>

    <?= isset($additional_header) ? $additional_header : "" ?>
  </div><!-- /header -->

  <div data-role="content">
        <?= $content_for_layout ?>
  </div><!-- /content -->
</div>

<?= isset($additional_pages) ? $additional_pages : "" ?>
