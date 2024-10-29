<?php
/*
Plugin Name: Aopush
Plugin URI: https://wordpress.org/plugins/aopush/
Description: Плагин для лёгкого подключения push-уведомлений на вашем сайте. Пройдя двухминутную регистрацию прямо через Wordpress, вы сможете включить подписку на push на вашем сайте одной кнопкой. Плагин поддерживает два способа отправки пуш: автоматический - при создании или обновлении записи на блоге, и ручную рассылку по подписчикам.
Author: Autooffice
Version: 1.1.02
Author URI: https://profiles.wordpress.org/autooffice#content-plugins
*/

define('AOPH_AOPUSH_DIR', plugin_dir_path(__FILE__));
define('AOPH_AOPUSH_URL', plugin_dir_url(__FILE__));
define('AOPH_DEBUG', false);

require_once __DIR__ . '/autoload.php';

load_plugin_textdomain('aopush', false, dirname(plugin_basename(__FILE__)) . '/lang/');

register_activation_hook(__FILE__, ['AopushAdmin', 'aopush_install']);
register_uninstall_hook(__FILE__, ['AopushAdmin', 'aopush_uninstall']);
register_deactivation_hook(__FILE__, ['AopushAdmin', 'aopush_deactivation']);

function aoph_aopush_load() {

	$is_admin_page = false;

	if (is_admin()) {

		if (!empty($_GET['page']) && preg_match('/aopush/i', $_GET['page'])) {
			$is_admin_page = true;
		}

		AopushAdmin::aoph($is_admin_page)->aopush_wpAdmin();
	}

	AopushCore::aoph()->aopush_wpPush();
}

add_action('plugins_loaded', 'aoph_aopush_load');