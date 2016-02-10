<?php $view = get_view(); ?>

<div id="nakala-export-settings">
<h2><?php echo __('Configuration du plugin NAKALA Import'); ?></h2>

    <div class="field">
        <div class="one columns">
            <?php echo $view->formLabel('nakala-oai-url', __('URL du dépôt OAI NAKALA liée à cette instance d\'OMEKA :')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('nakala-oai-url', $options['nakala-oai-url']); ?>
        </div>
    </div>
  
</div>
