<?php 
class AopushCore
{	
	public $settings;
	
	public function __construct()
	{
		$config_path = realpath(AOPH_AOPUSH_DIR) . DIRECTORY_SEPARATOR . 'config.php';
		if (file_exists($config_path)) {
			$this->settings = require($config_path);
		}
		
		$this->settings['view_path'] =  realpath(AOPH_AOPUSH_DIR) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * wpPush()
	 */
	public function aopush_wpPush()
	{
		add_action('wp_print_footer_scripts', [$this, 'aoph_push']);
		add_action('wp_head', [$this, 'aoph_manifest_link']);

		if (preg_match('/(sw.js)/i', $_SERVER['REQUEST_URI'])) {
			self::aoph_getSWJS();
		}
		
		if (preg_match('/(manifest.json)/i', $_SERVER['REQUEST_URI'])) {
			self::aoph_getManifest();
		}
		
		add_action('save_post', [$this, 'aoph_event_save_post'], 10, 3);
	}
	
	/**
	 * aopp_player()
	 */
	public function aoph_push()
	{
		if (
			!empty(get_option('aoph_pushsender_post_used')) && 
			!empty(get_option('aoph_pushsender_id'))
		) {
			$id_user = 0;
			if (get_current_user_id()) {
				$id_user = get_current_user_id();
			}
			
			$hash = !empty(get_option('aoph_pushsender_token')) ? get_option('aoph_pushsender_token') : self::aopush_getActivateHash(get_option('aoph_pushsender_email'));
			$login = '';
			if (!empty(get_option('aoph_pushsender_token'))) {
				$login = get_option('aoph_pushsender_email');
			}
			
			echo '
<script type="text/javascript">
	if ("serviceWorker" in navigator) {
		navigator.serviceWorker.register("/sw.js");
		navigator.serviceWorker.ready.then(function(reg) {
			reg.pushManager.subscribe({
				userVisibleOnly: true
			}).then(function(sub) {
				fetch("https://aoserver.ru/resurces/push2/pushdata?'.$hash.'", {
					method: "POST",
					headers: {
						"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
					},
					body: "sub=" + JSON.stringify(sub) + "&idContact='.$id_user.'&test=0&login='.$login.'&allowUrl='.get_option('aoph_pushsender_domain').'"
				})
			});
		}).catch(function(error) {
			console.log(error);
		});
	}
</script>		
			';
		}
	}
	
	/**
	 * aoph_manifest_link()
	 */
	public function aoph_manifest_link()
	{
		if (
			!empty(get_option('aoph_pushsender_post_used')) && 
			!empty(get_option('aoph_pushsender_id'))
		) {
		
			echo '<link rel="manifest" href="/manifest.json">';
		}
	}
	
	/**
	 * aoph_event_save_post($post_ID, $post, $update)
	 */
	public function aoph_event_save_post($post_ID, $post, $update)
	{
		if (
			empty(get_option('aoph_pushsender_post_used')) || 
			empty(get_option('aoph_pushsender_id')) || 
			$post->post_status!=='publish' ||
			$post->post_type!=='post'
		) {
			return false;
		}

		preg_match_all('/(\<img[\s]{1,}src="(.*?)")/i', $post->post_content, $arr);
		$data = [
			'text' => $post->post_title,
			'url' => $post->guid,
			'id' => $post_ID,
			'icon' => '',
			'subject' => '',
		];

		if (strtotime($post->post_date) == strtotime($post->post_modified)) {
		
			if (
				empty(get_option('aoph_pushsender_template_insert_subject')) ||
				empty(get_option('aoph_pushsender_template_insert_icon')) ||
				empty(get_option('aoph_pushsender_post_insert'))
			) {
				return false;
			}

			$data['subject'] = get_option('aoph_pushsender_template_insert_subject');
			$data['icon'] = get_option('aoph_pushsender_template_insert_icon');
			$data['send_type'] = 1;
			$send = AopushPushApi::aoph()->aopush_sendpush($data);
		
		} elseif (strtotime($post->post_date) < strtotime($post->post_modified)) {
			
			if (
				empty(get_option('aoph_pushsender_template_update_subject')) ||
				empty(get_option('aoph_pushsender_template_update_icon')) || 
				empty(get_option('aoph_pushsender_post_update'))
			) {
				return false;
			}

			$data['subject'] = get_option('aoph_pushsender_template_update_subject');
			$data['icon'] = get_option('aoph_pushsender_template_update_icon');
			$data['send_type'] = 2;
			$send = AopushPushApi::aoph()->aopush_sendpush($data);
			
		}

		if (!empty($send)) {
			return true;		
		} 
		
		return false;
	}
	/**
	 * getSWJS()
	 */
	public function aoph_getSWJS()
	{
		if (
			!empty(get_option('aoph_pushsender_post_used')) && 
			!empty(get_option('aoph_pushsender_id'))
		) {
			
			$hash = !empty(get_option('aoph_pushsender_token')) ? get_option('aoph_pushsender_token') : self::aopush_getActivateHash(get_option('aoph_pushsender_email'));
			$login = '';
			if (!empty(get_option('aoph_pushsender_token'))) {
				$login = get_option('aoph_pushsender_email');
			}
			
			$script = "
'use strict';

importScripts('".plugins_url('aopush/assets/js/Dexie.min.js')."');

var dDB;
function getDb(){
	if(!dDB){
		dDB=new Dexie('chrome-push-notifications');
		dDB.version(1).stores({pushData:'tag,url',});
		dDB.open();
	}
	return dDB;
}

self.addEventListener('install', function (event) {
	event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function (event) {
	console.log('The ServiceWorker was activated.');
});

self.addEventListener('push',function(event){
	event.waitUntil(
		self.registration.pushManager.getSubscription().then(function(subscription){
			var ids = subscription.endpoint.split('/').slice(-1);
			return fetch('https://aoserver.ru/resurces/push2/getdata?".$hash."', {
				method: 'post',
				headers: {
					'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
				},
				body: 'ids=' + ids[0] + '&login=".$login."&allowUrl=".get_option('aoph_pushsender_domain')."'
			}).then(function(response){
				return response.json().then(function(data){
					var promises=[];
					if(!data.notification.title) data.notification.title='New Notification!';
					if(!data.notification.body) data.notification.body='';
					getDb().pushData.put({tag:data.notification.tag,url:data.notification.url});
					promises.push(showNotification(data.notification.title,data.notification.body,data.notification.tag,data.notification.icon,data.notification.image,data.notification.interaction));
					return Promise.all(promises);
				});
			});
		})
	);
});

self.addEventListener('notificationclick', function (event) {
	event.notification.close();
	event.waitUntil(getDb().pushData.get(event.notification.tag).then(function(push_data){
		getDb().pushData.delete();
		return clients.openWindow(push_data.url).focus();
	}));
});
				 
function showNotification(title,body,tag,icon,image,interaction){
	var options={body:body,tag:tag,icon:icon,image:image,requireInteraction:interaction};
	return self.registration.showNotification(title,options);
};
		";
		
			header('Content-Type: application/json; charset=utf-8');
			header('Content-Type: application/javascript');
			header('Content-Type: application/x-javascript');
			header('Content-Type: text/javascript');
			exit($script);
		}
	}
	
	/**
	 * getManifest()
	 */
	public function aoph_getManifest()
	{
		if (
			!empty(get_option('aoph_pushsender_post_used')) && 
			!empty(get_option('aoph_pushsender_id'))
		) {
			$manifest = '
{
	"name": "AutoOffice.Push",
	"short_name": "AO Push",
	"display": "standalone",
	"gcm_sender_id": "'.get_option('aoph_pushsender_id').'",
	"permissions": ["gcm", "storage"],
	"gcm_user_visible_only": true,
	"theme_color": "#ffffff",
	"background_color": "#ffffff"
}';

			header('Content-Type: application/manifest+json');
			exit($manifest);
		}
	}

	/**
	 * aomailer($className=__CLASS__)
	 */ 
	public static function aoph($className=__CLASS__)
	{
		return new $className();
	}
}
