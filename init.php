<?php defined('SYSPATH') or die('No direct script access.');

Route::set('jssettings', 'media/js/jssettings.js')
	->defaults(array
	(
		'controller' => 'jssettings',
		'action'     => 'script',
	));

/**
 * The following properties are required by the JSSettings object
 */
JSSettings::instance()
	->properties('kohana', array
	(
		'base_url'   => Kohana::$base_url,
		'index_file' => Kohana::$index_file,
	))
	->properties('request', array
	(
		// default to http
		'protocol'   => 'http',
	));
