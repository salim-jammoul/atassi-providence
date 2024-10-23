<?php
/* ----------------------------------------------------------------------
 * app/views/editor/interstitial/interstitial_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013-2024 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */ 
global $g_ui_locale_id;

$t_subject 			= $this->getVar('t_subject');
$vn_subject_id 		= $this->getVar('subject_id');

$va_restrict_to_types = $this->getVar('restrict_to_types');

$vs_field_name_prefix = $this->getVar('field_name_prefix');
$vs_n 				= $this->getVar('n');
$vs_q				= caUcFirstUTF8Safe($this->getVar('q'), true);

$vb_can_edit	 	= true; //$t_subject->isSaveable($this->request);

$vs_form_name = "InterstitialEditorForm";
	
$t_left= $t_subject->getLeftTableInstance();
$t_right= $t_subject->getRightTableInstance();
$vs_rel_name = "<em>".$t_left->getTypeName()."</em> ⇔ <em>".$t_right->getTypeName()."</em>";
?>		
<form action="#" name="<?= $vs_form_name; ?>" method="POST" enctype="multipart/form-data" id="<?= $vs_form_name.$vs_field_name_prefix.$vs_n; ?>">
	<div class='dialogHeader quickAddDialogHeader'><?php 
	print "<div class='quickAddTypeList'>"._t('Edit %1 relationship', $vs_rel_name)."</div>"; 
	
	if ($vb_can_edit) {	
		print "<div style='float: right;'>".caJSButton($this->request, __CA_NAV_ICON_SAVE__, _t("Save"), "{$vs_form_name}{$vs_field_name_prefix}{$vs_n}", array("onclick" => "caSave{$vs_form_name}{$vs_field_name_prefix}{$vs_n}(event);"))
		.' '.caJSButton($this->request, __CA_NAV_ICON_CANCEL__, _t("Cancel"), "{$vs_form_name}{$vs_field_name_prefix}{$vs_n}", array("onclick" => "jQuery(\"#{$vs_form_name}".$vs_field_name_prefix.$vs_n."\").parent().data(\"panel\").hidePanel();"))."</div><br style='clear: both;'/>\n";
	}
?>
	</div>

	<div class="quickAddErrorContainer" id="<?= $vs_form_name; ?>Errors<?= $vs_field_name_prefix.$vs_n; ?>"> </div>

	<div class="quickAddSectionBox" id="{$vs_form_name}Container<?= $vs_field_name_prefix.$vs_n; ?>">
		<div class="quickAddFormTopPadding"><!-- empty --></div>
<?php

			
			if(is_array($va_form_elements = $t_subject->getBundleFormHTMLForScreen($this->getVar('screen'), array(
					'request' => $this->request, 
					'formName' => $vs_form_name.$vs_field_name_prefix.$vs_n,
					'restrictToTypes' => array($t_subject->get('type_id'))
			)))) {
			
				print join("\n", $va_form_elements);
			} else {
			
			//TODO better errors
?>
			<h2><?= _t("No user interface defined"); ?></h2>
<?php
			}
?>
		<input type='hidden' name='_formName' value='<?= $vs_form_name.$vs_field_name_prefix.$vs_n; ?>'/>
		<input type='hidden' name='screen' value='<?= htmlspecialchars($this->getVar('screen')); ?>'/>
		<input type='hidden' name='t' value='<?= $t_subject->tableName(); ?>'/>
		<input type='hidden' name='relation_id' value='<?= $t_subject->getPrimaryKey(); ?>'/>
		<input type='hidden' name='primary' value='<?= $this->getVar('primary_table'); ?>'/>
		<input type='hidden' name='primary_id' value='<?= $this->getVar('primary_id'); ?>'/>
		<input type='hidden' name='type_id' value='<?= $t_subject->get('type_id'); ?>'/>
		<input type='hidden' name='placement_id' value='<?= $this->getVar('placement_id'); ?>'/>
		<input type='hidden' name='n' value='<?= $this->getVar('n'); ?>'/>
		
		<script type="text/javascript">
			function caSave<?= $vs_form_name.$vs_field_name_prefix.$vs_n; ?>(e) {
				jQuery.each(CKEDITOR.instances, function(k, instance) {
					instance.updateElement();
				});
				
				var fdata = new FormData(jQuery('#<?= $vs_form_name.$vs_field_name_prefix.$vs_n; ?>')[0]);   
				$.ajax({
					type: 'POST',
					method: 'POST',
					url: '<?= caNavUrl($this->request, "editor", "Interstitial", "Save"); ?>',
					data: fdata,
					contentType: false,
					processData: false,
					cache: false,
					success: function(resp, textStatus) {
						let fieldPrefix = <?= json_encode($vs_field_name_prefix); ?>;
						if (resp.status == 0) {
							jQuery.jGrowl('<?= addslashes(_t('Saved changes to')); ?> <em>' + resp.display + '</em>', { header: '<?= addslashes(_t('Edit %1', $t_subject->getProperty('NAME_SINGULAR'))); ?>' }); 
							jQuery("#<?= $vs_form_name.$vs_field_name_prefix.$vs_n; ?>").parent().data('panel').hidePanel();
							
							// Type name may be set dynamically during interstitial editing, and needs form prefix set
							var t = jQuery('#caRelationEditorPanel<?= substr($vs_field_name_prefix, 0, strlen($vs_field_name_prefix)-1); ?> .caBundleDisplayTemplate').prop('outerHTML');
							t = t.replace(/_type_id{n}/, fieldPrefix + 'type_id{n}');
							
							let displayContent = jQuery(t).template(resp.bundleDisplay); 
							
							let rel_type_select = jQuery("#<?= $vs_field_name_prefix; ?>BundleTemplateDisplay<?= $this->getVar('n'); ?> .listRelRelationshipTypeEdit").detach(); // Save current relationship type drop-down
							jQuery("#<?= $vs_field_name_prefix; ?>BundleTemplateDisplay<?= $this->getVar('n'); ?>").empty().append(displayContent);
							jQuery("#<?= $vs_field_name_prefix; ?>BundleTemplateDisplay<?= $this->getVar('n'); ?> .listRelRelationshipTypeEdit").replaceWith(rel_type_select); // replace empty relationship dropdown after template refresh with working value
							jQuery("input[name='form_timestamp']").val(resp['time']);
						} else {
							// error
							var content = '<div class="notification-error-box rounded"><ul class="notification-error-box">';
							for(var e in resp.errors) {
								content += '<li class="notification-error-box">' + e + '</li>';
							}
							content += '</ul></div>';

							jQuery("#<?= $vs_form_name; ?>Errors<?= $vs_field_name_prefix.$vs_n; ?>").html(content).slideDown(200);

							var quickAddClearErrorInterval = setInterval(function() {
								jQuery("#<?= $vs_form_name; ?>Errors<?= $vs_field_name_prefix.$vs_n; ?>").slideUp(500);
								clearInterval(quickAddClearErrorInterval);
							}, 3000);
						}
					},
					dataType: 'json'
				});
			}
			
			jQuery(document).ready(function() {
				jQuery('#<?= $vs_form_name; ?>').bind('keydown', 
				function(e){
					e = e || event;
					var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
					return txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
				});
			});
		</script>
	</div>
</form>
