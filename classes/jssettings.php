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
		$this->read_routes();

		$view = View::factory('jssettings/jssettings.js')
			->set('routes', $this->_routes)
			->set('config', $this->_config)
			->set('properties', $this->_properties);

		return $view;
	}

	public function script()
	{
		return HTML::script(Route::get('jssettings')->uri());
	}
}
