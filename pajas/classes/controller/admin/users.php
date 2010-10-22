<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Users extends Admincontroller {

	public function __construct()
	{
		parent::__construct();
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/users';
		xml::to_XML(array('admin_page' => 'Users'), $this->xml_meta);
	}

	public function action_index()
	{
		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));

		$fields = array();
		foreach (User::get_data_fields() as $field_id => $field_name)
		{
			$fields['field id="'.$field_id.'"'] = $field_name;
		}

		xml::to_XML($fields, $this->xml_content_users);
		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));
		xml::to_XML(User::get_users(FALSE, FALSE, FALSE, 'user_id'), $this->xml_content_users, 'user');
	}

	public function action_add_user()
	{
		$fields = array();
		foreach (User::get_data_fields() as $field_id => $field_name)
		{
			$fields['field id="'.$field_id.'"'] = $field_name;
		}

		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));
		xml::to_XML($fields, $this->xml_content_users);

		if (count($_POST))
		{
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->filter('username', 'strtolower');
			$post->rule('username', 'not_empty');
			$post->rule('username', 'User::username_available');
			$post->rule('password', 'not_empty');
			foreach ($_POST as $field => $content)
			{
				if ($field != 'username' && $field != 'password')
				{
					$post->label($field, $field);
				}
			}

			if ($post->check())
			{
				$fields = array();
				foreach ($post as $field => $value)
				{
					if (substr($field, 0, 6) == 'field_')
					{
						$fields[substr($field, 6)] = $value;
					}
				}

				User::new_user(
					$post['username'],
					$post['password'],
					$fields
				);
				$this->add_message('User '.$post['username'].' added');
			}
			else
			{
				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());

				$this->set_formdata(array_intersect_key($post->as_array(), $_POST));
			}
		}
	}

	public function action_edit_user($user_id)
	{
		$this->xml_content_user  = $this->xml_content->appendChild($this->dom->createElement('user'));
		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));

		$user = new User($user_id, FALSE, FALSE, 'default', FALSE);
		if ($user->logged_in())
		{
			xml::to_XML(array('user_id' => $user_id, 'username' => $user->get_username()), $this->xml_content_user);
			xml::to_XML($user->get_user_data(), $this->xml_content_user);
		}
		else
		{
			$this->redirect();
		}

		$fields = array();
		foreach (User::get_data_fields() as $field_id => $field_name)
		{
			$fields['field id="'.$field_id.'"'] = $field_name;
		}

		xml::to_XML($fields, $this->xml_content_users);

		if (count($_POST))
		{
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->filter('username', 'strtolower');
			$post->rule('username', 'not_empty');
			foreach ($_POST as $field => $content)
			{
				if ($field != 'username')
				{
					$post->label($field, $field);
				}
			}

			if ($post->check())
			{
				if ($post['username'] != $user->get_username() && !User::username_available($post['username']))
				{
					$post->error('username', 'username_available');
				}
			}

			if (!count($post->errors()))
			{
				$fields = array(
					'username' => $post['username']
				);

				if (isset($post['password']) && $post['password'] != '')
				{
					$fields['password'] = $post['password'];
				}

				foreach ($post as $field => $value)
				{
					if (substr($field, 0, 6) == 'field_')
					{
						$fields[substr($field, 6)] = $value;
					}
				}

				$user->set_user_data($fields);

				$this->add_message('User data saved');
			}
			else
			{
				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());
			}

			$this->set_formdata(array_intersect_key($post->as_array(), $_POST));
		}
		else
		{
			$formdata = array(
				'username' => $user->get_username(),
			);
			foreach ($user->get_user_data() as $field => $data)
			{
				$formdata['field_'.$field] = $data;
			}
			$this->set_formdata($formdata);
		}
	}

	public function action_rm_user($user_id)
	{
		$user = new User($user_id, FALSE, FALSE, 'default', FALSE);

		$user->rm_user();

		$this->redirect();
	}

}
