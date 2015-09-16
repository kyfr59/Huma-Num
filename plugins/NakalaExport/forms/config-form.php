<?php $view = get_view(); ?>

<div id="nakala-export-settings">
<h2><?php echo __('Configuration du plugin NAKALA Export'); ?></h2>

    <div class="field">
        <div class="one columns">
            <?php echo $view->formLabel('nakala-user-handle', __('Handle du compte utilisateur NAKALA :')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('nakala-user-handle', $options['nakala-user-handle']); ?>
        </div>
    </div>
  
    <div class="field">
        <div class="one columns">
            <?php echo $view->formLabel('nakala-user', __('Identifiant du compte utilisateur NAKALA :')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('nakala-user', $options['nakala-user']); ?>
        </div>
    </div>

    <div class="field">
        <div class="one columns">
            <?php echo $view->formLabel('nakala-user-password', __('Mot de passe du compte utilisateur NAKALA :')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('nakala-user-password', $options['nakala-user-password']); ?>
        </div>
    </div>

</div>
