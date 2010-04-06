<?php defined('SYSPATH') or die('No direct script access.');

class Route extends Kohana_Route {

	public function get_uri()
	{
		return $this->_uri;
	}

	public function get_defaults()
	{
		return $this->_defaults;
	}

}
