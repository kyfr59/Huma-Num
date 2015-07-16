<?php
echo head(array('title' => __('ShibbolethLogin Error'), 'bodyclass' => 'users'));
echo flash();
?>
<h1><?php echo __("The ShibbolethLogin plugin has detected an error ") ?></h1>

<?php echo $this->errorMessage; ?>.

<br /><br />Please contact your administrator : <a href="mailo:<?php echo $this->adminEmail?>"><?php echo $this->adminEmail?></a>

<br /><br /><a href="javascript:history.back();"><?php echo __('Back')?></a>

<?php echo foot();?>
