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
        <!--
        <form method="post" action="<?php echo url('nakala-import');?>">
          <tbody><tr><td><?php echo $this->formSubmit('import', "Reprendre l'import ultérieuement"); ?></td></tr></tbody>
        </form>
        -->
    </table>

    
</div>

<?php echo foot(); ?>

<script>
jQuery(document).ready(function($) {

  var dataUrls        = <?php echo ($this->dataUrls) ?>;
  var importId        = <?php echo ($this->importId) ?>;
  var nbImports       = dataUrls.length;
  var i               = 0;
  var url             = "<?php echo html_escape(url('nakala-import/index/import-via-ajax')); ?>";
  var serverResponse  = jQuery("#response");
  var last            = false;

  call = function() {

    console.log(dataUrls[i]);
    
    if (i >= (nbImports-1))
      last = true;

    jQuery.ajax({
      method: "POST",
      url: url,
      data: { dataUrl:dataUrls[i], 
              i:i,
              last: last,
              importId: importId
            }
    }).done(function( response ) {

        if (i == 1) {
          console.log(jQuery("#start"));
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
        }

    });
    i++;

    return false;
  };
  call();

});
</script>
