<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Fields extends Admincontroller {

	public function __construct()
	{
		parent::__construct();
		$this->xslt_stylesheet = 'admin/users';
		xml::to_XML(array('admin_page' => 'Fields'), $this->xml_meta);
	}

	public function action_index()
	{
		$fields = array();
		foreach (User::get_data_fields() as $field_id => $field_name)
		{
			$fields['field id="'.$field_id.'"'] = $field_name;
		}

		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));
		xml::to_XML($fields, $this->xml_content_users);
	}

	public function action_add_field()
	{
		if (count($_POST))
		{
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->rule('field_name', 'not_empty');
			$post->rule('field_name', 'User::field_name_available');

			if ($post->check())
			{
				User::new_field($post['field_name']);
				$this->add_message('Field '.$post['field_name'].' added');
			}
			else
			{
				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());

				$this->set_formdata($post->as_array());
			}
		}
	}

	public function action_edit_field($field_id)
	{
		xml::to_XML(
			array(
				'field' => array(
					'@id' => $field_id,
					'$content' => User::get_data_field_name($field_id)
				)
			),
			$this->xml_content
		);

		if (count($_POST))
		{
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->rule('field_name', 'not_empty');


			if ($post->check())
			{
				if ($post['field_name'] != User::get_data_field_name($field_id) && !User::field_name_available($post['field_name']))
				{
					$post->error('field_name', 'field_name_available');
				}
			}

			if (!count($post->errors()))
			{
				User::update_field($field_id, $post['field_name']);
				$this->add_message('Field '.$post['field_name'].' updated');
				$this->set_formdata(array('field_name' => $post['field_name']));
			}
			else
			{
				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());

				$this->set_formdata(array_intersect_key($post->as_array(), $_POST));
			}
		}
		else
		{
			$this->set_formdata(array('field_name' => User::get_data_field_name($field_id)));
		}
	}

	public function action_rm_field($field_id)
	{
		User::rm_field($field_id);
		$this->redirect();
	}

}
