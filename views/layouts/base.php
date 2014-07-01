<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1, initial-scale=1, user-scalable=no">

    <title>Stud.IP Mobile</title>

    <link rel="apple-touch-icon" href="<?= $plugin_path ?>/public/images/quickdial/ios.png" type="image/gif" />

    <link rel="stylesheet" href="<?= $plugin_path ?>/public/vendor/jquery.mobile.1.3.2/jquery.mobile-1.3.2.min.css" />
    <link rel="stylesheet" href="<?= $plugin_path ?>/public/vendor/jquery.mobile.themes/studip.css" />
    <link rel="stylesheet" href="<?= $plugin_path ?>/public/stylesheets/mobile.css" />
    <link rel="stylesheet"  href="<?= $plugin_path ?>/public/stylesheets/jquery.swipeButton.css" />

    <script src="<?= $plugin_path ?>/public/vendor/jquery/jquery-1.9.1.min.js"></script>
    <script src="<?= $plugin_path ?>/public/vendor/jquery.mobile.1.3.2/jquery.mobile-1.3.2.min.js"></script>

      <script src="<?= $plugin_path ?>/public/vendor/jquery.mobile.plugins/inlinelistview.js"></script>

    <!-- MAP-->
    <script src="//maps.google.com/maps/api/js?sensor=true" type="text/javascript"></script>
    <script src="<?= $plugin_path ?>/public/vendor/map/jquery.ui.map.full.min.js" type="text/javascript"></script>
    <!-- END MAP-->

    <script src="<?= $plugin_path ?>/public/javascripts/custom.js"></script>
    <!-- CUSTOM END -->
    <script src="<?= $plugin_path ?>/public/vendor/date/date.js"></script>
    <script src="<?= $plugin_path ?>/public/javascripts/jquery.swipeButton.min.js"></script>
    <script>
      var STUDIP = STUDIP || {};
      STUDIP.ABSOLUTE_URI_STUDIP = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>";

      //register, cause external link like class="externallink" data-ajax="false" not work for android standard browser
      $('a.externallink').bind( 'tap', function(){ window.location = this.href; } );

        $(document).ready(function() {

                // attach the plugin to an element
                $('#swipeMe li').swipeDelete({
                        btnTheme: 'f',
                        click: function(e){
                                e.preventDefault();
                                var url = $(e.target).attr('href');
                                $(this).parents('li').remove();
                                $.post(url, function(data) {
                                        console.log(data);
                                });
                        }
                });

        });
    </script>

    <style>
      /* TODO put this into external CSS stylesheets */
      body.calendar .ui-page {
        background: url('<?= $plugin_path ?>/public/images/rag.jpg');
      }
    </style>

  </head>
  <body class="<?= $body_class ?>">
    <?= $content_for_layout ?>
  </body>
</html>
