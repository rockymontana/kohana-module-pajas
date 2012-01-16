<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Logout extends Admincontroller {

	public function action_index()
	{
		$user = User::instance();
		$user->logout();
		$this->redirect('admin/login');
	}

}
