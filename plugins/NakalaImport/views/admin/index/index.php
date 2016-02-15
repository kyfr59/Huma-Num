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
    <?php // echo flash(); ?>

    <?php if (!$this->options['nakala-handle']): ?>

        Merci de renseigner <a href="<?php echo html_escape(url('plugins/config', array('name' => 'NakalaImport'))); ?>">l'identifiant Handle</a> correspondant à cette instance du plugin.

    <?php else: ?>

        <h2>Liste des notices à importer</h2>
        <div id="waiting">
            Recherche des notices à importer<?php if (!$this->options['ignore-updates']) echo ' et à mettre à jour'; ?>.<br />
            Pour les dépôts volumineux, cette étape peut prendre du temps tant que le premier import n'est pas entièrement réalisé.<br />
            <img src="<?php echo WEB_ROOT ?>/plugins/NakalaImport/images/progress_bar.gif" style="margin-left:-8px;padding-top:10px;" /></td><td style="width:130px;">
        </div>
        <div id="results"></div>

    <?php endif; ?>    
</div>


<script>
jQuery(document).ready(function($) {

    var url = "<?php echo html_escape(url('nakala-import/index/home')); ?>";
    var results  = jQuery("#results");
    var waiting  = jQuery("#waiting");
    
    jQuery.ajax({
      method: "POST",
      url: url,
      data: {}
    }).done(function( response ) {
        waiting.hide();
        results.append(response);
    });
});
</script>

<?php echo foot(); ?>
