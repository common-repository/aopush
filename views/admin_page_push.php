
<script src='/wp-content/plugins/aopush/assets/js/jquery.js'></script>
<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_script('aoph_timepicker_min_js', plugins_url('assets/js/jquery-ui-timepicker-addon-1.6.3.min.js', dirname(__FILE__)));

add_action('admin_print_footer_scripts', 'aoph_script_push', 99);
function aoph_script_push() {
	?>
	<script type="text/javascript">
		function getBalance(token) {
			jQuery.ajax({
				type: "POST",
				url: '<?=admin_url()?>admin-ajax.php?action=aoph_balance_action',
				data: {token: token, type: 'balance'},
				success: function(d){ 
					if (d) {
						obj = JSON.parse(d);
						jQuery('#aoph-balance').text(obj.balance);
						jQuery('#aoph-limit').text(obj.limit);
						jQuery('#aoph-balance').parents('.alert').removeClass('alert-warning');
						jQuery('#aoph-balance').parents('.alert').removeClass('alert-success');
						jQuery('#aoph-balance').parents('.alert').removeClass('alert-danger');
						
						if (obj.balance>0) {
							var add_class = 'alert-success';
						} else {
							var add_class = 'alert-warning';
						}
						
						if (obj.limit<=0) {
							var add_class = 'alert-danger';
						}
						
						jQuery('#aoph-balance').parents('.alert').addClass(add_class);
					}	
				}
			});
		}

		function loadDateTimePickerPlugin() {
			jQuery.datepicker.setDefaults({
				closeText: 'Закрыть',
				prevText: '<Пред',
				nextText: 'След>',
				currentText: 'Сегодня',
				monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
				monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
				dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
				dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
				dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
				weekHeader: 'Нед',
				dateFormat : 'yy-mm-dd',
				firstDay: 1,
				showAnim: 'slideDown',
				isRTL: false,
				showMonthAfterYear: false,
				yearSuffix: '',
			});

			jQuery('#AophMailingNotifications_date_send, #AophHistoryNotifications_date_start, #AophHistoryNotifications_date_end').datetimepicker({
				dateFormat : 'yy-mm-dd',
				currentText: 'Сегодня',
				closeText: 'Закрыть',
				timeFormat: 'HH:mm:ss',
				timeOnlyTitle: 'Выберите время',
				timeText: 'Время',
				hourText: 'Часы',
				minuteText: 'Минуты',
				secondText: 'Секунды',
				timeSuffix: ''
			});	
		}

		jQuery(document).ready(function($) {
			var token = '<?=wp_get_session_token()?>';
			setTimeout(function(){jQuery('.bs-callout').find('.alert').fadeOut('slow')}, 15000);
			setInterval(function(){getBalance(token)}, 10000);
		});
	</script>
<?php } ?>

<div id="aoph-page-push" class="container-fluid">

	<div id="aoph-overlay"></div>
	<i id="aoph-loader" class="fa fa-cog fa-spin fa-fw"></i>
	
	<div class="masthead">
		
		<h3 class="text-muted">
			<img src="<?=$this->settings['logo']?>" alt="logo" class="" width="50"><br><br>
			
			<?=__('Notifications AutoOffice.Push', 'aopush')?>
		</h3>
		
    </div>
	
	<?php if (!empty($this->settings['active']) && !empty($this->settings['balance_view'])) : ?>
	
		<div class="row">
			<div class="col-sm-6">
				<?php if ($this->settings['limit']<=0) : ?>
				
					<div class="alert alert-danger" style="display:table">
				
				<?php else: ?>

					<div class="alert alert-<?=($this->settings['balance']>0) ? 'success' : 'warning'?>" style="display:table">
					
				<?php endif; ?>

					<small>
						<span title="" style="cursor:default">
						
							<i class="fa fa-balance-scale" aria-hidden="true"></i> 
							<?=__('Balance', 'aopush')?>:
							<span id="aoph-balance"><?=$this->settings['balance']?></span>
							
							<?php if (!empty($this->settings['currency'])) : ?>
							
								<?php if ($this->settings['currency']=='RUB') : ?>
								
									&nbsp;<i class="fa fa-rub" aria-hidden="true"></i>
									
								<?php endif; ?>
								
							<?php endif; ?>
						</span>
						<span title="" style="cursor:default">
							&nbsp;|&nbsp;
						</span>
						<span class="tips" data-toggle="tooltip" data-placement="top" title="Лимит сообщений за месяц" style="cursor:help">
							<i class="fa fa-bell-slash-o" aria-hidden="true"></i>
							<?=__('Limit', 'aopush')?>:
							<span id="aoph-limit"><?=$this->settings['limit']?></span>
						</span>
					</small>
					
					<div class="small" style="margin-top:10px;font-style:italic;color:#666">
						<?=__('Tech Support', 'aopush')?>: <a href="mailto:info@autooffice24.ru">info@autooffice24.ru</a>
					</div>
					
				</div>

			</div>	
			<div class="col-sm-6">	
					
				<?php if (!empty($this->settings['balance_link'])) : ?>
					
					<a href="<?=$this->settings['balance_link']?>" target="_blank" class="btn btn-success pull-right btn-xs-block">
						<?=__('Top up balance', 'aopush')?>
					</a>
					
				<?php endif; ?>
					
				<?php if (!empty($this->settings['personal_account_link'])) : ?>
					
					<a href="<?=$this->settings['personal_account_link']?>" target="_blank" class="btn btn-info pull-right btn-xs-block" style="margin-right:2px">
						<?=__('Personal Area', 'aopush')?>
					</a>
					
				<?php endif; ?>	

			</div>
		</div>
	
	<?php endif; ?>
	
	<p class="clearfix"></p>
	
	<div class="row">
		<div class="col-sm-12">
			
			<ul class="nav nav-tabs" id="aoph-settings-tab">
				<li class="<?=(empty($sub_page) || $sub_page=='settings') ? 'active' : ''?>">
					<a href="#aoph-settings" data-toggle="tab">
						<i class="fa fa-cogs" aria-hidden="true"></i> <?=__('Settings', 'aopush')?>
					</a>
				</li>
				<li class="<?=($sub_page=='mailing') ? 'active' : ''?>">
					<a href="#aoph-mailing" data-toggle="tab">
						<i class="fa fa-paper-plane" aria-hidden="true"></i> <?=__('Newsletters', 'aopush')?>
					</a>
				</li>
				<?php /*
				<li class="<?=($sub_page=='events') ? 'active' : ''?>">
					<a href="#aoph-events" data-toggle="tab">
						<i class="fa fa-calendar-check-o" aria-hidden="true"></i> <?=__('Developments', 'aopush')?>
					</a>
				</li>
				*/ ?>
				<li class="<?=($sub_page=='templates') ? 'active' : ''?>">
					<a href="#aoph-templates" data-toggle="tab">
						<i class="fa fa-file-text-o" aria-hidden="true"></i> <?=__('Templates', 'aopush')?>
					</a>
				</li>
				<li class="<?=($sub_page=='test') ? 'active' : ''?>">
					<a href="#aoph-test" data-toggle="tab">
						<i class="fa fa-check-square-o" aria-hidden="true"></i> <?=__('Test', 'aopush')?>
					</a>
				</li>
				<li class="<?=($sub_page=='history') ? 'active' : ''?>">
					<a href="#aoph-history" data-toggle="tab">
						<i class="fa fa-history" aria-hidden="true"></i> <?=__('Story', 'aopush')?>
					</a>
				</li>
				<?php /*
				<li class="<?=($sub_page=='help') ? 'active' : ''?>">
					<a href="#aoph-help" data-toggle="tab">
						<i class="fa fa-question-circle-o" aria-hidden="true"></i> <?=__('Help', 'aopush')?>
					</a>
				</li>
				*/ ?>
			</ul>
			
		</div>
	</div>

	<div class="tab-content">
		<div class="tab-pane fade <?=(empty($sub_page) || $sub_page=='settings') ? 'in active' : ''?>" id="aoph-settings">
		
			<?php require_once __DIR__ . '/page/__settings.php'; ?>
			
		</div>
		
		<div class="tab-pane fade <?=($sub_page=='mailing') ? 'in active' : ''?>" id="aoph-mailing">
				
			<?php require_once __DIR__ . '/page/__mailing.php'; ?>
				
		</div>
		
		<?php /*
		<div class="tab-pane fade <?=($sub_page=='events') ? 'in active' : ''?>" id="aoph-events">
				
			<?php require_once __DIR__ . '/page/__events.php'; ?>
			
		</div>
		*/?>
		
		<div class="tab-pane fade <?=($sub_page=='templates') ? 'in active' : ''?>" id="aoph-templates">
				
			<?php require_once __DIR__ . '/page/__templates.php'; ?>
				
		</div>
		
		<div class="tab-pane fade <?=($sub_page=='test') ? 'in active' : ''?>" id="aoph-test">
				
			<?php require_once __DIR__ . '/page/__test.php'; ?>
				
		</div>
		
		<div class="tab-pane fade <?=($sub_page=='history') ? 'in active' : ''?>" id="aoph-history">
				
			<?php require_once __DIR__ . '/page/__history.php'; ?>
				
		</div>		

		<?php /*
		<div class="tab-pane fade <?=($sub_page=='help') ? 'in active' : ''?>" id="aoph-help">
				
			<?php require_once __DIR__ . '/page/__help.php'; ?>
				
		</div>
		*/ ?>

	</div>
	
</div>

