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
 * @since		Version 1.0
 * @filesource
 */

/**
 * Postgre Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the query builder
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_postgre_driver extends CI_DB {

	public $dbdriver = 'postgre';

	protected $_escape_char = '"';

	protected $_random_keyword = ' RANDOM()'; // database specific random keyword

	/**
	 * Database schema
	 *
	 * @var	string
	 */
	public $schema = 'public';

	/**
	 * Constructor
	 *
	 * Creates a DSN string to be used for db_connect() and db_pconnect()
	 *
	 * @param	array	$params
	 * @return	void
	 */
	public function __construct($params)
	{
		parent::__construct($params);

		if ( ! empty($this->dsn))
		{
			return;
		}

		$this->dsn === '' OR $this->dsn = '';

		if (strpos($this->hostname, '/') !== FALSE)
		{
			// If UNIX sockets are used, we shouldn't set a port
			$this->port = '';
		}

		$this->hostname === '' OR $this->dsn = 'host='.$this->hostname.' ';

		if ( ! empty($this->port) && ctype_digit($this->port))
		{
			$this->dsn .= 'port='.$this->port.' ';
		}

		if ($this->username !== '')
		{
			$this->dsn .= 'user='.$this->username.' ';

			/* An empty password is valid!
			 *
			 * $db['password'] = NULL must be done in order to ignore it.
			 */
			$this->password === NULL OR $this->dsn .= "password='".$this->password."' ";
		}

		$this->database === '' OR $this->dsn .= 'dbname='.$this->database.' ';

		/* We don't have these options as elements in our standard configuration
		 * array, but they might be set by parse_url() if the configuration was
		 * provided via string. Example:
		 *
		 * postgre://username:password@localhost:5432/database?connect_timeout=5&sslmode=1
		 */
		foreach (array('connect_timeout', 'options', 'sslmode', 'service') as $key)
		{
			if (isset($this->$key) && is_string($this->key) && $this->key !== '')
			{
				$this->dsn .= $key."='".$this->key."' ";
			}
		}

		$this->dsn = rtrim($this->dsn);
	}

	// --------------------------------------------------------------------

	/**
	 * Non-persistent database connection
	 *
	 * @return	resource
	 */
	public function db_connect()
	{
		return @pg_connect($this->dsn);
	}

	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @return	resource
	 */
	public function db_pconnect()
	{
		$conn = @pg_pconnect($this->dsn);
		if ($conn && pg_connection_status($conn) === PGSQL_CONNECTION_BAD)
		{
			if (pg_ping($conn) === FALSE)
			{
				return FALSE;
			}
		}
		return $conn;
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
		if (pg_ping($this->conn_id) === FALSE)
		{
			$this->conn_id = FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set client character set
	 *
	 * @param	string
	 * @return	bool
	 */
	protected function _db_set_charset($charset)
	{
		return (pg_set_client_encoding($this->conn_id, $charset) === 0);
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

		if (($pg_version = pg_version($this->conn_id)) === FALSE)
		{
			return FALSE;
		}

		/* If PHP was compiled with PostgreSQL lib versions earlier
		 * than 7.4, pg_version() won't return the server version
		 * and so we'll have to fall back to running a query in
		 * order to get it.
		 */
		return isset($pg_version['server'])
			? $this->data_cache['version'] = $pg_version['server']
			: parent::version();
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
		return @pg_query($this->conn_id, $sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @param	bool
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

		return (bool) @pg_query($this->conn_id, 'BEGIN');
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
		if ( ! $this->trans_enabled OR $this->_trans_depth > 0)
		{
			return TRUE;
		}

		return (bool) @pg_query($this->conn_id, 'COMMIT');
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

		return (bool) @pg_query($this->conn_id, 'ROLLBACK');
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

		$str = pg_escape_string($str);

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
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function escape($str)
	{
		if (is_bool($str))
		{
			return ($str) ? 'TRUE' : 'FALSE';
		}

		return parent::escape($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @return	int
	 */
	public function affected_rows()
	{
		return @pg_affected_rows($this->result_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return	string
	 */
	public function insert_id()
	{
		$v = pg_version($this->conn_id);
		$v = isset($v['server']) ? $v['server'] : 0; // 'server' key is only available since PosgreSQL 7.4

		$table	= (func_num_args() > 0) ? func_get_arg(0) : NULL;
		$column	= (func_num_args() > 1) ? func_get_arg(1) : NULL;

		if ($table === NULL && $v >= '8.1')
		{
			$sql = 'SELECT LASTVAL() AS ins_id';
		}
		elseif ($table !== NULL)
		{
			if ($column !== NULL && $v >= '8.0')
			{
				$sql = 'SELECT pg_get_serial_sequence(\''.$table."', '".$column."') AS seq";
				$query = $this->query($sql);
				$query = $query->row();
				$seq = $query->seq;
			}
			else
			{
				// seq_name passed in table parameter
				$seq = $table;
			}

			$sql = 'SELECT CURRVAL(\''.$seq."') AS ins_id";
		}
		else
		{
			return pg_last_oid($this->result_id);
		}

		$query = $this->query($sql);
		$query = $query->row();
		return (int) $query->ins_id;
	}

	// --------------------------------------------------------------------

	/**
	 * Show table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param	bool	$prefix_limit = FALSE
	 * @return	string
	 */
	protected function _list_tables($prefix_limit = FALSE)
	{
		$sql = 'SELECT "table_name" FROM "information_schema"."tables" WHERE "table_schema" = \''.$this->schema."'";

		if ($prefix_limit !== FALSE && $this->dbprefix !== '')
		{
			return $sql.' AND "table_name" LIKE \''
				.$this->escape_like_str($this->dbprefix)."%' "
				.sprintf($this->_like_escape_str, $this->_like_escape_chr);
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
		return 'SELECT "column_name" FROM "information_schema"."columns" WHERE "table_name" = '.$this->escape($table);
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
		return 'SELECT * FROM '.$table.' LIMIT 1';
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
		return array('code' => '', 'message' => pg_last_error($this->conn_id));
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the update data
	 * @return	string
	 */
	protected function _update($table, $values)
	{
		$this->qb_limit = FALSE;
		$this->qb_orderby = array();
		return parent::_update($table, $values);
	}

	// --------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	string	the where key
	 * @return	string
	 */
	protected function _update_batch($table, $values, $index)
	{
		$ids = array();
		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field !== $index)
				{
					$final[$field][] =  'WHEN '.$val[$index].' THEN '.$val[$field];
				}
			}
		}

		$cases = '';
		foreach ($final as $k => $v)
		{
			$cases .= $k.' = (CASE '.$index."\n"
				.implode("\n", $v)."\n"
				.'ELSE '.$k.' END), ';
		}

		$this->where($index.' IN('.implode(',', $ids).')', NULL, FALSE);

		return 'UPDATE '.$table.' SET '.substr($cases, 0, -2).$this->_compile_wh('qb_where');
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _delete($table)
	{
		$this->qb_limit = FALSE;
		return parent::_delete($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Limit string
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @param	string	the sql query string
	 * @return	string
	 */
	protected function _limit($sql)
	{
		return $sql.' LIMIT '.$this->qb_limit.($this->qb_offset ? ' OFFSET '.$this->qb_offset : '');
	}

	// --------------------------------------------------------------------

	/**
	 * WHERE, HAVING
	 *
	 * Called by where(), or_where(), having(), or_having()
	 *
	 * @param	string	'qb_where' or 'qb_having'
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	protected function _wh($qb_key, $key, $value = NULL, $type = 'AND ', $escape = NULL)
	{
		$qb_cache_key = ($qb_key === 'qb_having') ? 'qb_cache_having' : 'qb_cache_where';

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		// If the escape value was not set will will base it on the global setting
		is_bool($escape) OR $escape = $this->_protect_identifiers;

		foreach ($key as $k => $v)
		{
			$prefix = (count($this->$qb_key) === 0 && count($this->$qb_cache_key) === 0)
				? $this->_group_get_type('')
				: $this->_group_get_type($type);

			if (is_null($v) && ! $this->_has_operator($k))
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}

			if ( ! is_null($v))
			{
				if (is_bool($v))
				{
					$v = ' '.($v ? 'TRUE' : 'FALSE');
				}
				elseif ($escape === TRUE)
				{
					$v = ' '.(is_int($v) ? $v : $this->escape($v));
				}

				if ( ! $this->_has_operator($k))
				{
					$k .= ' = ';
				}
			}

			$this->{$qb_key}[] = array('condition' => $prefix.$k.$v, 'escape' => $escape);
			if ($this->qb_caching === TRUE)
			{
				$this->{$qb_cache_key}[] = array('condition' => $prefix.$k.$v, 'escape' => $escape);
				$this->qb_cache_exists[] = substr($qb_key, 3);
			}

		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @return	void
	 */
	protected function _close()
	{
		@pg_close($this->conn_id);
	}

}

/* End of file postgre_driver.php */
/* Location: ./system/database/drivers/postgre/postgre_driver.php */