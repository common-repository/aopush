<?php
class AopushAdmin
{
	protected $page;
	protected $capability;
	protected $url;
	protected $functions_page;
	protected $view_path;
	protected $icons;
	protected $settings = [];
	protected $history = [];
	protected $is_plugin_page;
	protected $position;

	public function __construct($is_plugin_page)
	{
		$this->page = __('Aopush', 'aopush');
		$this->capability = 'edit_others_pages';
		$this->url = 'aopush';
		$this->functions_page = 'aopush_page';
		$this->icons = AOPH_AOPUSH_URL . '/assets/img/icon.png';
		$this->is_plugin_page = $is_plugin_page;
		$this->view_path =  realpath(AOPH_AOPUSH_DIR) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * wpAdmin()
	 */
	public function aopush_wpAdmin()
	{
		
		$post = [
			'login' => get_option('aoph_pushsender_email'),
			'domain' => get_option('aoph_pushsender_domain'),
		];

		add_action('admin_head', [$this, 'aoph_manifest_link']);
		add_action('admin_menu', [$this, 'aopush_menu']);
		add_action('wp_ajax_aoph_balance_action', [$this, 'aoph_balance_action']);
		add_action('wp_ajax_aoph_load_form_settings', [$this, 'aoph_load_form_settings']);
		add_action('wp_ajax_aoph_load_form_mailing', [$this, 'aoph_load_form_mailing']);
		add_action('wp_ajax_aoph_load_form_events', [$this, 'aoph_load_form_events']);
		add_action('wp_ajax_aoph_load_form_templates', [$this, 'aoph_load_form_templates']);
		add_action('wp_ajax_aoph_load_form_history', [$this, 'aoph_load_form_history']);
		add_action('wp_ajax_aoph_load_form_test', [$this, 'aoph_load_form_test']);
		add_action('wp_ajax_aoph_load_form_subscribe', [$this, 'aoph_load_form_subscribe']);
	}

	/**
	 * aomailer_settings_menu()
	 */
	public function aopush_menu()
	{
		add_menu_page( 
			$this->page, 
			$this->page,
			$this->capability, 
			$this->url, 
			[$this, $this->functions_page], 
			$this->icons, 
			$this->position 
		);
	}

	/**
	 * settings_page_sms()
	 */
	public function aopush_page()
	{
		if (!empty($this->is_plugin_page)) {
			self::aoph_resourceRegistration();
			$this->settings = self::aopush_loadSettings();
		}

		if (file_exists($this->view_path.'admin_page_push.php')) {
			require_once $this->view_path.'admin_page_push.php';
		}
	}

	/** 
	 * aoph_load_form_settings()
	 */
	public function aoph_load_form_settings()
	{
		// Validation token
		if (empty($_POST['data']['token']) || $_POST['data']['token']!==wp_get_session_token()) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing or expired token', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__settings.php'; 
			wp_die();
		}

		if (
			empty(get_option('aoph_pushsender_email')) ||
			empty(get_option('aoph_pushsender_domain')) ||
			empty(get_option('aoph_pushsender_id'))
		) {
			$data['domain'] = AopushPushApi::aoph()->aopush_getHost();
			$data['domain'] = preg_replace('/^(www\.)/i', '', $data['domain']);
			$data['login'] = AopushPushApi::aoph()->aopush_validateForm('email', $_POST['data']['AophPushSettings']['login']);

			if (empty($data['login']) || empty($data['domain'])) {
				$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing data', 'aopush'), $this->settings['error']);
				$this->settings = $this->settings + self::aopush_loadSettings();
				require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__settings.php'; 
				wp_die();
			}

			// Save POST data
			$check = self::aopush_saveSettings($data);
			if (!empty($check['error'])) {
				$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__($check['message'], 'aopush'), $this->settings['error']);
				$this->settings = $this->settings + self::aopush_loadSettings();
				require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__settings.php'; 
				wp_die();	
			} 
			
			$this->settings['success'] =__($check['message'], 'aopush');
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__settings.php'; 
			wp_die();
			
		} else {
			
			$event_used = AopushPushApi::aoph()->aopush_validateForm('boolean', $_POST['data']['AophPushSettings']['event_used']);
			update_option('aoph_pushsender_post_used', $event_used);
			$this->settings['success'] =__('Success Save Data', 'aopush');
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__settings.php'; 
			wp_die();
		}	
	}
	
	/** 
	 * aoph_load_form_mailing()
	 */
	public function aoph_load_form_mailing()
	{
		// Validation token
		if (empty($_POST['data']['token']) || $_POST['data']['token']!==wp_get_session_token()) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing or expired token', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__mailing.php'; 
			wp_die();
		}

		$data['icon'] = AopushPushApi::aoph()->aopush_validateForm('url', $_POST['data']['AophMailingNotifications']['icon']);
		if (empty($data['icon'])) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Incorrect data', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__mailing.php'; 
			wp_die();
		}
		
		$data['url'] = AopushPushApi::aoph()->aopush_validateForm('url', $_POST['data']['AophMailingNotifications']['url']);
		if (empty($data['url'])) {
			unset($data['url']);
		}

		$data['subject'] = AopushPushApi::aoph()->aopush_validateForm('text', $_POST['data']['AophMailingNotifications']['subject']);
		$data['text'] = AopushPushApi::aoph()->aopush_validateForm('text', $_POST['data']['AophMailingNotifications']['text']);
		if (empty($data['subject']) || empty($data['text'])) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing data', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__mailing.php'; 
			wp_die();
		} 

		$data['id_admin'] = (int) get_current_user_id();
		$data['send_type'] = 3;

		$results = AopushPushApi::aoph()->aopush_sendpush($data);
		if (!empty($results['error'])) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__($results['message'], 'aopush'), $this->settings['error']);
		} else {
			$this->settings['success'] = __('Notifications successfully sentsent', 'aopush');
		}

		$this->settings = $this->settings + self::aopush_loadSettings();
		require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__mailing.php'; 
		wp_die();
	}
	
	/** 
	 * aoph_load_form_events()
	 */
	public function aoph_load_form_events()
	{
		// Validation token
		if (empty($_POST['data']['token']) || $_POST['data']['token']!==wp_get_session_token()) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing or expired token', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__settings.php'; 
			wp_die();
		}

		$data['update'] = AopushPushApi::aoph()->aopush_validateForm('boolean', $_POST['data']['AophEventsNotifications']['update']);
		update_option('aoph_pushsender_post_update', $data['update']);
		
		$data['insert'] = AopushPushApi::aoph()->aopush_validateForm('boolean', $_POST['data']['AophEventsNotifications']['insert']);
		update_option('aoph_pushsender_post_insert',$data['insert']);

		$this->settings['success'] =__('Success Save Data', 'aopush');
		$this->settings = $this->settings + self::aopush_loadSettings();
		require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__events.php'; 
		wp_die();
	}
	
	/** 
	 * aoph_load_form_templates()
	 */
	public function aoph_load_form_templates()
	{
		// Validation token
		if (empty($_REQUEST['data']['token']) || $_REQUEST['data']['token']!==wp_get_session_token()) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing or expired token', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__templates.php'; 
			wp_die();
		}
		
		$this->settings['id_template'] = AopushPushApi::aoph()->aopush_validateForm('int', $_REQUEST['data']['AophTemplatesNotifications']['events_type']);
		if (empty($this->settings['id_template'])) {
			$this->settings['id_template'] = 1;
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing data', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__templates.php'; 
			wp_die();
		}

		if (isset($_REQUEST['data']['AophTemplatesNotifications']['subject']) && isset($_REQUEST['data']['AophTemplatesNotifications']['icon'])) {
			
			$data['used'] = AopushPushApi::aoph()->aopush_validateForm('boolean', $_REQUEST['data']['AophTemplatesNotifications']['used']);
			$data['subject'] = AopushPushApi::aoph()->aopush_validateForm('text', $_REQUEST['data']['AophTemplatesNotifications']['subject']);
			$data['icon'] = AopushPushApi::aoph()->aopush_validateForm('url', $_REQUEST['data']['AophTemplatesNotifications']['icon']);
			if (empty($data['subject']) || empty($data['icon'])) {
				$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing data', 'aopush'), $this->settings['error']);
				$this->settings = $this->settings + self::aopush_loadSettings();
				require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__templates.php'; 
				wp_die();
			}
			
			if ($this->settings['id_template']==1) {
			
				update_option('aoph_pushsender_post_insert', $data['used']);
				update_option('aoph_pushsender_template_insert_subject', $data['subject']);
				update_option('aoph_pushsender_template_insert_icon', $data['icon']);
				$this->settings['success'] =__('Success Save Data', 'aopush');
			
			} elseif ($this->settings['id_template']==2) {
			
				update_option('aoph_pushsender_post_update', $data['used']);
				update_option('aoph_pushsender_template_update_subject', $data['subject']);
				update_option('aoph_pushsender_template_update_icon', $data['icon']);
				$this->settings['success'] =__('Success Save Data', 'aopush');
			
			}
		}

		$this->settings = $this->settings + self::aopush_loadSettings();
		require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__templates.php'; 
		wp_die();
	}
	
	/** 
	 * aoph_load_form_history()
	 */
	public function aoph_load_form_test()
	{
		// Validation token
		if (empty($_POST['data']['token']) || $_POST['data']['token']!==wp_get_session_token()) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing or expired token', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__test.php'; 
			wp_die();
		}

		$data['icon'] = AopushPushApi::aoph()->aopush_validateForm('url', $_POST['data']['AophTestNotifications']['icon']);
		if (empty($data['icon'])) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Incorrect data', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__test.php'; 
			wp_die();
		}
		
		$data['subject'] = AopushPushApi::aoph()->aopush_validateForm('text', $_POST['data']['AophTestNotifications']['subject']);
		$data['text'] = AopushPushApi::aoph()->aopush_validateForm('text', $_POST['data']['AophTestNotifications']['text']);
		if (empty($data['subject']) || empty($data['text'])) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__('Missing data', 'aopush'), $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__test.php'; 
			wp_die();
		} 

		$data['id_admin'] = (int) get_current_user_id();
		
		$results = AopushPushApi::aoph()->aopush_sendTest($data);
		
		if (!empty($results['token'])) {
			update_option('aoph_pushsender_token', $result['token']); 
		}
		
		if (!empty($results['error'])) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError(__($results['message'], 'aopush'), $this->settings['error']);
		} else {
			$this->settings['success'] = __('Notifications successfully sentsent', 'aopush');
		}
	
		$this->settings = $this->settings + self::aopush_loadSettings();
		require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__test.php'; 
		wp_die();
	}
	
	/** 
	 * aoph_load_form_subscribe()
	 */
	public function aoph_load_form_subscribe()
	{
		if (!empty($_REQUEST['data']['AophTestNotifications']['error'])) {
			$this->settings['error'] = AopushPushApi::aoph()->aopush_addError($_REQUEST['data']['AophTestNotifications']['message'], $this->settings['error']);
			$this->settings = $this->settings + self::aopush_loadSettings();
			require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__test.php'; 
			wp_die();
		}

		if (!empty($_REQUEST['data']['AophTestNotifications']['message'])) {
			$this->settings['success'] = AopushPushApi::aoph()->aopush_validateForm('text', $_REQUEST['data']['AophTestNotifications']['message']);
		}
		
		$this->settings = $this->settings + self::aopush_loadSettings();
		require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__test.php'; 
		wp_die();
	}

	/** 
	 * aoph_load_form_history()
	 */
	public function aoph_load_form_history()
	{
		$this->history = AopushPushApi::aoph()->aopush_getHistory();
		
		if (!empty($this->history['token'])) {
			update_option('aoph_pushsender_token', $this->history['token']);
		}
		
		$this->settings = $this->settings + self::aopush_loadSettings();
		require_once realpath(AOPH_AOPUSH_DIR) . '/views/page/__history.php'; 
		wp_die();
	}

	/** 
	 * aoph_balance_action()
	 */
	public function aoph_balance_action()
	{
		$data = [
			'balance' => 0,
			'currency' => 'RUB',
			'limit' => 500,
		];
		
		if (empty($_POST['token']) || $_POST['token']!==wp_get_session_token()) {
			echo json_encode($data);
			wp_die();
		}

		$balance = AopushPushApi::aoph()->aopush_getBalance();
		
		if (!empty($balance['token'])) {
			update_option('aoph_pushsender_token', $balance['token']);
		}
		
		if (empty($balance) || !is_array($balance)) {
			echo json_encode($data);
			wp_die();
		}
	
		echo json_encode($balance);
		wp_die();
	}
	
	/**
	 * saveSettings($data=[])
	 */
	public function aopush_saveSettings($data=[])
	{
		if (empty($data['login']) || empty($data['domain'])) {
			return ['error'=>1, 'message'=>'Missing data'];
		}
		
		$result = AopushPushApi::aoph()->aopush_registration($data);
		if (empty($result) || !empty($result['error']) || empty($result['hash'])) {
			return ['error'=>1, 'message'=>$result['message']];
		}
		
		update_option('aoph_pushsender_email', $data['login']);
		update_option('aoph_pushsender_domain', $data['domain']);
		update_option('aoph_pushsender_token', $result['hash']);
		update_option('aoph_pushsender_id', $result['id']);	
		
		return ['error'=>0, 'message'=>$result['message']];
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
	 * resourceRegistration()
	 */
	public function aoph_resourceRegistration() 
	{
		wp_enqueue_style('aoph_bootstrap_min_css', plugins_url('assets/css/bootstrap-3.3.7.min.css', dirname(__FILE__)));
		wp_enqueue_style('aoph_bootstrap_switch_min_css', plugins_url('assets/css/bootstrap-switch-3.3.2.min.css', dirname(__FILE__)));
		wp_enqueue_style('aoph_fontawesom_min_css', plugins_url('assets/css/font-awesome-4.7.0.min.css', dirname(__FILE__)));
			
		if (AOPH_DEBUG) {
			wp_enqueue_style('aoph_plugin_css', plugins_url('assets/css/style.css', dirname(__FILE__)));
		} else {
			wp_enqueue_style('aoph_plugin_css', plugins_url('assets/css/style.min.css', dirname(__FILE__)));
		}

		wp_enqueue_script('aoph_bootstrap_min_js', plugins_url('assets/js/bootstrap3.3.7.min.js', dirname(__FILE__)));
		wp_enqueue_script('aoph_bootstrap_switch_min_js', plugins_url('assets/js/bootstrap-switch-3.3.2.min.js', dirname(__FILE__)));

		if (AOPH_DEBUG) {
			wp_enqueue_script('aoph_plugin_js', plugins_url('assets/js/script.js', dirname(__FILE__)));
		} else {
			wp_enqueue_script('aoph_plugin_js', plugins_url('assets/js/script.min.js', dirname(__FILE__)));
		}

		wp_enqueue_script('pic', plugins_url('assets/js/jquery-ui-timepicker-addon-1.6.3.min.js', dirname(__FILE__)));
	}
	
	/**
	 * loadSettings()
	 */
	private function aopush_loadSettings()
	{
		$config_path = realpath(AOPH_AOPUSH_DIR) . '/config.php';
		if (file_exists($config_path)) {
			$settings = require($config_path);
		}

		$balance = AopushPushApi::aoph()->aopush_getBalance();

		$settings['logo'] = AOPH_AOPUSH_URL . '/assets/img/logo.png';
		$settings['balance'] = !empty($balance['balance']) ? $balance['balance'] : 0;
		$settings['currency'] = !empty($balance['currency']) ? $balance['currency'] : 'RUB';
		$settings['limit'] = !empty($balance['limit']) ? $balance['limit'] : 500;
		$settings['active'] = !empty(get_option('aoph_pushsender_id')) ? true : false;
		
		$settings['template'] = [
			1 => [
				'subject' => get_option('aoph_pushsender_template_insert_subject'),
				'icon' => get_option('aoph_pushsender_template_insert_icon'),
			],
			2 => [
				'subject' => get_option('aoph_pushsender_template_update_subject'),
				'icon' => get_option('aoph_pushsender_template_update_icon'),
			],
		];
		
		$settings['id_template'] = 1;

		return $settings;
	}
	
	/**
	 * aopush_loadHistory()
	 */
	private function aopush_loadHistory()
	{
		$history = AopushPushApi::aoph()->aopush_getHistory();
		return $history;
	}

	/**
	 * uninstall()
	 */
	public static function aopush_uninstall()
	{
		$post = [
			'login' => get_option('aoph_pushsender_email'),
			'domain' => get_option('aoph_pushsender_domain'),
		];

		$results = AopushPushApi::aoph()->aopush_uninstall($post);

		if (empty($results['error'])) {
			delete_option('aoph_pushsender_email');
			delete_option('aoph_pushsender_domain');
			delete_option('aoph_pushsender_token');
			delete_option('aoph_pushsender_id');
			
			delete_option('aoph_pushsender_post_used');
			delete_option('aoph_pushsender_post_update');
			delete_option('aoph_pushsender_post_insert');
			
			delete_option('aoph_pushsender_template_update_text');
			delete_option('aoph_pushsender_template_update_subject');
			delete_option('aoph_pushsender_template_update_icon');
			
			delete_option('aoph_pushsender_template_insert_text');
			delete_option('aoph_pushsender_template_insert_subject');
			delete_option('aoph_pushsender_template_insert_icon');
		}
	}
	
	/**
	 * deactivation()
	 */
	public static function aopush_deactivation()
	{
		delete_option('aoph_pushsender_post_used');
		delete_option('aoph_pushsender_post_update');
		delete_option('aoph_pushsender_post_insert');
		
		delete_option('aoph_pushsender_template_update_text');
		delete_option('aoph_pushsender_template_update_subject');
		delete_option('aoph_pushsender_template_update_icon');
		
		delete_option('aoph_pushsender_template_insert_text');
		delete_option('aoph_pushsender_template_insert_subject');
		delete_option('aoph_pushsender_template_insert_icon');
	}
	
	/**
	 * aomailer($className=__CLASS__)
	 */ 
	public static function aoph($is_plugin_page, $className=__CLASS__)
	{
		return new $className($is_plugin_page);
	}
}
