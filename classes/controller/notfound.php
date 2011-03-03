<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Notfound extends Xsltcontroller {

	public function action_index()
	{
		$this->xslt_stylesheet = '404';
	}

}
