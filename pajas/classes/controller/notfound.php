<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Notfound extends Xsltcontroller {

	public function __construct()
	{
		parent::__construct();
	}

	public function action_index()
	{
		$this->xslt_stylesheet = '404';
	}

}
