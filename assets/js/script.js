function switcher() {
	jQuery('#events-form, #templates-form, #settings-form').find('.form-group input[type="checkbox"]').bootstrapSwitch({
		'size': 'mini',
		'onColor': 'danger',
		'offColor': 'default',
		'onText': '<i class="fa fa-check" aria-hidden="true"></i>',
		'offText': '<i class="fa fa-times" aria-hidden="true"></i>'
	});
};

function loadPage() {
	setTimeout(function(){jQuery('.message_output').fadeOut('slow')}, 5000);
	switcher();
	loadDateTimePickerPlugin();
	jQuery('#aoph-overlay, #aoph-loader').hide();
};

function addMessages(data) {
	if (typeof data.AophTestNotifications.message==='undefined') {
		data.AophTestNotifications.error = 1;
		data.AophTestNotifications.message = 'Подписка на Web Push уведомления завершилась неудачей';
	}
	
	if (data && !data.AophTestNotifications['error']) {
		if (jQuery('.message_output').hasClass('.alert-success')) {
			success_block.html(data.AophTestNotifications['message']);
		} else {
			jQuery('.message_output').html('<p class="alert alert-success">' + data.AophTestNotifications['message'] + '</p>');
		}
	} else {
		if (jQuery('.message_output').hasClass('.alert-danger')) {
			error_block.html(data.AophTestNotifications['message']);
		} else {
			jQuery('.message_output').html('<p class="alert alert-danger">' + data.AophTestNotifications['message'] + '</p>');
		}
	}
};

function reloadForm(id, token, data, url) {			
	jQuery('#aoph-overlay, #aoph-loader').show();

	var action = 'aoph_load_form_' + id;
	if (data.AophTestNotifications && data.AophTestNotifications['id_form']) {
		var id = '#aoph-' + data.AophTestNotifications['id_form'];
	} else {
		var id = '#aoph-' + id;
	}

	jQuery(id).load(url + 'admin-ajax.php?action=' + action, {data: data}, function(){
		loadPage();
		getBalance(token);	
		if (data.AophTestNotifications && data.AophTestNotifications['action'] && data.AophTestNotifications['action']=='subscribe') {
			addMessages(data);
		}
	});
};

jQuery(document).ready(function() {

	loadPage();
		
	jQuery(document).delegate('#aoph-settings-tab a', 'click', function(e){
		jQuery('.message_output').hide();
		e.preventDefault();
		jQuery(this).tab('show');
	});
	
	jQuery(document).delegate('.tips', 'mouseover', function(e){
		jQuery(this).tooltip('show');
	});
});