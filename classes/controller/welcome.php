<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Welcome extends Controller_Staticpage {

	public function action_index()
	{
		parent::action_index('welcome');
	}
}
