<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Types extends Admincontroller {

	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);
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
		if (count($_POST) && isset($_POST['name']) && isset($_POST['description']))
		{
			$post = new Validation($_POST);
			$post->filter('trim');
			$post->rule('Valid::not_empty',                  'name');
			$post->rule('Content_Type::type_name_available', 'name');
			$post_values = $post->as_array();

			if ($post->validate())
			{
				$type_id = Content_Type::new_type($post_values['name'], $post_values['description']);
				$this->add_message('Content type "'.$post_values['name'].'" added');
			}
			else
			{
				// Form errors detected!

				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());
				$this->set_formdata($post_values);
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
				$post = new Validation($_POST);
				$post->filter('trim');
				$post->rule('Valid::not_empty', 'name');
				$post_values = $post->as_array();

				if ($post->validate())
				{
					$current_type_data = $content_type->get_type_data();

					if ($post_values['name'] != $current_type_data['name'] && ! Content_Type::type_name_available($post_values['name']))
					{
						$post->add_error('name', 'Content_Type::type_name_available');
					}
				}

				if ($post->validate())
				{
					$content_type->update_type_data($post_values['name'], $post_values['description']);
					$this->add_message('Content type "'.$post_values['name'].'" updated');
					$this->set_formdata($content_type->get_type_data());
				}
				else
				{
					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());
					$this->set_formdata($post_values);
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
