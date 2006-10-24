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

	var $_parent_name = '';

	/**
	 * Constructor
	 *
	 * @access public
	 */
	function Model()
	{
		// If the magic __get() method is used in a Model references can't be used.
		$this->_assign_libraries( (method_exists($this, '__get')) ? FALSE : TRUE );
		//$this->_assign_libraries( (method_exists($this, '__get') OR method_exists('__set')) ? FALSE : TRUE );
		
		// We don't want to assign the model object to itself when using the
		// assign_libraries function below so we'll grab the name of the model parent
		$methods = get_class_methods($this);
		if (isset($methods[0]))
		{
			$this->_parent_name = $methods[0];
		}
		
		log_message('debug', "Model Class Initialized");
	}

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
		foreach (array_keys(get_object_vars($CI)) as $key)
		{
			if ( ! isset($this->$key) AND $key != $this->_parent_name)
			{
				// In some cases using references can cause
				// problems so we'll conditionally use them
				if ($use_reference == TRUE)
				{
					// Needed to prevent reference errors with some configurations
					$this->$key = '';
					$this->$key =& $CI->$key;
				}
				else
				{
					$this->$key = $CI->$key;
				}
			}
		}		
	}

}
// END Model Class
?>