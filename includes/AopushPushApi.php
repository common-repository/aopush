<?php 
class AopushPushApi
{	
	public $config = [];
	
	public function __construct()
	{
		$config_path = realpath(AOPH_AOPUSH_DIR) . '/config.php';
		if (file_exists($config_path)) {
			$this->config = require($config_path);
		}
	}
	
	/**
	 * aopush_getBalance()
	 */
	public function aopush_getBalance()
	{
		$data = [
			'balance' => 0,
			'currency' => 'RUB',
			'limit' => 500,
		];
		
		return self::aopush_request('balance', $data);
	}
	
	/**
	 * aopush_getHistory($data=[])
	 */
	public function aopush_getHistory()
	{
		$history['stat'][1] = [
			'type' => __('NewPost', 'aopush'),
			'send' => 0,
			'productive' => 0,
		];
		$history['stat'][2] = [
			'type' => __('UpdatePost', 'aopush'),
			'send' => 0,
			'productive' => 0,
		];
		$history['stat'][3] = [
			'type' => __('Send Mailing list', 'aopush'),
			'send' => 0,
			'productive' => 0,
		];

		$history['total'] = [
			'client' => 0,
			'send' => 0,
			'productive' => 0,
		];
		
		if (!empty($_POST['data']['AophHistoryNotifications']['date_start'])) {
			$date['date_start'] = self::aopush_validateForm('date', $_POST['data']['AophHistoryNotifications']['date_start']);
		}
		
		if (empty($date['date_start'])) {
			$date['date_start'] = date('Y-m-d H:i:s', strtotime('-1 month'));
		}
		
		if (!empty($_POST['data']['AophHistoryNotifications']['date_end'])) {
			$date['date_end'] = self::aopush_validateForm('date', $_POST['data']['AophHistoryNotifications']['date_end']);
		}

		if (empty($date['date_end'])) {
			$date['date_end'] = date('Y-m-d H:i:s');
		}

		$request = self::aopush_request('history', $date);
		if (!empty($request['error'])) {
			
			$history['error'] = self::aopush_validateForm('text', $request['message']);
			
		} elseif (!empty($request) && is_array($request)) {

			$request = self::aopush_validateForm('stat', $request);
			return ['data' => $request, 'date' => $date];
		}

		return ['data' => $history, 'date' => $date];
	}
	
	/**
	 * aopush_registration($data=[])
	 */
	public function aopush_registration($data=[])
	{
		return self::aopush_request('registration', $data);
	}
	
	/**
	 * aopush_uninstall($post=[])
	 */
	public function aopush_uninstall($data=[])
	{
		return self::aopush_request('uninstall', $data);
	}
	
	/**
	 * aopush_sendpush($post=[])
	 */
	public function aopush_sendpush($post=[])
	{
		if (empty($post)) {
			return false;
		}
		
		return self::aopush_request('sendpush', $post);
	}
	
	/**
	 * aopush_sendtest($post=[])
	 */
	public function aopush_sendtest($post=[])
	{
		if (empty($post)) {
			return false;
		}
		
		return self::aopush_request('sendtest', $post);
	}

	/**
	 * aopush_getFormat($float)
	 */
	public static function aopush_getFormat($int=0, $format=0)
	{
		if (empty($int)) {
			return 0;
		}
		
		if (empty($format)) {
			return $int;
		} 
		
		if ($format=='money') {
			
			return @number_format($int, 2, '.', ' ');
			
		} elseif ($format=='number') {
			
			return @number_format($int, 0, ',', ' ');
			
		} elseif ($format=='float') {
			
			return @number_format($int, 2, ',', ' ');
			
		} elseif ($format=='phone') {
			
			$int = preg_replace('/[^0-9]/i', '', $int);
			return preg_replace('/^[^7]/i', '7', $int);

		} else {
			return $int;
		}
	}
	
	/**
	 * aopush_addError($error)
	 */
	public function aopush_addError($error='', $settings=[])
	{
		$error = self::aopush_validateForm('text', $error);
		
		if (!empty($error)) {

			if (empty($settings['error'])) {
				
				$settings['error'] = $error.'<br>';

			} else {
		
				if (!preg_match('/('.$error.')/i', $settings['error'])) {
					$settings['error'] .= $error.'<br>';
				}
			}
		}
		
		return $settings['error'];
	}
	
	/**
	 * getHost() 
	 */
	public function aopush_getHost() 
	{
		$host_sources = ['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];
		$source_transformations = [
			'HTTP_X_FORWARDED_HOST' => function($value) {
				$elements = explode(',', $value);
				return trim(end($elements));
			}
		];
		
		$host = '';
		foreach ($host_sources as $source) {
			if (!empty($host)) {
				break;
			}
			
			if (empty($_SERVER[$source])) {
				continue;
			}
			
			$host = $_SERVER[$source];
			if (array_key_exists($source, $source_transformations)) {
				$host = $source_transformations[$source]($host);
			} 
		}

		// Remove port number from host
		$host = preg_replace('/:\d+$/', '', $host);

		return trim($host);
	}
	
	/**
	 * aopush_getRule()
	 */
	public function aopush_getRule()
	{
		$rule = [
			'email' => 'email',
			'url' => 'url',
			'int' => 'int',
			'boolean' => 'boolean',
			'text' => 'text',
			'date' => 'date',
			'stat' => 'stat',
		];
		
		return $rule;
	}

	/**
	 * aopush_validateForm() 
	 */
	public function aopush_validateForm($index, $str) 
	{
		$rule = self::aopush_getRule();
		foreach ($rule as $key=>$value) {
			if ($index==$key) {
				$method = 'aopush_validate'.ucfirst($value);
				if (!method_exists($this, $method)) {
					return false;
				}

				return $this->$method($str);
			}
		}
	}
	
	/**
	 * aopush_validateEmail()
	 */
	public function aopush_validateEmail($str='')
	{
		if (empty($str)) {
			return false;
		}
		
		if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
			return $str;
		}
		
		return false;
	}
	
	/**
	 * aopush_validateInt()
	 */
	public function aopush_validateInt($int=0)
	{
		return (int) $int;
	}
	
	/**
	 * aopush_validateBoolean()
	 */
	public function aopush_validateBoolean($boolean=false)
	{
		if ($boolean==='true' || $boolean===true) {
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * aopush_validateUrl()
	 */
	public function aopush_validateUrl($str='')
	{
		if (empty($str)) {
			return false;
		}
		
		if (filter_var($str, FILTER_VALIDATE_URL)) {
			return $str;
		}
		
		return false;
	}
	
	
	/**
	 * aopush_validateText()
	 */
	public function aopush_validateText($str='')
	{
		$str = strip_tags($str);
		$str = htmlentities($str);
		return $str;
	}
	
	/**
	 * aopush_validateDate()
	 */
	public function aopush_validateDate($date='')
	{
		$pattern = '/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/i';
		if (preg_match($pattern, $date)) {
			return $date;
		}

		return false;
	}
	
	/**
	 * aopush_validateStat($stat=[])
	 */
	public function aopush_validateStat($stat=[])
	{
		if (empty($stat) || !is_array($stat)) {
			return false;
		}

		foreach ($stat as $a=>$b) {
			if (is_array($b)) {
				foreach ($b as $c=>$d) {
					if (is_array($d)) {
						foreach ($d as $e=>$f) {
							if (!is_array($f)) {
								$stat[$a][$c][$e] = self::aopush_validateForm('int', $f);
							}
						}
					} else {
						$stat[$a][$c] = self::aopush_validateForm('int', $d);
					}
				}
			} else {
				$stat[$a] = self::aopush_validateForm('int', $b);
			}
		}
		
		return $stat;
	}

	/**
	 * aopush_request()
	 */
	private function aopush_request($method='', $data=[])
	{		
		if (empty($method)) {
			return false;
		}
		
		$domain = self::aopush_getHereDomain();
		if (empty($domain)) {
			return false;
		}
		
		$data['domain'] = $domain;
		

		if (empty($data['login'])) {
			$data['login'] = get_option('aoph_pushsender_email');
		}

		if (empty($data['login'])) {
			return false;
		}

		$hash = !empty(get_option('aoph_pushsender_token')) ? get_option('aoph_pushsender_token') : self::aopush_getActivateHash($data['login']);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://aoserver.ru/resurces/push2/'.$method.'?'.$hash);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);		
		$answer = curl_exec($ch);

		$array = json_decode($answer, true);
		if (empty($array) || !is_array($array)) {
			return ['error'=>1, 'message'=>'Incorrect data', 'code'=>100];
		}
		
		if (!empty($array['token'])) {
			update_option('aoph_pushsender_token', $array['token']);
		}

		return $array;
	}
	
	/**
	 * aopush_getActivateHash($email)
	 */
	private function aopush_getActivateHash($email='')
	{
		if (empty($email)) {
			return false;
		}
		
		$domain = self::aopush_getHereDomain();
		if (empty($domain)) {
			return false;
		}

		return hash_hmac('sha256', $email, $domain);	
	}
	
	/**
	 * aopush_getHereDomain()
	 */
	private function aopush_getHereDomain()
	{
		$server_name = self::aopush_getHost();
		$server_name = preg_replace('/^(www\.)/i', '', $server_name);
		return !empty(get_option('aoph_pushsender_domain')) ? get_option('aoph_pushsender_domain') : $server_name;
	}

	/**
	 * aoph($className=__CLASS__)
	 */ 
	public static function aoph($className=__CLASS__)
	{
		return new $className();
	}
	
}
