function create_menu(basepath)
{
	var base = (basepath == 'null') ? '' : basepath;

	document.write(
		'<table cellpadding="0" cellspaceing="0" border="0" style="width:98%"><tr>' +
		'<td class="td" valign="top">' +

		'<p><a href="'+base+'index.html">User Guide Home</a></p>' +	

		'<h3>Basic Info</h3>' +
		'<ul>' +
			'<li><a href="'+base+'general/requirements.html">Server Requirements</a></li>' +
			'<li><a href="'+base+'license.html">License Agreement</a></li>' +
			'<li><a href="'+base+'general/changelog.html">Change Log</a></li>' +
			'<li><a href="'+base+'general/credits.html">Credits</a></li>' +
		'</ul>' +	
		
		'<h3>Installation</h3>' +
		'<ul>' +
			'<li><a href="'+base+'installation/downloads.html">Downloading Code Igniter</a></li>' +
			'<li><a href="'+base+'installation/index.html">Installation Instructions</a></li>' +
			'<li><a href="'+base+'installation/upgrading.html">Upgrading from a Previous Version</a></li>' +
			'<li><a href="'+base+'installation/troubleshooting.html">Troubleshooting</a></li>' +
		'</ul>' +
		
		'<h3>Introduction</h3>' +
		'<ul>' +
			'<li><a href="'+base+'overview/at_a_glance.html">Code Igniter at a Glance</a></li>' +
			'<li><a href="'+base+'overview/features.html">Supported Features</a></li>' +
			'<li><a href="'+base+'overview/appflow.html">Application Flow Chart</a></li>' +
			'<li><a href="'+base+'overview/mvc.html">Model-View-Controller</a></li>' +
			'<li><a href="'+base+'overview/goals.html">Architectural Goals</a></li>' +
		'</ul>' +	
				
		'</td><td class="td_sep" valign="top">' +

		'<h3>General Topics</h3>' +
		'<ul>' +
			'<li><a href="'+base+'general/index.html">Getting Started</a></li>' +
			'<li><a href="'+base+'general/urls.html">Code Igniter URLs</a></li>' +
			'<li><a href="'+base+'general/controllers.html">Controllers</a></li>' +
			'<li><a href="'+base+'general/views.html">Views</a></li>' +
			'<li><a href="'+base+'general/models.html">Models</a></li>' +
			'<li><a href="'+base+'general/helpers.html">Helpers</a></li>' +
			'<li><a href="'+base+'general/plugins.html">Plugins</a></li>' +
			'<li><a href="'+base+'general/libraries.html">Using Code Igniter Libraries</a></li>' +
			'<li><a href="'+base+'general/creating_libraries.html">Creating Your Own Libraries</a></li>' +
			'<li><a href="'+base+'general/core_classes.html">Creating Core Classes</a></li>' +
			'<li><a href="'+base+'general/hooks.html">Hooks - Extending the Core</a></li>' +
			'<li><a href="'+base+'general/autoloader.html">Auto-loading Resources</a></li>' +
			'<li><a href="'+base+'general/scaffolding.html">Scaffolding</a></li>' +
			'<li><a href="'+base+'general/routing.html">URI Routing</a></li>' +
			'<li><a href="'+base+'general/errors.html">Error Handling</a></li>' +
			'<li><a href="'+base+'general/caching.html">Caching</a></li>' +
			'<li><a href="'+base+'general/profiling.html">Profiling Your Application</a></li>' +
			'<li><a href="'+base+'general/multiple_apps.html">Running Multiple Applications</a></li>' +
			'<li><a href="'+base+'general/alternative_php.html">Alternative PHP Syntax</a></li>' +
			'<li><a href="'+base+'general/security.html">Security</a></li>' +
		'</ul>' +
		
		'</td><td class="td_sep" valign="top">' +

				
		'<h3>Class Reference</h3>' +
		'<ul>' +
		'<li><a href="'+base+'libraries/benchmark.html">Benchmarking Class</a></li>' +
		'<li><a href="'+base+'libraries/calendar.html">Calendaring Class</a></li>' +
		'<li><a href="'+base+'libraries/config.html">Config Class</a></li>' +
		'<li><a href="'+base+'database/index.html">Database Class</a></li>' +
		'<li><a href="'+base+'libraries/email.html">Email Class</a></li>' +
		'<li><a href="'+base+'libraries/encryption.html">Encryption Class</a></li>' +
		'<li><a href="'+base+'libraries/file_uploading.html">File Uploading Class</a></li>' +
		'<li><a href="'+base+'libraries/image_lib.html">Image Manipulation Class</a></li>' +		
		'<li><a href="'+base+'libraries/input.html">Input and Security Class</a></li>' +
		'<li><a href="'+base+'libraries/loader.html">Loader Class</a></li>' +
		'<li><a href="'+base+'libraries/language.html">Language Class</a></li>' +
		'<li><a href="'+base+'libraries/output.html">Output Class</a></li>' +
		'<li><a href="'+base+'libraries/pagination.html">Pagination Class</a></li>' +
		'<li><a href="'+base+'libraries/sessions.html">Session Class</a></li>' +
		'<li><a href="'+base+'libraries/trackback.html">Trackback Class</a></li>' +
		'<li><a href="'+base+'libraries/parser.html">Template Parser Class</a></li>' +
		'<li><a href="'+base+'libraries/unit_testing.html">Unit Testing Class</a></li>' +
		'<li><a href="'+base+'libraries/uri.html">URI Class</a></li>' +
		'<li><a href="'+base+'libraries/validation.html">Validation Class</a></li>' +
		'<li><a href="'+base+'libraries/xmlrpc.html">XML-RPC Class</a></li>' +
		'<li><a href="'+base+'libraries/zip.html">Zip Encoding Class</a></li>' +
		'</ul>' +

		'</td><td class="td_sep" valign="top">' +

		'<h3>Helper Reference</h3>' +
		'<ul>' +
		'<li><a href="'+base+'helpers/array_helper.html">Array Helper</a></li>' +
		'<li><a href="'+base+'helpers/cookie_helper.html">Cookie Helper</a></li>' +
		'<li><a href="'+base+'helpers/date_helper.html">Date Helper</a></li>' +
		'<li><a href="'+base+'helpers/directory_helper.html">Directory Helper</a></li>' +
		'<li><a href="'+base+'helpers/download_helper.html">Download Helper</a></li>' +
		'<li><a href="'+base+'helpers/file_helper.html">File Helper</a></li>' +
		'<li><a href="'+base+'helpers/form_helper.html">Form Helper</a></li>' +
		'<li><a href="'+base+'helpers/html_helper.html">HTML Helper</a></li>' +
		'<li><a href="'+base+'helpers/inflector_helper.html">Inflector Helper</a></li>' +
		'<li><a href="'+base+'helpers/security_helper.html">Security Helper</a></li>' +
		'<li><a href="'+base+'helpers/string_helper.html">String Helper</a></li>' +
		'<li><a href="'+base+'helpers/text_helper.html">Text Helper</a></li>' +
		'<li><a href="'+base+'helpers/typography_helper.html">Typography Helper</a></li>' +
		'<li><a href="'+base+'helpers/url_helper.html">URL Helper</a></li>' +
		'<li><a href="'+base+'helpers/user_agent_helper.html">User Agent Helper</a></li>' +
		'<li><a href="'+base+'helpers/xml_helper.html">XML Helper</a></li>' +
		'</ul>' +	


		'<h3>Additional Resources</h3>' +
		'<ul>' +
		'<li><a href="'+base+'general/quick_reference.html">Quick Reference Chart</a></li>' +
		'<li><a href="http://www.codeigniter.com/forums/">Community Forums</a></li>' +
		'<li><a href="http://www.codeigniter.com/wiki/">Community Wiki</a></li>' +
		'</ul>' +	
		
		'</td></tr></table>');
}