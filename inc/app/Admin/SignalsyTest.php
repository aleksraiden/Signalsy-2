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
	
}