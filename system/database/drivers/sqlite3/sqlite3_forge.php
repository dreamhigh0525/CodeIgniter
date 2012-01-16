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
 * bundled with this package in the files license.txt / license.rst. It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license	http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link	http://codeigniter.com
 * @since	Version 1.0
 * @filesource
 */

/**
 * SQLite3 Forge Class
 *
 * @category	Database
 * @author	Andrey Andreev
 * @link	http://codeigniter.com/user_guide/database/
 */
class CI_DB_sqlite3_forge extends CI_DB_forge {

	/**
	 * Create database
	 *
	 * @param	string	the database name
	 * @return	bool
	 */
	public function _create_database()
	{
		// In SQLite, a database is created when you connect to the database.
		// We'll return TRUE so that an error isn't generated
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param	string	the database name
	 * @return	bool
	 */
	public function _drop_database($name)
	{
		// In SQLite, a database is dropped when we delete a file
		if (@file_exists($this->db->database))
		{
			// We need to close the pseudo-connection first
			$this->db->close();
			if ( ! @unlink($this->db->database))
			{
				return $this->db->db_debug ? $this->db->display_error('db_unable_to_drop') : FALSE;
			}

			return TRUE;
		}

		return $this->db->db_debug ? $this->db->display_error('db_unable_to_drop') : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param	string	the table name
	 * @param	array	the fields
	 * @param	mixed	primary key(s)
	 * @param	mixed	key(s)
	 * @param	bool	should 'IF NOT EXISTS' be added to the SQL
	 * @return	bool
	 */
	public function _create_table($table, $fields, $primary_keys, $keys, $if_not_exists)
	{
		$sql = 'CREATE TABLE ';

		// IF NOT EXISTS added to SQLite in 3.3.0
		if ($if_not_exists === TRUE && version_compare($this->db->version(), '3.3.0', '>=') === TRUE)
		{
			$sql .= 'IF NOT EXISTS ';
		}

		$sql .= $this->db->_escape_identifiers($table).'(';
		$current_field_count = 0;

		foreach ($fields as $field=>$attributes)
		{
			// Numeric field names aren't allowed in databases, so if the key is
			// numeric, we know it was assigned by PHP and the developer manually
			// entered the field information, so we'll simply add it to the list
			if (is_numeric($field))
			{
				$sql .= "\n\t".$attributes;
			}
			else
			{
				$attributes = array_change_key_case($attributes, CASE_UPPER);

				$sql .= "\n\t".$this->db->_protect_identifiers($field)
					.' '.$attributes['TYPE']
					.(array_key_exists('CONSTRAINT', $attributes) ? '('.$attributes['CONSTRAINT'].')' : '')
					.((array_key_exists('UNSIGNED', $attributes) && $attributes['UNSIGNED'] === TRUE) ? ' UNSIGNED' : '')
					.(array_key_exists('DEFAULT', $attributes) ? ' DEFAULT \''.$attributes['DEFAULT'].'\'' : '')
					.((array_key_exists('NULL', $attributes) && $attributes['NULL'] === TRUE) ? ' NULL' : ' NOT NULL')
					.((array_key_exists('AUTO_INCREMENT', $attributes) && $attributes['AUTO_INCREMENT'] === TRUE) ? ' AUTO_INCREMENT' : '');
			}

			// don't add a comma on the end of the last field
			if (++$current_field_count < count($fields))
			{
				$sql .= ',';
			}
		}

		if (count($primary_keys) > 0)
		{
			$primary_keys = $this->db->_protect_identifiers($primary_keys);
			$sql .= ",\n\tPRIMARY KEY (".implode(', ', $primary_keys).')';
		}

		if (is_array($keys) && count($keys) > 0)
		{
			foreach ($keys as $key)
			{
				if (is_array($key))
				{
					$key = $this->db->_protect_identifiers($key);
				}
				else
				{
					$key = array($this->db->_protect_identifiers($key));
				}

				$sql .= ",\n\tUNIQUE (".implode(', ', $key).')';
			}
		}

		return $sql."\n)";
	}

	// --------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	public function _drop_table($table)
	{
		return 'DROP TABLE '.$table.' IF EXISTS';
	}

	// --------------------------------------------------------------------

	/**
	 * Alter table query
	 *
	 * Generates a platform-specific query so that a table can be altered
	 * Called by add_column(), drop_column(), and column_alter(),
	 *
	 * @param	string	the ALTER type (ADD, DROP, CHANGE)
	 * @param	string	the column name
	 * @param	string	the table name
	 * @param	string	the column definition
	 * @param	string	the default value
	 * @param	bool	should 'NOT NULL' be added
	 * @param	string	the field after which we should add the new field
	 * @return	string
	 */
	public function _alter_table($alter_type, $table, $column_name, $column_definition = '', $default_value = '', $null = '', $after_field = '')
	{
		/* SQLite only supports adding new columns and it does
		 * NOT support the AFTER statement. Each new column will
		 * be added as the last one in the table.
		 */
		if ($alter_type !== 'ADD COLUMN')
		{
			// Not supported
			return FALSE;
		}

		return 'ALTER TABLE '.$this->db->_protect_identifiers($table).' '.$alter_type.' '.$this->db->_protect_identifiers($column_name)
			.' '.$column_definition
			.($default_value != '' ? ' DEFAULT '.$default_value : '')
			// If NOT NULL is specified, the field must have a DEFAULT value other than NULL
			.(($null !== NULL && $default_value !== 'NULL') ? ' NOT NULL' : ' NULL');
	}

	// --------------------------------------------------------------------

	/**
	 * Rename a table
	 *
	 * Generates a platform-specific query so that a table can be renamed
	 *
	 * @param	string	the old table name
	 * @param	string	the new table name
	 * @return	string
	 */
	public function _rename_table($table_name, $new_table_name)
	{
		return 'ALTER TABLE '.$this->db->_protect_identifiers($table_name).' RENAME TO '.$this->db->_protect_identifiers($new_table_name);
	}
}

/* End of file sqlite3_forge.php */
/* Location: ./system/database/drivers/sqlite3/sqlite3_forge.php */
