<?php
if (!empty($this->is_plugin_page)) {
	$this->history = $this->aopush_loadHistory();
	if (!empty($this->history['data']['error'])) {
		$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__($this->history['data']['error'], 'aopush'), $this->settings['error']);
	}
}

$name = [
	1 => __('NewPost', 'aopush'),
	2 => __('UpdatePost', 'aopush'),
	3 => __('Send Mailing list', 'aopush'),
];

add_action('admin_print_footer_scripts', 'aoph_page_history', 99);
function aoph_page_history() {
	?>
	<script type="text/javascript">
		var token = '<?=wp_get_session_token()?>';
		jQuery(document).ready(function($) {
			jQuery(document).delegate('#history-form', 'submit', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.AophHistoryNotifications = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					jQuery.each(event.currentTarget, function(index, value){
						if (value.type=='submit') {
							return false;
						}
							
						var name = value.name.replace(/.*\[|\]/gi,'');
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AophHistoryNotifications[name] = value.checked;
						} else {
							form_data.AophHistoryNotifications[name] = value.value;
						}
					});
				}
					
				reloadForm('history', token, form_data, '<?=admin_url()?>');
				event.preventDefault();
			});	
		});
	</script>
<?php } ?>

<div class="row">

	<div class="col-sm-12">
						
		<div class="row">
			<div class="col-sm-12">
				<div class="bs-callout bs-callout-info">
					<h4><?=__('Stastistics Send Push', 'aopush')?></h4>
					<p><i><?=__('Title Stastistics Send Push', 'aopush')?></i></p>
					
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
		
		<div class="row" id="aomp-history-load-content">
			<div class="col-sm-10 col-md-8 col-lg-6">

				<div class="table-responsive">
					<table class="table table-striped table-hover table-condensed">
						<thead>
							<tr>
								<th><?=__('Send Type', 'aopush')?></th>
								<th><?=__('Send Push', 'aopush')?></th>
								<th><?=__('Productive', 'aopush')?></th>
							</tr>
						</thead>
						<tbody>
							
							<?php if (empty($this->history['data']['stat'])) : ?>
								<tr>
									<td><?=$name[1]?></td>
									<td style="font-weight:bold">0</td>
									<td style="font-weight:bold">0</td>
								</tr>
								<tr>
									<td><?=$name[2]?></td>
									<td style="font-weight:bold">0</td>
									<td style="font-weight:bold">0</td>
								</tr>
								<tr>
									<td><?=$name[3]?></td>
									<td style="font-weight:bold">0</td>
									<td style="font-weight:bold">0</td>
								</tr>
							<?php else : ?>
							
								<?php foreach ($this->history['data']['stat'] as $key => $value) : ?>
									
									<tr>
										<td ><?=$name[$key]?></td>
										<td style="font-weight:bold"><?=$value['send']?></td>
										<td style="font-weight:bold"><?=$value['productive']?></td>
									</tr>
			
								<?php endforeach; ?>

							<?php endif; ?>

						</tbody>
					</table>
				</div>

			</div>
		</div>

		<div class="row">
			<div class="col-xs-8 col-sm-3 col-md-2"  style="border-bottom:1px solid #ccc">
				<?=__('Total Client', 'aopush')?>: 
			</div>	
			<div class="col-xs-4 col-sm-3 col-md-2" style="font-weight:bold;border-bottom:1px solid #ccc">	
				<?=$this->history['data']['total']['client']?>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-8 col-sm-3 col-md-2"  style="border-bottom:1px solid #ccc">
				<?=__('Total New Post', 'aopush')?>: 
			</div>	
			<div class="col-xs-4 col-sm-3 col-md-2" style="font-weight:bold;border-bottom:1px solid #ccc">	
				<?=$this->history['data']['total']['send']?>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-8 col-sm-3 col-md-2"  style="border-bottom:1px solid #ccc">
				<?=__('Total Update Post', 'aopush')?>: 
			</div>	
			<div class="col-xs-4 col-sm-3 col-md-2" style="font-weight:bold;border-bottom:1px solid #ccc">	
				<?=$this->history['data']['total']['productive']?>
			</div>
		</div>

		<p>&nbsp;</p>
		
		<form id="history-form" action="" method="post" class="form-horizontal" role="form">

			<div class="form-group">
				<!--<label class="col-sm-2 control-label"><?=__('Period', 'aopush')?></label>-->
				
				 <div class="col-sm-10 col-md-8 col-lg-6">
					 <div class="input-group">
						<span class="input-group-addon">
							<?=__('from', 'aopush')?>
						</span>

						<input type="datetime" class="form-control" name="AophHistoryNotifications[date_start]" id="AophHistoryNotifications_date_start" class="datepicker" value="<?=$this->history['date']['date_start']?>">
						
						<span class="input-group-addon">
							<?=__('to', 'aopush')?>
						</span>
						
						<input type="datetime" class="form-control" name="AophHistoryNotifications[date_end]" id="AophHistoryNotifications_date_end" class="datepicker" value="<?=$this->history['date']['date_end']?>">
						
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-10 col-md-8 col-lg-6">
					<input type="submit" class="btn btn-primary pull-left btn-xs-block" value="<?=__('Send', 'aopush')?>">
				</div>
			</div>
			
		</form>
						
	</div>

</div>