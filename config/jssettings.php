<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	// Whether or not the JS file is written to disk
	'cache'          => TRUE,
	// Where the JS file is written to
	'cache_file'     => DOCROOT.'media/js/jssettings.js',
	// The URI used in HTML::script() to load the cached JS file
	'cache_uri'      => 'media/js/jssettings.js',
	// Lifetime (in seconds) for the JS file before a new one is generated
	'cache_lifetime' => '600',
);
