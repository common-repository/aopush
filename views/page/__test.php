<?php
add_action('admin_print_footer_scripts', 'aoph_page_test', 99);
function aoph_page_test() {
	?>
	<script type="text/javascript">
		var token = '<?=wp_get_session_token()?>';

		function unsibscribeTestPush(form_data) {
			if ('serviceWorker' in navigator) {
				navigator.serviceWorker.ready.then(function(uns) {
					uns.pushManager.getSubscription().then(function(subscription) {
						if (typeof(subscription.unsubscribe()) !== 'undefined' && subscription.unsubscribe()!==null) {
							subscription.unsubscribe().then(function(successful) {
								form_data.AophTestNotifications['message'] = '<?=__('Unsubscribe Success', 'aopush')?>';
								form_data.AophTestNotifications['error'] = 0;
								return subscribePush(form_data);
							}).catch(function(e) {
								form_data.AophTestNotifications['message'] = '<?=__('Unsubscribe Error', 'aopush')?>';
								form_data.AophTestNotifications['error'] = 1;
								return subscribePush(form_data);
							});
						}
					}).catch(function(error) {
						form_data.AophTestNotifications['message'] = '<?=__('Unsubscribe Already', 'aopush')?>';
						form_data.AophTestNotifications['error'] = 1;
						return subscribePush(form_data);
					});
				});
			}
		}		

		function subscribePush(form_data) {
			if ('serviceWorker' in navigator) {
				var alert_subs = new Promise(function(resolve, reject) {
					const permissionResult = Notification.requestPermission(function(result) {
						if (result=='granted') {
							navigator.serviceWorker.register('/sw.js').then(function(reg) {
								reg.pushManager.subscribe({
									userVisibleOnly: true
								}).then(function(sub) {								
									fetch('https://aoserver.ru/resurces/push2/pushdata?' + form_data.AophTestNotifications['hash'], {
										method: 'POST',
										headers: {
											'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
										},
										body: 'sub=' + JSON.stringify(sub) + '&allowUrl=' + form_data.AophTestNotifications['domain'] + '&idContact=' + form_data.AophTestNotifications['id_user'] + '&test=' + form_data.AophTestNotifications['test'] + '&login=' +form_data.AophTestNotifications['email']
									});
									
									form_data.AophTestNotifications['message'] = '<?=__('Subscribe Success', 'aopush')?>';
									form_data.AophTestNotifications['error'] = 0;
		
								}).catch(function(error) {
									form_data.AophTestNotifications['message'] = '<?=__('Could not create subscription', 'aopush')?>';
									form_data.AophTestNotifications['error'] = 1;
								});
							}).catch(function(error) {
								form_data.AophTestNotifications['message'] = '<?=__('Could not create subscription', 'aopush')?>';
								form_data.AophTestNotifications['error'] = 1;
							});
						}
					});

					if (permissionResult) {
						permissionResult.then(resolve, reject);
					}
					
				}).then(function(permissionResult) {
					if (permissionResult !== 'granted') {
						form_data.AophTestNotifications['message'] = '<?=__('Could not create subscription', 'aopush')?>';
						form_data.AophTestNotifications['error'] = 1;
					}
				});
			}

			reloadForm('subscribe', form_data.token, form_data, '<?=admin_url()?>');
		}
		
		jQuery(document).ready(function($) {
			jQuery(document).delegate('#test-form', 'submit', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.AophTestNotifications = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					jQuery.each(event.currentTarget, function(index, value){
						if (value.type=='submit') {
							return false;
						}
							
						var name = value.name.replace(/.*\[|\]/gi,'');
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AophTestNotifications[name] = value.checked;
						} else {
							if (name=='icon' && !value.value) {
								value.value = value.placeholder;
							}

							form_data.AophTestNotifications[name] = value.value;
						}
					});
				}

				reloadForm('test', token, form_data, '<?=admin_url()?>');
				event.preventDefault();
				return false;
			});	
			
			jQuery(document).delegate('#subscribe-send', 'click', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.AophTestNotifications = {};
				form_data.AophTestNotifications['id_user'] = <?=get_current_user_id()?>;
				form_data.AophTestNotifications['hash'] = '<?=get_option('aoph_pushsender_token')?>';
				form_data.AophTestNotifications['test'] = 1;
				form_data.AophTestNotifications['action'] = 'subscribe';
				form_data.AophTestNotifications['id_form'] = 'test';
				form_data.AophTestNotifications['login'] = '<?=get_option('aoph_pushsender_email')?>';
				form_data.AophTestNotifications['domain'] = '<?=get_option('aoph_pushsender_domain')?>';
				unsibscribeTestPush(form_data);
				event.preventDefault();
				return false;
			});	
			
			jQuery(document).delegate('#AophTestNotifications_subject, #AophTestNotifications_text, #AophTestNotifications_icon', 'focusout', function(event){
				jQuery(this).parent('div').find('.speech_wrap').hide('slow');
			});
			
			jQuery(document).delegate('#AophTestNotifications_subject, #AophTestNotifications_text, #AophTestNotifications_icon', 'focus', function(event){
				jQuery(this).parent('div').find('.speech_wrap').show('slow');
			});
		});
	</script>
<?php } ?>

<div class="row">

	<div class="col-sm-12">
						
		<form id="test-form" action="" method="post" class="form-horizontal" role="form">
			
			<div class="form-group">
				<div class="col-sm-12">
					<div class="bs-callout bs-callout-info">
						<h4><?=__('Test Push', 'aopush')?></h4>
						<p><i><?=__('Detail Send Push Test', 'aopush')?></i></p>

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
				<label for="AophTestNotifications_subject" class="col-sm-2 control-label"><?=__('SubjectPush', 'aopush')?></label>
				<div class="col-sm-10 col-md-6">
					<input type="text" class="form-control" name="AophTestNotifications[subject]" id="AophTestNotifications_subject" placeholder="<?=__('SubjectPush', 'aopush')?>" autocomplete="off" required>
					<div class="speech_wrap">
						<p class="speech">
							<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
							</i><small> <?=__('Warning Subject Push Form', 'aopush')?></small>
						</p>
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<label for="AophTestNotifications_text" class="col-sm-2 control-label">
					<?=__('TextPush', 'aopush')?>
				</label>
				<div class="col-sm-10 col-md-6">
					<textarea class="form-control" name="AophTestNotifications[text]" id="AophTestNotifications_text" placeholder="<?=__('TextPush', 'aopush')?>" rows="5" required></textarea>
					<div class="speech_wrap">
						<p class="speech">
							<i style="color:#e6c302" class="fa fa-exclamation-triangle" aria-hidden="true">
							</i><small> <?=__('Warning Text Push Form', 'aopush')?></small>
						</p>
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<label for="AophTestNotifications_icon" class="col-sm-2 control-label">
					<?=__('IconPush', 'aopush')?>
				</label>	
				<div class="col-sm-10 col-md-6">
					<input type="url" name="AophTestNotifications[icon]" id="AophTestNotifications_icon" class="form-control" placeholder="<?=plugins_url('assets/img/default_push_icon.png', dirname(dirname(__FILE__)))?>" autocomplete="off">
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
					<div class="">
						<input type="button" style="margin-right:4px" class="btn btn-success pull-left btn-xs-block" id="subscribe-send" name="subscribe" value="<?=__('Subscribe', 'aopush')?>">
						<input type="submit" class="btn btn-primary pull-left btn-xs-block" name="send" value="<?=__('Send', 'aopush')?>">
					</div>
				</div>
			</div>
			
		</form>
		
	</div>

</div>	