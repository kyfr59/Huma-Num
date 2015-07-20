<?php
/**
 * Admin index view.
 * 
 * @package OaipmhHarvester
 * @subpackage Views
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

$head = array('title'      => 'Nakala import',
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

<img src="<?php echo WEB_ROOT ?>/plugins/NakalaImport/images/logo-huma-num.png"/ style="margin-bottom:30px;">
<?php echo flash(); ?>
    <h2>Source des données</h2>
    <?php echo $this->harvestForm; ?> 
    <br/>
    <div id="harvests">
    <h2>Liste des imports</h2>
    <?php if (empty($this->harvests)): ?>
    <p>Il n'y a pas d'imports.</p>
    <?php else: ?>
    <table>
       <thead>
            <tr>
                <th>URL OAI</th>
                <th>Prefixe</th>
                <th>Section</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->harvests as $harvest): ?>
            <tr>
                <td title="<?php echo html_escape($harvest->base_url); ?>" class="base-url">
                    <div><?php echo html_escape($harvest->base_url); ?></div>
                </td>
                <td><?php echo html_escape($harvest->metadata_prefix); ?></td>
                <td>
                    <?php
                    if ($harvest->set_spec):
                        echo html_escape($harvest->set_name)
                            . ' (' . html_escape($harvest->set_spec) . ')';
                    else:
                        echo '[Répertoire complet]';
                    endif;
                    ?>
                </td>
                <td class="harvest-status">
                    <a href="<?php echo url("nakala-import/index/status?harvest_id={$harvest->id}"); ?>"><?php echo html_escape(ucwords($harvest->status)); ?></a>
                    <?php if ($harvest->status == OaipmhHarvester_Harvest::STATUS_COMPLETED): ?>
                        <br>
                        <form method="post" action="<?php echo url('nakala-import/index/harvest');?>">
                        <?php echo $this->formHidden('harvest_id', $harvest->id); ?>
                        <?php echo $this->formSubmit('submit_reharvest', 'Importer à nouveau'); ?>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    </div>
</div>
<?php echo foot(); ?>
