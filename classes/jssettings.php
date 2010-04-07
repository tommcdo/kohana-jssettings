<?php defined('SYSPATH') or die('No direct script access.');

/**
 * JSSettings Library
 *
 * @package    JSSettings
 * @author     Lorenzo Pisani
 * @copyright  (c) 2009-2010 Synapse Studios
 */
class JSSettings {

	/**
	 * Routes to export for reverse routing in JS
	 *
	 * @var array $_routes
	 */
	protected $_routes = array();

	/**
	 * config items to export
	 *
	 * @var array $_config
	 */
	protected $_config = array();

	/**
	 * class properties to export
	 *
	 * @var array $_properties
	 */
	protected $_properties = array();

	/**
	 * Returns the JSSettings instance
	 *
	 * @return self
	 */
	public static function instance()
	{
		static $instance;

		! $instance AND $instance = new JSSettings();

		return $instance;
	}

	/**
	 * This class is a singleton!
	 */
	final private function __construct() {}

	/**
	 * Exports an array of class properties to JSSettings.
	 * 
	 * @param string $class      - the class the properties belong to
	 * @param array  $properties - key => value pairs to export
	 * @return self
	 */
	public function properties($class, array $properties)
	{
		if ( ! isset($this->_properties[$class]))
			$this->_properties[$class] = array();

		$this->_properties[$class] = $properties + $this->_properties[$class];

		return $this;
	}

	/**
	 * Exports an array of config items to JSSettings.
	 * 
	 * @param string $group   - the group the configs belong to
	 * @param array  $configs - key => value pairs to export
	 * @return self
	 */
	public function configs($group, array $configs)
	{
		if ( ! isset($this->_configs[$group]))
			$this->_configs[$group] = array();

		$this->_configs[$group] = $configs + $this->_configs[$group];

		return $this;
	}

	/**
	 * Loops through the routes and stored them in _routes
	 *
	 * @return self
	 */
	protected function read_routes()
	{
		foreach (Route::all() as $name => $route)
		{
			$this->_routes[$name] = array
			(
				'uri'      => $route->get_uri(),
				'defaults' => $route->get_defaults(),
			);
		}

		return $this;
	}

	/**
	 * Returns the view of the JS file to include. Writes the cache if needed.
	 *
	 * @return object
	 */
	public function script_content()
	{
		$config = Kohana::config('jssettings');

		$this->read_routes();

		$view = View::factory('jssettings/jssettings.js')
			->set('routes', $this->_routes)
			->set('config', $this->_config)
			->set('properties', $this->_properties);

		if ($config->cache === TRUE)
		{
			// Cache this file for future requests
			file_put_contents($config->cache_file, $view->render());
			Kohana::$log->add(Kohana::INFO, 'Cached JSSettings to '.$config->cache_file);
		}

		return $view;
	}

	/**
	 * Returns the output of HTML::script() for either the url that generates
	 * the JS file or for the cached copy
	 *
	 * @return string
	 */
	public function script()
	{
		$config = Kohana::config('jssettings');

		if ($config->cache === TRUE AND file_exists($config->cache_file))
		{
			$creation = filemtime($config->cache_file);
			$expiration = $creation + ($config->cache_lifetime);
			if (time() > $expiration)
			{
				// Cache Expired (force the application to create a new one)
				unlink($config->cache_file);
				Kohana::$log->add(Kohana::INFO, 'Deleted Cached JSSettings '.$config->cache_file);
			}
			else
			{
				Kohana::$log->add(Kohana::INFO, 'Using Cached JSSettings '.$config->cache_uri);
				// Include the cached js file
				return HTML::script($config->cache_uri);
			}
		}
		return HTML::script(Route::get('jssettings')->uri());
	}
}
