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

            <table>
               <thead>
                    <tr>
                        <th>Nom de la collection</th>
                        <th style="width:120px; text-align:center;"></th>
                    </tr>
                </thead>
                <tbody>

            <h2>Liste des collections</h2>

                <?php foreach ($this->collections as $collection): ?>
                    <form method="post" action="<?php echo url('nakala-import/');?>">
                        <tr>
                            <td><?php echo cut_string((string)$collection->nomCollection, 80); ?></td>
                            <td style="width:120px; text-align:center;">
                                <?php echo $this->formHidden('collectionUrl', $collection->collectionUrl); ?>
                                <?php echo $this->formHidden('collectionName', $collection->nomCollection); ?>
                                <?php echo $this->formSubmit('import', 'Voir les notices', array('style'=>'margin-bottom:0px;')); ?>
                            </td>
                        </tr>
                    </form>
                <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post" action="<?php echo url('nakala-import/');?>">
                <?php echo $this->formSubmit('import', 'Voir toutes les notices'); ?>
            </form>
        

    <?php endif; ?>    
</div>


<script>
jQuery(document).ready(function($) {

    var selectAll   = jQuery("#select-all");
    var checkboxes  = jQuery("input.checkboxes");

    selectAll.click(function() {

        var isChecked  = jQuery(this).is(':checked');

        checkboxes.each(function(checkbox) {
            jQuery(this).prop('checked', isChecked);;
        });
    });

    checkboxes.click(function() {
        selectAll.prop('checked', false);
    });
});
</script>

<?php echo foot(); ?>
