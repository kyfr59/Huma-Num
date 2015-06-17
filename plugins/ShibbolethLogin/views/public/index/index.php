<?php
echo head(array('title' => __('Add New Shibboleth User'), 'bodyclass' => 'users'));
echo flash();
?>
<h1><?php echo __("Add new Shibboleth user") ?></h1>
<form method="post">
<section class="seven columns alpha">
    <?php echo $this->form; ?>
</section>
<section class="three columns omega">
    <div id="save" class="panel">
        <?php echo $this->formSubmit('submit', __('Create my OMEKA account from Shibboleth'), array('class' => 'submit big green button')); ?>
    </div>
</section>
</form>



<?php echo foot();?>
