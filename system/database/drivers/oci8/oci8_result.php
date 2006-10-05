<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package     CodeIgniter
 * @author      Rick Ellis
 * @copyright   Copyright (c) 2006, pMachine, Inc.
 * @license     http://www.codeignitor.com/user_guide/license.html 
 * @link        http://www.codeigniter.com
 * @since       Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * oci8 Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category    Database
 * @author      Rick Ellis
 * @link        http://www.codeigniter.com/user_guide/database/
 */
class CI_DB_oci8_result extends CI_DB_result {

    var $stmt_id;
    var $curs_id;
    var $limit_used;

    /**
     * Number of rows in the result set
     *
     * @access  public
     * @return  integer
     */
    function num_rows()
    {
        // get the results, count them,
        // rerun query - otherwise we
        // won't have data after calling 
        // num_rows()
        $this->result_array();
        $rowcount = count($this->result_array);
        @ociexecute($this->stmt_id);
        if ($this->curs_id)
		{
			@ociexecute($this->curs_id);
		}
        return $rowcount;
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

        // if we used a limit, we added a field,
        // subtract it out
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
	function field_names()
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
            $F              = new stdClass();
            $F->name        = ocicolumnname($this->stmt_id, $c);
            $F->type        = ocicolumntype($this->stmt_id, $c);
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
        	OCIFreeStatement($this->result_id);
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
        // if pulling from a cursor, use curs_id
        if ($this->curs_id)
		{
			return ocifetchinto($this->curs_id, $row, OCI_ASSOC + OCI_RETURN_NULLS);
		}
		else
		{
			return ocifetchinto($this->stmt_id, $row, OCI_ASSOC + OCI_RETURN_NULLS);
		}
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
		return FALSE;
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
        // the PHP 4 version of the oracle functions do not
        // have a fetch method so we call the array version
        // and build an object from that

        $row = array();
        $res = $this->_fetch_assoc($row);
        if ($res != FALSE)
		{
			$obj = new stdClass();
			foreach ($row as $key => $value)
			{
				$obj->{$key} = $value;
			}
			
			$res = $obj;
		}
        return $res;
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

        // oracle's fetch functions do not
        // return arrays, the information
        // is returned in reference parameters
        //
        $row = NULL;
        while ($this->_fetch_assoc($row))
        {
            $this->result_array[] = $row;
        }

        if (count($this->result_array) == 0)
        {
            return FALSE;
        }

        return $this->result_array;
    }

}

?>