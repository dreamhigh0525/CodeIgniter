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
 * Database Result Class
 * 
 * This is the platform-independent result class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @category	Database
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/database/
 */
class CI_DB_result {

	var $conn_id		= FALSE;
	var $result_id		= FALSE;
	var $result_array	= array();
	var $result_object	= array();
	var $current_row 	= 0;
	var $num_rows		= 0;


	/**
	 * Query result.  Acts as a wrapper function for the following functions.
	 * 
	 * @access	public
	 * @param	string	can be "object" or "array"
	 * @return	mixed	either a result object or array	 
	 */	
	function result($type = 'object')
	{	
		return ($type == 'object') ? $this->result_object() : $this->result_array();
	}

	// --------------------------------------------------------------------

	/**
	 * Query result.  "object" version.
	 * 
	 * @access	public
	 * @return	object 
	 */	
	function result_object()
	{
		if (count($this->result_object) > 0)
		{
			return $this->result_object;
		}
		
		$this->_data_seek(0);
		while ($row = $this->_fetch_object())
		{ 
			$this->result_object[] = $row;
		}
		
		return $this->result_object;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Query result.  "array" version.
	 * 
	 * @access	public
	 * @return	array 
	 */	
	function result_array()
	{
		if (count($this->result_array) > 0)
		{
			return $this->result_array;
		}

		$this->_data_seek(0);			
		while ($row = $this->_fetch_assoc())
		{
			$this->result_array[] = $row;
		}
		
		return $this->result_array;
	}

	// --------------------------------------------------------------------

	/**
	 * Query result.  Acts as a wrapper function for the following functions.
	 * 
	 * @access	public
	 * @param	string	can be "object" or "array"
	 * @return	mixed	either a result object or array	 
	 */	
	function row($n = 0, $type = 'object')
	{
		return ($type == 'object') ? $this->row_object($n) : $this->row_array($n);
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a single result row - object version
	 * 
	 * @access	public
	 * @return	object 
	 */	
	function row_object($n = 0)
	{
		$result = $this->result_object();
		
		if (count($result) == 0)
		{
			return $result;
		}

		if ($n != $this->current_row AND isset($result[$n]))
		{
			$this->current_row = $n;
		}

		return $result[$this->current_row];
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a single result row - array version
	 * 
	 * @access	public
	 * @return	array 
	 */	
	function row_array($n = 0)
	{
		$result = $this->result_array();

		if (count($result) == 0)
		{
			return $result;
		}
			
		if ($n != $this->current_row AND isset($result[$n]))
		{
			$this->current_row = $n;
		}
		
		return $result[$this->current_row];
	}

		
	// --------------------------------------------------------------------

	/**
	 * Returns the "first" row
	 * 
	 * @access	public
	 * @return	object 
	 */	
	function first_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}
		return $result[0];
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the "last" row
	 * 
	 * @access	public
	 * @return	object 
	 */	
	function last_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}
		return $result[count($result) -1];
	}	

	// --------------------------------------------------------------------

	/**
	 * Returns the "next" row
	 * 
	 * @access	public
	 * @return	object 
	 */	
	function next_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}

		if (isset($result[$this->current_row + 1]))
		{
			++$this->current_row;
		}
				
		return $result[$this->current_row];
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the "previous" row
	 * 
	 * @access	public
	 * @return	object 
	 */	
	function previous_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}

		if (isset($result[$this->current_row - 1]))
		{
			--$this->current_row;
		}
		return $result[$this->current_row];
	}

	// --------------------------------------------------------------------

	/**
	 * Number of rows in the result set
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @access	public
	 * @return	integer
	 */
	function num_rows()
	{
		return $this->num_rows;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Number of fields in the result set
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @access	public
	 * @return	integer
	 */
	function num_fields()
	{
		return 0;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Fetch Field Names
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @access	public
	 * @return	array
	 */
	function field_names()
	{		
		return array();
	}

	// --------------------------------------------------------------------

	/**
	 * Field data
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @access	public
	 * @return	array
	 */
	function field_data()
	{
		$F				= new stdClass();
		$F->name 		= NULL;
		$F->type 		= NULL;
		$F->default		= NULL;
		$F->max_length	= NULL;
		$F->primary_key = NULL;
			
		return $retval[] = $F;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Free the result
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @return	null
	 */		
	function free_result()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Data Seek
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @access	private
	 * @return	array
	 */
	function _data_seek()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Result - associative array
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @access	private
	 * @return	array
	 */
	function _fetch_assoc()
	{
		return array();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Result - object
	 *
	 * Note: This function is normally overloaded by the identically named 
	 * method in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * This function will only be called when a cached result object is in use.
	 *
	 * @access	private
	 * @return	object
	 */
	function _fetch_object()
	{
		return array();
	}

}
// END DB_result class
?>