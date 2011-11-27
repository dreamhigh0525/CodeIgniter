##################################
XML-RPC and XML-RPC Server Classes
##################################

CodeIgniter's XML-RPC classes permit you to send requests to another
server, or set up your own XML-RPC server to receive requests.

****************
What is XML-RPC?
****************

Quite simply it is a way for two computers to communicate over the
internet using XML. One computer, which we will call the client, sends
an XML-RPC **request** to another computer, which we will call the
server. Once the server receives and processes the request it will send
back a **response** to the client.

For example, using the MetaWeblog API, an XML-RPC Client (usually a
desktop publishing tool) will send a request to an XML-RPC Server
running on your site. This request might be a new weblog entry being
sent for publication, or it could be a request for an existing entry for
editing. When the XML-RPC Server receives this request it will examine
it to determine which class/method should be called to process the
request. Once processed, the server will then send back a response
message.

For detailed specifications, you can visit the
`XML-RPC <http://www.xmlrpc.com/>`_ site.

Initializing the Class
======================

Like most other classes in CodeIgniter, the XML-RPC and XML-RPCS classes
are initialized in your controller using the $this->load->library
function:

To load the XML-RPC class you will use::

	$this->load->library('xmlrpc');

Once loaded, the xml-rpc library object will be available using:
$this->xmlrpc

To load the XML-RPC Server class you will use::

	$this->load->library('xmlrpc');
	$this->load->library('xmlrpcs');

Once loaded, the xml-rpcs library object will be available using:
$this->xmlrpcs

.. note:: When using the XML-RPC Server class you must load BOTH the
	XML-RPC class and the XML-RPC Server class.

Sending XML-RPC Requests
========================

To send a request to an XML-RPC server you must specify the following
information:

-  The URL of the server
-  The method on the server you wish to call
-  The *request* data (explained below).

Here is a basic example that sends a simple Weblogs.com ping to the
`Ping-o-Matic <http://pingomatic.com/>`_

::

	$this->load->library('xmlrpc');

	$this->xmlrpc->server('http://rpc.pingomatic.com/', 80);
	$this->xmlrpc->method('weblogUpdates.ping');

	$request = array('My Photoblog', 'http://www.my-site.com/photoblog/');
	$this->xmlrpc->request($request);

	if ( ! $this->xmlrpc->send_request())
	{
	    echo $this->xmlrpc->display_error();
	}

Explanation
-----------

The above code initializes the XML-RPC class, sets the server URL and
method to be called (weblogUpdates.ping). The request (in this case, the
title and URL of your site) is placed into an array for transportation,
and compiled using the request() function. Lastly, the full request is
sent. If the send_request() method returns false we will display the
error message sent back from the XML-RPC Server.

Anatomy of a Request
====================

An XML-RPC request is simply the data you are sending to the XML-RPC
server. Each piece of data in a request is referred to as a request
parameter. The above example has two parameters: The URL and title of
your site. When the XML-RPC server receives your request, it will look
for parameters it requires.

Request parameters must be placed into an array for transportation, and
each parameter can be one of seven data types (strings, numbers, dates,
etc.). If your parameters are something other than strings you will have
to include the data type in the request array.

Here is an example of a simple array with three parameters::

	$request = array('John', 'Doe', 'www.some-site.com');
	$this->xmlrpc->request($request);

If you use data types other than strings, or if you have several
different data types, you will place each parameter into its own array,
with the data type in the second position::

	$request = array (
	                   array('John', 'string'),
	                   array('Doe', 'string'),
	                   array(FALSE, 'boolean'),
	                   array(12345, 'int')
	                 ); 
	$this->xmlrpc->request($request);

The `Data Types <#datatypes>`_ section below has a full list of data
types.
Creating an XML-RPC Server
==========================

An XML-RPC Server acts as a traffic cop of sorts, waiting for incoming
requests and redirecting them to the appropriate functions for
processing.

To create your own XML-RPC server involves initializing the XML-RPC
Server class in your controller where you expect the incoming request to
appear, then setting up an array with mapping instructions so that
incoming requests can be sent to the appropriate class and method for
processing.

Here is an example to illustrate::

	$this->load->library('xmlrpc');
	$this->load->library('xmlrpcs');

	$config['functions']['new_post'] = array('function' => 'My_blog.new_entry'),
	$config['functions']['update_post'] = array('function' => 'My_blog.update_entry');
	$config['object'] = $this;

	$this->xmlrpcs->initialize($config);
	$this->xmlrpcs->serve();

The above example contains an array specifying two method requests that
the Server allows. The allowed methods are on the left side of the
array. When either of those are received, they will be mapped to the
class and method on the right.

The 'object' key is a special key that you pass an instantiated class
object with, which is necessary when the method you are mapping to is
not part of the CodeIgniter super object.

In other words, if an XML-RPC Client sends a request for the new_post
method, your server will load the My_blog class and call the new_entry
function. If the request is for the update_post method, your server
will load the My_blog class and call the update_entry function.

The function names in the above example are arbitrary. You'll decide
what they should be called on your server, or if you are using
standardized APIs, like the Blogger or MetaWeblog API, you'll use their
function names.

There are two additional configuration keys you may make use of when
initializing the server class: debug can be set to TRUE in order to
enable debugging, and xss_clean may be set to FALSE to prevent sending
data through the Security library's xss_clean function.

Processing Server Requests
==========================

When the XML-RPC Server receives a request and loads the class/method
for processing, it will pass an object to that method containing the
data sent by the client.

Using the above example, if the new_post method is requested, the
server will expect a class to exist with this prototype::

	class My_blog extends CI_Controller {

	    function new_post($request)
	    {

	    }
	}

The $request variable is an object compiled by the Server, which
contains the data sent by the XML-RPC Client. Using this object you will
have access to the *request parameters* enabling you to process the
request. When you are done you will send a Response back to the Client.

Below is a real-world example, using the Blogger API. One of the methods
in the Blogger API is getUserInfo(). Using this method, an XML-RPC
Client can send the Server a username and password, in return the Server
sends back information about that particular user (nickname, user ID,
email address, etc.). Here is how the processing function might look::

	class My_blog extends CI_Controller {

	    function getUserInfo($request)
	    {
	        $username = 'smitty';
	        $password = 'secretsmittypass';

	        $this->load->library('xmlrpc');

	        $parameters = $request->output_parameters();

	        if ($parameters['1'] != $username AND $parameters['2'] != $password)
	        {
	            return $this->xmlrpc->send_error_message('100', 'Invalid Access');
	        }

	        $response = array(array('nickname'  => array('Smitty','string'),
	                                'userid'    => array('99','string'),
	                                'url'       => array('http://yoursite.com','string'),
	                                'email'     => array('jsmith@yoursite.com','string'),
	                                'lastname'  => array('Smith','string'),
	                                'firstname' => array('John','string')
	                                ),
	                         'struct');

	        return $this->xmlrpc->send_response($response);
	    }
	}

Notes:
------

The output_parameters() function retrieves an indexed array
corresponding to the request parameters sent by the client. In the above
example, the output parameters will be the username and password.

If the username and password sent by the client were not valid, and
error message is returned using send_error_message().

If the operation was successful, the client will be sent back a response
array containing the user's info.

Formatting a Response
=====================

Similar to *Requests*, *Responses* must be formatted as an array.
However, unlike requests, a response is an array **that contains a
single item**. This item can be an array with several additional arrays,
but there can be only one primary array index. In other words, the basic
prototype is this::

	$response = array('Response data',  'array');

Responses, however, usually contain multiple pieces of information. In
order to accomplish this we must put the response into its own array so
that the primary array continues to contain a single piece of data.
Here's an example showing how this might be accomplished::

	$response = array (
	                   array(
	                         'first_name' => array('John', 'string'),
	                         'last_name' => array('Doe', 'string'),
	                         'member_id' => array(123435, 'int'),
	                         'todo_list' => array(array('clean house', 'call mom', 'water plants'), 'array'),
	                        ),
	                 'struct'
	                 );

Notice that the above array is formatted as a struct. This is the most
common data type for responses.

As with Requests, a response can be one of the seven data types listed
in the `Data Types <#datatypes>`_ section.

Sending an Error Response
=========================

If you need to send the client an error response you will use the
following::

	return $this->xmlrpc->send_error_message('123', 'Requested data not available');

The first parameter is the error number while the second parameter is
the error message.

Creating Your Own Client and Server
===================================

To help you understand everything we've covered thus far, let's create a
couple controllers that act as XML-RPC Client and Server. You'll use the
Client to send a request to the Server and receive a response.

The Client
----------

Using a text editor, create a controller called xmlrpc_client.php. In
it, place this code and save it to your applications/controllers/
folder::

	<?php

	class Xmlrpc_client extends CI_Controller {

		function index()
		{
			$this->load->helper('url');
			$server_url = site_url('xmlrpc_server');

			$this->load->library('xmlrpc');

			$this->xmlrpc->server($server_url, 80);
			$this->xmlrpc->method('Greetings');

			$request = array('How is it going?');
			$this->xmlrpc->request($request);

			if ( ! $this->xmlrpc->send_request())
			{
				echo $this->xmlrpc->display_error();
			}
			else
			{
				echo '<pre>';
				print_r($this->xmlrpc->display_response());
				echo '</pre>';
			}
		}
	}
	?>

.. note:: In the above code we are using a "url helper". You can find more
	information in the :doc:`Helpers Functions <../general/helpers>` page.

The Server
----------

Using a text editor, create a controller called xmlrpc_server.php. In
it, place this code and save it to your applications/controllers/
folder::

	<?php

	class Xmlrpc_server extends CI_Controller {

		function index()
		{
			$this->load->library('xmlrpc');
			$this->load->library('xmlrpcs');

			$config['functions']['Greetings'] = array('function' => 'Xmlrpc_server.process');

			$this->xmlrpcs->initialize($config);
			$this->xmlrpcs->serve();
		}


		function process($request)
		{
			$parameters = $request->output_parameters();

			$response = array(
								array(
										'you_said'  => $parameters['0'],
										'i_respond' => 'Not bad at all.'),
								'struct');

			return $this->xmlrpc->send_response($response);
		}
	}
	?>


Try it!
-------

Now visit the your site using a URL similar to this::

	example.com/index.php/xmlrpc_client/

You should now see the message you sent to the server, and its response
back to you.

The client you created sends a message ("How's is going?") to the
server, along with a request for the "Greetings" method. The Server
receives the request and maps it to the "process" function, where a
response is sent back.

Using Associative Arrays In a Request Parameter
===============================================

If you wish to use an associative array in your method parameters you
will need to use a struct datatype::

	$request = array(
	                 array(
	                       // Param 0
	                       array(
	                             'name'=>'John'
	                            	),
	                             'struct'
	                       ),
	                       array(
	                             // Param 1
	                             array(
	                                  	'size'=>'large',
	                                   'shape'=>'round'
	                                  	),
	                             'struct'
	                       )
	                 );
	$this->xmlrpc->request($request);

You can retrieve the associative array when processing the request in
the Server.

::

	$parameters = $request->output_parameters();
	$name = $parameters['0']['name'];
	$size = $parameters['1']['size'];
	$size = $parameters['1']['shape'];

**************************
XML-RPC Function Reference
**************************

$this->xmlrpc->server()
=======================

Sets the URL and port number of the server to which a request is to be
sent::

	$this->xmlrpc->server('http://www.sometimes.com/pings.php', 80);

$this->xmlrpc->timeout()
========================

Set a time out period (in seconds) after which the request will be
canceled::

	$this->xmlrpc->timeout(6);

$this->xmlrpc->method()
=======================

Sets the method that will be requested from the XML-RPC server::

	$this->xmlrpc->method('method');

Where method is the name of the method.

$this->xmlrpc->request()
========================

Takes an array of data and builds request to be sent to XML-RPC server::

	$request = array(array('My Photoblog', 'string'), 'http://www.yoursite.com/photoblog/');
	$this->xmlrpc->request($request);

$this->xmlrpc->send_request()
==============================

The request sending function. Returns boolean TRUE or FALSE based on
success for failure, enabling it to be used conditionally.

$this->xmlrpc->set_debug(TRUE);
================================

Enables debugging, which will display a variety of information and error
data helpful during development.

$this->xmlrpc->display_error()
===============================

Returns an error message as a string if your request failed for some
reason.

::

	echo $this->xmlrpc->display_error();

$this->xmlrpc->display_response()
==================================

Returns the response from the remote server once request is received.
The response will typically be an associative array.

::

	$this->xmlrpc->display_response();

$this->xmlrpc->send_error_message()
=====================================

This function lets you send an error message from your server to the
client. First parameter is the error number while the second parameter
is the error message.

::

	return $this->xmlrpc->send_error_message('123', 'Requested data not available');

$this->xmlrpc->send_response()
===============================

Lets you send the response from your server to the client. An array of
valid data values must be sent with this method.

::

	$response = array(
	                 array(
	                        'flerror' => array(FALSE, 'boolean'),
	                        'message' => "Thanks for the ping!"
	                     )
	                 'struct');
	return $this->xmlrpc->send_response($response);

Data Types
==========

According to the `XML-RPC spec <http://www.xmlrpc.com/spec>`_ there are
seven types of values that you can send via XML-RPC:

-  *int* or *i4*
-  *boolean*
-  *string*
-  *double*
-  *dateTime.iso8601*
-  *base64*
-  *struct* (contains array of values)
-  *array* (contains array of values)

