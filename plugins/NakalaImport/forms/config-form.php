<?php $view = get_view(); ?>

<div id="nakala-export-settings">
<h2><?php echo __('Configuration du plugin NAKALA Import'); ?></h2>

    <div class="field">
        <div class="one columns">
            <?php echo $view->formLabel('nakala-hande', __('Handle du dépôt NAKALA lié à cette instance d\'OMEKA')); ?>
        </div>
        
        <div class="inputs five columns omega" style="margin-bottom:0px;">
            <?php echo $view->formText('nakala-handle', $options['nakala-handle']); ?>
            <?php echo $view->formSubmit('test-handle', 'Tester la connectivité du compte'); ?>
        </div>

        <div class="inputs seven columns omega" id="response"></div>

    </div>
</div>

<style>
#nakala-handle {
	float:left;
	width:130px;
	margin-right:15px;
}
</style>

<script>
jQuery(document).ready(function($) {

	var button			= jQuery("#test-handle");
	var input   		= jQuery("#nakala-handle");
	var url 			= "<?php echo html_escape(url('nakala-import/index/test')); ?>";
	var serverResponse 	= jQuery("#response");
		
	button.click(function() {
		var handle 	= input.val();
		$.ajax({
		  method: "POST",
		  url: url,
		  data: { handle: handle }
		})
		  .done(function( response ) {
		    if (response.server_ok)
				serverResponse.html("&nbsp;Réponse du serveur : connexion établie avec le compte \""+handle+"\".");
			else
				serverResponse.html("&nbsp;Réponse du serveur : problème de connectivité avec le compte \""+handle+"\".");
		})
		.fail(function( msg ) {
			alert( "Erreur d'appel Ajax");
		});
		return false;
	});
});
</script>