<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Types extends Admincontroller {

	public function __construct()
	{
		parent::__construct();
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/types';
		xml::to_XML(array('admin_page' => 'Content types'), $this->xml_meta);
	}

	public function action_index()
	{
		$this->xml_content_types = $this->xml_content->appendChild($this->dom->createElement('types'));
		xml::to_XML(Content_Type::get_types(), $this->xml_content_types, 'type', 'id');
	}

	public function action_add_type()
	{
		if (count($_POST))
		{
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->rule('name', 'not_empty');
			$post->rule('name', 'Content_Type::type_name_available');
			$post->label('description', 'description');

			if ($post->check())
			{
				$type_id = Content_Type::new_type($post['name'], $post['description']);
				$this->add_message('Content type "'.$post['name'].'" added');
			}
			else
			{
				// Form errors detected!

				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());
				$this->set_formdata($post);
			}
		}
	}

	public function action_edit_type($id)
	{
		$content_type = new Content_Type($id);
		if ($content_type->get_type_id())
		{
			$this->xml_content_type_data = $this->xml_content->appendChild($this->dom->createElement('type_data'));

			if (count($_POST))
			{
				$post = new Validate($_POST);
				$post->filter(TRUE, 'trim');
				$post->rule('name', 'not_empty');
				$post->label('description', 'description');

				$valid = TRUE;
				if ($post->check())
				{
					$current_type_data = $content_type->get_type_data();

					if ($post['name'] != $current_type_data['name'] && !Content_Type::type_name_available($post['name']))
					{
						$post->error('name', 'Content_Type::type_name_available');
						$valid = FALSE;
					}

					if ($valid)
					{
						$content_type->update_type_data($post['name'], $post['description']);
						$this->add_message('Content type "'.$post['name'].'" updated');
						$this->set_formdata($content_type->get_type_data());
					}
				}
				else $valid = FALSE;

				if ($valid == FALSE)
				{
					// Form errors detected!

					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());
					$this->set_formdata($post);
				}

			}
			else
			{
				$this->set_formdata($content_type->get_type_data());
			}

			xml::to_XML($content_type->get_type_data(), $this->xml_content_type_data, NULL, 'id');
		}
		else $this->redirect();
	}

	public function action_rm_type($id)
	{
		$content_type = new Content_Type($id);
		$content_type->rm_type();

		$this->redirect();
	}

}
