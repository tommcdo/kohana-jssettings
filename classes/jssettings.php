<?php defined('SYSPATH') or die('No direct script access.');

class JSSettings {

	protected $_routes = array();
	protected $_config = array();
	protected $_properties = array();

	public static function instance()
	{
		static $instance;

		! $instance AND $instance = new JSSettings();

		return $instance;
	}

	final private function __construct() {}

	public function properties($class, array $properties)
	{
		if ( ! isset($this->_properties[$class]))
			$this->_properties[$class] = array();

		$this->_properties[$class] = $properties + $this->_properties[$class];

		return $this;
	}

	public function configs($group, array $configs)
	{
		if ( ! isset($this->_configs[$group]))
			$this->_configs[$group] = array();

		$this->_configs[$group] = $configs + $this->_configs[$group];

		return $this;
	}

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
