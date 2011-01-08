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
 * @package    Signalsy Exceptions
 * @copyright  Copyright (c) 2009 AGPsource Team
 * @license    http://signalsy.com/license/ New BSD License
 */
 
 
 // Main Exception class
class Signalsy_Exception extends Exception {}

// Exception to aborting routing operation (all next signals in table be canceled)
class Signalsy_StopRouting_Exception extends Signalsy_Exception {}

// Some critical error in preparing routing or signals table
class Signalsy_SignalSlot_Exception extends Signalsy_Exception {}

// Unknown or invalide signal
class Signalsy_Unknown_Signal_Exception extends  Signalsy_Exception {}

//Unknown or invalide (not callable) signal slot
class Signalsy_Unknown_Connect_Exception extends  Signalsy_Exception {}

// Unknown or invalide signal namespace
class Signalsy_Unknown_Signal_Namespace_Exception extends  Signalsy_Exception {}

//Unknown URL
class Signalsy_Unknown_Url_Exception extends  Signalsy_Exception {}
 
 