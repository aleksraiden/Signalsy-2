<?php
ob_start();
/**
 * Signalsy Platform Framework
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
 * @package    Signalsy Core
 * @copyright  Copyright (c) 2009 AGPsource Team
 * @license    http://signalsy.com/license/ New BSD License
 */
$_signalsy_st = microtime(true);

 date_default_timezone_set('Europe/Kiev');
 error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);
 //! при использовании XCache могут быть E_WARNING сообщения от загрузчика, их можно игнорировать
 if (isset($_REQUEST['debug']))
 {
 	error_reporting(E_ALL);
 }
 else
 	error_reporting(E_ERROR);
 	
 error_reporting(E_ALL);
 
 //если системная загрузка больше 0.8, не обслуживаем дальше клиентов
 $load = sys_getloadavg();
 if ($load[0] > 80) {
    header('HTTP/1.1 503 Too busy, try again later');
    die('Server too busy. Please try again later.');
 }

 
 //default including code path	
 set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/inc/lib' . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/inc/app' );
 
 require_once('Zend/Loader/Autoloader.php');
 require_once('connectManager.php');
 
 //rewrite base URL, default for index = www.domain.com/sire/index
 if ((empty($_REQUEST['__route__'])) || ($_REQUEST['__route__'] == '/') || (!isset($_REQUEST['__route__'])))
 {
	//удалить сессию
	Zend_Session::destroy(true, true);
	
	@header('Location: /');
	die(1);
 }
 else
 {
	$loader = Zend_Loader_Autoloader::getInstance();
	 
	$loader->registerNamespace('Admin'); //для админ-панели
	$loader->registerNamespace('App'); // для приложения
	$loader->registerNamespace('Api'); // для API
	$loader->registerNamespace('Default'); // для API
	$loader->registerNamespace('Signalsy'); // для API
	
	//главный конфиг
	$config = parse_ini_file( $_SERVER['DOCUMENT_ROOT'] . '/inc/config.ini', true); 
	Zend_Registry::set('config', $config);
	
	//время начала обработки
	Zend_Registry::set('signalsy_st', $_signalsy_st);
	
	//staring routings
	$router = Signalsy_xRouter::getInstance($config);

	//start dispatch
 	$router->dispatchURL($_REQUEST['__route__']); 
 }
 


 
