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
 * Code Igniter Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/libraries/config.html
 */
class Model {

	/**
	 * Constructor
	 *
	 * @access public
	 */
	function Model()
	{
		$this->_assign_libraries(FALSE);
		log_message('debug', "Model Class Initialized");
	}
	// END Model()

	/**
	 * Assign Libraries
	 *
	 * Creates local references to all currently instantiated objects
	 * so that any syntax that can be legally used in a controller 
	 * can be used within models.
	 *
	 * @access private
	 */	
	function _assign_libraries($use_reference = TRUE)
	{
		$CI =& get_instance();
		foreach (get_object_vars($CI) as $key => $var)
		{
			if (is_object($var) AND ! isset($this->$key))
			{
				if ($use_reference === TRUE)
				{
					$this->$key =& $CI->$key;						
				}
				else
				{
					$this->$key = $CI->$key;
				}
			}
		}

	}
	// END _assign_libraries()

}
// END Model Class
?>