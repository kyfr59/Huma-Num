<?php $view = get_view(); ?>

<div id="shibboleth-login-settings">
<h2><?php echo __('General Settings'); ?></h2>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('idp-url', __('URL of IdP service')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('idp-url', $options['idp-url']); ?>
        </div>
    </div>
  

</div>
