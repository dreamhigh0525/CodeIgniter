<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html 
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Logging Class
 * 
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Logging
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/general/errors.html
 */
class CI_Log {

	var $log_path;
	var $_threshold	= 1;
	var $_date_fmt	= 'Y-m-d H:i:s';
	var $_enabled	= TRUE;
	var $_levels	= array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	string	the log file path
	 * @param	string	the error threshold
	 * @param	string	the date formatting codes
	 */
	function CI_Log($path = '', $threshold = '', $date_fmt = '')
	{	
		$this->log_path = ($path != '') ? $path : BASEPATH.'logs/';

		if ( ! is_dir($this->log_path) OR ! is_writable($this->log_path))
		{
			$this->_enabled = FALSE;
		}
		
		if (ctype_digit($threshold))
		{
			$this->_threshold = $threshold;
		}
			
		if ($date_fmt != '')
		{
			$this->_date_fmt = $date_fmt;
		}
	}
	// END CI_Log()
	
	// --------------------------------------------------------------------
	
	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @access	public
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */		
	function write_log($level = 'error', $msg, $php_error = FALSE)
	{		
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}
	
		$level = strtoupper($level);
		
		if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}
	
		$filepath = $this->log_path.'log-'.date('Y-m-d').EXT;
		$message  = '';
		
		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}
			
		if ( ! $fp = @fopen($filepath, "a"))
		{
			return FALSE;
		}

		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";
		
		flock($fp, LOCK_EX);	
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);
	
		@chmod($filepath, 0666); 		
		return TRUE;
	}
	// END write_log()
}
// END Log Class
?>