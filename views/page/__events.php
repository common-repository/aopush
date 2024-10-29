<?php
add_action('admin_print_footer_scripts', 'aoph_page_events', 99);
function aoph_page_events() {
	?>
	<script type="text/javascript">
		var token = '<?=wp_get_session_token()?>';
		jQuery(document).ready(function($) {
			jQuery(document).delegate('#events-form', 'submit', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.AophEventsNotifications = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					jQuery.each(event.currentTarget, function(index, value){
						if (value.type=='submit') {
							return false;
						}
							
						var name = value.name.replace(/.*\[|\]/gi,'');
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AophEventsNotifications[name] = value.checked;
						} else {
							form_data.AophEventsNotifications[name] = value.value;
						}
					});
				}
					
				reloadForm('events', token, form_data, '<?=admin_url()?>');
				event.preventDefault();
			});	
		});
	</script>
<?php } ?> 

<div class="row">

	<div class="col-sm-12">
						
		<form id="events-form" action="" method="post" class="form-horizontal" role="form">
			
			<div class="form-group">
				<div class="col-sm-12">
					<div class="bs-callout bs-callout-info">
						<h4><?=__('Notifications', 'aopush')?></h4>
						<p><i><?=__('Title Notifications', 'aopush')?></i></p>
						
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
				<label for="AophEventsNotifications_insert" class="col-sm-2 control-label"><?=__('Create a record', 'aopush')?></label>
				<div class="col-sm-10 col-md-6">
					<input type="checkbox" name="AophEventsNotifications[insert]" id="AophEventsNotifications_insert" <?=!empty(get_option('aoph_pushsender_post_insert')) ? "checked" : ""?>>
				</div>
			</div>
			
			<div class="form-group">
				<label for="AophEventsNotifications_update" class="col-sm-2 control-label"><?=__('Change record', 'aopush')?></label>
				<div class="col-sm-10 col-md-6">
					<input type="checkbox" name="AophEventsNotifications[update]" id="AophEventsNotifications_update" <?=!empty(get_option('aoph_pushsender_post_update')) ? "checked" : ""?>>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-2"></div>
				<div class="col-sm-10 col-md-6">
					<input type="submit" class="btn btn-primary pull-left btn-xs-block" name="AomailerEvents[btn]" value="<?=__('Save', 'aopush')?>">
				</div>
			</div>
			
		</form>
						
	</div>

</div>
