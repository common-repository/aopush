<?php
add_action('admin_print_footer_scripts', 'aoph_page_settings', 99);
function aoph_page_settings() {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var token = '<?=wp_get_session_token()?>';
			jQuery(document).delegate('#settings-form', 'submit', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.AophPushSettings = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					jQuery.each(event.currentTarget, function(index, value){
						if (value.type=='submit') {
							return false;
						}
							
						var name = value.name.replace(/.*\[|\]/gi,'');
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AophPushSettings[name] = value.checked;
						} else {
							form_data.AophPushSettings[name] = value.value;
						}
					});
				}

				reloadForm('settings', token, form_data, '<?=admin_url()?>');
				event.preventDefault();
				return false;
			});	
			
			jQuery(document).delegate('#AophSettingsNotifications_login, #AophSettingsNotifications_domain', 'focusout', function(event){
				jQuery(this).parent('div').find('.speech_wrap').hide('slow');
			});
			
			jQuery(document).delegate('#AophSettingsNotifications_login, #AophSettingsNotifications_domain', 'focus', function(event){
				jQuery(this).parent('div').find('.speech_wrap').show('slow');
			});
		});
	</script>
<?php } ?>

<div class="row">

	<div class="col-sm-12">
						
		<form id="settings-form" action="" method="post" class="form-horizontal" role="form">
			
			<div class="form-group">
				<div class="col-sm-12">
					<div class="bs-callout bs-callout-info">
						<h4><?=__('Basic settings', 'aopush')?></h4>
						
						<?php if (empty($this->settings['active'])) : ?>
							
							<p class="alert alert-warning">
								<i><?=empty($this->settings['active']) ? __('Title Settings', 'aopush') : ''?></i>
							</p>
							
						<?php endif; ?>

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
			
			<?php if (empty($this->settings['active'])) : ?>
			
				<div class="form-group">
					<label for="AophSettingsNotifications_login" class="col-sm-2 control-label"><?=__('Email', 'aopush')?></label>
					<div class="col-sm-10 col-md-6">
						<input type="email" class="form-control" name="AophSettingsNotifications[login]" id="AophSettingsNotifications_login" placeholder="email@example.com" autocomplete="off" required>
						<div class="speech_wrap">
							<p class="speech">
								<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
								</i><small> <?=__('Warning Login Form', 'aopush')?></small>
							</p>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<div class="col-sm-2"></div>
					<div class="col-sm-10 col-md-6">
						<input type="submit" class="btn btn-primary pull-left btn-xs-block" value="<?=__('Further', 'aopush')?>">
					</div>
				</div>

			<?php else: ?>
			
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=__('Email', 'aopush')?></label>
					<div class="col-sm-10 col-md-6">
						<?=get_option('aoph_pushsender_email')?>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=__('Domain', 'aopush')?></label>
					<div class="col-sm-10 col-md-6">
						<?=get_option('aoph_pushsender_domain')?>
					</div>
				</div>
				
				<div class="form-group">
					<label for="AophSettingsNotifications_event_used" class="col-sm-2 control-label"><?=__('UsePushSite', 'aopush')?></label>
					<div class="col-sm-10 col-md-6">
						<input type="checkbox" name="AophSettingsNotifications[event_used]" id="AophSettingsNotifications_event_used" <?=!empty(get_option('aoph_pushsender_post_used')) ? "checked" : ""?>>
						<p class="speech">
							<i class="fa fa-info-circle" aria-hidden="true" style="color:#37799f;font-size:16px"></i> 
							<small> <?=__('Warning used checkbox', 'aopush')?></small>
						</p>
					</div>
				</div>
				
				<div class="form-group">
					<div class="col-sm-2"></div>
					<div class="col-sm-10 col-md-6">
						<input type="submit" class="btn btn-primary pull-left btn-xs-block" value="<?=__('Save', 'aopush')?>">
					</div>
				</div>

			<?php endif; ?>

		</form>
						
	</div>

</div>