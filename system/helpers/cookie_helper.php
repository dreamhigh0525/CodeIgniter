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
 * Code Igniter Cookie Helpers
 * 
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/cookie_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Set cookie
 *
 * Accepts six parameter, or you can submit an associative 
 * array in the first parameter containing all the values.
 *
 * @access	public
 * @param	mixed
 * @param	string	the value of the cookie
 * @param	string	the number of seconds until expiration
 * @param	string	the cookie domain.  Usually:  .yourdomain.com
 * @param	string	the cookie path
 * @param	string	the cookie prefix
 * @return	void
 */
function set_cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '')
{ 
	if (is_array($name))
	{		
		foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'name') as $item)
		{
			if (isset($name[$item]))
			{
				$$item = $name[$item];
			}
		}
	}
	
	// Set the config file options
	$obj =& get_instance();
	
	if ($prefix == '' AND $obj->config->item('cookie_prefix') != '')
	{
		$obj->config->item('cookie_prefix');
	}
	if ($domain == '' AND $obj->config->item('cookie_domain') != '')
	{
		$obj->config->item('cookie_domain');
	}
	if ($prefix == '/' AND $obj->config->item('cookie_path') != '/')
	{
		$obj->config->item('cookie_path');
	}
		
	if ( ! is_numeric($expire))
	{
		$expire = time() - 86500;
	}
	else
	{
		if ($expire > 0)
		{
			$expire = time() + $expire;
		}
		else
		{
			$expire = 0;
		}
	}
	
	setcookie($prefix.$name, $value, $expire, $path, $domain, 0);
}
	
// --------------------------------------------------------------------

/**
 * Fetch an item from the COOKIE array
 *
 * @access	public
 * @param	string
 * @param	bool
 * @return	mixed
 */
function get_cookie($index = '', $xss_clean = FALSE)
{
	$obj =& get_instance();
	return $obj->input->cookie($index, $xss_clean);
}

// --------------------------------------------------------------------

/**
 * Delete a COOKIE
 *
 * @param	mixed
 * @param	string	the cookie domain.  Usually:  .yourdomain.com
 * @param	string	the cookie path
 * @param	string	the cookie prefix
 * @return	void
 */
function delete_cookie($name = '', $domain = '', $path = '/', $prefix = '')
{
	set_cookie($name, '', '', $domain, $path, $prefix);
}


?>