<?php
/**
 * Signalsy Platform Framework 2.0
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@wheemplay.com so we can send you a copy immediately.
 *
 * @category   Signalsy
 * @package    Signalsy Router
 * @copyright  Copyright (c) 2009 - 2010 AGPsource Team
 * @license    http://signalsy.com/license/ New BSD License
 */

 
 /* including Exceptions */
 include_once ( 'Signalsy/Exceptions.php' );
 

/**
 * Router is main class to provide signal's routing and preparing all data
 *
 * @category   Signalsy
 * @package    Signalsy Router 2
 * @copyright  Copyright (c) 2009 - 2010 AGPsource Team
 * @license    http://signalsy.com/license/     New BSD License
 */
 class Signalsy_xRouter
 {
 	/**
 	 * @var Array config section for this component
 	 */
 	private $config = null;
	 	
 	//какие модули по дефолту инициализируются для обработчика
 	public $modules = Array('file', 'session', 'db'); //, 'memcache', 'redis', 'apc');
 	
 	
 	public $defaultURLcache = 30; //на сколько дефолтно кешировать ответы 
 	public $defaultCacheType = 'file';
 	/**
 	 * @var Object object instance for singlton
 	 * @access public
 	 * @static
 	 */
	static private $instance = null;
	
	/**
	 *@var Array Connecting table with all signals connector, from cache
	 */
	protected $__routings_table = Array();
	

	/**
	 * @var Array Full param array to all signals calling
	 */
	private $param = Array();
	
	
	/**
	 * *******************************************************************************************
	 */
	
	/**
	 * @access private
	 * @param Array config array
	 * @return void
	 */ 
	private function __construct($config = null)
	{
		if (empty($config))
		{
			$this->config = Zend_Registry::get('config');
		}
		else
			$this->config = $config;
		
		// Construct or getting from cache routings and connectings tables
		$this->_constructRoutesTable();
				
		//Init required params
		$this->createParam();
		
		//инит кеш 
		$this->_prepareCache(Array($this->defaultCacheType));
	}
	
	private function __clone(){
		throw new Signalsy_Exception('Do not clone router object!');
	}
	
	/**
	 * @access public
	 * @static
	 * @return Object  Signalsy_Router instance
	 */
	public static function getInstance($config = null)
	{
		if (isset(self::$instance))
		return self::$instance;
		else
			{
				self::$instance = new Signalsy_xRouter($config);
				return self::$instance;
			}
	}
	
	
	/**
	 * Construct or gets from cache routing table
	 * In this, we use simple file cache or APC/XCache system (without Zend Cache)
	 * @access private
	 * @return Boolean 
	 */
	private function _constructRoutesTable()
	{
		//construct manually
		$this->__routings_table = connectManager::exportRouting();			
		
		return true;		
	}
	
	
	/**
	 * Utilite function, prepare cache if using routes cache
	 * Принимает на вход идентификатор кешей, какие создавать - массив ('memcache','file','redis','apc')
	 * 
	 * 
	 * @return Object Zend_Cache
	 */
	private function _prepareCache($caches = Array('file'))
	{
	
		//servers 	Array 	
		$_default_frontend = array(
				'caching' => true,
				'cache_id_prefix' => 'ssy_',
				'logging' => false,
				'write_сontrol' => false,
				'automatic_serialization' => true,
				'automatic_cleaning_factor' => 0,
				'ignore_user_abort' => true
		);
		
		foreach($caches as $c)
		{
			if (Zend_Registry::isRegistered('cache_' . $c) === true) continue;	
			
			if ($c == 'memcache')
			{
				$_backend_name = 'Libmemcached';
				$_backend_opt = array(
									'servers' => array(array(
										'host' => 'localhost', 
										'port' => 11211, 
										'persistent' => true, 
										'weight' => 1, 
										'timeout' => 5, 
										'retry_interval' => 15, 
										'status' => true, 
										'failure_callback' => ''
									)),
									'client' => array(
										'COMPRESSION' => true,
										'SERIALIZER' => Memcached::SERIALIZER_JSON,
										'HASH' => Memcached::HASH_MD5,
										'DISTRIBUTION' => Memcached::DISTRIBUTION_CONSISTEN,
										'LIBKETAMA_COMPATIBLE' => true,
										'BUFFER_WRITES' => false,
										'BINARY_PROTOCOL' => true,
										'NO_BLOCK' => true,
										'CACHE_LOOKUPS' => false										
									)
								);
			}
			else
			if ($c == 'file')
			{
				$_backend_name = 'File';
				$_backend_opt = Array(	'cache_dir' => $this->config['Cache']['cache_path'],
										'file_locking' => false,
										'read_control' => true,
										'read_control_type' => 'crc32'				
									);
			}
			
			
			try
			{
				
				if (!empty($_backend_name))
				{
					$_cache = Zend_Cache::factory('Core', $_backend_name, $_default_frontend, $_backend_opt);

					if ((!empty($_cache)) && ($_cache instanceOf Zend_Cache_Core))
					{
						//this is default cache to all system, adding them to registry
						Zend_Registry::set('cache_' . $c, $_cache);
					}
				}
				
			}catch(Exception $e){
				continue;
			}			
		}
		
		return true;
	}
	
	//готовим сессию
	private function _prepareSession(){
		
		$session = new Zend_Session_Namespace('Default'); //default session ns
		Zend_Registry::set('session', $session);
		
		return true;
	}
	
	//готовим БД
 	private function _prepareDb($useCache = false){
		
		$config = Zend_Registry::get('config');
		
 		$options = array(
		    	Zend_Db::AUTO_QUOTE_IDENTIFIERS => true,
				Zend_Db::ALLOW_SERIALIZATION => true,
				Zend_Db::AUTO_RECONNECT_ON_UNSERIALIZE => true
 		);

		try
		{
			$db = new Zend_Db_Adapter_Mysqli(array(
				    'host'     => $config['Database']['db_host'],
				    'username' => $config['Database']['db_user'],
				    'password' => $config['Database']['db_password'],
				    'dbname'   => $config['Database']['db_database_name'],
				    'port' => $config['Database']['db_port'],
					'charset'   => 'utf8',
					'options' => $options			
			));
		}
		catch (Exception $e)
		{
		    die($e->getMessage());
		}
			
		if ((!is_object($db)) || ($db == FALSE) || (mysqli_connect_errno()))
		{
			die('Platform required DB connection to start. Error: ' . mysqli_connect_error());
		}
	
		//Мы работем ТОЛЬКО с UTF-8 кодировкой
		mysqli_set_charset($db->getConnection(), 'utf8');

		//использовать встроенное кеширование		
		
		Zend_Registry::set('db', $db);
		
		return true;
	}
	
	
	/**
	 * Create required param array
	 * @return void
	 */
	private function createParam()
	{
		$this->param = Array('http' => Array(), 'url' => null);
	}
	

	/**
	 * Preparing HTTP params
	 */
	private function prepareHTTPRequest($includeServiceOpt = false)
	{
		//exclude flash amf query: Content-type: application/x-amf
		
		if ((!isset($_SERVER['CONTENT_TYPE'])) || ((isset($_SERVER['CONTENT_TYPE'])) && ($_SERVER['CONTENT_TYPE'] != 'application/x-amf')))
		{		
		
			// из запроса убираются все параметры, которые начинаются с символа _, например, _dc
			$tmp = $_REQUEST;
			$httprequest = Array();
			
			foreach ($tmp as $name=>$item)
			{
				$name = substr(strtolower(trim($name)), 0, 1024); //максимальная длина имени переменной 1024 символа
				
				if ($_SERVER["REQUEST_METHOD"] == 'GET')
				{					
					$item = substr(trim($item), 0, 4096); //максимальная длина значения, передаваемого - 4 Кб
				}
				else
				{
					$name = substr(strtolower(trim($name)), 0, 1024); //максимальная длина имени переменной 1024 символа
				}
				
				//служебный параметр для кеширования не передается 
				if ($includeServiceOpt == false) 
				{	
					if (strpos($name, '_') === 0)    continue;
					else
						$httprequest[$name] = $item;
				}
				else
					$httprequest[$name] = $item;
			}
		}
		
		$this->param['http'] = $httprequest;
	}



	/**
	 * Preparing URL to routing
	 * @param string URL
	 * @return string
	 */
	public function prepareURL($url = null)
	{
		if (empty($url)) return '';
		else
			{
				//!NOTE: max URL length is 4096 symbols
				$url = strtolower(substr($url, 0, 4096));
				
				if ($url{0} == '/')
				{
					$url{0} = '';
				}
				
				if ($url{(strlen($url)-1)} == '/')
				{
					$url{(strlen($url)-1)} = '';
				}
				
				$url = trim($url);
				
				if (!empty($url)) return $url;
				else
					return '';				
			}
	}
	
	
	/**
	 * Main function - dispatch URL and all signals'
	 * @param string URL
	 * @param Boolean в серверном варианте не обрабатывать HTTP заголовки
	 */
	public function dispatchURL($_url = null)
	{
		$_url = $this->prepareURL($_url);
		$this->param['url'] = $_url;
		
		//processing them!
		//ищем URL, сопоставимый с одним из таблицы 
		//!TODO: быдлокод детектед
		$_x = explode('/', $_url);
		$_ns = ucwords(trim($_x[0])); //в какой части искать адрес
		unset($_x[0]);
		$_url = implode('/', $_x); //соберем без префикса
			
			if (!array_key_exists($_ns, $this->__routings_table))
			{
				//если не нашли?
				$_ns = 'Default';				
			}		
			
			//
			$_rt_def = $this->__routings_table[$_ns]['_default'];
			
			//поиск адреса
			$_handler_url = null;
			
			foreach($this->__routings_table[$_ns]['url'] as $_rt_url => $hndl)
			{
				if ($_rt_url == $_url)
				{
					$_handler_url = $hndl;
					break;					
				}			
			}
			
			if (empty($_handler_url))
			{
				$_handler_url = $this->__routings_table['Default']['url']['error/404']; //дефолтный пустой урл	
				$_rt_def = $this->__routings_table['Default']['_default'];			
			}			
		
			//теперь обработка 
			$_opt = array_merge($_rt_def, $_handler_url);			
			
			$this->process($_opt);
			
	}
	
	

	
	/**
	 * Manually emmit signal with optionally param
	 * special thanks Appocaliptica One for this method :)	 * 
	 * 
	 * @access public
	 * @param string|array signal
	 * @param mixed optional arguments
	 * @return mixed
	 */
	public function process($opt = null)
	{
		if (empty($opt)) return false;
		else
			{
				//режим тестирования - проверим глобальный флаг 
				if (Zend_Registry::isRegistered('isTestMode') === true)
				{
					$opt['isTest'] = Zend_Registry::get('isTestMode');												
				}
				
				if ($opt['isTest'] === true)
				{
					$opt['cache'] = false; //отключаем кеш для режима тестирования
				}
				
				
				if ($opt['cache'] === true)
					$opt['cache'] = $this->defaultURLcache;
			
				//0. Проверим, можно ли кешировать этот вызов 
				if ((!empty($opt['cache'])) && (Zend_Registry::isRegistered('cache_' . $this->defaultCacheType) === true))
				{
					$cache = Zend_Registry::get('cache_' . $this->defaultCacheType);
					
					if ((!empty($cache)) && ($cache instanceOf Zend_Cache_Core))
					{
						//пробуем загрузить из кеша 
						$_data = $cache->load('url_' . md5(implode(';', $this->param['http']) . ';' . $this->param['url']));	
						
						if (!empty($_data))
						{
							$this->output($_data, $opt, true);
							return true;						
						}
					}
				}
				
				
				//1. Проверим, можно ли вызвать переданное 
				if (is_callable($opt['handler']))
				{
					//теперь генерируем, что надо
					$_modules = Array();
					
					// disabledModules
					foreach($this->modules as $_mod)
					{
						if (!in_array($_mod, $opt['disabledModules']))
						{
							$_modules[] = $_mod;
						}						
					}
					
					try {
						
						//инит модулей
						$this->initModules($_modules);
						
						
						
						//ну как бы и все, запускаем 
						$result = call_user_func($opt['handler'], $this->param['http'], $this->param['url'], $opt['isTest']);
						
						if ((!empty($opt['cache'])) && (Zend_Registry::isRegistered('cache_' . $this->defaultCacheType) === true))
						{
							$cache = Zend_Registry::get('cache_' . $this->defaultCacheType);
							
							if ((!empty($cache)) && ($cache instanceOf Zend_Cache_Core))
							{
								//!TODO: работа с тегами 
								$_cacheResult = $cache->save($result, 'url_' . md5(implode(';', $this->param['http']) . ';' . $this->param['url']), Array('url'), $opt['cache']);	
							
								if ($_cacheResult === false)
									echo 'Save cache problem!!!';
							}
						}
						
						//обработаем результат 
						$this->output($result, $opt, false);
					
					}catch(Exception $e)
					{
						throw new Signalsy_SignalSlot_Exception('Exception by processing URL handler. MSG: ' . $e->getMessage());						
					}
					
					return true;
					
				}
				else
					throw new Signalsy_SignalSlot_Exception('URL Handler must be callable (function or static class method)');
			}
	}
	
	
	
	
	/**
	 * Готовит нужные модули для работы обработчика 
	 * 
	 */
	public function initModules($_mods = Array())
	{
		//инициализировать модули  ИМЕННО в таком порядке
		$_caches = Array();
		
		if (in_array('memcache', $_mods)) $_caches[] = 'memcache';
		if (in_array('file', $_mods)) $_caches[] = 'file';
		if (in_array('apc', $_mods)) $_caches[] = 'apc';
		
		$this->_prepareCache($_caches);
		
		if (in_array('db', $_mods)) $this->_prepareDb();
		if (in_array('session', $_mods)) $this->_prepareSession();

		return true;
	}
	

	static public function output($data = Array(), $opt = Array(), $isCached = false)
	{
		if ($opt['type'] == 'html')
		{
			header('Content-type: text/html; charset=UTF-8');
			echo $data['data'];
		}
		else 
		{
		
			//добавляет метку времени 
			$data['timestamp'] = time();
			if (!array_key_exists('status', $data)) $data['status'] = 'OK'; //дефолтно, если дошли сюда - статус ОК			
						
			if ($opt['debug'] == false)
			{
				//убрать дебаг информацию
				$data['debug'] = null;
			}
			else 
			{
				//попробуем достать профайлинг запросов 
				$profiler = Zend_Registry::getInstance()->get('db')->getProfiler();
				
				if (!empty($profiler))
				{
					$p = $profiler->getQueryProfiles();
			 
					 if (!empty($p))
					 {
						foreach($p as $q)
						{
							$prof[] = Array(
								'sql' => $q->getQuery(),
								'time' => $q->getElapsedSecs()
							);
						}					 
					 
			 			$data['debug']['db'] = array(
							'num_queries' => $profiler->getTotalNumQueries(),
							'total_secs' => $profiler->getTotalElapsedSecs(),
							'profile' => $prof,
							'last' => Array(
								'sql' => $profiler->getLastQueryProfile()->getQuery(),
								'time' => $profiler->getLastQueryProfile()->getElapsedSecs()
							)
						);				
					}
				}

				$data['debug']['memoryUsage'] = memory_get_peak_usage(true);
				$data['debug']['systemLoad'] = sys_getloadavg();
			}
			
			if ($opt['type'] == 'json')
			{
				header('Content-type: application/json');
				$data = Zend_Json::encode($data, false);			
			}
			else 
			if ($opt['type'] == 'cvs')
			{
				header('Content-type: text/csv');
			}
			else 
			if ($opt['type'] == 'xml')
			{
				header('Content-type: application/xml');
				
				//!TODO: сделать генерацию наверное
			}
		
			echo $data;
		}
		
		//выставим время обработки 
		$_signalsy_ft = microtime(true);
		$_diff = $_signalsy_ft - Zend_Registry::get('signalsy_st');
		
		header('X-Signalsy-Profiling: ' . $_diff);
		header('X-Powered-By: Signalsy Platform 2.0');	

		if ($isCached == true)
			header('X-Signalsy-Cached: ' . $opt['cache'] . ' s.');
		
		ob_end_flush();

		return true;
	}
	
 }