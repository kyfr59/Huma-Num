<?php
echo head(array('title' => __('ShibbolethLogin Error'), 'bodyclass' => 'users'));
echo flash();
?>
<h1><?php echo __("The ShibbolethLogin plugin has detected an error ") ?></h1>

<?php echo $this->errorMessage; ?>

<?php echo foot();?>
