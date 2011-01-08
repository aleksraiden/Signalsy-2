<?php
/**
 * Основные дефолтные обработчики 
 * @author aleks_raiden
 *
 */
class Default_Error
{

	static public function error_404($data = Array(), $url = null)
	{
		return Array('status' => 'OK', 'data' => 'Requested URL: ' . $url . ' is invalid and can\'t be processed by application.');
	}

}