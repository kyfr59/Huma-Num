<?php
/**
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
* @subpackage Views
*/

echo head(array('title' => 'NAKALA import'));
?>

<div id="primary">
    <img src="<?php echo WEB_ROOT ?>/plugins/NakalaImport/images/logo-huma-num.png"/ style="margin-bottom:30px;">

    <table>
      <thead>
        <tr>
            <th>Titre</th>
            <th>Statut</th>
        </tr>
        </thead>
        <tbody id="start"><tr><td><img src="<?php echo WEB_ROOT ?>/plugins/NakalaImport/images/progress_bar.gif" style="margin-left:-8px" /></td><td style="width:130px;">Initialisation du processus d'import</td></tr></tbody>
        <tbody id="response"></tbody>
        
        <form method="post" action="<?php echo url('nakala-import');?>">
          <tbody><tr><td><?php echo $this->formSubmit('home', "Retour à la page d'accueil"); ?></td></tr></tbody>
        </form>
        
    </table>

    
</div>

<?php echo foot(); ?>

<script>
jQuery(document).ready(function($) {

  var dataUrls        = <?php echo ($this->dataUrls) ?>;
  var importId        = <?php echo ($this->importId) ?>;
  var setPublic       = <?php echo ($this->setPublic) ?>;
  var nbImports       = dataUrls.length;
  var i               = 0;
  var url             = "<?php echo html_escape(url('nakala-import/index/import-via-ajax')); ?>";
  var serverResponse  = jQuery("#response");
  var homeButton      = jQuery("#home");
  var last            = false;

  homeButton.hide();

  call = function() {

    console.log("i: " + i + ", nbImports: " + nbImports + ", nbImports-1 : " + (nbImports-1));
    

    if (jQuery.isNumeric(nbImports) && i >= (nbImports-1))
      last = true;

    jQuery.ajax({
      method: "POST",
      url: url,
      data: { dataUrl:dataUrls[i], 
              i:i,
              last: last,
              setPublic: setPublic,
              importId: importId
            }
    }).done(function( response ) {
        if (i == 1) {
          jQuery("#start").hide();
        }
        
        jQuery("#waiting").remove();

        serverResponse.append("<tr>\
                                <td>" + response.title + "</td>\
                                <td style=\"width:130px\">" + response.insertType + "</td>\
                              </tr>");

        if (response.last == 'false') {
            serverResponse.append("<tr id=\"waiting\">\
                                    <td><img src=\"<?php echo WEB_ROOT ?>/plugins/NakalaImport/images/progress_bar.gif\" style=\"margin-left:-8px;\"/></td>\
                                    <td style=\"width:130px\">En attente</td>\
                                  </tr>");
            call();
        } else {
          homeButton.show();
        }

    });
    i++;

    return false;
  };
  call();

});
</script>
