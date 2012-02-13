<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * @copyright   Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

/**
 * oci8 Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage  Drivers
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */

/**
 * oci8 Database Adapter Class
 *
 * This is a modification of the DB_driver class to
 * permit access to oracle databases
 *
 * @author	  Kelly McArdle
 */

class CI_DB_oci8_driver extends CI_DB {

	public $dbdriver = 'oci8';

	// The character used for excaping
	protected $_escape_char = '"';

	// clause and character used for LIKE escape sequences
	protected $_like_escape_str = " escape '%s' ";
	protected $_like_escape_chr = '!';

	/**
	 * The syntax to count rows is slightly different across different
	 * database engines, so this string appears in each driver and is
	 * used for the count_all() and count_all_results() functions.
	 */
	protected $_count_string = 'SELECT COUNT(1) AS ';
	protected $_random_keyword = ' ASC'; // not currently supported

	// Set "auto commit" by default
	protected $_commit = OCI_COMMIT_ON_SUCCESS;

	// need to track statement id and cursor id
	public $stmt_id;
	public $curs_id;

	// if we use a limit, we will add a field that will
	// throw off num_fields later
	public $limit_used;

	public function __construct($params)
	{
		parent::__construct($params);

		$valid_dsns = array(
					'tns'	=> '/^\(DESCRIPTION=(\(.+\)){2,}\)$/', // TNS
					// Easy Connect string (Oracle 10g+)
					'ec'	=> '/^(\/\/)?[a-z0-9.:_-]+(:[1-9][0-9]{0,4})?(\/[a-z0-9$_]+)?(:[^\/])?(\/[a-z0-9$_]+)?$/i',
					'in'	=> '/^[a-z0-9$_]+$/i' // Instance name (defined in tnsnames.ora)
				);

		/* Space characters don't have any effect when actually
		 * connecting, but can be a hassle while validating the DSN.
		 */
		$this->dsn = str_replace(array("\n", "\r", "\t", ' '), '', $this->dsn);

		if ($this->dsn !== '')
		{
			foreach ($valid_dsns as $regexp)
			{
				if (preg_match($regexp, $this->dsn))
				{
					return;
				}
			}
		}

		// Legacy support for TNS in the hostname configuration field
		$this->hostname = str_replace(array("\n", "\r", "\t", ' '), '', $this->hostname);
		if (preg_match($valid_dsns['tns'], $this->hostname))
		{
			$this->dsn = $this->hostname;
			return;
		}
		elseif ($this->hostname !== '' && strpos($this->hostname, '/') === FALSE && strpos($this->hostname, ':') === FALSE
			&& (( ! empty($this->port) && ctype_digit($this->port)) OR $this->database !== ''))
		{
			/* If the hostname field isn't empty, doesn't contain
			 * ':' and/or '/' and if port and/or database aren't
			 * empty, then the hostname field is most likely indeed
			 * just a hostname. Therefore we'll try and build an
			 * Easy Connect string from these 3 settings, assuming
			 * that the database field is a service name.
			 */
			$this->dsn = $this->hostname
					.(( ! empty($this->port) && ctype_digit($this->port)) ? ':'.$this->port : '')
					.($this->database !== '' ? '/'.ltrim($this->database, '/') : '');

			if (preg_match($valid_dsns['ec'], $this->dsn))
			{
				return;
			}
		}

		/* At this point, we can only try and validate the hostname and
		 * database fields separately as DSNs.
		 */
		if (preg_match($valid_dsns['ec'], $this->hostname) OR preg_match($valid_dsns['in'], $this->hostname))
		{
			$this->dsn = $this->hostname;
			return;
		}

		$this->database = str_replace(array("\n", "\r", "\t", ' '), '', $this->database);
		foreach ($valid_dsns as $regexp)
		{
			if (preg_match($regexp, $this->database))
			{
				return;
			}
		}

		/* Well - OK, an empty string should work as well.
		 * PHP will try to use environment variables to
		 * determine which Oracle instance to connect to.
		 */
		$this->dsn = '';
	}

	/**
	 * Non-persistent database connection
	 *
	 * @return	resource
	 */
	public function db_connect()
	{
		return ( ! empty($this->char_set))
			? @oci_connect($this->username, $this->password, $this->dsn, $this->char_set)
			: @oci_connect($this->username, $this->password, $this->dsn);
	}

	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @return	resource
	 */
	public function db_pconnect()
	{
		return ( ! empty($this->char_set))
			? @oci_pconnect($this->username, $this->password, $this->dsn, $this->char_set)
			: @oci_pconnect($this->username, $this->password, $this->dsn);
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
		// not implemented in oracle
		return;
	}

	// --------------------------------------------------------------------

	/**
	 * Select the database
	 *
	 * @return	resource
	 */
	public function db_select()
	{
		// Not in Oracle - schemas are actually usernames
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set client character set
	 *
	 * @param	string
	 * @param	string
	 * @return	resource
	 */
	public function db_set_charset($charset, $collation)
	{
		// this is done upon connect
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Version number query string
	 *
	 * @return	string
	 */
	protected function _version()
	{
		return oci_server_version($this->conn_id);
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
		/* Oracle must parse the query before it is run. All of the actions with
		 * the query are based on the statement id returned by oci_parse().
		 */
		$this->stmt_id = FALSE;
		$this->_set_stmt_id($sql);
		oci_set_prefetch($this->stmt_id, 1000);
		return @oci_execute($this->stmt_id, $this->_commit);
	}

	/**
	 * Generate a statement ID
	 *
	 * @param	string	an SQL query
	 * @return	void
	 */
	private function _set_stmt_id($sql)
	{
		if ( ! is_resource($this->stmt_id))
		{
			$this->stmt_id = oci_parse($this->conn_id, $this->_prep_query($sql));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Prep the query
	 *
	 * If needed, each database adapter can prep the query string
	 *
	 * @param	string	an SQL query
	 * @return	string
	 */
	private function _prep_query($sql)
	{
		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * getCursor. Returns a cursor from the database
	 *
	 * @return	resource	cursor id
	 */
	public function get_cursor()
	{
		return $this->curs_id = oci_new_cursor($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Stored Procedure.  Executes a stored procedure
	 *
	 * @param	string	package name in which the stored procedure is in
	 * @param	string	stored procedure name to execute
	 * @param	array	parameters
	 * @return	mixed
	 *
	 * params array keys
	 *
	 * KEY	  OPTIONAL	NOTES
	 * name		no	the name of the parameter should be in :<param_name> format
	 * value	no	the value of the parameter.  If this is an OUT or IN OUT parameter,
	 *				this should be a reference to a variable
	 * type		yes	the type of the parameter
	 * length	yes	the max size of the parameter
	 */
	public function stored_procedure($package, $procedure, $params)
	{
		if ($package == '' OR $procedure == '' OR ! is_array($params))
		{
			if ($this->db_debug)
			{
				log_message('error', 'Invalid query: '.$package.'.'.$procedure);
				return $this->display_error('db_invalid_query');
			}
			return FALSE;
		}

		// build the query string
		$sql = 'BEGIN '.$package.'.'.$procedure.'(';

		$have_cursor = FALSE;
		foreach ($params as $param)
		{
			$sql .= $param['name'].',';

			if (isset($param['type']) && $param['type'] === OCI_B_CURSOR)
			{
				$have_cursor = TRUE;
			}
		}
		$sql = trim($sql, ',') . '); END;';

		$this->stmt_id = FALSE;
		$this->_set_stmt_id($sql);
		$this->_bind_params($params);
		return $this->query($sql, FALSE, $have_cursor);
	}

	// --------------------------------------------------------------------

	/**
	 * Bind parameters
	 *
	 * @return	void
	 */
	private function _bind_params($params)
	{
		if ( ! is_array($params) OR ! is_resource($this->stmt_id))
		{
			return;
		}

		foreach ($params as $param)
		{
			foreach (array('name', 'value', 'type', 'length') as $val)
			{
				if ( ! isset($param[$val]))
				{
					$param[$val] = '';
				}
			}

			oci_bind_by_name($this->stmt_id, $param['name'], $param['value'], $param['length'], $param['type']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return	bool
	 */
	public function trans_begin($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;

		$this->_commit = (is_php('5.3.2')) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
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
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		$this->_commit = OCI_COMMIT_ON_SUCCESS;
		return oci_commit($this->conn_id);
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

		$this->_commit = OCI_COMMIT_ON_SUCCESS;
		return oci_rollback($this->conn_id);
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

		$str = str_replace("'", "''", remove_invisible_characters($str));

		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
			return str_replace(array('%', '_', $this->_like_escape_chr),
						array($this->_like_escape_chr.'%', $this->_like_escape_chr.'_', $this->_like_escape_chr.$this->_like_escape_chr),
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
		return @oci_num_rows($this->stmt_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return	int
	 */
	public function insert_id()
	{
		// not supported in oracle
		return $this->display_error('db_unsupported_function');
	}

	// --------------------------------------------------------------------

	/**
	 * "Count All" query
	 *
	 * Generates a platform-specific query string that counts all records in
	 * the specified database
	 *
	 * @param	string
	 * @return	int
	 */
	public function count_all($table = '')
	{
		if ($table == '')
		{
			return 0;
		}

		$query = $this->query($this->_count_string.$this->_protect_identifiers('numrows').' FROM '.$this->_protect_identifiers($table, TRUE, NULL, FALSE));

		if ($query == FALSE)
		{
			return 0;
		}

		$row = $query->row();
		$this->_reset_select();
		return (int) $row->numrows;
	}

	// --------------------------------------------------------------------

	/**
	 * Show table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param	bool
	 * @return	string
	 */
	protected function _list_tables($prefix_limit = FALSE)
	{
		$sql = 'SELECT TABLE_NAME FROM ALL_TABLES';

		if ($prefix_limit !== FALSE && $this->dbprefix != '')
		{
			return $sql." WHERE TABLE_NAME LIKE '".$this->escape_like_str($this->dbprefix)."%' ".sprintf($this->_like_escape_str, $this->_like_escape_chr);
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
		return 'SELECT COLUMN_NAME FROM all_tab_columns WHERE table_name = \''.$table.'\'';
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
		return 'SELECT * FROM '.$table.' WHERE rownum = 1';
	}

	// --------------------------------------------------------------------

	/**
	 * The error message string
	 *
	 * @return	string
	 */
	protected function _error_message()
	{
		$error = self::_get_error_data();
		return $error['message'];
	}

	// --------------------------------------------------------------------

	/**
	 * The error message number
	 *
	 * @return	int
	 */
	protected function _error_number()
	{
		$error = self::_get_error_data();
		return $error['code'];
	}

	// --------------------------------------------------------------------

	/* Get error data
	 *
	 * Used by _error_message() and _error_number()
	 *
	 * @return	array
	 */
	private function _get_error_data()
	{
		$res = NULL;
		foreach (array('curs_id', 'stmt_id', 'conn_id') as $key)
		{
			if (is_resource($this->$key))
			{
				$res = $this->$key;
				break;
			}
		}

		return ( ! is_null($res)) ? oci_error($res) : oci_error();
	}

	// --------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param	string
	 * @return	string
	 */
	public function _escape_identifiers($item)
	{
		if ($this->_escape_char == '')
		{
			return $item;
		}

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
	 * This function implicitly groups FROM tables so there is no confusion
	 * about operator precedence in harmony with SQL standards
	 *
	 * @param	array
	 * @return	string
	 */
	protected function _from_tables($tables)
	{
		return is_array($tables) ? implode(', ', $tables) : $tables;
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
	 * Insert_batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @param	string  the table name
	 * @param	array   the insert keys
	 * @param 	array   the insert values
	 * @return 	string
	 */
	protected function _insert_batch($table, $keys, $values)
	{
		$keys = implode(', ', $keys);
		$sql = "INSERT ALL\n";

		for ($i = 0, $c = count($values); $i < $c; $i++)
		{
			$sql .= '	INTO '.$table.' ('.$keys.') VALUES '.$values[$i].'\n';
		}

		return $sql.'SELECT * FROM dual';
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

		return 'UPDATE '.$table.' SET '.implode(', ', $valstr)
			.(($where != '' && count($where) > 0) ? ' WHERE '.implode(' ', $where) : '')
			.(count($orderby) > 0 ? ' ORDER BY '.implode(', ', $orderby) : '')
			.( ! $limit ? '' : ' LIMIT '.$limit);
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 * If the database does not support the truncate() command
	 * This function maps to "DELETE FROM table"
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _truncate($table)
	{
		return 'TRUNCATE TABLE '.$table;
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
		$conditions = '';
		if (count($where) > 0 OR count($like) > 0)
		{
			$conditions = "\nWHERE ".implode("\n", $this->ar_where);

			if (count($where) > 0 && count($like) > 0)
			{
				$conditions .= ' AND ';
			}
			$conditions .= implode("\n", $like);
		}

		return 'DELETE FROM '.$table.$conditions.( ! $limit ? '' : ' LIMIT '.$limit);
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
		$this->limit_used = TRUE;
		return 'SELECT * FROM (SELECT inner_query.*, rownum rnum FROM ('.$sql.') inner_query WHERE rownum < '.($offset + $limit).')'
			.($offset != 0 ? ' WHERE rnum >= '.$offset : '');
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
		@oci_close($conn_id);
	}

}

/* End of file oci8_driver.php */
/* Location: ./system/database/drivers/oci8/oci8_driver.php */
