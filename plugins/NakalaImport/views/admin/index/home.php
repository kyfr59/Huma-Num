<?php
/**
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
* @subpackage Views
*/

?>

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
                <?php if (!$this->options['ignore-updates']): ?>
                    <?php echo $this->formCheckbox('ignore_updates', 1); ?>&nbsp;<strong>Ignorer les mises à jour</strong> (seules les notices à ajouter seront traitées)<br />
                    Vous pouvez interrompre l'import à tout moment en quittant cette page ou en cliquant sur un lien.
                <?php endif; ?>
            </form>

        <?php endif; ?>
        
    
</div>


