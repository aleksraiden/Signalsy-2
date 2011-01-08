<?php
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


class connectManager
{
	
	/**
	 * Return array of URL and associate signals list
	 * @static
	 * @public
	 * @return Array 
	 */
	static public function exportRouting()
	{
		return Array(
			//специальный блок для индексного урла или для ненайденных
			'Default' => Array(
				//дефолтные настройки
				'_default' => Array(		
					'type' => 'html', //тип выводимой инфыормации, если не указан, дефолтный json, csv, html, xml
					'cache' => 3600, //задает, кешировать ли весь урл, если тру - стандартное кеширования, фалсе - запрет, число - на сколько кешировать. Дефолт 30 сек.
					'params' => array(), //массив обязательных параметров для обработки, проверяются через empty
					'debug' => false, //выводить ли дебаг информацию
					'isTest' => true, //флаг, указывает на возможность тестирования этого URL
					'testParam' => array(), //массив параметр=>значение для тестирования
					'disabledModules' => array('session', 'db', 'memcache') //какие модули можно не инитить = кеш, базу, сессию и т.п.			
				),
				
				'url' => Array(
					'' => array(
						'handler' => array('Admin_SignalsyTest', 'test_PHPInfo') //непосредственно обработчик
					),
					//обработчик неизвестных адресов 
					'error/404' => array(
						'type' => 'html',
						'cache' => false,
						'debug' => false, 
						'handler' => array('Default_Error', 'error_404')
					)
				) 
		
			),
		
		
			//основная часть адреса - admin/<url>
			'Admin' => Array(
				//дефолтные настройки
				'_default' => Array(		
					'type' => 'json', //тип выводимой инфыормации, если не указан, дефолтный json, csv, html, xml
					'cache' => true, //задает, кешировать ли весь урл, если тру - стандартное кеширования, фалсе - запрет, число - на сколько кешировать. Дефолт 30 сек.
					'params' => array(), //массив обязательных параметров для обработки, проверяются через empty
					'debug' => false, //выводить ли дебаг информацию
					'isTest' => true, //флаг, указывает на возможность тестирования этого URL
					'testParam' => array(), //массив параметр=>значение для тестирования
					'disabledModules' => array() //какие модули можно НЕ инитить = кеш, базу, сессию и т.п.			
				),
				//непосредственно URL-ы для обработки
				//настройки совпадающие с _default для блока можно опускать 
				'url' => Array(
					'test/phpinfo' => array(
						'type' => 'html',
						'disabledModules' => array('session', 'db', 'file', 'memcache'),
						'handler' => array('Admin_SignalsyTest', 'test_PHPInfo') //непосредственно обработчик
					),
					'test/session' => array(
						'debug' => true,
						'handler' => array('Admin_SignalsyTest', 'test_Session') //непосредственно обработчик
					)
				) 
		
			),
			'App' => Array(),
			'Api' => Array()
		
		);
	}
	
}