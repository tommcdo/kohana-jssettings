<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_JSSettings extends Controller {

	/**
	 * Media action for the JSSettings js file
	 */
	public function action_script()
	{
		header('Content-type: application/x-javascript');
		echo JSSettings::instance()->script_content();
		die();
	}

}
