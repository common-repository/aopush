<?php
add_action('admin_print_footer_scripts', 'aoph_page_mailing', 99);
function aoph_page_mailing() {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			loadDateTimePickerPlugin();
			var token = '<?=wp_get_session_token()?>';
			jQuery(document).delegate('#mailing-form', 'submit', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.query_type = 'send';
				form_data.AophMailingNotifications = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					jQuery.each(event.currentTarget, function(index, value){
						if (value.type=='submit') {
							return false;
						}
							
						var name = value.name.replace(/.*\[|\]/gi,'');
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AophMailingNotifications[name] = value.checked;
						} else {
							form_data.AophMailingNotifications[name] = value.value;
						}
					});
				}
					
				reloadForm('mailing', token, form_data, '<?=admin_url()?>');
				event.preventDefault();
				return false;
			});	
			
			jQuery(document).delegate('#AophMailingNotifications_subject, #AophMailingNotifications_text, #AophMailingNotifications_icon, #AophMailingNotifications_link', 'focusout', function(event){
				jQuery(this).parent('div').find('.speech_wrap').hide('slow');
			});
			
			jQuery(document).delegate('#AophMailingNotifications_subject, #AophMailingNotifications_text, #AophMailingNotifications_icon, #AophMailingNotifications_link', 'focus', function(event){
				jQuery(this).parent('div').find('.speech_wrap').show('slow');
			});	
		});
	</script>
<?php } ?>

<div class="row">

	<div class="col-sm-12">
						
		<form id="mailing-form" action="" method="post" class="form-horizontal" role="form">

			<div class="form-group">
				<div class="col-sm-12">
					<div class="bs-callout bs-callout-info">
						<h4><?=__('Mailing list', 'aopush')?></h4>
						<p><i><?=__('Title Mailing list', 'aopush')?></i></p>
						
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
			
			<div id="aoph-mailing-settings">
			
				<div class="form-group">
					<label for="AophMailingNotifications_subject" class="col-sm-2 control-label"><?=__('SubjectPush', 'aopush')?></label>
					<div class="col-sm-10 col-md-6">
						<input type="text" class="form-control" name="AophMailingNotifications[subject]" id="AophMailingNotifications_subject" placeholder="<?=__('SubjectPush', 'aopush')?>" autocomplete="off" required>
						<div class="speech_wrap">
							<p class="speech">
								<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
								</i><small> <?=__('Warning Subject Push Form', 'aopush')?></small>
							</p>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<label for="AophMailingNotifications_text" class="col-sm-2 control-label">
						<?=__('TextPush', 'aopush')?>
					</label>
					<div class="col-sm-10 col-md-6">
						<textarea class="form-control" name="AophMailingNotifications[text]" id="AophMailingNotifications_text" placeholder="<?=__('TextPush', 'aopush')?>" rows="5" required></textarea>
						<div class="speech_wrap">
							<p class="speech">
								<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
								</i><small> <?=__('Warning Text Push Form', 'aopush')?></small>
							</p>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<label for="AophMailingNotifications_icon" class="col-sm-2 control-label">
						<?=__('IconPush', 'aopush')?>
					</label>	
					<div class="col-sm-10 col-md-6">
						<input type="url" name="AophMailingNotifications[icon]" id="AophMailingNotifications_icon" class="form-control" autocomplete="off" required>
						<div class="speech_wrap">
							<p class="speech">
								<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
								</i><small> <?=__('Warning Url Push Form', 'aopush')?></small>
							</p>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<label for="AophMailingNotifications_url" class="col-sm-2 control-label">
						<?=__('LinkPush', 'aopush')?>
					</label>	
					<div class="col-sm-10 col-md-6">
						<input type="url" name="AophMailingNotifications[url]" id="AophMailingNotifications_url" class="form-control" autocomplete="off">
						<div class="speech_wrap">
							<p class="speech">
								<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
								</i><small> <?=__('Warning Link Push Form', 'aopush')?></small>
							</p>
						</div>
					</div>
				</div>
	
			</div>
			
			<div class="form-group">
				<div class="col-sm-2"></div>
				<div class="col-sm-10 col-md-6">
					<input type="submit" class="btn btn-primary pull-left btn-xs-block" value="<?=__('Send', 'aopush')?>">
				</div>
			</div>
			
		</form>
						
	</div>

</div>