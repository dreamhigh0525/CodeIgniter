<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
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
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 3.0
 * @filesource
 */

/**
 * Firebird/Interbase Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_interbase_driver extends CI_DB {

	public $dbdriver = 'interbase';

	// The character used to escape with
	protected $_escape_char = '"';

	// clause and character used for LIKE escape sequences
	protected $_like_escape_str = " ESCAPE '%s' ";
	protected $_like_escape_chr = '!';

	/**
	 * The syntax to count rows is slightly different across different
	 * database engines, so this string appears in each driver and is
	 * used for the count_all() and count_all_results() functions.
	 */
	protected $_count_string	= 'SELECT COUNT(*) AS ';
	protected $_random_keyword	= ' Random()'; // database specific random keyword

	// Keeps track of the resource for the current transaction
	protected $trans;

	/**
	 * Non-persistent database connection
	 *
	 * @return	resource
	 */
	public function db_connect()
	{
		return @ibase_connect($this->hostname.':'.$this->database, $this->username, $this->password, $this->char_set);
	}

	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @return	resource
	 */
	public function db_pconnect()
	{
		return @ibase_pconnect($this->hostname.':'.$this->database, $this->username, $this->password, $this->char_set);
	}

	// --------------------------------------------------------------------

	/**
	 * Reconnect
	 *
	 * Keep / reestablish the db connection if no queries have been
	 * sent for a length of time exceeding the server's idle timeout
	 *
	 * @return	void
	 */
	public function reconnect()
	{
		// not implemented in Interbase/Firebird
	}

	// --------------------------------------------------------------------

	/**
	 * Select the database
	 *
	 * @return	bool
	 */
	public function db_select()
	{
		// Connection selects the database
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Database version number
	 *
	 * @return	string
	 */
	public function version()
	{
		if (isset($this->data_cache['version']))
		{
			return $this->data_cache['version'];
		}

		if (($service = ibase_service_attach($this->hostname, $this->username, $this->password)))
		{
			$this->data_cache['version'] = ibase_server_info($service, IBASE_SVC_SERVER_VERSION);

			// Don't keep the service open
			ibase_service_detach($service);
			return $this->data_cache['version'];
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Execute the query
	 *
	 * @param	string	an SQL query
	 * @return	resource
	 */
	protected function _execute($sql)
	{
		return @ibase_query($this->conn_id, $sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return	bool
	 */
	public function trans_begin($test_mode = FALSE)
	{
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( ! $this->trans_enabled OR $this->_trans_depth > 0)
		{
			return TRUE;
		}

		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->_trans_failure = ($test_mode === TRUE);

		$this->trans = @ibase_trans($this->conn_id);

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return	bool
	 */
	public function trans_commit()
	{
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( ! $this->trans_enabled OR $this->_trans->depth > 0)
		{
			return TRUE;
		}

		return @ibase_commit($this->trans);
	}

	// --------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return	bool
	 */
	public function trans_rollback()
	{
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( ! $this->trans_enabled OR $this->_trans_depth > 0)
		{
			return TRUE;
		}

		return @ibase_rollback($this->trans);
	}

	// --------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @param	string
	 * @param	bool	whether or not the string will be used in a LIKE condition
	 * @return	string
	 */
	public function escape_str($str, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
			return str_replace(array($this->_like_escape_chr, '%', '_'),
						array($this->_like_escape_chr.$this->_like_escape_chr, $this->_like_escape_chr.'%', $this->_like_escape_chr.'_'),
						$str);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @return	int
	 */
	public function affected_rows()
	{
		return @ibase_affected_rows($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @param	string	$generator_name
	 * @param	int	$inc_by
	 * @return	int
	 */
	public function insert_id($generator_name, $inc_by=0)
	{
		//If a generator hasn't been used before it will return 0
		return ibase_gen_id('"'.$generator_name.'"', $inc_by);
	}

	// --------------------------------------------------------------------

	/**
	 * "Count All" query
	 *
	 * Generates a platform-specific query string that counts all records in
	 * the specified database
	 *
	 * @param	string
	 * @return	string
	 */
	public function count_all($table = '')
	{
		if ($table == '')
		{
			return 0;
		}

		$query = $this->query($this->_count_string.$this->protect_identifiers('numrows').' FROM '.$this->protect_identifiers($table, TRUE, NULL, FALSE));
		if ($query->num_rows() == 0)
		{
			return 0;
		}

		$query = $query->row();
		$this->_reset_select();
		return (int) $query->numrows;
	}

	// --------------------------------------------------------------------

	/**
	 * List table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param	bool
	 * @return	string
	 */
	protected function _list_tables($prefix_limit = FALSE)
	{
		$sql = 'SELECT "RDB$RELATION_NAME" FROM "RDB\$RELATIONS" WHERE "RDB$RELATION_NAME" NOT LIKE \'RDB$%\' AND "RDB$RELATION_NAME" NOT LIKE \'MON$%\'';

		if ($prefix_limit !== FALSE && $this->dbprefix != '')
		{
			return $sql.' AND "RDB$RELATION_NAME" LIKE \''.$this->escape_like_str($this->dbprefix)."%' ".sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Show column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _list_columns($table = '')
	{
		return 'SELECT "RDB$FIELD_NAME" FROM "RDB$RELATION_FIELDS" WHERE "RDB$RELATION_NAME" = \''.$this->escape_str($table)."'";
	}

	// --------------------------------------------------------------------

	/**
	 * Field data query
	 *
	 * Generates a platform-specific query so that the column data can be retrieved
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _field_data($table)
	{
		// Need to find a more efficient way to do this
		// but Interbase/Firebird seems to lack the
		// limit clause
		return 'SELECT * FROM '.$table;
	}

	// --------------------------------------------------------------------

	/**
	 * Error
	 *
	 * Returns an array containing code and message of the last
	 * database error that has occured.
	 *
	 * @return	array
	 */
	public function error()
	{
		return array('code' => ibase_errcode(), 'message' => ibase_errmsg());
	}

	// --------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This public function escapes column and table names
	 *
	 * @param	string
	 * @return	string
	 */
	protected function _escape_identifiers($item)
	{
		foreach ($this->_reserved_identifiers as $id)
		{
			if (strpos($item, '.'.$id) !== FALSE)
			{
				$item = str_replace('.', $this->_escape_char.'.', $item);

				// remove duplicates if the user already included the escape
				return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $this->_escape_char.$item);
			}
		}

		if (strpos($item, '.') !== FALSE)
		{
			$item = str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item);
		}

		// remove duplicates if the user already included the escape
		return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $this->_escape_char.$item.$this->_escape_char);
	}

	// --------------------------------------------------------------------

	/**
	 * From Tables
	 *
	 * This public function implicitly groups FROM tables so there is no confusion
	 * about operator precedence in harmony with SQL standards
	 *
	 * @param	array
	 * @return	string
	 */
	protected function _from_tables($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = array($tables);
		}

		//Interbase/Firebird doesn't like grouped tables
		return implode(', ', $tables);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	protected function _insert($table, $keys, $values)
	{
		return 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @param	array	the orderby clause
	 * @param	array	the limit clause
	 * @return	string
	 */
	protected function _update($table, $values, $where, $orderby = array(), $limit = FALSE)
	{
		foreach ($values as $key => $val)
		{
			$valstr[] = $key.' = '.$val;
		}

		//$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		return 'UPDATE '.$table.' SET '.implode(', ', $valstr)
			.(($where != '' && count($where) > 0) ? ' WHERE '.implode(' ', $where) : '')
			.(count($orderby) > 0 ? ' ORDER BY '.implode(', ', $orderby) : '');
	}


	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 * If the database does not support the truncate() command
	 * This public function maps to "DELETE FROM table"
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _truncate($table)
	{
		return $this->_delete($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the where clause
	 * @param	string	the limit clause
	 * @return	string
	 */
	protected function _delete($table, $where = array(), $like = array(), $limit = FALSE)
	{
		if (count($where) > 0 OR count($like) > 0)
		{
			$conditions = "\nWHERE ".implode("\n", $where)
					.((count($where) > 0 && count($like) > 0) ? ' AND ' : '')
					.implode("\n", $like);
		}
		else
		{
			$conditions = '';
		}

		//$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		return 'DELETE FROM '.$table.' '.$conditions;
	}

	// --------------------------------------------------------------------

	/**
	 * Limit string
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @param	string	the sql query string
	 * @param	int	the number of rows to limit the query to
	 * @param	int	the offset value
	 * @return	string
	 */
	protected function _limit($sql, $limit, $offset)
	{
		// Limit clause depends on if Interbase or Firebird
		if (stripos($this->version(), 'firebird') !== FALSE)
		{
			$select = 'FIRST '. (int) $limit
				.($offset > 0 ? ' SKIP '. (int) $offset : '');
		}
		else
		{
			$select = 'ROWS '
				.($offset > 0 ? (int) $offset.' TO '.($limit + $offset) : (int) $limit);
		}

		return preg_replace('`SELECT`i', 'SELECT '.$select, $sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @param	resource
	 * @return	void
	 */
	protected function _close($conn_id)
	{
		@ibase_close($conn_id);
	}

}

/* End of file interbase_driver.php */
/* Location: ./system/database/drivers/interbase/interbase_driver.php */