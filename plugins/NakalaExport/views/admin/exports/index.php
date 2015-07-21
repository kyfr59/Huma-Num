<?php
/**
 * Admin index view.
 * 
 * @package OaipmhHarvester
 * @subpackage Views
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

$head = array('title'      => 'Nakala export | Liste des exports',
              'body_class' => 'primary oaipmh-harvester');
echo head($head);
?>
<style type="text/css">
.base-url, .harvest-status {
    white-space: nowrap;
}

.base-url div{
    max-width: 18em;
    overflow: hidden;
    text-overflow: ellipsis;
}

.harvest-status input[type="submit"] {
    margin: .25em 0 0 0;
}

p.explanation {
    width:600px;
}

input#base_url {
    width:540px;
}
</style>
<div id="primary">

    <div id="harvests">
    <h2>Liste des exports</h2>
    <?php if (empty($this->exports)): ?>
    <p>Il n'y a pas d'exports.</p>
    <?php else: ?>
    <table>
       <thead>
            <tr>
                <th>Débuté le</th>
                <th>Terminé le</th>
                <th>Résultat</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->exports as $export): ?>
            <tr>
                <td title="<?php echo html_escape($harvest->base_url); ?>" class="base-url">
                    <?php echo $export->start_from; ?>
                </td>
                <td title="<?php echo html_escape($harvest->base_url); ?>" class="base-url">
                    <?php echo $export->completed ?>
                </td>
                <td>
                    Résultat
                </td>
                <td>
                    <a href="#"><?php echo $export->status; ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    </div>
</div>
<?php echo foot(); ?>
