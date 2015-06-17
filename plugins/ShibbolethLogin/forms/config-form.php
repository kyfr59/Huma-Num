<?php $view = get_view(); ?>

<div id="shibboleth-login-settings">
<h2><?php echo __('General Settings'); ?></h2>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('admin-email', __('Email of the Shibboleth administrator')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('admin-email', $options['admin-email']); ?>
        </div>
    </div>
  

</div>
