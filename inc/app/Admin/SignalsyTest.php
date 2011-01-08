<?php
class Admin_SignalsyTest
{
	
	static public function test_Session($data = Array(), $url = null, $isTest = false)
	{
		$_x = Zend_Registry::get('session');
		
		$_x->testSessionValue_memUsage = memory_get_usage(true);
		
		
		return Array('status' => 'OK', 'data' => $_x->testSessionValue_memUsage);
	}
	
	
	
	static public function test_PHPInfo($data = Array(), $url = null, $isTest = false)
	{
		phpinfo();
	}
	
	
	static public function test_DB($data = Array(), $url = null, $isTest = false)
	{
		$db = Zend_Registry::get('db');
		
		$sql = 'SELECT message, message_at, message_type FROM messages_feed_tbl WHERE user_id = 2 ORDER BY message_at DESC LIMIT 50';
		$res = $db->fetchAll($sql);
		
		
		return Array(
			'status' => 'OK',
			'debug' => Array(
				'params' => $data
			),
			'data' => $res
		);	
	}
	
}