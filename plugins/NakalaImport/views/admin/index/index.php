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

    <h2>Liste des notices mises à jour</h2>

    <?php if (!$this->options['nakala-handle']): ?>
        Merci de renseigner <a href="<?php echo html_escape(url('plugins/config', array('name' => 'NakalaImport'))); ?>">l'identifiant Handle</a> correspondant à cette instance du plugin.
    <?php else: ?>

        <?php if (count($this->imports)==0): ?>

            <div>Aucune notice à importer depuis NAKALA</div>

        <?php else: ?>

            <form method="post" action="<?php echo url('nakala-import/index/import');?>">
                <table>
                   <thead>
                        <tr>
                            <th>Titre de la notice</th>
                            <th><center>Type d'import</center></th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->imports as $import): ?>
                        <tr>
                            <td><?php echo cut_string((string)$import->title,80); ?></td>
                            <td><center><?php echo cut_string((string)$import->importType); ?></center></td>
                            <td><center><a href="<?php echo (string)$import->resourceUrl ?>" target="_blank">Fiche NAKALA</a></center></td>
                            <input type="hidden" name="dataUrl[]" value="<?php echo (string)$import->dataUrl ?>" />
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php echo $this->formSubmit('import', 'Importer les notices dans OMEKA'); ?>
                <?php echo $this->formInput('text', $this->options['nakala-handle'], array('type'=>'hidden')); ?>
            </form>

            <div>
                <?php echo nl2br(htmlspecialchars($_SESSION['sparql_query'])) ?>
            </div>


        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php echo foot(); ?>
