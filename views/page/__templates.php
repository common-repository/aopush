<?php
$events_type = !empty($id) ? $id : 1;
$events_checked = '';

if ($this->settings['id_template']==1) {
	
	if (!empty(get_option('aoph_pushsender_post_insert'))) {
		$events_checked = 'checked';
	}
	$event_message = __('Help Events Insert', 'aopush');
	
} elseif ($this->settings['id_template']==2) {
	
	if (!empty(get_option('aoph_pushsender_post_update'))) {
		$events_checked = 'checked';
	}
	$event_message = __('Help Events Update', 'aopush');
}

add_action('admin_print_footer_scripts', 'aopush_page_template', 99);
function aopush_page_template() {
	?>
	<script type="text/javascript">
		var token = '<?=wp_get_session_token()?>';
		jQuery(document).ready(function($) {
			jQuery(document).delegate('#templates-form', 'submit', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.AophTemplatesNotifications = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					jQuery.each(event.currentTarget, function(index, value){
						if (value.type=='submit') {
							return false;
						}
						
						var name = value.name.replace(/.*\[|\]/gi,'');
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AophTemplatesNotifications[name] = value.checked;
						} else {
							form_data.AophTemplatesNotifications[name] = value.value;
						}				
					});
				}
	
				reloadForm('templates', token, form_data, '<?=admin_url()?>');
				event.preventDefault();
			});	

			jQuery(document).delegate('#AophTemplatesNotifications_events_type', 'change', function(e){
				var id = jQuery('#AophTemplatesNotifications_events_type').val();
				if(id) {
					jQuery('#aoph-overlay, #aoph-loader').show();
					jQuery('#aoph-form').load('<?=admin_url()?>admin-ajax.php?action=aoph_load_form_templates&data[AophTemplatesNotifications][events_type]=' + id + '&data[token]=' + token, function() {
						loadPage();
					});
				}
			});

			jQuery(document).delegate('#AophTemplatesNotifications_subject, #AophTemplatesNotifications_text, #AophTemplatesNotifications_icon, #AophTemplatesNotifications_used', 'focusout', function(event){
				jQuery(this).parent('div').find('.speech_wrap').hide('slow');
			});
			
			jQuery(document).delegate('#AophTemplatesNotifications_subject, #AophTemplatesNotifications_text, #AophTemplatesNotifications_icon, #AophTemplatesNotifications_used', 'focus', function(event){
				jQuery(this).parent('div').find('.speech_wrap').show('slow');
			});			
		});
	</script>
<?php } ?>

<div class="row" id="aoph-form">
	
	<div class="col-sm-12">
						
		<form id="templates-form" action="" method="POST" class="form-horizontal" role="form">
			
			<div class="form-group">
				<div class="col-sm-12">
					<div class="bs-callout bs-callout-info">
						<h4><?=__('Push Templates', 'aopush')?></h4>
						<p><i><?=__('Title Push Templates', 'aopush')?></i></p>
						
						<div class="message_output">
							<?php if (!empty($this->settings['error'])) : ?>
					
								<p class="alert alert-danger">
									<?=$this->settings['error']?>
								</p>
									
							<?php elseif (!empty($this->settings['success'])) : ?>
								
								<p class="alert alert-success">
									<?=$this->settings['success']?>
								</p>
								
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label for="AophTemplatesNotifications_events_type" class="col-sm-2 control-label"><?=__('EventsType', 'aopush')?></label>
				<div class="col-sm-10 col-md-6">
					<select name="AophTemplatesNotifications[events_type]" class="form-control" id="AophTemplatesNotifications_events_type">
						<option <?=($this->settings['id_template']==1) ? 'selected' : ''?> value="1"><?=__('NewPost', 'aopush')?></option>
						<option <?=($this->settings['id_template']==2) ? 'selected' : ''?> value="2"><?=__('UpdatePost', 'aopush')?></option>
					</select>
				</div>
			</div>

			<div class="form-group">
				<label for="AophTemplatesNotifications_events_type" class="col-sm-2 control-label"><?=__('Event Used', 'aopush')?></label>
				<div class="col-sm-10 col-md-6">
					<input type="checkbox" name="AophTemplatesNotifications[used]" id="AophTemplatesNotifications_used" <?=$events_checked?>>
					<div class="speech_wrap">
						<p class="speech">
							<i class="fa fa-info-circle" aria-hidden="true" style="color:#37799f;font-size:16px"></i> 
							<small> <?=$event_message?></small>
						</p>
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<label for="AophTemplatesNotifications_subject" class="col-sm-2 control-label">
					<?=__('SubjectPush', 'aopush')?>
				</label>	
				<div class="col-sm-10 col-md-6">
					<input type="text" class="form-control" name="AophTemplatesNotifications[subject]" id="AophTemplatesNotifications_subject" value="<?=$this->settings['template'][$this->settings['id_template']]['subject']?>" autocomplete="off" required>
					<div class="speech_wrap">
						<p class="speech">
							<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
							</i><small> <?=__('Warning Subject Push Form', 'aopush')?></small>
						</p>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label for="AophTemplatesNotifications_text" class="col-sm-2 control-label">
					<?=__('TextPush', 'aopush')?>
				</label>
				<div class="col-sm-10 col-md-6">
					<div style="padding:15px;border:1px solid #ccc;border-radius:4px;background:#eee">
						<p class="speech">
							<i class="fa fa-info-circle" aria-hidden="true" style="color:#37799f;font-size:16px"></i> 
							<small> <?=__('WarningTextPush', 'aopush')?></small>
						</p>
					</div>
				</div>	
			</div>
			
			<?php if (!empty($this->settings['template'][$this->settings['id_template']]['icon'])) : ?>
			<div class="form-group">
				<label class="col-sm-2 control-label">
					<?=__('UploadIconPush', 'aopush')?>
				</label>	
				<div class="col-sm-10 col-md-6">
					<img src="<?=$this->settings['template'][$this->settings['id_template']]['icon']?>" title="Push Icon" class="img-responsive" style="max-width:100px">
				</div>
			</div>
			<?php endif; ?>
			
			<div class="form-group">
				<label for="AophTemplatesNotifications_icon" class="col-sm-2 control-label">
					<?=__('IconPush', 'aopush')?>
				</label>	
				<div class="col-sm-10 col-md-6">
					<input type="url" name="AophTemplatesNotifications[icon]" id="AophTemplatesNotifications_icon" class="form-control" value="<?=$this->settings['template'][$this->settings['id_template']]['icon']?>" autocomplete="off" required>
					<div class="speech_wrap">
						<p class="speech">
							<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
							</i><small> <?=__('Warning Url Push Form', 'aopush')?></small>
						</p>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-2"></div>
				<div class="col-sm-10 col-md-6">
					<input type="submit" id="aoph-form-button" class="btn btn-primary pull-left btn-xs-block" value="<?=__('Save', 'aopush')?>">
				</div>
			</div>
			
		</form>
						
	</div>

</div>