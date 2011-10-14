###########
HTML Helper
###########

The HTML Helper file contains functions that assist in working with
HTML.

.. contents:: Page Contents

Loading this Helper
===================

This helper is loaded using the following code::

	$this->load->helper('html');

The following functions are available:

br()
====

Generates line break tags (<br />) based on the number you submit.
Example::

	echo br(3);

The above would produce: <br /><br /><br />

heading()
=========

Lets you create HTML <h1> tags. The first parameter will contain the
data, the second the size of the heading. Example::

	echo heading('Welcome!', 3);

The above would produce: <h3>Welcome!</h3>

Additionally, in order to add attributes to the heading tag such as HTML
classes, ids or inline styles, a third parameter is available.

::

	echo heading('Welcome!', 3, 'class="pink"')

The above code produces: <h3 class="pink">Welcome!<<h3>

img()
=====

Lets you create HTML <img /> tags. The first parameter contains the
image source. Example

::

	echo img('images/picture.jpg'); // gives <img src="http://site.com/images/picture.jpg" />

There is an optional second parameter that is a TRUE/FALSE value that
specifics if the src should have the page specified by
$config['index_page'] added to the address it creates. Presumably, this
would be if you were using a media controller.

::

	echo img('images/picture.jpg', TRUE); // gives <img src="http://site.com/index.php/images/picture.jpg" alt="" />


Additionally, an associative array can be passed to the img() function
for complete control over all attributes and values. If an alt attribute
is not provided, CodeIgniter will generate an empty string.

::

	$image_properties = array(               
		'src' 	=> 'images/picture.jpg',               
		'alt' 	=> 'Me, demonstrating how to eat 4 slices of pizza at one time',  
		'class' => 'post_images',               
		'width' => '200',               
		'height'=> '200',               
		'title' => 'That was quite a night',               
		'rel' 	=> 'lightbox'
	);

		img($image_properties);     // <img src="http://site.com/index.php/images/picture.jpg" alt="Me, demonstrating how to eat 4 slices of pizza at one time" class="post_images" width="200" height="200" title="That was quite a night" rel="lightbox" />


link_tag()
===========

Lets you create HTML <link /> tags. This is useful for stylesheet links,
as well as other links. The parameters are href, with optional rel,
type, title, media and index_page. index_page is a TRUE/FALSE value
that specifics if the href should have the page specified by
$config['index_page'] added to the address it creates.

::

	 echo link_tag('css/mystyles.css'); // gives <link href="http://site.com/css/mystyles.css" rel="stylesheet" type="text/css" />


Further examples

::

	echo link_tag('favicon.ico', 'shortcut icon', 'image/ico');     // <link href="http://site.com/favicon.ico" rel="shortcut icon" type="image/ico" />

	echo link_tag('feed', 'alternate', 'application/rss+xml', 'My RSS Feed');     // <link href="http://site.com/feed" rel="alternate" type="application/rss+xml" title="My RSS Feed" />

Additionally, an associative array can be passed to the link() function
for complete control over all attributes and values.

::

	$link = array(               
		'href' 	=> 'css/printer.css',               
		'rel' 	=> 'stylesheet',               
		'type' 	=> 'text/css',               
		'media' => 'print'
	);

	echo link_tag($link);     // <link href="http://site.com/css/printer.css" rel="stylesheet" type="text/css" media="print" />


nbs()
=====

Generates non-breaking spaces (&nbsp;) based on the number you submit.
Example

::

	echo nbs(3);

The above would produce

::

	&nbsp;&nbsp;&nbsp;

ol() and ul()
=============

Permits you to generate ordered or unordered HTML lists from simple or
multi-dimensional arrays. Example

::

	$this->load->helper('html');

	$list = array(             
		'red',              
		'blue',              
		'green',             
		'yellow'             
	);

	$attributes = array(                     
		'class' => 'boldlist',                     
		'id'    => 'mylist'                    
	);

	echo ul($list, $attributes);

The above code will produce this

::

	 <ul class="boldlist" id="mylist">   
		<li>red</li>   
		<li>blue</li>   
		<li>green</li>   
		<li>yellow</li>
	</ul>

Here is a more complex example, using a multi-dimensional array

::

	$this->load->helper('html');

	$attributes = array(                     
		'class' => 'boldlist',                     
		'id'    => 'mylist'                     
	);

	$list = array(             
		'colors'  => array(                                 
			'red',                                 
			'blue',                                 
			'green'                             
		),
		'shapes'  => array(                                 
			'round',                                  
			'square',                                 
			'circles' => array(                                             
				'ellipse',
				'oval',
				'sphere'
			)                             
		),             
		'moods'  => array(                                 
			'happy',                                  
			'upset' => array( 	                                       
				'defeated' => array(
					'dejected',                
					'disheartened',
					'depressed'
				),
				'annoyed',
				'cross',
				'angry'
			)
		)
	);

	echo ul($list, $attributes);

The above code will produce this

::

	<ul class="boldlist" id="mylist">   
		<li>colors     
			<ul>       
				<li>red</li>       
				<li>blue</li>       
				<li>green</li>     
			</ul>   
		</li>   
		<li>shapes     
			<ul>       
				<li>round</li>       
				<li>suare</li>       
				<li>circles         
					<ul>           
						<li>elipse</li>           
						<li>oval</li>           
						<li>sphere</li>         
					</ul>       
				</li>     
			</ul>   
		</li>   
		<li>moods     
			<ul>       
				<li>happy</li>       
				<li>upset         
					<ul>           
						<li>defeated             
							<ul>               
								<li>dejected</li>
								<li>disheartened</li>
								<li>depressed</li>
							</ul>
						</li>
						<li>annoyed</li>
						<li>cross</li>           
						<li>angry</li>         
					</ul>       
				</li>     
			</ul>   
		</li>
	</ul>

meta()
======

Helps you generate meta tags. You can pass strings to the function, or
simple arrays, or multidimensional ones. Examples

::

	echo meta('description', 'My Great site');
	// Generates:  <meta name="description" content="My Great Site" />

	echo meta('Content-type', 'text/html; charset=utf-8', 'equiv');
	// Note the third parameter.  Can be "equiv" or "name"
	// Generates:  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

	echo meta(array('name' => 'robots', 'content' => 'no-cache'));
	// Generates:  <meta name="robots" content="no-cache" />

	$meta = array(         
		array(
			'name' => 'robots',
			'content' => 'no-cache'
		),
		array(
			'name' => 'description',
			'content' => 'My Great Site'
		),
		array(
			'name' => 'keywords',
			'content' => 'love, passion, intrigue, deception'
		),         
		array(
			'name' => 'robots',
			'content' => 'no-cache'
		),
		array(
			'name' => 'Content-type',
			'content' => 'text/html; charset=utf-8', 'type' => 'equiv'
		)
	);

	echo meta($meta);
	// Generates:
	// <meta name="robots" content="no-cache" />
	// <meta name="description" content="My Great Site" />
	// <meta name="keywords" content="love, passion, intrigue, deception" />
	// <meta name="robots" content="no-cache" />
	// <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

doctype()
=========

Helps you generate document type declarations, or DTD's. XHTML 1.0
Strict is used by default, but many doctypes are available.

::

	echo doctype(); // <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

	echo doctype('html4-trans'); // <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

The following is a list of doctype choices. These are configurable, and
pulled from application/config/doctypes.php

+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| Doctype                | Option                   | Result                                                                                                                    |
+========================+==========================+===========================================================================================================================+
| XHTML 1.1              | doctype('xhtml11')       | <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">                         |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| XHTML 1.0 Strict       | doctype('xhtml1-strict') | <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">             |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| XHTML 1.0 Transitional | doctype('xhtml1-trans')  | <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| XHTML 1.0 Frameset     | doctype('xhtml1-frame')  | <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">         |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| XHTML Basic 1.1        | doctype('xhtml-basic11') | <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">             |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| HTML 5                 | doctype('html5')         | <!DOCTYPE html>                                                                                                           |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| HTML 4 Strict          | doctype('html4-strict')  | <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">                                |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| HTML 4 Transitional    | doctype('html4-trans')   | <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">                    |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
| HTML 4 Frameset        | doctype('html4-frame')   | <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">                     |
+------------------------+--------------------------+---------------------------------------------------------------------------------------------------------------------------+
