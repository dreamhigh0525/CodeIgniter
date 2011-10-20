<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * NOTICE OF LICENSE
 * 
 * Licensed under the Open Software License version 3.0
 * 
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2011, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * oci8 Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_oci8_result extends CI_DB_result {

	var $stmt_id;
	var $curs_id;
	var $limit_used;

	/**
	 * Number of rows in the result set.
	 *
	 * Oracle doesn't have a graceful way to retun the number of rows
	 * so we have to use what amounts to a hack.
	 *
	 *
	 * @access  public
	 * @return  integer
	 */
	function num_rows()
	{
		if ($this->num_rows === 0 && count($this->result_array()) > 0)
		{
			$this->num_rows = count($this->result_array());
			@ociexecute($this->stmt_id);

			if ($this->curs_id)
			{
				@ociexecute($this->curs_id);
			}
		}

		return $this->num_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Number of fields in the result set
	 *
	 * @access  public
	 * @return  integer
	 */
	function num_fields()
	{
		$count = @ocinumcols($this->stmt_id);

		// if we used a limit we subtract it
		if ($this->limit_used)
		{
			$count = $count - 1;
		}

		return $count;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Field Names
	 *
	 * Generates an array of column names
	 *
	 * @access	public
	 * @return	array
	 */
	function list_fields()
	{
		$field_names = array();
		$fieldCount = $this->num_fields();
		for ($c = 1; $c <= $fieldCount; $c++)
		{
			$field_names[] = ocicolumnname($this->stmt_id, $c);
		}
		return $field_names;
	}

	// --------------------------------------------------------------------

	/**
	 * Field data
	 *
	 * Generates an array of objects containing field meta-data
	 *
	 * @access  public
	 * @return  array
	 */
	function field_data()
	{
		$retval = array();
		$fieldCount = $this->num_fields();
		for ($c = 1; $c <= $fieldCount; $c++)
		{
			$F				= new stdClass();
			$F->name		= ocicolumnname($this->stmt_id, $c);
			$F->type		= ocicolumntype($this->stmt_id, $c);
			$F->max_length  = ocicolumnsize($this->stmt_id, $c);

			$retval[] = $F;
		}

		return $retval;
	}

	// --------------------------------------------------------------------

	/**
	 * Free the result
	 *
	 * @return	null
	 */
	function free_result()
	{
		if (is_resource($this->result_id))
		{
			ocifreestatement($this->result_id);
			$this->result_id = FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Result - associative array
	 *
	 * Returns the result set as an array
	 *
	 * @access  private
	 * @return  array
	 */
	function _fetch_assoc(&$row)
	{
		$id = ($this->curs_id) ? $this->curs_id : $this->stmt_id;

		return ocifetchinto($id, $row, OCI_ASSOC + OCI_RETURN_NULLS);
	}

	// --------------------------------------------------------------------

	/**
	 * Result - object
	 *
	 * Returns the result set as an object
	 *
	 * @access  private
	 * @return  object
	 */
	function _fetch_object()
	{
		$result = array();

		// If PHP 5 is being used we can fetch an result object
		if (function_exists('oci_fetch_object'))
		{
			$id = ($this->curs_id) ? $this->curs_id : $this->stmt_id;

			return @oci_fetch_object($id);
		}

		// If PHP 4 is being used we have to build our own result
		foreach ($this->result_array() as $key => $val)
		{
			$obj = new stdClass();
			if (is_array($val))
			{
				foreach ($val as $k => $v)
				{
					$obj->$k = $v;
				}
			}
			else
			{
				$obj->$key = $val;
			}

			$result[] = $obj;
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Query result.  "array" version.
	 *
	 * @access  public
	 * @return  array
	 */
	function result_array()
	{
		if (count($this->result_array) > 0)
		{
			return $this->result_array;
		}

		// oracle's fetch functions do not return arrays.
		// The information is returned in reference parameters
		$row = NULL;
		while ($this->_fetch_assoc($row))
		{
			$this->result_array[] = $row;
		}

		return $this->result_array;
	}

	// --------------------------------------------------------------------

	/**
	 * Data Seek
	 *
	 * Moves the internal pointer to the desired offset.  We call
	 * this internally before fetching results to make sure the
	 * result set starts at zero
	 *
	 * @access	private
	 * @return	array
	 */
	function _data_seek($n = 0)
	{
		return FALSE; // Not needed
	}

}


/* End of file oci8_result.php */
/* Location: ./system/database/drivers/oci8/oci8_result.php */
