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
 * Code Igniter Security Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/security_helper.html
 */

// ------------------------------------------------------------------------

/**
 * XSS Filtering
 *
 * @access	public
 * @parm	string
 * @parm	string	the character set of your data
 * @return	string
 */	
function xss_clean($str, $charset = 'ISO-8859-1')
{
	$obj =& get_instance();
	return $obj->input->xss_clean($str, $charset);
}

// --------------------------------------------------------------------

/**
 * Hash encode a string
 *
 * @access	public
 * @param	string
 * @return	string
 */		
function hash($str, $type = 'sha1')
{
	if ($type == 'sha1')
	{
		if ( ! function_exists('sha1'))
		{
			if ( ! function_exists('mhash'))
			{	
				require_once(BASEPATH.'libraries/Sha1'.EXT);
				$SH = new CI_SHA;
				return $SH->generate($str);            
			}
			else
			{
				return bin2hex(mhash(MHASH_SHA1, $str));
			}
		}
		else
		{
			return sha1($str);
		}	
	}
	else
	{
		return md5($str);
	}
}
	
// ------------------------------------------------------------------------

/**
 * Strip Image Tags
 *
 * @access	public
 * @parm	string
 * @return	string
 */	
function strip_image_tags($str)
{    
	$str = preg_replace("#<img\s+.*?src\s*=\s*[\"'](.+?)[\"'].*?\>#", "\\1", $str);
	$str = preg_replace("#<img\s+.*?src\s*=\s*(.+?).*?\>#", "\\1", $str);
			
	return $str;
}
	
// ------------------------------------------------------------------------

/**
 * Convert PHP tags to entities
 *
 * @access	public
 * @parm	string
 * @return	string
 */	
function encode_php_tags($str)
{
	return str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
}

?>